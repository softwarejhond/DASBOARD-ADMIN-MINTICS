<?php
session_start();
include_once('../../controller/conexion.php');

// Habilitar la visualización de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

// Consultar la cantidad de usuarios por estrato (1 al 6)
$query = "SELECT stratum, COUNT(*) as cantidad FROM user_register WHERE stratum BETWEEN 1 AND 6 GROUP BY stratum";
$resultado = $conn->query($query);

$data = [
    'labels' => [],
    'data' => []
];

if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $data['labels'][] = 'Estrato ' . $fila['stratum'];  // Etiquetas como "Estrato 1", "Estrato 2", etc.
        $data['data'][] = $fila['cantidad'];
    }
}

// Retornar los datos en formato JSON si es una solicitud específica
if (isset($_GET['json'])) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

?>
