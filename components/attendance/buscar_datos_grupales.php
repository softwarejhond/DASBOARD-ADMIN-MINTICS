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

// Función para obtener las horas totales según el tipo de curso
function getHorasTotalesCurso($courseType) {
    switch ($courseType) {
        case 'bootcamp':
            return 120; // Técnico
        case 'english_code':
            return 24;  // English Code
        case 'skills':
            return 15;  // Habilidades de poder
        default:
            return 0;
    }
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
    $sql = "SELECT g.*, ar.attendance_status,
        (SELECT COUNT(DISTINCT class_date) 
         FROM attendance_records 
         WHERE student_id = g.number_id 
         AND (attendance_status = 'presente' OR attendance_status = 'tarde')) as total_attendance
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

        // Obtener las horas según el tipo de curso
        $horasAsistidas = 0;
        switch ($courseType) {
            case 'bootcamp':
                $horasAsistidas = (float)$row['b_intensity'] ?? 0;
                break;
            case 'english_code':
                $horasAsistidas = (float)$row['ec_intensity'] ?? 0;
                break;
            case 'skills':
                $horasAsistidas = (float)$row['s_intensity'] ?? 0;
                break;
        }

        // Obtener el total de horas para el tipo de curso
        $totalHorasRequeridas = getHorasTotalesCurso($courseType);

        // Calcular el porcentaje
        $attendance_percent = ($totalHorasRequeridas > 0) ? ($horasAsistidas / $totalHorasRequeridas) * 100 : 0;

        // Redondear para mejor visualización
        $horasAsistidasRound = round($horasAsistidas, 1);
        $totalHorasRequeridasRound = $totalHorasRequeridas;

        $circumference = 2 * pi() * 21;
        $offset = $circumference - ($circumference * ($attendance_percent / 100));

        $tableContent .= '<tr>
            <td class="text-center align-middle" style="width: 8%">' . htmlspecialchars($row['type_id']) . '</td>
            <td class="text-center align-middle" style="width: auto">' . htmlspecialchars($row['number_id']) . '</td>
            <td class="align-middle text-truncate" style="width: 30%; max-width: 300px">' . htmlspecialchars($row['full_name']) . '</td>
            <td class="email-cell">' . htmlspecialchars($row['institutional_email']) . '</td>

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
                <div class="circular-progress">
                    <svg width="50" height="50">
                        <circle class="progress-background" cx="25" cy="25" r="21" />
                        <circle class="progress-bar" cx="25" cy="25" r="21" 
                            stroke-dasharray="' . $circumference . '" 
                            stroke-dashoffset="' . $offset . '" />
                    </svg>
                    <div class="progress-text">' . round($attendance_percent) . '%</div>
                </div>
            </td>
            
            <td class="text-center align-middle">
                <div class="attendance-hours">
                    <span class="font-weight-bold">' . $horasAsistidasRound . '</span> / 
                    <span>' . $totalHorasRequeridasRound . '</span>
                    <small class="d-block">hrs</small>
                </div>
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
