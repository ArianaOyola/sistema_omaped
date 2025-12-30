<?php
// OMAPED/index.php
session_start();

// Si el usuario ya tiene una sesión activa, va al Dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: views/dashboard/index.php");
    exit();
} else {
    // Si no, lo mandamos al Login
    header("Location: views/auth/login.php");
    exit();
}
?>