<?php
session_start();

// Timeout de sesión
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    echo json_encode(["success" => false, "message" => "Sesión expirada"]);
    exit;
}
$_SESSION['last_activity'] = time();

include __DIR__ . '/../config/db.php';

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "No autenticado"]);
    exit;
}

// Limpiar entrada
function cleanInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Validar cliente
function validateClientData($name, $email, $phone) {
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "El nombre es requerido";
    } elseif (strlen($name) > 100) {
        $errors[] = "El nombre no puede exceder 100 caracteres";
    }
    
    if (empty($email)) {
        $errors[] = "El email es requerido";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Formato de email inválido";
    }
    
    if (!empty($phone) && strlen($phone) > 20) {
        $errors[] = "El teléfono no puede exceder 20 caracteres";
    }
    
    return $errors;
}

// Crear cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $name = cleanInput($_POST['name'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $phone = cleanInput($_POST['phone'] ?? '');
    $address = cleanInput($_POST['address'] ?? '');
    $purchase_history = cleanInput($_POST['purchase_history'] ?? '');
    
    $validation_errors = validateClientData($name, $email, $phone);
    if (!empty($validation_errors)) {
        echo json_encode(["success" => false, "message" => implode(". ", $validation_errors)]);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM clients WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(["success" => false, "message" => "El email ya está registrado"]);
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO clients (name, email, phone, address, purchase_history, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$name, $email, $phone, $address, $purchase_history]);
        
        echo json_encode(["success" => true, "message" => "Cliente creado exitosamente"]);
        exit;
        
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error interno"]);
        exit;
    }
}

// Eliminar cliente
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        
        echo json_encode(["success" => true, "message" => "Cliente eliminado exitosamente"]);
        exit;
        
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error al eliminar"]);
        exit;
    }
}

// Buscar clientes
$search = cleanInput($_GET['search'] ?? '');
try {
    if (!empty($search)) {
        $stmt = $pdo->prepare("SELECT id, name, email, phone FROM clients WHERE name LIKE ? OR email LIKE ? ORDER BY name");
        $stmt->execute(["%$search%", "%$search%"]);
    } else {
        $stmt = $pdo->prepare("SELECT id, name, email, phone FROM clients ORDER BY name");
        $stmt->execute();
    }
    
    $clients = $stmt->fetchAll();
    echo json_encode(["success" => true, "data" => $clients]);
    
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error al obtener datos"]);
}
?>
