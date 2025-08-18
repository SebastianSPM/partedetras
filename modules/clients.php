<?php
session_start();
include '../config/db.php';

echo "Método: " . $_SERVER['REQUEST_METHOD'] . "<br>"; // Depuración
echo "Datos POST recibidos: " . print_r($_POST, true) . "<br>"; // Mostrar datos enviados

if (!isset($_SESSION['user_id'])) {
    header('Location: /abc_tech_platform/frontend/index.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create'])) {
    try {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $purchase_history = trim($_POST['purchase_history'] ?? '');

        echo "Datos a insertar: Name=$name, Email=$email, Phone=$phone, Address=$address, Purchase=$purchase_history<br>";

        if (empty($name) || empty($email)) {
            throw new Exception("El nombre y el correo son requeridos.");
        }

        // Desactivar transacciones y forzar inserción directa
        $pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
        $pdo->beginTransaction(); // Iniciar transacción manual
        $stmt = $pdo->prepare("INSERT INTO clients (name, email, phone, address, purchase_history) VALUES (?, ?, ?, ?, ?)");
        $result = $stmt->execute([$name, $email, $phone, $address, $purchase_history]);
        if ($result === false) {
            $errorInfo = $stmt->errorInfo();
            throw new PDOException("Inserción falló. Código: " . $errorInfo[0] . ", Mensaje: " . $errorInfo[2]);
        }
        $lastId = $pdo->lastInsertId();
        $pdo->commit(); // Confirmar transacción
        echo "Cliente creado con éxito. ID: $lastId<br>";
    } catch (PDOException $e) {
        echo "Error de base de datos: " . $e->getMessage() . "<br>";
        if (isset($pdo)) {
            $pdo->rollBack(); // Revertir si falla
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "<br>";
    }
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$clients = $pdo->prepare("SELECT * FROM clients WHERE name LIKE ? OR email LIKE ?");
$clients->execute(["%$search%", "%$search%"]);
$clients = $clients->fetchAll();

header('Content-Type: text/html; charset=UTF-8');
echo "<table>";
echo "<tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Acciones</th></tr>";
if (count($clients) == 0) {
    echo "<tr><td colspan='5'>No hay clientes registrados.</td></tr>";
} else {
    foreach ($clients as $client) {
        echo "<tr>";
        echo "<td>{$client['id']}</td>";
        echo "<td>{$client['name']}</td>";
        echo "<td>{$client['email']}</td>";
        echo "<td>{$client['phone']}</td>";
        echo "<td><a href='?delete={$client['id']}' onclick='return confirm(\"¿Seguro?\");'>Eliminar</a></td>";
        echo "</tr>";
    }
}
echo "</table>";