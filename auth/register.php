<?php
include '../config/db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, $email]);
        header('Location: ../frontend/index.html');
        exit;
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
        header('Location: /abc_tech_platform/frontend/register.html?error=' . urlencode($error));
        exit;
    }
}
?>