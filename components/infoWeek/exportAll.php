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
    // Obtener datos de la API DIVIPOLA
    $divipolaData = file_get_contents('https://www.datos.gov.co/resource/gdxc-w37w.json?$limit=1112');
    $divipolaArray = json_decode($divipolaData, true);

    // Crear mapeos de nombres a códigos
    $departamentosMap = [];
    $municipiosMap = [];

    foreach ($divipolaArray as $item) {
        // Normalizar nombres
        $dptoNormalizado = strtoupper(normalizeString($item['dpto']));
        $mpioNormalizado = strtoupper(normalizeString($item['nom_mpio']));

        // Manejar caso especial de Bogotá
        if ($dptoNormalizado === 'BOGOTA, D.C.' || $dptoNormalizado === 'BOGOTA D.C.' || $dptoNormalizado === 'BOGOTA') {
            $departamentosMap['BOGOTA'] = $item['cod_dpto'];
            $departamentosMap['BOGOTA D.C.'] = $item['cod_dpto'];
            $departamentosMap['BOGOTA, D.C.'] = $item['cod_dpto'];

            // También mapear el municipio de Bogotá con sus variantes
            $municipiosMap['BOGOTA|BOGOTA'] = $item['cod_mpio'];
            $municipiosMap['BOGOTA D.C.|BOGOTA'] = $item['cod_mpio'];
            $municipiosMap['BOGOTA, D.C.|BOGOTA'] = $item['cod_mpio'];
        }

        // Mapeo normal para otros departamentos y municipios
        $departamentosMap[$dptoNormalizado] = $item['cod_dpto'];
        $municipiosMap[$dptoNormalizado . '|' . $mpioNormalizado] = $item['cod_mpio'];
    }

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
    g.creation_date,
    g.cohort,
    g.bootcamp_mentor_id,
    g.le_mentor_id,
    g.ec_mentor_id,
    g.skills_mentor_id,
    g.bootcamp_monitor_id,
    g.le_monitor_id,
    g.ec_monitor_id,
    g.skills_monitor_id,
    c.start_date,
    u1.nombre as bootcamp_teacher_name,
    u2.nombre as le_teacher_name,
    u3.nombre as ec_teacher_name,
    u4.nombre as skills_teacher_name,
    u5.nombre as bootcamp_mentor_name,
    u6.nombre as le_mentor_name,
    u7.nombre as ec_mentor_name,
    u8.nombre as skills_mentor_name,
    u9.nombre as bootcamp_monitor_name,
    u10.nombre as le_monitor_name,
    u11.nombre as ec_monitor_name,
    u12.nombre as skills_monitor_name,
    (SELECT COALESCE(SUM(b_intensity), 0) + COALESCE(SUM(ec_intensity), 0) + COALESCE(SUM(s_intensity), 0) 
     FROM groups 
     WHERE number_id = user_register.number_id) as total_intensities
    FROM user_register
    INNER JOIN municipios ON user_register.municipality = municipios.id_municipio
    INNER JOIN departamentos ON user_register.department = departamentos.id_departamento
    LEFT JOIN groups g ON user_register.number_id = g.number_id
    LEFT JOIN users u1 ON g.bootcamp_teacher_id = u1.username
    LEFT JOIN users u2 ON g.le_teacher_id = u2.username
    LEFT JOIN users u3 ON g.ec_teacher_id = u3.username
    LEFT JOIN users u4 ON g.skills_teacher_id = u4.username
    LEFT JOIN users u5 ON g.bootcamp_mentor_id = u5.username
    LEFT JOIN users u6 ON g.le_mentor_id = u6.username
    LEFT JOIN users u7 ON g.ec_mentor_id = u7.username
    LEFT JOIN users u8 ON g.skills_mentor_id = u8.username
    LEFT JOIN users u9 ON g.bootcamp_monitor_id = u9.username
    LEFT JOIN users u10 ON g.le_monitor_id = u10.username
    LEFT JOIN users u11 ON g.ec_monitor_id = u11.username
    LEFT JOIN users u12 ON g.skills_monitor_id = u12.username
    LEFT JOIN cohorts c ON g.cohort = c.cohort_number
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

    // Consulta para obtener el conteo de asistencias por estudiante
    $sqlAttendance = "SELECT student_id, COUNT(*) as total_attendance 
                        FROM attendance_records 
                        GROUP BY student_id";
    $resultAttendance = $conn->query($sqlAttendance);
    $attendanceCount = [];

    if ($resultAttendance && $resultAttendance->num_rows > 0) {
        while ($attendance = $resultAttendance->fetch_assoc()) {
            $attendanceCount[$attendance['student_id']] = $attendance['total_attendance'];
        }
    }

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
            $victimaConflictoArmado = ($row['vulnerable_type'] === 'Victima del conflicto armado') ? 'Sí' : 'No';


            // Verificar si el usuario está en la tabla groups
            $estaEnGroups = !empty($row['id_bootcamp']) || !empty($row['id_leveling_english']) || !empty($row['id_english_code']) || !empty($row['id_skills']);

            //Tiene profesor asisnado
            $tieneProfesor = '';
            if (!$estaEnGroups) {
                $tieneProfesor = '';
            } else if ($row['bootcamp_teacher_id'] > 0 || 
                $row['ec_teacher_id'] > 0 || 
                $row['skills_teacher_id'] > 0) {
                $tieneProfesor = 'En formacion';
            } elseif ($row['bootcamp_teacher_id'] == 0 &&  
                     $row['ec_teacher_id'] == 0 && 
                     $row['skills_teacher_id'] == 0) {
                $tieneProfesor = 'Beneficiario en programacion';
            } elseif ($row['statusAdmin'] === '2') {
                $tieneProfesor = 'Rechazado';
            } else {
                $tieneProfesor = 'Por verificar'; 
            }

            // Construir fila de datos
            $data[] = [
                'Ejecutor (contratista)' => '',
                'id' => $row['id'],
                'Tipo_documento' => $row['typeID'],
                'Número_documento' => $row['number_id'],
                'Nombre1' => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['A', 'E', 'I', 'O', 'U', 'A', 'E', 'I', 'O', 'U'], $row['first_name'])),
                'Nombre2' => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['A', 'E', 'I', 'O', 'U', 'A', 'E', 'I', 'O', 'U'], $row['second_name'])),
                'Apellido1' => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['A', 'E', 'I', 'O', 'U', 'A', 'E', 'I', 'O', 'U'], $row['first_last'])),
                'Apellido2' => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['A', 'E', 'I', 'O', 'U', 'A', 'E', 'I', 'O', 'U'], $row['second_last'])),
                'Fecha_nacimiento' => date('d/m/Y', strtotime($row['birthdate'])),
                'Correo' => $row['email'],
                'Codigo_epartamento' => $departamentosMap[strtoupper(normalizeString($row['departamento']))] ?? $row['department'],
                'Departamento' => $row['departamento'],

                'Region' => '7',

                'Codigo_municipio' => (strtoupper(normalizeString($row['municipio'])) === 'BOGOTA D.C.' ||
                    strtoupper(normalizeString($row['municipio'])) === 'BOGOTA, D.C.' ||
                    strtoupper(normalizeString($row['municipio'])) === 'BOGOTA')
                    ? '11001'
                    : ($municipiosMap[strtoupper(normalizeString($row['departamento'])) . '|' . strtoupper(normalizeString($row['municipio']))] ?? $row['municipality']),

                'Municipio' => $row['municipio'],

                'Telefono_movil' => $row['first_phone'],

                'Genero' => ($row['gender'] === 'No binario' || $row['gender'] === 'No reporta') ? 'Otro' : $row['gender'],

                'Campesino' => '',

                'Estrato' => ($row['stratum'] == '0' ? 'Sin estratificar' : ($row['residence_area'] == 'Rural' ? $row['stratum'] . ' - Rural' : $row['stratum'])),

                'Autoidentificacion_Etnica' => ($row['ethnic_group'] === 'No aplica') ? 'Ninguna de las anteriores' : $row['ethnic_group'],

                'Nivel_educacion' => match ($row['training_level']) {
                    'Primaria (hasta 5°)' => 'Básica Primaria (1-5)',
                    'Secundaria (Hasta 9°)' => 'Básica Secundaria (6-9)',
                    'Media (Bachiller)' => 'Media (10-11)',
                    'Técnico', 'Tecnico' => 'Técnico Profesional',
                    'Pregrado' => 'Profesional Universitario',
                    'Especialización', 'Maestria', 'Doctorado' => 'Posgrado',
                    default => $row['training_level']
                },
                'Discapacidad' => ($row['disability'] === 'No') ? 'No aplica' : $row['disability'],

                'IP' => '',
                'Motivaciones' => $row['motivations_belong_program'],
                'Compromiso_10_horas' => $row['availability'],
                'Tipo_formacion' => $row['mode'],
                'Acepta_requisitos_convotaria' => $row['accepts_tech_talent'],
                'Victima_del_conflicto' => $victimaConflictoArmado,
                'Autoriza_manejo_datos_personales' => $row['accept_data_policies'],
                'Disponibilidad_d_Equipo' => $row['technologies'],
                'creationdate' => $row['creationDate'],
                'Presento' => $puntaje ? 'Sí' : 'No',
                'fecha_ini' => $row['creation_date'],
                'tiempo_segundos' => '',
                'Eje_tematico' => '',
                'Eje_final' => $row['program'],
                'Puntaje eje tematico seleccionado' => $puntaje ? $puntaje : 'Sin presentar',
                'linea_1_programacion' => '',
                'linea_2_inteligecia_artificial' => '',
                'linea_3_analisis_de_datos' => '',
                'linea_4_blockchain' => '',
                'linea_5_arquitectura_en_la_nube' => '',
                'linea_6_ciberseguridad' => '',
                'linea_1_des_programacion' => '',
                'linea_2_des_inteligecia_artificial' => '',
                'linea_3_des_analisis_de_datos' => '',
                'linea_4_des_blockchain' => '',
                'linea_5_des_arquitectura_en_la_nube' => '',
                'linea_6_des_ciberseguridad' => '',
                'area_1_alfabetizacion_datos' => '',
                'area_2_comunicacion_y_colaboracion' => '',
                'area_3_contenidos_digitales' => '',
                'area_4_seguridad' => '',
                'area_5_solucion_de_problemas' => '',
                'area_6_ingles' => '',
                'area_1_des_alfabetizacion_datos' => '',
                'area_2_des_comunicacion_y_colaboracion' => '',
                'area_3_des_contenidos_digitales' => '',
                'area_4_des_seguridad' => '',
                'area_5_des_solucion_de_problemas' => '',
                'Origen' => 'UTTT-R7L1',
                'Matriculado' => $estaEnGroups ? 'SI' : '',
                'Estado' => $tieneProfesor,
                'Programa de interés' => $row['program'],
                'Nivel' => $row['level'],
                'Documento_Profesor principal a cargo del programa de formación' => $row['bootcamp_teacher_id'],
                'Profesor principal a cargo del programa de formación' => $row['bootcamp_teacher_name'],
                'Fecha Inicio de la formación (dd/mm/aaaa)' => '',
                'Cohorte (1,2,3,4,5,6,7 o 8)' => $row['cohort'],
                'Año Cohorte' => $row['start_date'] ? date('Y', strtotime($row['start_date'])) : '',
                'Tipo de formación' => '',
                'Enlace al certificado en Sharepoint' => '',
                'Observaciones (menos de 50 cracteres)' => '',
                'Codigo del curso' => $row['id_bootcamp'],
                'Nombre del curso' => $row['bootcamp_name'],
                'Asistencias' => $attendanceCount[$row['number_id']] ?? 0,
                'Asistencias programadas' => $row['total_intensities'] ?? 0,
                'Documento_Mentor' => $row['bootcamp_mentor_id'],
                'Mentor' => $row['bootcamp_mentor_name'],
                'Documento_Monitor' => $row['bootcamp_monitor_id'],
                'Monitor' => $row['bootcamp_monitor_name'],
                'Documento_Ejecutor_ingles' => $row['ec_teacher_id'],
                'Ejecutor de ingles' => $row['ec_teacher_name'],
                'Documento_Ejecutor de habilidades de poder' => $row['skills_teacher_id'],
                'Ejecutor de habilidades de poder' => $row['skills_teacher_name'],
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
    $sheet->getStyle('A1:BF1')
        ->getFill()->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFDAB9');

    // Columnas BO a BU: color verde
    $sheet->getStyle('BG1:BQ1')
        ->getFill()->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FF00FF00');

    // Columnas BV a BW: color verde claro
    $sheet->getStyle('BR1:BS1')
        ->getFill()->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FF90EE90');

    // Columnas BX a CI: color amarillo
    $sheet->getStyle('BT1:CG1')
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
    header('Content-Disposition: attachment;filename="informe_semanal_' . date('Y-m-d') . '.xlsx"');
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

// Agregar esta función helper para normalizar strings
function normalizeString($string)
{
    $unwanted_array = array(
        'Š' => 'S',
        'š' => 's',
        'Ž' => 'Z',
        'ž' => 'z',
        'À' => 'A',
        'Á' => 'A',
        'Â' => 'A',
        'Ã' => 'A',
        'Ä' => 'A',
        'Å' => 'A',
        'Æ' => 'A',
        'Ç' => 'C',
        'È' => 'E',
        'É' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'Ì' => 'I',
        'Í' => 'I',
        'Î' => 'I',
        'Ï' => 'I',
        'Ñ' => 'N',
        'Ò' => 'O',
        'Ó' => 'O',
        'Ô' => 'O',
        'Õ' => 'O',
        'Ö' => 'O',
        'Ø' => 'O',
        'Ù' => 'U',
        'Ú' => 'U',
        'Û' => 'U',
        'Ü' => 'U',
        'Ý' => 'Y',
        'Þ' => 'B',
        'ß' => 'Ss',
        'à' => 'a',
        'á' => 'a',
        'â' => 'a',
        'ã' => 'a',
        'ä' => 'a',
        'å' => 'a',
        'æ' => 'a',
        'ç' => 'c',
        'è' => 'e',
        'é' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'ì' => 'i',
        'í' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ð' => 'o',
        'ñ' => 'n',
        'ò' => 'o',
        'ó' => 'o',
        'ô' => 'o',
        'õ' => 'o',
        'ö' => 'o',
        'ø' => 'o',
        'ù' => 'u',
        'ú' => 'u',
        'û' => 'u',
        'ý' => 'y',
        'þ' => 'b',
        'ÿ' => 'y'
    );
    return strtr($string, $unwanted_array);
}
