<?php
// Incluir conexión y obtener datos de Moodle
require_once __DIR__ . '/../../controller/conexion.php';

// Definir las variables globales para Moodle
$api_url = "https://talento-tech.uttalento.co/webservice/rest/server.php";
$token   = "3f158134506350615397c83d861c2104";
$format  = "json";

// Función para llamar a la API de Moodle
function callMoodleAPI($function, $params = [])
{
    global $api_url, $token, $format;
    $params['wstoken'] = $token;
    $params['wsfunction'] = $function;
    $params['moodlewsrestformat'] = $format;
    $url = $api_url . '?' . http_build_query($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error en la solicitud cURL: ' . curl_error($ch);
    }
    curl_close($ch);
    return json_decode($response, true);
}

// Función para obtener cursos desde Moodle
function getCourses()
{
    return callMoodleAPI('core_course_get_courses');
}

// Obtener cursos y almacenarlos en $courses_data
$courses_data = getCourses();



?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Filtrar Inscritos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilos para la tabla */
        #listaInscritos {
            table-layout: fixed;
        }

        #listaInscritos th,
        #listaInscritos td {
            vertical-align: middle;
            white-space: nowrap;
        }

        #listaInscritos td:nth-child(3) {
            white-space: normal !important;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .estado-asistencia {
            width: 25px;
            height: 25px;
            margin: auto;
            display: block;
        }
    </style>
</head>

<body>
    <div class="container-fluid mt-4">
        <div class="card shadow mb-3">
            <div class="card-body rounded-0">
                <div class="container-fluid">
                    <div class="row align-items-end">
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                            <label class="form-label">ID del Estudiante</label>
                            <input type="text" id="student_id" class="form-control" placeholder="Ingrese el ID del estudiante">
                        </div>
                        <!-- Selección de Bootcamp (Clase) -->
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                            <label class="form-label">Clase</label>
                            <select id="bootcamp" class="form-select course-select">
                                <?php foreach ($courses_data as $course): ?>
                                    <option value="<?= htmlspecialchars($course['id']) ?>">
                                        <?= htmlspecialchars($course['id'] . ' - ' . $course['fullname']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Selección de Modalidad -->
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                            <label class="form-label">Modalidad</label>
                            <select name="modalidad" id="modalidad" class="form-select" onchange="toggleSede()">
                                <option value="">Seleccione modalidad</option>
                                <option value="virtual">Virtual</option>
                                <option value="Presencial">Presencial</option>
                            </select>
                        </div>
                        <!-- Selección de Sede -->
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12"><br>
                            <label class="form-label">Sede</label>
                            <select name="sede" id="sede" class="form-select">
                                <option value="">Seleccione una sede</option>
                                <option value="Cota">Cota</option>
                                <option value="Tunja">Tunja</option>
                                <option value="Sogamoso">Sogamoso</option>
                                <option value="Soacha">Soacha</option>
                                <option value="No aplica">No aplica</option>
                            </select>
                        </div>
                        <!-- Selección de Fecha -->
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12"><br>
                            <label class="form-label">Fecha</label>
                            <input type="date" name="class_date" id="class_date" class="form-control" required max="<?= date('Y-m-d'); ?>">
                        </div>
                        <!-- Título con nombre del usuario -->
                        <div class="col-12 mb-3"><br>
                            <h4>
                                Docente: <?= isset($_SESSION['nombre']) ? htmlspecialchars($_SESSION['nombre']) : 'Usuario' ?>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 d-flex gap-3">
            <button id="saveAttendance" class="btn btn-primary">
                <i class="fa fa-save"></i> Guardar Asistencias
            </button>
        </div>
        <!-- Modal para exportar informe mensual -->
        <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exportModalLabel">Exportar Informe Mensual</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="card shadow mb-3 mt-4">
                            <div class="card-body">
                                <h5 class="card-title">Exportar Informe Mensual</h5>
                                <form id="exportForm" method="POST" action="components/attendance/exportar_informe.php">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="month" class="form-label">Mes</label>
                                            <select id="month" name="month" class="form-select" required>
                                                <option value="1">Enero</option>
                                                <option value="2">Febrero</option>
                                                <option value="3">Marzo</option>
                                                <option value="4">Abril</option>
                                                <option value="5">Mayo</option>
                                                <option value="6">Junio</option>
                                                <option value="7">Julio</option>
                                                <option value="8">Agosto</option>
                                                <option value="9">Septiembre</option>
                                                <option value="10">Octubre</option>
                                                <option value="11">Noviembre</option>
                                                <option value="12">Diciembre</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="year" class="form-label">Año</label>
                                            <input type="number" id="year" name="year" class="form-control" min="2020" max="<?= date('Y'); ?>" value="<?= date('Y'); ?>" required>
                                        </div>
                                        <div class="col-md-4 d-flex align-items-end">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fa fa-download"></i> Exportar Excel
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Tabla donde se mostrarán los datos -->
        <div class="table-responsive">
            <table id="listaInscritos" class="table table-hover table-bordered">
                <thead>
                    <tr class="text-center">
                        <th>Número de ID</th>
                        <th style="min-width: 350px;">Nombre completo</th>
                        <th style="min-width: 350px;">Correo institucional</th>
                        <th style="width: 8%">Presente</th>
                        <th style="width: 8%">Tarde</th>
                        <th style="width: 8%">Ausente</th>
                        <th style="width: 8%">Festivo</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Se llenará dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>
    <!-- jQuery para la solicitud AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function buscarAsistencia() {
                const studentId = $('#student_id').val();
                const courseId = $('#bootcamp').val();
                const classDate = $('#class_date').val();

                if (!studentId || !courseId || !classDate) {
                    return;
                }

                $.ajax({
                    url: 'components/attendance/buscar_datos_individual.php',
                    type: 'POST',
                    data: {
                        student_id: studentId,
                        bootcamp: courseId,
                        class_date: classDate
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#listaInscritos tbody').html(response.html);
                        } else {
                            alert('Error: ' + (response.error || 'Error desconocido'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la solicitud AJAX:', error);
                        alert('Error al procesar la solicitud');
                    }
                });
            }

            // Eventos para actualizar la tabla
            $('#student_id, #bootcamp, #class_date').on('change', buscarAsistencia);
        });

        function toggleSede() {
            const modalidad = document.getElementById('modalidad').value;
            const sede = document.getElementById('sede');

            if (modalidad === 'virtual') {
                sede.value = 'No aplica';
                sede.disabled = true;
            } else {
                sede.disabled = false;
                if (sede.value === 'No aplica') {
                    sede.value = '';
                }
            }
        }

        // Manejador para el botón de guardar
        $('#saveAttendance').click(function() {
            const attendance = {};
            const courseId = $('#bootcamp').val();
            const classDate = $('#class_date').val();

            // Recolectar datos de los radio buttons seleccionados
            $('input[type="radio"]:checked').each(function() {
                const recordId = $(this).data('record-id');
                const estado = $(this).val();
                attendance[recordId] = estado;
            });

            // Verificar si hay datos para guardar
            if (Object.keys(attendance).length === 0) {
                alert('No hay cambios para guardar');
                return;
            }

            // Enviar datos al servidor
            $.ajax({
                url: 'components/attendance/guardar_asistencia_individual.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    attendance: attendance,
                    course_id: courseId,
                    class_date: classDate
                }),
                success: function(response) {
                    if (response.success) {
                        alert('Asistencias guardadas correctamente');
                        // Recargar los datos
                        buscarAsistencia();
                    } else {
                        alert('Error al guardar: ' + (response.error || 'Error desconocido'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error en la solicitud AJAX:', error);
                    alert('Error al procesar la solicitud');
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>