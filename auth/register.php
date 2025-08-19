<?php
session_start();
require_once '../backend/config.php'; // Ajusta la ruta a tu config.php si está en otra carpeta

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recibir datos del formulario
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validar campos vacíos
    if (empty($username) || empty($email) || empty($password)) {
        header("Location: /abc_tech_platform/frontend/register.html?error=" . urlencode("Todos los campos son obligatorios"));
        exit;
    }

    // Hashear la contraseña
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Verificar si el usuario o email ya existen
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            header("Location: /abc_tech_platform/frontend/register.html?error=" . urlencode("El usuario o correo ya existe"));
            exit;
        }
        $stmt->close();

        // Insertar nuevo usuario
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashedPassword);

        if ($stmt->execute()) {
            // Registro exitoso -> redirigir a login
            header("Location: /abc_tech_platform/frontend/login.html?success=" . urlencode("Registro exitoso. Inicia sesión"));
            exit;
        } else {
            header("Location: /abc_tech_platform/frontend/register.html?error=" . urlencode("Error al registrar usuario"));
            exit;
        }

    } catch (Exception $e) {
        header("Location: /abc_tech_platform/frontend/register.html?error=" . urlencode("Error en el servidor"));
        exit;
    }
}
?>
