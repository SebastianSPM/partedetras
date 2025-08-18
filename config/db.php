<?php
$host = 'localhost';
$dbname = 'abc_tech';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conexión exitosa<br>"; // Depuración temporal
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>