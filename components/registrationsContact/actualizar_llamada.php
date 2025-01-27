<?php
include_once('../../controller/conexion.php');

// Habilitar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Asegurarse de que la conexión a la base de datos esté configurada
if (!isset($conn) || !$conn) {
    die('Error: La conexión a la base de datos no está configurada.');
}

// Verificar que los datos necesarios se hayan enviado mediante POST
if (isset($_POST['number_id']) && isset($_POST['advisor_name']) && isset($_POST['details']) && 
    isset($_POST['contact_established']) && isset($_POST['continues_interested']) && 
    isset($_POST['observation']) && isset($_POST['contact_date'])) {
    
    // Depuración: Verifica los valores recibidos
    error_log("ID recibido: " . $_POST['number_id']);
    error_log("Asesor recibido: " . $_POST['advisor_name']);
    error_log("Detalles recibidos: " . $_POST['details']);
    error_log("Contacto establecido recibido: " . $_POST['contact_established']);
    error_log("Continúa interesado recibido: " . $_POST['continues_interested']);
    error_log("Observación recibida: " . $_POST['observation']);
    error_log("Fecha de contacto recibida: " . $_POST['contact_date']);

    $number_id = $_POST['number_id'];
    $advisor_name = $_POST['advisor_name'];
    $details = $_POST['details'];
    $contact_established = $_POST['contact_established'];
    $continues_interested = $_POST['continues_interested'];
    $observation = $_POST['observation'];
    $contact_date = $_POST['contact_date'];

    // Verificar que el number_id sea un número entero
    if (!is_numeric($number_id)) {
        echo "invalid_data"; // Si el number_id no es válido
        exit;
    }

    // Consulta SQL para actualizar la información de llamadas
    $updateSql = "UPDATE contact_log 
                  SET idAdvisor = (SELECT id FROM advisors WHERE name = ?), 
                      details = ?, 
                      contact_established = ?, 
                      continues_interested = ?, 
                      observation = ?, 
                      contact_date = ? 
                  WHERE number_id = ?";
    $stmt = $conn->prepare($updateSql);

    if ($stmt) {
        // Vincular los parámetros para la consulta preparada
        $stmt->bind_param('ssiiisi', $advisor_name, $details, $contact_established, $continues_interested, $observation, $contact_date, $number_id);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            echo "success"; // Devolver éxito si la actualización fue exitosa
        } else {
            // Log de error para depuración
            error_log("Error en la ejecución de la consulta: " . $stmt->error);
            echo "error"; // Devolver error si hubo un problema con la consulta
        }

        // Cerrar la consulta preparada
        $stmt->close();
    } else {
        // Log de error si la preparación de la consulta falló
        error_log("Error al preparar la consulta: " . $conn->error);
        echo "error"; // Si la preparación de la consulta falló
    }
} else {
    // Log de error si no se enviaron los datos requeridos
    error_log("Datos no enviados correctamente: " . json_encode($_POST));
    echo "invalid_data"; // Si no se enviaron los datos requeridos
}
?>