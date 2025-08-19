<?php
session_start();

// Tiempo de sesión (30 min)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    // Redirección simple sin parámetros dinámicos
    header('Location: index.html');
    exit;
}
$_SESSION['last_activity'] = time();

// Incluye db.php en el mismo directorio
include 'db.php';

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}

// Función para limpiar datos
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Validación de datos del proveedor
function validateSupplierData($company_name, $contact, $phone) {
    $errors = [];
    
    if (empty($company_name)) $errors[] = "El nombre de la empresa es requerido";
    elseif (strlen($company_name) > 100) $errors[] = "El nombre de la empresa no puede exceder 100 caracteres";
    
    if (empty($contact)) $errors[] = "El contacto es requerido";
    elseif (strlen($contact) > 100) $errors[] = "El contacto no puede exceder 100 caracteres";
    
    if (!empty($phone) && !preg_match('/^[0-9+\-\s()]+$/', $phone))
        $errors[] = "El teléfono contiene caracteres no válidos";
    
    return $errors;
}

// CREAR proveedor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create'])) {
    $company_name = cleanInput($_POST['company_name'] ?? '');
    $contact = cleanInput($_POST['contact'] ?? '');
    $phone = cleanInput($_POST['phone'] ?? '');
    $products_provided = cleanInput($_POST['products_provided'] ?? '');
    
    $validation_errors = validateSupplierData($company_name, $contact, $phone);
    if (!empty($validation_errors)) {
        echo json_encode(['success' => false, 'message' => implode(". ", $validation_errors)]);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM suppliers WHERE company_name = ?");
        $stmt->execute([$company_name]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'La empresa ya está registrada']);
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO suppliers (company_name, contact, phone, products_provided, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$company_name, $contact, $phone, $products_provided]);
        
        echo json_encode(['success' => true, 'message' => 'Proveedor creado exitosamente']);
        exit;
        
    } catch (PDOException $e) {
        error_log("Error al crear proveedor: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error interno. Intente nuevamente.']);
        exit;
    }
}

// ELIMINAR proveedor
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE supplier_id = ?");
        $stmt->execute([$_GET['delete']]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'No se puede eliminar el proveedor porque tiene productos asociados']);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        
        echo json_encode(['success' => true, 'message' => 'Proveedor eliminado exitosamente']);
        exit;
        
    } catch (PDOException $e) {
        error_log("Error al eliminar proveedor: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al eliminar proveedor']);
        exit;
    }
}

// LISTAR proveedores
$search = cleanInput($_GET['search'] ?? '');
try {
    if (!empty($search)) {
        $stmt = $pdo->prepare("SELECT id, company_name, contact, phone FROM suppliers WHERE company_name LIKE ? ORDER BY company_name");
        $stmt->execute(["%$search%"]);
    } else {
        $stmt = $pdo->prepare("SELECT id, company_name, contact, phone FROM suppliers ORDER BY company_name");
        $stmt->execute();
    }
    
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $suppliers]);
    
} catch (PDOException $e) {
    error_log("Error al obtener proveedores: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al obtener datos']);
}
?>
