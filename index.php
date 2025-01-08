<?php 
// Incluir la conexión a la base de datos
include("./controller/conexion.php"); 

// Iniciar sesión
session_start();

// Habilitar la visualización de errores
ini_set('display_errors', 1);
error_reporting(E_ALL); // Mostrar todos los errores

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Si no está logueado, redirigir a la página de inicio de sesión
    header('Location: components/login.php');
    exit;
}

// $rol = $infoUsuario['rol']; // Descomentar si se necesita más adelante
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include("./controller/head.php"); // Incluir cabecera ?>
</head>
<body>
    
    <?php include("./controller/scripts.php"); // Incluir scripts ?>
</body>
</html>
