<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /abc_tech_platform/frontend/index.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create'])) {
    $stmt = $pdo->prepare("INSERT INTO products (code, description, price, stock, supplier_id) VALUES (?, ?, ?, ?, ?)");
    $supplier_id = isset($_POST['supplier_id']) ? $_POST['supplier_id'] : null;
    $stmt->execute([$_POST['code'], $_POST['description'], $_POST['price'], $_POST['stock'], $supplier_id]);
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
    $stmt->execute([$_GET['delete']]);
}

$products = $pdo->query("SELECT p.*, s.company_name FROM products p LEFT JOIN suppliers s ON p.supplier_id = s.id")->fetchAll();

header('Content-Type: text/html; charset=UTF-8');
echo "<table>";
echo "<tr><th>ID</th><th>Código</th><th>Descripción</th><th>Precio</th><th>Stock</th><th>Proveedor</th><th>Acciones</th></tr>";
foreach ($products as $product) {
    echo "<tr>";
    echo "<td>{$product['id']}</td>";
    echo "<td>{$product['code']}</td>";
    echo "<td>{$product['description']}</td>";
    echo "<td>{$product['price']}</td>";
    echo "<td>{$product['stock']}</td>";
    echo "<td>" . (isset($product['company_name']) ? $product['company_name'] : 'N/A') . "</td>";
    echo "<td><a href='?delete={$product['id']}' onclick='return confirm(\"¿Seguro?\");'>Eliminar</a></td>";
    echo "</tr>";
}
echo "</table>";