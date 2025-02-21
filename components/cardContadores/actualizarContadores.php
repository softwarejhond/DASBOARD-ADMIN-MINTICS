<?php
// NO SE REQUIERE IMPORTAR LA CONEXIÓN PORQUE DESDE EL MAIN YA ESTÁ CONECTADA
require '.././../controller/conexion.php';
// Agregar manejo de errores
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Obtener total de usuarios verificados
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

    // Obtener total de usuarios sin verificar
    $sql_sin_verificar = "SELECT COUNT(*) AS total_sinVerificar FROM user_register WHERE status = '1' AND statusAdmin = '0'";
    $result_sinVerificar = mysqli_query($conn, $sql_sin_verificar);
    $total_sinVerificar = mysqli_fetch_assoc($result_sinVerificar)['total_sinVerificar'];

    // Obtener total de Gobernación de Boyacá
    $sql_GobernacionBoyaca = "SELECT COUNT(*) AS total_GobernacionBoyaca FROM user_register WHERE status = '1' AND statusAdmin = '0' AND institution = 'Gobernación de Boyacá'";
    $result_GobernacionBoyaca = mysqli_query($conn, $sql_GobernacionBoyaca);
    $total_GobernacionBoyaca = mysqli_fetch_assoc($result_GobernacionBoyaca)['total_GobernacionBoyaca'];

    // Obtener total de contactos establecidos (Sí)
    $sql_contacto_si = "SELECT COUNT(DISTINCT ur.number_id) AS total_contacto_si FROM user_register ur
                        JOIN contact_log cl ON ur.number_id = cl.number_id
                        WHERE cl.contact_established = '1'";
    $result_contacto_si = mysqli_query($conn, $sql_contacto_si);
    $total_contacto_si = mysqli_fetch_assoc($result_contacto_si)['total_contacto_si'];

    // Obtener total de contactos no establecidos (No)
    $sql_contacto_no = "SELECT COUNT(DISTINCT ur.number_id) AS total_contacto_no FROM user_register ur
                        JOIN contact_log cl ON ur.number_id = cl.number_id
                        WHERE cl.contact_established = '0'";
    $result_contacto_no = mysqli_query($conn, $sql_contacto_no);
    $total_contacto_no = mysqli_fetch_assoc($result_contacto_no)['total_contacto_no'];

    // Calcular porcentajes
    $porc_boyaca = ($total_usuarios > 0) ? round(($total_boyaca / $total_usuarios) * 100, 2) : 0;
    $porc_cundinamarca = ($total_usuarios > 0) ? round(($total_cundinamarca / $total_usuarios) * 100, 2) : 0;
    $porc_sinVerificar = ($total_usuarios > 0) ? round(($total_sinVerificar / $total_usuarios) * 100, 2) : 0;
    $porc_GobernacionBoyaca = ($total_usuarios > 0) ? round(($total_GobernacionBoyaca / $total_usuarios) * 100, 2) : 0;
    $porc_contacto_si = ($total_usuarios > 0) ? round(($total_contacto_si / $total_usuarios) * 100, 2) : 0;
    $porc_contacto_no = ($total_usuarios > 0) ? round(($total_contacto_no / $total_usuarios) * 100, 2) : 0;

    // Devolver los datos en formato JSON
    header('Content-Type: application/json');
    echo json_encode([
        "total_usuarios" => $total_usuarios,
        "total_boyaca" => $total_boyaca,
        "porc_boyaca" => $porc_boyaca,
        "total_cundinamarca" => $total_cundinamarca,
        "porc_cundinamarca" => $porc_cundinamarca,
        "total_sinVerificar" => $total_sinVerificar,
        "porc_sinVerificar" => $porc_sinVerificar,
        "total_GobernacionBoyaca" => $total_GobernacionBoyaca,
        "porc_GobernacionBoyaca" => $porc_GobernacionBoyaca,
        "total_contacto_si" => $total_contacto_si,
        "porc_contacto_si" => $porc_contacto_si,
        "total_contacto_no" => $total_contacto_no,
        "porc_contacto_no" => $porc_contacto_no
    ]);
} catch (Exception $e) {
    // Manejo de errores
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}
?>
