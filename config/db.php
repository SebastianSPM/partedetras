<?php
// Configuración de base de datos segura
$host = 'localhost';
$dbname = 'abc_tech';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
} catch (PDOException $e) {
    // No mostrar información sensible en producción
    error_log("Error de conexión a BD: " . $e->getMessage());
    die("Error de conexión a la base de datos. Contacte al administrador.");
}
?>