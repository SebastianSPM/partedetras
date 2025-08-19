<?php
session_start();

// Verificar timeout de sesión
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: /abc_tech_platform/frontend/index.html?error=' . urlencode("Sesión expirada"));
    exit;
}
$_SESSION['last_activity'] = time();

include '../config/db.php';

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    header('Location: /abc_tech_platform/frontend/index.html');
    exit;
}

// Función para limpiar datos de entrada
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Función para validar datos del servicio
function validateServiceData($name, $technician, $cost, $estimated_time) {
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "El nombre del servicio es requerido";
    } elseif (strlen($name) > 100) {
        $errors[] = "El nombre no puede exceder 100 caracteres";
    }
    
    if (empty($technician)) {
        $errors[] = "El técnico es requerido";
    } elseif (strlen($technician) > 100) {
        $errors[] = "El nombre del técnico no puede exceder 100 caracteres";
    }
    
    if (empty($cost) || !is_numeric($cost) || $cost < 0) {
        $errors[] = "El costo debe ser un número válido mayor o igual a 0";
    }
    
    if (empty($estimated_time)) {
        $errors[] = "El tiempo estimado es requerido";
    } elseif (strlen($estimated_time) > 50) {
        $errors[] = "El tiempo estimado no puede exceder 50 caracteres";
    }
    
    return $errors;
}

// Procesar creación de servicio
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create'])) {
    $name = cleanInput($_POST['name'] ?? '');
    $technician = cleanInput($_POST['technician'] ?? '');
    $cost = cleanInput($_POST['cost'] ?? '');
    $estimated_time = cleanInput($_POST['estimated_time'] ?? '');
    
    // Validar datos
    $validation_errors = validateServiceData($name, $technician, $cost, $estimated_time);
    if (!empty($validation_errors)) {
        $error_message = implode(". ", $validation_errors);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $error_message]);
        exit;
    }
    
    try {
        // Insertar servicio
        $stmt = $pdo->prepare("INSERT INTO services (name, technician, cost, estimated_time, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$name, $technician, $cost, $estimated_time]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Servicio creado exitosamente']);
        exit;
        
    } catch (PDOException $e) {
        error_log("Error al crear servicio: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error interno. Intente nuevamente.']);
        exit;
    }
}

// Procesar eliminación de servicio
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Servicio eliminado exitosamente']);
        exit;
        
    } catch (PDOException $e) {
        error_log("Error al eliminar servicio: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error al eliminar servicio']);
        exit;
    }
}

// Obtener servicios (solo si no es POST o DELETE)
try {
    $stmt = $pdo->prepare("SELECT id, name, technician, cost, estimated_time FROM services ORDER BY name");
    $stmt->execute();
    $services = $stmt->fetchAll();
    
    // Devolver datos como JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $services]);
    
} catch (PDOException $e) {
    error_log("Error al obtener servicios: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al obtener datos']);
}
?>