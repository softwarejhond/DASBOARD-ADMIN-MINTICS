<?php
require __DIR__ . '/../../conexion.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);


header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

// Validar campos requeridos
$required = [
    'type_id', 'number_id', 'full_name', 'email', 'institutional_email', 'password',
    'id_bootcamp', 'bootcamp_name', 'id_leveling_english', 'leveling_english_name',
    'id_english_code', 'english_code_name', 'id_skills', 'skills_name'
];
foreach ($required as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Falta el campo: $field"]);
        exit;
    }
}

// Verificar si el correo ya existe
$stmt = $conn->prepare("SELECT id FROM groups WHERE email = ? OR institutional_email = ?");
$stmt->bind_param('ss', $input['email'], $input['institutional_email']);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'El correo ya está registrado']);
    exit;
}

$password = $input['password'];

// Insertar en la BD
$sql = "INSERT INTO groups (
    type_id, number_id, full_name, email, institutional_email, password,
    id_bootcamp, bootcamp_name, id_leveling_english, leveling_english_name,
    id_english_code, english_code_name, id_skills, skills_name
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    'ssssssisssssis', // Updated 'i' to 's' for bootcamp_name
    $input['type_id'],
    $input['number_id'],
    $input['full_name'],
    $input['email'],
    $input['institutional_email'],
    $password,
    $input['id_bootcamp'],
    $input['bootcamp_name'],
    $input['id_leveling_english'],
    $input['leveling_english_name'],
    $input['id_english_code'],
    $input['english_code_name'],
    $input['id_skills'],
    $input['skills_name']
);

if ($stmt->execute()) {
    // Después de la inserción exitosa, actualizar statusMoodle
    $updateSql = "UPDATE user_register SET statusMoodle = '1' WHERE number_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    if (!$updateStmt) {
        throw new Exception("Error al preparar la actualización: " . $conn->error);
    }

    $updateStmt->bind_param('s', $input['number_id']);
    if (!$updateStmt->execute()) {
        throw new Exception("Error al actualizar statusMoodle: " . $updateStmt->error);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Usuario matriculado exitosamente'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();