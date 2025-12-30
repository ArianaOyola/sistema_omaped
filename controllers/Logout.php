<?php
// controllers/Logout.php
session_start();
session_destroy(); // Destruye todos los datos de la sesión
header("Location: ../views/auth/login.php"); // Nos manda de vuelta al login
exit();
?>