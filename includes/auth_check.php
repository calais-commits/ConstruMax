<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$allowed_roles = ['admin', 'vendedor', 'almacenista'];
if (!in_array($_SESSION['rol'], $allowed_roles)) {
    header("Location: index.php");
    exit();
}