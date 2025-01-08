<?php
// obtener_proporciones.php
include("conexion.php"); // Incluye el archivo de conexión

function obtenerProporcionesUsuarios()
{
    global $conn;

    $data = [
        'total_masculinos' => 0,
        'total_femeninos' => 0,
        'total_solteros' => 0,
        'total_casados' => 0,
        'total_con_programa' => 0,
        'total_usuarios' => 0,
        'porcentaje_masculino' => 0,
        'porcentaje_femenino' => 0,
        'porcentaje_solteros' => 0,
        'porcentaje_casados' => 0,
        'porcentaje_con_programa' => 0,
    ];

    // Consulta para contar usuarios masculinos
    $queryMasculinos = "SELECT COUNT(*) as total_masculinos FROM user_register WHERE gender = 'Masculino' AND status = 'ACTIVO'";
    $resultMasculinos = mysqli_query($conn, $queryMasculinos);
    if ($resultMasculinos) {
        $dataMasculinos = mysqli_fetch_assoc($resultMasculinos);
        $data['total_masculinos'] = (int)$dataMasculinos['total_masculinos'];
    }

    // Consulta para contar usuarios femeninos
    $queryFemeninos = "SELECT COUNT(*) as total_femeninos FROM user_register WHERE gender = 'Femenino' AND status = 'ACTIVO'";
    $resultFemeninos = mysqli_query($conn, $queryFemeninos);
    if ($resultFemeninos) {
        $dataFemeninos = mysqli_fetch_assoc($resultFemeninos);
        $data['total_femeninos'] = (int)$dataFemeninos['total_femeninos'];
    }

    // Consulta para contar usuarios solteros
    $querySolteros = "SELECT COUNT(*) as total_solteros FROM user_register WHERE marital_status = 'Soltero' AND status = 'ACTIVO'";
    $resultSolteros = mysqli_query($conn, $querySolteros);
    if ($resultSolteros) {
        $dataSolteros = mysqli_fetch_assoc($resultSolteros);
        $data['total_solteros'] = (int)$dataSolteros['total_solteros'];
    }

    // Consulta para contar usuarios casados
    $queryCasados = "SELECT COUNT(*) as total_casados FROM user_register WHERE marital_status = 'Casado' AND status = 'ACTIVO'";
    $resultCasados = mysqli_query($conn, $queryCasados);
    if ($resultCasados) {
        $dataCasados = mysqli_fetch_assoc($resultCasados);
        $data['total_casados'] = (int)$dataCasados['total_casados'];
    }

    // Consulta para contar usuarios con programa asignado
    $queryConPrograma = "SELECT COUNT(*) as total_con_programa FROM user_register WHERE program IS NOT NULL AND status = 'ACTIVO'";
    $resultConPrograma = mysqli_query($conn, $queryConPrograma);
    if ($resultConPrograma) {
        $dataConPrograma = mysqli_fetch_assoc($resultConPrograma);
        $data['total_con_programa'] = (int)$dataConPrograma['total_con_programa'];
    }

    // Consulta para contar el total de usuarios
    $queryTotal = "SELECT COUNT(*) as total_usuarios FROM user_register WHERE status = 'ACTIVO'";
    $resultTotal = mysqli_query($conn, $queryTotal);
    if ($resultTotal) {
        $dataTotal = mysqli_fetch_assoc($resultTotal);
        $data['total_usuarios'] = (int)$dataTotal['total_usuarios'];
    }

    // Calcular los porcentajes
    if ($data['total_usuarios'] > 0) {
        $data['porcentaje_masculino'] = ($data['total_masculinos'] / $data['total_usuarios']) * 100;
        $data['porcentaje_femenino'] = ($data['total_femeninos'] / $data['total_usuarios']) * 100;
        $data['porcentaje_solteros'] = ($data['total_solteros'] / $data['total_usuarios']) * 100;
        $data['porcentaje_casados'] = ($data['total_casados'] / $data['total_usuarios']) * 100;
        $data['porcentaje_con_programa'] = ($data['total_con_programa'] / $data['total_usuarios']) * 100;
    }

    header('Content-Type: application/json');
    echo json_encode($data);
}
obtenerProporcionesUsuarios();
