<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /abc_tech_platform/frontend/index.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create'])) {
    $stmt = $pdo->prepare("INSERT INTO suppliers (company_name, contact, phone, products_provided) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['company_name'], $_POST['contact'], $_POST['phone'], $_POST['products_provided']]);
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id=?");
    $stmt->execute([$_GET['delete']]);
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$suppliers = $pdo->prepare("SELECT * FROM suppliers WHERE company_name LIKE ?");
$suppliers->execute(["%$search%"]);
$suppliers = $suppliers->fetchAll();

header('Content-Type: text/html; charset=UTF-8');
echo "<table>";
echo "<tr><th>ID</th><th>Nombre de Empresa</th><th>Contacto</th><th>Teléfono</th><th>Acciones</th></tr>";
foreach ($suppliers as $supplier) {
    echo "<tr>";
    echo "<td>{$supplier['id']}</td>";
    echo "<td>{$supplier['company_name']}</td>";
    echo "<td>{$supplier['contact']}</td>";
    echo "<td>{$supplier['phone']}</td>";
    echo "<td><a href='?delete={$supplier['id']}' onclick='return confirm(\"¿Seguro?\");'>Eliminar</a></td>";
    echo "</tr>";
}
echo "</table>";