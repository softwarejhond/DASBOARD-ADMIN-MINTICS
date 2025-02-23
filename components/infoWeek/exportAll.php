<?php
require __DIR__ . '../../../vendor/autoload.php';
require __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar si se solicita la exportación
if (isset($_GET['action']) && $_GET['action'] === 'export') {
    exportDataToExcel($conn); // Ejecutar la función de exportación
    exit;
}

function exportDataToExcel($conn)
{
    // Obtener niveles de usuarios
    $nivelesUsuarios = obtenerNivelesUsuarios($conn);

    // Consulta principal
    $sql = "SELECT 
        user_register.*, 
        municipios.municipio, 
        departamentos.departamento,
        g.bootcamp_teacher_id,
        g.id_bootcamp,
        g.bootcamp_name,
        g.le_teacher_id,
        g.id_leveling_english,
        g.leveling_english_name,
        g.ec_teacher_id,
        g.id_english_code,
        g.english_code_name,
        g.skills_teacher_id,
        g.id_skills,
        g.skills_name,
        u.nombre as teacher_name
    FROM user_register
    INNER JOIN municipios ON user_register.municipality = municipios.id_municipio
    INNER JOIN departamentos ON user_register.department = departamentos.id_departamento
    LEFT JOIN groups g ON user_register.number_id = g.number_id
    LEFT JOIN users u ON g.bootcamp_teacher_id = u.username 
    WHERE departamentos.id_departamento IN (15, 25)
    AND user_register.status = '1' 
    ORDER BY user_register.first_name ASC";

    $result = $conn->query($sql);
    $data = [];

    // Consulta para obtener todos los asesores
    $sqlAsesores = "SELECT idAdvisor, name FROM advisors ORDER BY name ASC";
    $resultAsesores = $conn->query($sqlAsesores);
    $asesores = [];
    if ($resultAsesores && $resultAsesores->num_rows > 0) {
        while ($asesor = $resultAsesores->fetch_assoc()) {
            $asesores[$asesor['idAdvisor']] = $asesor['name'];
        }
    }

    // Consulta para contact_log
    $sqlContactLog = "SELECT cl.*, a.name AS advisor_name
                      FROM contact_log cl
                      LEFT JOIN advisors a ON cl.idAdvisor = a.id
                      WHERE cl.number_id = ?";

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Procesar contact_logs
            $stmtContactLog = $conn->prepare($sqlContactLog);
            $stmtContactLog->bind_param('i', $row['number_id']);
            $stmtContactLog->execute();
            $resultContactLog = $stmtContactLog->get_result();
            $contactLogs = $resultContactLog->fetch_all(MYSQLI_ASSOC);

            $lastLog = !empty($contactLogs) ? end($contactLogs) : [
                'advisor_name' => 'Sin asignar',
                'details' => 'Sin detalles',
                'contact_established' => 0,
                'continues_interested' => 0,
                'observation' => 'Sin observaciones'
            ];

            // Verificar y asignar nombre del asesor
            if (!empty($lastLog['idAdvisor']) && isset($asesores[$lastLog['idAdvisor']])) {
                $lastLog['advisor_name'] = $asesores[$lastLog['idAdvisor']];
            }

            // Calcular edad
            $birthday = new DateTime($row['birthdate']);
            $now = new DateTime();
            $age = $now->diff($birthday)->y;

            // Determinar estado CUMPLE/NO CUMPLE
            $isAccepted = false;
            if ($row['mode'] === 'Presencial') {
                $isAccepted = (
                    $row['typeID'] === 'C.C' &&
                    $age > 17 &&
                    in_array(strtoupper($row['departamento']), ['CUNDINAMARCA', 'BOYACÁ']) &&
                    $row['internet'] === 'Sí'
                );
            } elseif ($row['mode'] === 'Virtual') {
                $isAccepted = (
                    $row['typeID'] === 'C.C' &&
                    $age > 17 &&
                    in_array(strtoupper($row['departamento']), ['CUNDINAMARCA', 'BOYACÁ']) &&
                    $row['internet'] === 'Sí' &&
                    $row['technologies'] === 'computador'
                );
            }

            // Determinar estado de prueba
            $puntaje = $nivelesUsuarios[$row['number_id']] ?? '';
            $estadoPrueba = 'No presentó prueba';
            if ($puntaje) {
                if ($puntaje >= 1 && $puntaje <= 5) {
                    $estadoPrueba = 'Básico';
                } elseif ($puntaje >= 6 && $puntaje <= 10) {
                    $estadoPrueba = 'Intermedio';
                } elseif ($puntaje >= 11 && $puntaje <= 15) {
                    $estadoPrueba = 'Avanzado';
                }
            }



            //Asignacion a estado de admision
            $estadoAdmision = 'PENDIENTE';
            if ($row['statusAdmin'] === '1') {
                $estadoAdmision = 'ACEPTADO';
            } elseif ($row['statusAdmin'] === '0') {
                $estadoAdmision = 'RECHAZADO';
            }

            //Victima del conflicto armado
            $victimaConflictoArmado = ($row['vulnerable_type'] === 'Victima del conflicto armado') ? 'Sí' : '';


            // Construir fila de datos
            $data[] = [
                'Ejecutor (contratista)' => '',
                'ID' => $row['id'],
                'Tipo documento' => $row['typeID'],
                'Número' => $row['number_id'],
                'Primer Nombre' => $row['first_name'],
                'Segundo Nombre' => $row['second_name'],
                'Primer Apellido' => $row['first_last'],
                'Segundo Apellido' => $row['second_last'],
                'Fecha de Nacimiento' => $row['birthdate'],
                'Edad' => $age,
                'Correo' => $row['email'],
                'Nacionalidad' => $row['nationality'],
                'Código Departamento' => $row['department'],
                'Departamento' => $row['departamento'],
                'Region' => '7',
                'Código Municipio' => $row['municipality'],
                'Municipio' => $row['municipio'],
                'Teléfono principal' => $row['first_phone'],
                'Teléfono secundario' => $row['second_phone'],
                'Genero' => $row['gender'],
                'Campesino' => '',
                'Estrato' => $row['stratum'],
                'Auto identificación etnica' => $row['ethnic_group'],
                'Nivel de educación' => $row['training_level'],
                'Discapacidad' => $row['disability'],
                'IP' => '',
                'Motivaciones' => $row['motivations_belong_program'],
                'Compromiso 10 horas' => '',
                'Tipo formación' => $row['mode'],
                'Acepta requisitos de convotaria' => $row['accepts_tech_talent'],
                'Victima del conflicto armado' => $victimaConflictoArmado,
                'Acepta políticas' => $row['accept_data_policies'],
                'Dispnibilidad de Equipo' => $row['technologies'],
                'Fecha de creación' => $row['creationDate'],
                'Presento prueba' => $puntaje ? 'Sí' : 'No',
                'Fecha de inicio' => '',
                'Tiempo en segundos' => '',
                'Eje tematico' => '',
                'Eje final' => '',
                'Puntaje eje tematico seleccionado' => $puntaje,
                'Linea 1 programación' => '',
                'Linea 2 inteligencia artificial' => '',
                'Linea 3 analisis de datos' => '',
                'Linea 4 blockchain' => '',
                'Linea 5 arquitectura en la nube' => '',
                'Linea 6 ciberseguridad' => '',
                'Linea 1 des programación' => '',
                'Linea 2 des inteligencia artificial' => '',
                'Linea 3 des analisis de datos' => '',
                'Linea 4 des blockchain' => '',
                'Linea 5 des arquitectura en la nube' => '',
                'Linea 6 des ciberseguridad' => '',
                'Area 1 Alfabetización de datos' => '',
                'Area 2 comunicación y colaboración' => '',
                'Area 3 contenidos digitales' => '',
                'Area 4 seguridad' => '',
                'Area 5 solución de problemas' => '',
                'Area 6 ingles' => '',
                'Area 1 des Alfabetización de datos' => '',
                'Area 2 des comunicación y colaboración' => '',
                'Area 3 des contenidos digitales' => '',
                'Area 4 des seguridad' => '',
                'Area 5 des solución de problemas' => '',
                'Origen' => '',
                'Matriculado' => ($row['statusAdmin'] == 3) ? 'si' : 'No',
                'Estado' => '',
                'Programa de interés' => $row['program'],
                'Nivel' => $row['level'],
                'Documento profesor principal a cargo del programa de formación' => $row['bootcamp_teacher_id'],
                'Nombre de profesor principal a cargo del programa de formación' => $row['teacher_name'],
                'Fecha de inicio del programa de formación' => '',
                'Cohorte (1, 2, 3, 4, 5, 6, 7 o 8)' => '',
                'Tipo de programa de formación' => '',
                'Enlace al certificado en Sharepoint' => '',
                'Observaciones (menos de 50 cracteres)' => '',
                'Codigo del curso' => $row['id_bootcamp'],
                'Nombre del curso' => $row['bootcamp_name'],
                'Asistencias' => '',
                'Asistencias programadas' => '',
                'Documento mentor' => '',
                'Nombre mentor' => '',
                'Documento monitor' => '',
                'Nombre monitor' => '',
                'Documento ejecutor de ingles' => $row['ec_teacher_id'],
                'Nombre ejecutor de ingles' => $row['teacher_name'],
                'Documento ejecutor de habilidades de poder' => $row['skills_teacher_id'],
                'Nombre ejecutor de habilidades de poder' => $row['teacher_name'],
            ];
        }
    }

    // Crear archivo Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Data');


    // Encabezados
    $headers = array_keys($data[0] ?? []);
    $sheet->fromArray($headers, NULL, 'A1');

    // Datos
    $rowIndex = 2;
    foreach ($data as $row) {
        $sheet->fromArray(array_values($row), NULL, "A{$rowIndex}");
        $rowIndex++;
    }

    // Estilo para la primera hoja
    $lastColumn = Coordinate::stringFromColumnIndex(count($headers));

    // Columnas A a BK: color durazno (peach)
    $sheet->getStyle('A1:BK1')
        ->getFill()->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFDAB9');

    // Columnas BO a BU: color verde
    $sheet->getStyle('BL1:BU1')
        ->getFill()->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FF00FF00');

    // Columnas BV a BW: color verde claro
    $sheet->getStyle('BV1:BW1')
        ->getFill()->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FF90EE90');

    // Columnas BX a CI: color amarillo
    $sheet->getStyle('BX1:CI1')
        ->getFill()->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFFFFF00');

    // Aplicar fuente en negrita a todos los encabezados
    $sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true);

    // Ajustar ancho de columnas según el texto del encabezado
    foreach ($headers as $colIndex => $headerText) {
        $column = Coordinate::stringFromColumnIndex($colIndex + 1);
        $width = mb_strlen($headerText) + 2; // +2 para un poco de padding
        $sheet->getColumnDimension($column)->setWidth($width);
    }

    ob_clean(); // Limpia cualquier salida previa
    // Configurar headers para descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="inscritos_' . date('Y-m-d') . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

function obtenerNivelesUsuarios($conn)
{
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
