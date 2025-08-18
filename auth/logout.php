<?php
session_start();
include '../config/db.php';
session_destroy();
header('Location: /abc_tech_platform/frontend/index.html'); 
?>