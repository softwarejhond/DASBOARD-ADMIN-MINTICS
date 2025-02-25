<?php

// Obtener el valor de 'estado' desde $_GET, o null si no existe
$estado_seleccionado = isset($_GET['estado']) ? $_GET['estado'] : null;
// Obtener el valor de 'tipo' desde $_GET, o null si no existe
$tipo_seleccionado = isset($_GET['tipo']) ? $_GET['tipo'] : null;

// -------------------- VALIDACIÓN DEL ESTADO --------------------
// Validar si el estado seleccionado es un entero válido
if ($estado_seleccionado !== null && $estado_seleccionado !== "") {
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

// -------------------- VALIDACIÓN DEL TIPO --------------------
// Definir los tipos válidos para el ENUM 'tipo'
$tipos_validos = ['Petición', 'Queja', 'Reclamo', 'Sugerencia'];
// Validar si el tipo seleccionado está dentro de los tipos válidos
if ($tipo_seleccionado !== null && $tipo_seleccionado !== "") {
    if (!in_array($tipo_seleccionado, $tipos_validos)) {
        // Si el tipo no es válido, mostrar un error y salir
        echo "Error: El tipo seleccionado no es válido.";
        exit;
    }
} else {
    // Si 'tipo' está vacío o no existe, establecer $tipo_seleccionado a null
    $tipo_seleccionado = null;
}

// -------------------- CONSTRUCCIÓN DE LA CONSULTA SQL --------------------
// Consulta SQL base para seleccionar datos de la tabla 'pqr' unida con la tabla 'estados'
$sql = "SELECT pqr.id, pqr.tipo, pqr.asunto, pqr.fecha_creacion, pqr.numero_radicado, estados.nombre AS estado_nombre
        FROM pqr
        INNER JOIN estados ON pqr.estado = estados.id";

// Array para almacenar las condiciones WHERE
$condiciones = [];
// Array para almacenar los parámetros que se van a bindear a la consulta preparada
$parametros = [];
// String para almacenar los tipos de datos de los parámetros
$tipos = "";

// -------------------- APLICACIÓN DE FILTROS --------------------
// Si se seleccionó un estado, agregar la condición WHERE para filtrar por estado
if ($estado_seleccionado !== null) {
    $condiciones[] = "pqr.estado = ?";
    $parametros[] = $estado_seleccionado;
    $tipos .= "i"; // 'i' para integer (el tipo de dato de 'pqr.estado')
}

// Si se seleccionó un tipo, agregar la condición WHERE para filtrar por tipo
if ($tipo_seleccionado !== null) {
    $condiciones[] = "pqr.tipo = ?";
    $parametros[] = $tipo_seleccionado;
    $tipos .= "s"; // 's' para string (el tipo de dato de 'pqr.tipo')
}

// -------------------- AGREGAR CONDICIONES WHERE A LA CONSULTA --------------------
// Si hay condiciones WHERE, agregarlas a la consulta SQL
if (!empty($condiciones)) {
    $sql .= " WHERE " . implode(" AND ", $condiciones);
}

// -------------------- PREPARAR Y EJECUTAR LA CONSULTA --------------------
// Si hay parámetros, preparar y ejecutar la consulta con bind_param
if (!empty($parametros)) {
    // Preparar la consulta SQL
    $stmt = $conn->prepare($sql);

    // Combinar el string de tipos con el array de parámetros
    $bind_params = array_merge(array($tipos), $parametros);

    // Crear un array de referencias para pasar a bind_param
    $refs = [];
    foreach ($bind_params as $key => $value) {
        $refs[$key] = &$bind_params[$key];  // Crear referencia
    }

    // Usar Reflection para llamar a bind_param con un número variable de argumentos
    $ref = new ReflectionClass('mysqli_stmt');
    $method = $ref->getMethod("bind_param");
    $method->invokeArgs($stmt, $refs);

    // Manejar errores al preparar la consulta
    if ($stmt === false) {
        error_log("Error al preparar la consulta: " . $conn->error);
        echo "Error interno del servidor.";
        exit;
    }

    // Ejecutar la consulta preparada
    if ($stmt->execute()) {
        // Obtener el resultado de la consulta
        $resultado = $stmt->get_result();
    } else {
        // Manejar errores al ejecutar la consulta
        error_log("Error al ejecutar la consulta: " . $stmt->error);
        echo "Error interno del servidor.";
        echo $stmt->error;
        exit;
    }

    // Cerrar la declaración preparada
    $stmt->close();
} else {
    // Si no hay parámetros, ejecutar la consulta directamente
    $resultado = $conn->query($sql);

    // Manejar errores al ejecutar la consulta
    if ($resultado === false) {
        error_log("Error al ejecutar la consulta: " . $conn->error);
        echo "Error interno del servidor.";
        exit;
    }
}

// -------------------- ALMACENAR LOS RESULTADOS --------------------
// Almacenar los datos en un array
$pqrs = array();
// Si hay resultados, recorrerlos y almacenarlos en el array
if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $pqrs[] = $fila;
    }
}
?>