<?php
//NO SE REQUIERE IMPORTAR LA CONEXIÓN PORQUE DESDE EL MAIN YA ESTÁ CONECTADA
include '../../controller/conexion.php';
// Obtener total de usuarios
$sql_total = "SELECT COUNT(*) AS total FROM user_register WHERE status = '1' AND statusAdmin = '1'";
$result_total = mysqli_query($conn, $sql_total);
$total_usuarios = mysqli_fetch_assoc($result_total)['total'];

// Obtener total de usuarios en Boyacá
$sql_boyaca = "SELECT COUNT(*) AS total_boyaca FROM user_register WHERE status = '1' AND statusAdmin = '1' AND department = 15";
$result_boyaca = mysqli_query($conn, $sql_boyaca);
$total_boyaca = mysqli_fetch_assoc($result_boyaca)['total_boyaca'];

// Obtener total de usuarios en Cundinamarca
$sql_cundinamarca = "SELECT COUNT(*) AS total_cundinamarca FROM user_register WHERE status = '1' AND statusAdmin = '1' AND department = 25";
$result_cundinamarca = mysqli_query($conn, $sql_cundinamarca);
$total_cundinamarca = mysqli_fetch_assoc($result_cundinamarca)['total_cundinamarca'];

// Obtener total de usuarios con programa
$sql_sin_verificar = "SELECT COUNT(*) AS total_sinVerificar FROM user_register WHERE status = '1' AND statusAdmin = '0'";
$result_sinVerificar= mysqli_query($conn, $sql_sin_verificar);
$total_sinVerificar= mysqli_fetch_assoc($result_sinVerificar)['total_sinVerificar'];

// Obtener total de Gobernacion de boyaca
$sql_GoberanacionBoyaca = "SELECT COUNT(*) AS total_GobernacionBoyaca FROM user_register WHERE status = '1' AND statusAdmin = '0' AND institution = 'Gobernación de Boyacá";
$result_GobernacionBoyaca= mysqli_query($conn, $sql_GoberanacionBoyaca);
$total_GobernacioBoyaca= mysqli_fetch_assoc($result_GobernacionBoyaca)['total_GobernacionBoyaca'];


// Calcular porcentajes
$porc_boyaca = ($total_usuarios > 0) ? round(($total_boyaca / $total_usuarios) * 100, 2) : 0;
$porc_cundinamarca = ($total_usuarios > 0) ? round(($total_cundinamarca / $total_usuarios) * 100, 2) : 0;
$porc_sinVerificar = ($total_usuarios > 0) ? round(($total_sinVerificar / $total_usuarios) * 100, 2) : 0;
$porc_GobernacionBoyaca = ($total_usuarios > 0) ? round(($total_GobernacioBoyaca / $total_usuarios) * 100, 2) : 0;
// Devolver los datos en formato JSON
echo json_encode([
    'total_usuarios' => $total_usuarios,
    'total_boyaca' => $total_boyaca,
    'total_cundinamarca' => $total_cundinamarca,
    'total_sinVerificar' => $total_sinVerificar,
    'total_GobernacionBoyaca' => $total_GobernacioBoyaca,
    'porc_boyaca' => $porc_boyaca,
    'porc_cundinamarca' => $porc_cundinamarca,
    'porc_sinVerificar' => $porc_sinVerificar,
    'porc_GobernacionBoyaca' => $porc_GobernacionBoyaca
]);
?>