<?php
session_start();

// Configuración de seguridad de sesiones
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 si usas HTTPS

include '../config/db.php';

// Función para limpiar datos de entrada
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Función para validar entrada
function validateInput($username, $password) {
    if (empty($username) || empty($password)) {
        return "Todos los campos son obligatorios";
    }
    
    if (strlen($username) < 3 || strlen($username) > 50) {
        return "El nombre de usuario debe tener entre 3 y 50 caracteres";
    }
    
    if (strlen($password) < 6) {
        return "La contraseña debe tener al menos 6 caracteres";
    }
    
    return null;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = cleanInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validar entrada
    $validation_error = validateInput($username, $password);
    if ($validation_error) {
        header('Location: /abc_tech_platform/frontend/index.html?error=' . urlencode($validation_error));
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Regenerar ID de sesión por seguridad
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['last_activity'] = time();
            
            header('Location: /abc_tech_platform/frontend/dashboard.html');
            exit;
        } else {
            $error = "Credenciales inválidas";
            header('Location: /abc_tech_platform/frontend/index.html?error=' . urlencode($error));
            exit;
        }
    } catch (PDOException $e) {
        error_log("Error en login: " . $e->getMessage());
        header('Location: /abc_tech_platform/frontend/index.html?error=' . urlencode("Error interno. Intente nuevamente."));
        exit;
    }
}
?>