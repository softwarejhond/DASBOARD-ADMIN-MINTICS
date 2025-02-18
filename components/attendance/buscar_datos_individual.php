<?php
// Asegurarnos de que no haya salida antes del JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once __DIR__ . '/../../controller/conexion.php';

$student_id = $_POST['student_id'] ?? '';
$course_id = $_POST['bootcamp'] ?? '';
$class_date = $_POST['class_date'] ?? '';

if (empty($student_id) || empty($course_id) || empty($class_date)) {
    echo json_encode(['error' => 'Faltan datos requeridos']);
    exit;
}

try {
    // Modificamos la consulta para asegurar que los campos coincidan con la base de datos
    $query = "SELECT 
                ar.id,
                ar.student_id,
                ar.attendance_status,
                g.full_name,
                g.institutional_email
              FROM attendance_records ar
              LEFT JOIN groups g ON TRIM(ar.student_id) = TRIM(g.number_id)
              WHERE ar.student_id = ? 
              AND ar.course_id = ? 
              AND ar.class_date = ?";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $conn->error);
    }

    $stmt->bind_param("sis", $student_id, $course_id, $class_date);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    $output = '';
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $output .= '<tr>';
            $output .= '<td>' . htmlspecialchars($row['student_id']) . '</td>';
            $output .= '<td>' . htmlspecialchars($row['full_name'] ?? 'No disponible') . '</td>';
            $output .= '<td>' . htmlspecialchars($row['institutional_email'] ?? 'No disponible') . '</td>';
            
            // Opciones de asistencia
            $estados = ['presente', 'tarde', 'ausente', 'festivo'];
            foreach ($estados as $estado) {
                $disabled = ($row['attendance_status'] !== 'ausente' && 
                           $row['attendance_status'] !== 'tarde') ? 'disabled' : '';
                $checked = ($row['attendance_status'] === $estado) ? 'checked' : '';
                
                $output .= '<td>
                    <input type="radio" 
                           name="attendance_' . $row['id'] . '"
                           value="' . $estado . '"
                           data-record-id="' . $row['id'] . '"
                           data-estado="' . $estado . '"
                           ' . $checked . '
                           ' . $disabled . '>
                </td>';
            }
            $output .= '</tr>';
        }
    } else {
        $output = '<tr><td colspan="8" class="text-center">No se encontraron registros</td></tr>';
    }
    
    echo json_encode(['success' => true, 'html' => $output]);
    
} catch (Exception $e) {
    // Log del error para debugging
    error_log("Error en buscar_datos_individual.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}