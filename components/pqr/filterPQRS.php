<?php

// Obtener el valor de 'estado' desde $_GET, o null si no existe
$estado_seleccionado = isset($_GET['estado']) ? $_GET['estado'] : null;

// Validar el valor de 'estado'
if ($estado_seleccionado !== null && $estado_seleccionado !== "") {
    // Si 'estado' no está vacío, intentar validarlo como un entero
    $estado_seleccionado = filter_var($estado_seleccionado, FILTER_VALIDATE_INT);
    if ($estado_seleccionado === false) {
        // Si la validación falla, mostrar un error y salir
        echo "Error: El estado seleccionado no es válido.";
        exit;
    }
} else {
    // Si 'estado' está vacío o no existe, establecer $estado_seleccionado a null
    $estado_seleccionado = null;
}

// Construir la consulta SQL base
$sql = "SELECT pqr.id, pqr.tipo, pqr.asunto, pqr.fecha_creacion, pqr.numero_radicado, estados.nombre AS estado_nombre
        FROM pqr
        INNER JOIN estados ON pqr.estado = estados.id";

// Modificar la consulta SQL si se seleccionó un estado específico
if ($estado_seleccionado !== null) {
    $sql .= " WHERE pqr.estado = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Error al preparar la consulta: " . $conn->error);
        echo "Error interno del servidor.";
        exit;
    }

    $stmt->bind_param("i", $estado_seleccionado);

    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
    } else {
        error_log("Error al ejecutar la consulta: " . $stmt->error);
        echo "Error interno del servidor.";
        exit;
    }

    $stmt->close();
} else {
    $resultado = $conn->query($sql);

    if ($resultado === false) {
        error_log("Error al ejecutar la consulta: " . $conn->error);
        echo "Error interno del servidor.";
        exit;
    }
}

// Almacenar los datos en un array
$pqrs = array();
if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $pqrs[] = $fila;
    }
}
?>