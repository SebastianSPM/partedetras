<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $message = "Se ha solicitado recuperación para $email. En producción, envía un enlace.";
    header('Location: /abc_tech_platform/frontend/forgot_password.html?message=' . urlencode($message));
    exit;
}
?>