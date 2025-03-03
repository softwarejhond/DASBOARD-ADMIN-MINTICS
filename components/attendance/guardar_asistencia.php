<?php
session_start();
require_once __DIR__ . '/../../controller/conexion.php';

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Usuario no autorizado']);
    exit;
}

$teacher_id = $_SESSION['username']; // Obtener el username de la sesión

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['error' => 'Datos no válidos']);
    exit;
}

$course_id = $data['course_id'] ?? null;
$modalidad = $data['modalidad'] ?? null;
$sede = $data['sede'] ?? null;
$class_date = $data['class_date'] ?? null;
$attendance = $data['attendance'] ?? [];
$intensity_data = $data['intensity_data'] ?? []; // Obtener los datos de intensidad por estudiante
$course_type = $data['course_type'] ?? null; // Obtener el tipo de curso
$selected_intensity = $data['selected_intensity'] ?? 0; // Nuevo valor del select

if (empty($course_id) || empty($modalidad) || empty($sede) || empty($class_date) || empty($attendance) || empty($course_type)) {
    echo json_encode(['error' => 'Faltan datos requeridos']);
    exit;
}

// Comenzar transacción para asegurar la integridad de datos
mysqli_begin_transaction($conn);

try {
    // 1. Insertar registros de asistencia
    $sql = "INSERT INTO attendance_records 
            (teacher_id, student_id, course_id, modality, sede, class_date, attendance_status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE attendance_status = VALUES(attendance_status)";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        throw new Exception('Error al preparar la consulta: ' . mysqli_error($conn));
    }

    $errors = [];
    foreach ($attendance as $student_id => $status) {
        mysqli_stmt_bind_param($stmt, "iisssss", 
            $teacher_id,      // 1. teacher_id
            $student_id,      // 2. student_id
            $course_id,       // 3. course_id
            $modalidad,       // 4. modality
            $sede,           // 5. sede
            $class_date,      // 6. class_date
            $status          // 7. attendance_status
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            $errors[] = "Error al guardar para $student_id: " . mysqli_stmt_error($stmt);
        }
    }

    mysqli_stmt_close($stmt);

    // 2. Actualizar la intensidad horaria en la tabla groups según el tipo de curso
    $intensity_column = "";
    $reals_column = ""; // Nueva columna para las horas reales

    switch ($course_type) {
        case 'bootcamp':
            $intensity_column = "b_intensity";
            $reals_column = "b_reals"; // Nueva columna para bootcamp
            break;
        case 'english_code':
            $intensity_column = "ec_intensity";
            $reals_column = "ec_reals"; // Nueva columna para english code
            break;
        case 'skills':
            $intensity_column = "s_intensity";
            $reals_column = "s_reals"; // Nueva columna para skills
            break;
        default:
            break;
    }

    // Solo actualizar si se identificó una columna válida
    if (!empty($intensity_column) && !empty($reals_column)) {
        foreach ($attendance as $student_id => $status) {
            // Obtener la intensidad específica para este estudiante
            $intensity = isset($intensity_data[$student_id]) ? intval($intensity_data[$student_id]) : 0;
            
            // Obtener las intensidades actuales del estudiante
            $check_sql = "SELECT $intensity_column, $reals_column FROM groups WHERE number_id = ?";
            $check_stmt = mysqli_prepare($conn, $check_sql);
            
            if (!$check_stmt) {
                throw new Exception('Error al preparar la consulta de verificación: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($check_stmt, "i", $student_id);
            
            if (!mysqli_stmt_execute($check_stmt)) {
                throw new Exception('Error al ejecutar la verificación: ' . mysqli_stmt_error($check_stmt));
            }
            
            $result = mysqli_stmt_get_result($check_stmt);
            $row = mysqli_fetch_assoc($result);
            
            // Obtener valores actuales o inicializar en 0
            $current_intensity = ($row) ? intval($row[$intensity_column]) : 0;
            $current_reals = ($row) ? intval($row[$reals_column]) : 0;
            
            // Calcular nuevas intensidades
            $intensity_value = $intensity_data[$student_id] ?? 0;
            $new_intensity = $current_intensity + ($status === 'ausente' ? 0 : $intensity_value);
            $new_reals = $current_reals + $selected_intensity; // Siempre sumar el valor del select, sin importar asistencia
            
            // Debug
            error_log("Actualizando intensidad para estudiante $student_id: actual=$current_intensity, incremento=$intensity, nuevo=$new_intensity, reals=$new_reals");
            
            // Actualizar intensidad y reals
            $update_sql = "UPDATE groups SET $intensity_column = ?, $reals_column = ? WHERE number_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            
            if (!$update_stmt) {
                throw new Exception('Error al preparar la actualización: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($update_stmt, "iii", $new_intensity, $new_reals, $student_id);
            
            if (!mysqli_stmt_execute($update_stmt)) {
                throw new Exception(
                    "Error al actualizar intensidad para estudiante $student_id. " .
                    "Actual: $current_intensity, " .
                    "Incremento: $intensity, " .
                    "Nuevo: $new_intensity, " .
                    "Reals: $new_reals. " .
                    "Error: " . mysqli_stmt_error($update_stmt)
                );
            }
            
            mysqli_stmt_close($update_stmt);
            mysqli_stmt_close($check_stmt);
        }
    }
    
    // Si todo salió bien, confirmar la transacción
    mysqli_commit($conn);
    
    echo json_encode(['success' => true, 'message' => 'Asistencias guardadas correctamente']);

} catch (Exception $e) {
    // Si ocurrió algún error, revertir la transacción
    mysqli_rollback($conn);
    echo json_encode(['error' => $e->getMessage()]);
}