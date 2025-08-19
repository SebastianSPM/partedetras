<?php
// Archivo con funciones de seguridad centralizadas

// Configurar headers de seguridad
function setSecurityHeaders() {
    // Prevenir XSS
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    
    // Content Security Policy básico
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';");
    
    // Prevenir información de servidor
    header('Server: Apache');
    header_remove('X-Powered-By');
}

// Función para validar token CSRF
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

// Generar token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Función para limpiar datos de entrada (más robusta)
function sanitizeInput($data, $type = 'string') {
    $data = trim($data);
    $data = stripslashes($data);
    
    switch ($type) {
        case 'email':
            return filter_var($data, FILTER_SANITIZE_EMAIL);
        case 'int':
            return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
        case 'float':
            return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        case 'url':
            return filter_var($data, FILTER_SANITIZE_URL);
        default:
            return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

// Función para validar sesión y timeout
function validateSession($timeout = 1800) { // 30 minutos por defecto
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        session_unset();
        session_destroy();
        return false;
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}

// Función para logging de seguridad
function logSecurityEvent($event, $details = '') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $timestamp = date('Y-m-d H:i:s');
    $user_id = $_SESSION['user_id'] ?? 'anonymous';
    
    $log_entry = "[{$timestamp}] IP: {$ip} | User: {$user_id} | Event: {$event} | Details: {$details} | UA: {$user_agent}" . PHP_EOL;
    
    // En producción, usar un archivo de log más seguro
    error_log($log_entry, 3, '../logs/security.log');
}

// Función para rate limiting básico
function checkRateLimit($action, $max_attempts = 5, $time_window = 300) { // 5 intentos en 5 minutos
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $key = $action . '_' . md5($ip);
    
    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = [];
    }
    
    $now = time();
    
    // Limpiar intentos antiguos
    $_SESSION['rate_limit'][$key] = array_filter($_SESSION['rate_limit'][$key], function($timestamp) use ($now, $time_window) {
        return ($now - $timestamp) < $time_window;
    });
    
    // Verificar si se excedió el límite
    if (count($_SESSION['rate_limit'][$key]) >= $max_attempts) {
        logSecurityEvent('RATE_LIMIT_EXCEEDED', "Action: {$action}");
        return false;
    }
    
    // Agregar intento actual
    $_SESSION['rate_limit'][$key][] = $now;
    return true;
}

// Función para validar contraseña segura
function validatePasswordStrength($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "La contraseña debe tener al menos 8 caracteres";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "La contraseña debe contener al menos una mayúscula";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "La contraseña debe contener al menos una minúscula";
    }
    
    if (!preg_match('/\d/', $password)) {
        $errors[] = "La contraseña debe contener al menos un número";
    }
    
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "La contraseña debe contener al menos un caracter especial";
    }
    
    return $errors;
}

// Configurar sesiones seguras
function configureSecureSessions() {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Cambiar a 1 en HTTPS
    ini_set('session.entropy_length', 32);
    ini_set('session.entropy_file', '/dev/urandom');
    ini_set('session.hash_function', 'sha256');
    ini_set('session.hash_bits_per_character', 5);
    
    session_name('ABC_TECH_SESSION');
}

// Función para escapar salida HTML
function escapeHtml($data) {
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// Verificar si la IP está en lista negra (básico)
function checkBlacklist() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // Lista básica de IPs bloqueadas (en producción usar base de datos)
    $blacklisted_ips = [
        // Agregar IPs problemáticas aquí
    ];
    
    if (in_array($ip, $blacklisted_ips)) {
        logSecurityEvent('BLACKLISTED_IP_ACCESS', "IP: {$ip}");
        http_response_code(403);
        die('Acceso denegado');
    }
}
?>