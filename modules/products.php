<?php
session_start();

// Timeout de sesión (30 min)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    echo json_encode(["success" => false, "message" => "Sesión expirada"]);
    exit;
}
$_SESSION['last_activity'] = time();

require_once __DIR__ . '/../config/db.php';

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "No autorizado"]);
    exit;
}

// Validación de producto
function validarProducto($code, $description, $price, $stock) {
    $errores = [];

    if (empty($code) || strlen($code) > 50) {
        $errores[] = "Código inválido";
    }
    if (empty($description) || strlen($description) > 255) {
        $errores[] = "Descripción inválida";
    }
    if (!is_numeric($price) || $price < 0) {
        $errores[] = "Precio inválido";
    }
    if (!is_numeric($stock) || $stock < 0) {
        $errores[] = "Stock inválido";
    }
    return $errores;
}

// Crear producto
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["create"])) {
    $code = trim($_POST["code"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $stock = trim($_POST["stock"] ?? "");
    $supplier_id = !empty($_POST["supplier_id"]) ? (int)$_POST["supplier_id"] : null;

    $errores = validarProducto($code, $description, $price, $stock);
    if (!empty($errores)) {
        echo json_encode(["success" => false, "message" => implode(". ", $errores)]);
        exit;
    }

    try {
        // Verificar duplicado
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE code = ?");
        $stmt->execute([$code]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(["success" => false, "message" => "El código ya existe"]);
            exit;
        }

        // Verificar proveedor
        if ($supplier_id !== null) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM suppliers WHERE id = ?");
            $stmt->execute([$supplier_id]);
            if ($stmt->fetchColumn() == 0) {
                echo json_encode(["success" => false, "message" => "Proveedor no válido"]);
                exit;
            }
        }

        // Insertar producto
        $stmt = $pdo->prepare("INSERT INTO products (code, description, price, stock, supplier_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$code, $description, $price, $stock, $supplier_id]);

        echo json_encode(["success" => true, "message" => "Producto creado correctamente"]);
        exit;

    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error interno"]);
        exit;
    }
}

// Eliminar producto (solo por POST para evitar bloqueos)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete"])) {
    try {
        $id = (int)$_POST["delete"];
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(["success" => true, "message" => "Producto eliminado"]);
        exit;
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error al eliminar"]);
        exit;
    }
}

// Listar productos
try {
    $stmt = $pdo->query("
        SELECT p.id, p.code, p.description, p.price, p.stock,
               COALESCE(s.company_name, 'Sin proveedor') AS supplier_name
        FROM products p
        LEFT JOIN suppliers s ON p.supplier_id = s.id
        ORDER BY p.code
    ");
    $productos = $stmt->fetchAll();

    echo json_encode(["success" => true, "data" => $productos]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error al obtener datos"]);
}
?>
