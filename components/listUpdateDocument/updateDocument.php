<?php
include_once('../../controller/conexion.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($conn) || !$conn) {
    die('Error: La conexión a la base de datos no está configurada.');
}

// Verificar si los datos fueron enviados correctamente
if (!isset($_POST['id']) || !isset($_FILES['file_front']) || !isset($_FILES['file_back'])) {
    die("Error: Datos no enviados correctamente.");
}

$id = $_POST['id'];

// Validar que el ID sea numérico
if (!is_numeric($id)) {
    die("Error: ID inválido.");
}

// Definir directorios para guardar los archivos
$upload_dir_front = '../../files/idFilesFront/';
$upload_dir_back = '../../files/idFilesBack/';

// Crear directorios si no existen
if (!file_exists($upload_dir_front)) {
    mkdir($upload_dir_front, 0777, true);
}
if (!file_exists($upload_dir_back)) {
    mkdir($upload_dir_back, 0777, true);
}

// Función para validar el tipo de archivo
function validarArchivo($file) {
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'pdf'];
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    return in_array(strtolower($extension), $extensiones_permitidas);
}

// Procesar archivo frontal
$file_front = $_FILES['file_front'];
$file_front_name = time() . 'front' . basename($file_front['name']);
$file_front_path = $upload_dir_front . $file_front_name;

// Procesar archivo posterior
$file_back = $_FILES['file_back'];
$file_back_name = time() . 'back' . basename($file_back['name']);
$file_back_path = $upload_dir_back . $file_back_name;

// Verificar tipo de archivo antes de subir
if (!validarArchivo($file_front) || !validarArchivo($file_back)) {
    die("Error: Formato de archivo no permitido.");
}

// Subir archivos a sus respectivos directorios
if (!move_uploaded_file($file_front['tmp_name'], $file_front_path) || 
    !move_uploaded_file($file_back['tmp_name'], $file_back_path)) {
    die("Error: No se pudieron subir los archivos.");
}

// Actualizar la base de datos con los nombres de los archivos subidos
$updateSql = "UPDATE user_register SET file_front_id = ?, file_back_id = ? WHERE number_id = ?";
$stmt = $conn->prepare($updateSql);

if ($stmt) {
    $stmt->bind_param('ssi', $file_front_name, $file_back_name, $id);
    
    if ($stmt->execute()) {
        echo "Archivos subidos y base de datos actualizada correctamente.";
    } else {
        echo "Error al actualizar la base de datos: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Error al preparar la consulta: " . $conn->error;
}
?>
