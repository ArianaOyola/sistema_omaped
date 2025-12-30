<?php
// config/db.php

// 1. Configuración de Credenciales (XAMPP por defecto)
$host = 'localhost';
$dbname = 'omaped_db';  // Nombre de tu base de datos (¡Ojo! Debes crearla en phpMyAdmin)
$username = 'root';     // Usuario por defecto de XAMPP
$password = '';         // Contraseña por defecto de XAMPP (vacía)

try {
    // 2. Crear la conexión PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // 3. Configurar manejo de errores (Importante para depurar)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Opcional: Configurar el modo de fetch por defecto a Array Asociativo
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // echo "Conexión exitosa"; // Descomenta esto solo para probar si funciona al inicio

} catch (PDOException $e) {
    // 4. Si falla, detener todo y mostrar mensaje
    die("Error de conexión a la Base de Datos: " . $e->getMessage());
}
?>