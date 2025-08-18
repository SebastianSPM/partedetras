<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /abc_tech_platform/frontend/index.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create'])) {
    $stmt = $pdo->prepare("INSERT INTO services (name, technician, cost, estimated_time) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['name'], $_POST['technician'], $_POST['cost'], $_POST['estimated_time']]);
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM services WHERE id=?");
    $stmt->execute([$_GET['delete']]);
}

$services = $pdo->query("SELECT * FROM services")->fetchAll();

header('Content-Type: text/html; charset=UTF-8');
echo "<table>";
echo "<tr><th>ID</th><th>Nombre</th><th>Técnico</th><th>Costo</th><th>Tiempo</th><th>Acciones</th></tr>";
foreach ($services as $service) {
    echo "<tr>";
    echo "<td>{$service['id']}</td>";
    echo "<td>{$service['name']}</td>";
    echo "<td>{$service['technician']}</td>";
    echo "<td>{$service['cost']}</td>";
    echo "<td>{$service['estimated_time']}</td>";
    echo "<td><a href='?delete={$service['id']}' onclick='return confirm(\"¿Seguro?\");'>Eliminar</a></td>";
    echo "</tr>";
}
echo "</table>";