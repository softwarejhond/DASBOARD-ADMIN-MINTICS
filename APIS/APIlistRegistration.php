<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dashboard";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Conexión fallida: " . $conn->connect_error
    ]);
    exit;
}

// Función para obtener niveles de usuarios
function obtenerNivelesUsuarios($conn) {
    $sql = "SELECT cedula, nivel FROM usuarios";
    $result = $conn->query($sql);
    
    $niveles = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $niveles[$row['cedula']] = $row['nivel'];
        }
    }
    return $niveles;
}

try {
    // Obtener niveles de usuarios
    $nivelesUsuarios = obtenerNivelesUsuarios($conn);

    // Consulta principal
    $sql = "SELECT user_register.*, municipios.municipio, departamentos.departamento
            FROM user_register
            INNER JOIN municipios ON user_register.municipality = municipios.id_municipio
            INNER JOIN departamentos ON user_register.department = departamentos.id_departamento
            WHERE departamentos.id_departamento IN (15, 25)
            AND user_register.status = '1' 
            AND user_register.statusAdmin = '' 
            ORDER BY user_register.first_name ASC";

    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Error en consulta principal: " . $conn->error);
    }

    $data = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Consulta de contact_log
            $contactLogs = [];
            $sqlContactLog = "SELECT cl.*, a.name AS advisor_name
                            FROM contact_log cl
                            LEFT JOIN advisors a ON cl.idAdvisor = a.id
                            WHERE cl.number_id = ?";
            
            $stmt = $conn->prepare($sqlContactLog);
            if (!$stmt) {
                throw new Exception("Error en preparación de contact_log: " . $conn->error);
            }
            
            $stmt->bind_param('i', $row['number_id']);
            if (!$stmt->execute()) {
                throw new Exception("Error ejecutando contact_log: " . $stmt->error);
            }
            
            $resultContactLog = $stmt->get_result();
            $contactLogs = $resultContactLog->fetch_all(MYSQLI_ASSOC);
            
            // Procesar contact_logs
            $row['contact_logs'] = [];
            $defaultValues = [
                'idAdvisor' => 'No registrado',
                'advisor_name' => 'Sin asignar',
                'details' => 'Sin detalles',
                'contact_established' => 0,
                'continues_interested' => 0,
                'observation' => 'Sin observaciones'
            ];
            
            if (!empty($contactLogs)) {
                foreach ($contactLogs as $log) {
                    $row['contact_logs'][] = [
                        'idAdvisor' => $log['idAdvisor'],
                        'advisor_name' => $log['advisor_name'],
                        'details' => $log['details'],
                        'contact_established' => (bool)$log['contact_established'],
                        'continues_interested' => (bool)$log['continues_interested'],
                        'observation' => $log['observation']
                    ];
                }
                $lastLog = end($contactLogs);
                $defaultValues = array_merge($defaultValues, [
                    'idAdvisor' => $lastLog['idAdvisor'],
                    'advisor_name' => $lastLog['advisor_name'],
                    'details' => $lastLog['details'],
                    'contact_established' => (bool)$lastLog['contact_established'],
                    'continues_interested' => (bool)$lastLog['continues_interested'],
                    'observation' => $lastLog['observation']
                ]);
            }
            
            // Asignar valores combinados
            $row = array_merge($row, $defaultValues);
            
            // Calcular edad
            $row['age'] = (new DateTime())->diff(
                new DateTime($row['birthdate'])
            )->y;
            
            // Agregar nivel del usuario
            $row['nivel'] = $nivelesUsuarios[$row['cedula']] ?? 'N/A';
            
            $data[] = $row;
        }
    }

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "data" => $data
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
} finally {
    $conn->close();
}
?>