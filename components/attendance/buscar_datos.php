<?php
session_start();
require_once __DIR__ . '/../../controller/conexion.php'; // Asegúrate de que $conn esté definido

// Verificar que se reciba una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Verificar que el usuario esté en sesión
    if (!isset($_SESSION['username'])) {
        echo json_encode(['error' => 'Usuario no autorizado']);
        exit;
    }

    $teacher_id = $_SESSION['username']; // Obtener el username de la sesión
    

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

    $teacherColumn = '';
    $courseIdColumn = '';

    switch($courseType) {
        case 'bootcamp':
            $teacherColumn = 'bootcamp_teacher_id';
            $courseIdColumn = 'id_bootcamp';
            break;
        case 'leveling_english':
            $teacherColumn = 'le_teacher_id';
            $courseIdColumn = 'id_leveling_english';
            break;
        case 'english_code':
            $teacherColumn = 'ec_teacher_id';
            $courseIdColumn = 'id_english_code';
            break;
        case 'skills':
            $teacherColumn = 'skills_teacher_id';
            $courseIdColumn = 'id_skills';
            break;
    }

    // Preparar la consulta
    $sql = "SELECT * FROM groups 
            WHERE $teacherColumn = ? 
            AND $courseIdColumn = ? 
            AND mode = ? 
            AND headquarters = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        echo json_encode(['error' => 'Error en la preparación: ' . mysqli_error($conn)]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "siss", $teacher_id, $bootcamp, $modalidad, $sede);

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
        $tableContent .= '<tr>
            <td class="text-center align-middle" style="width: 8%">' . htmlspecialchars($row['type_id']) . '</td>
            <td class="text-center align-middle" style="width: auto">' . htmlspecialchars($row['number_id']) . '</td>
            <td class="align-middle text-truncate" style="width: 30%; max-width: 300px">' . htmlspecialchars($row['full_name']) . '</td>
            <td class="align-middle">' . htmlspecialchars($row['institutional_email']) . '</td>

            <td class="text-center align-middle">
                <input type="radio" name="attendance_status_' . htmlspecialchars($row['number_id']) .'" class="form-check-input estado-asistencia" data-estado="presente">
            </td>
            <td class="text-center align-middle">
                <input type="radio" name="attendance_status_' . htmlspecialchars($row['number_id']) . '" class="form-check-input estado-asistencia" data-estado="tarde">
            </td>
            <td class="text-center align-middle">
                <input type="radio" name="attendance_status_' . htmlspecialchars($row['number_id']) . '" class="form-check-input estado-asistencia" data-estado="ausente">
            </td>
            <td class="text-center align-middle">
                <input type="radio" name="attendance_status_' . htmlspecialchars($row['number_id']) . '" class="form-check-input estado-asistencia" data-estado="festivo">
            </td>
        </tr>';
    }

    if (empty($tableContent)) {
        $tableContent = '<tr><td colspan="8" class="text-center">No se encontraron registros</td></tr>';
    }

    echo json_encode(['html' => $tableContent]);
    exit;
}