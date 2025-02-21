<?php
error_reporting(0); // Desactivar la salida de errores PHP
header('Content-Type: application/json'); // Establecer el header JSON

session_start();
require_once __DIR__ . '/../../controller/conexion.php'; // Asegúrate de que $conn esté definido

// Verificar la conexión a la base de datos
if (!$conn) {
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

// Verificar que se reciba una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Verificar que el usuario esté en sesión
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Usuario no autorizado']);
    exit;
}

try {
    // Recoger y validar datos
    $bootcamp   = isset($_POST['bootcamp']) ? (int)$_POST['bootcamp'] : 0;
    $modalidad  = $_POST['modalidad'] ?? '';
    $sede       = $_POST['sede'] ?? '';
    $class_date = $_POST['class_date'] ?? '';
    $courseType = $_POST['courseType'] ?? '';

    if (empty($bootcamp) || empty($modalidad) || empty($sede) || empty($class_date) || empty($courseType)) {
        echo json_encode(['error' => 'Faltan datos requeridos']);
        exit;
    }

    // Si la modalidad es virtual, se fuerza la sede a 'No aplica'
    if (strtolower($modalidad) === 'virtual') {
        $sede = 'No aplica';
    }

    $courseIdColumn = '';
    switch ($courseType) {
        case 'bootcamp':
            $courseIdColumn = 'id_bootcamp';
            break;
        case 'leveling_english':
            $courseIdColumn = 'id_leveling_english';
            break;
        case 'english_code':
            $courseIdColumn = 'id_english_code';
            break;
        case 'skills':
            $courseIdColumn = 'id_skills';
            break;
    }

    // Modificar la consulta SQL sin la validación del profesor
    $sql = "SELECT g.*, ar.attendance_status 
            FROM groups g 
            LEFT JOIN attendance_records ar ON g.number_id = ar.student_id 
                AND ar.class_date = ? 
            WHERE g.$courseIdColumn = ? 
            AND g.mode = ? 
            AND g.headquarters = ?";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        echo json_encode(['error' => 'Error en la preparación: ' . mysqli_error($conn)]);
        exit;
    }

    // Bind parameters sin el teacher_id
    mysqli_stmt_bind_param($stmt, "siss", $class_date, $bootcamp, $modalidad, $sede);

    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode(['error' => 'Error en la ejecución: ' . mysqli_stmt_error($stmt)]);
        exit;
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        echo json_encode(['error' => 'Error al obtener resultados: ' . mysqli_error($conn)]);
        exit;
    }

    // Construir el contenido de la tabla
    $tableContent = '';
    while ($row = mysqli_fetch_assoc($result)) {
        $attendanceStatus = $row['attendance_status'] ?? '';

        $tableContent .= '<tr>
            <td class="text-center align-middle" style="width: 8%">' . htmlspecialchars($row['type_id']) . '</td>
            <td class="text-center align-middle" style="width: auto">' . htmlspecialchars($row['number_id']) . '</td>
            <td class="align-middle text-truncate" style="width: 30%; max-width: 300px">' . htmlspecialchars($row['full_name']) . '</td>
            <td class="align-middle" style="width: 30%; max-width: 300px">' . htmlspecialchars($row['institutional_email']) . '</td>


            <td class="text-center align-middle">
                <input type="radio" name="attendance_status_' . htmlspecialchars($row['number_id']) . '" 
                    class="form-check-input estado-asistencia" data-estado="presente" 
                    ' . ($attendanceStatus === 'presente' ? 'checked' : '') . ' disabled>
            </td>
            <td class="text-center align-middle">
                <input type="radio" name="attendance_status_' . htmlspecialchars($row['number_id']) . '" 
                    class="form-check-input estado-asistencia" data-estado="tarde" 
                    ' . ($attendanceStatus === 'tarde' ? 'checked' : '') . ' disabled>
            </td>
            <td class="text-center align-middle">
                <input type="radio" name="attendance_status_' . htmlspecialchars($row['number_id']) . '" 
                    class="form-check-input estado-asistencia" data-estado="ausente" 
                    ' . ($attendanceStatus === 'ausente' ? 'checked' : '') . ' disabled>
            </td>
            <td class="text-center align-middle">
                <input type="radio" name="attendance_status_' . htmlspecialchars($row['number_id']) . '" 
                    class="form-check-input estado-asistencia" data-estado="festivo" 
                    ' . ($attendanceStatus === 'festivo' ? 'checked' : '') . ' disabled>
            </td>

            <td class="text-center align-middle">
                <button type="button" 
                    class="btn ' . ($attendanceStatus === 'ausente' ? 'btn-primary' : 'btn-secondary') . ' btn-sm registrar-ausencia" 
                    data-bs-toggle="modal" 
                    data-bs-target="#ausenciaModal" 
                    data-student-id="' . htmlspecialchars($row['number_id']) . '"
                    data-student-name="' . htmlspecialchars($row['full_name']) . '"
                    ' . ($attendanceStatus !== 'ausente' ? 'disabled' : '') . '>
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>
            </td>
        </tr>';
    }

    if (empty($tableContent)) {
        $tableContent = '<tr><td colspan="8" class="text-center">No se encontraron registros</td></tr>';
    }

    echo json_encode(['html' => $tableContent]);
    exit;
} catch (Exception $e) {
    echo json_encode(['error' => 'Error en el servidor: ' . $e->getMessage()]);
    exit;
}
