<?php
session_start();
include '../config/db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: /abc_tech_platform/frontend/dashboard.html');
        exit;
    } else {
        $error = "Credenciales inválidas";
        header('Location: /abc_tech_platform/frontend/index.html?error=' . urlencode($error));
        exit;
    }
}
?>