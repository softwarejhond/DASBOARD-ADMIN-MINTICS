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

            <!-- Botón para abrir el nuevo modal de Exportación -->
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="bi bi-file-earmark-excel"></i> Generar Informe Mensual
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
                        <th>ID</th>
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

            // Función para habilitar o deshabilitar la sede según la modalidad
            const toggleSede = () => {
                const modalidad = $('#modalidad').val();
                $('#sede').prop('disabled', modalidad === 'virtual');
                if (modalidad === 'virtual') {
                    $('#sede').val('No aplica');
                }
            };

            // Hacer la función global para que el onchange del select la encuentre
            window.toggleSede = toggleSede;

            // Función para actualizar la tabla
            const updateTable = () => {
                const data = {
                    bootcamp: $('#bootcamp').val(),
                    modalidad: $('#modalidad').val(),
                    sede: $('#sede').val(),
                    class_date: $('#class_date').val()
                };

                // Verificar que todos los campos requeridos tengan valor
                if (!data.bootcamp || !data.modalidad || !data.sede || !data.class_date) {
                    console.log('Por favor, complete todos los campos');
                    return;
                }

                $.ajax({
                    url: 'components/attendance/buscar_datos.php', // Se usa el archivo independiente para la solicitud AJAX
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: (response) => {
                        if (response && response.html) {
                            $('#listaInscritos tbody').html(response.html);
                        } else {
                            $('#listaInscritos tbody').html('<tr><td colspan="8" class="text-center">No se encontraron registros</td></tr>');
                        }
                    },
                    error: (xhr, status, error) => {
                        console.error('Error en la solicitud:', error);
                        $('#listaInscritos tbody').html('<tr><td colspan="8" class="text-center">Error al cargar los datos</td></tr>');
                    }
                });
            };

            // Actualizar la tabla cuando se cambie algún filtro
            $('#modalidad').change(function() {
                toggleSede();
                updateTable();
            });
            $('#bootcamp, #sede, #class_date').change(updateTable);

            // Ejecutar toggleSede al cargar la página
            toggleSede();
        });


        $('#saveAttendance').click(function() {
            const attendanceData = {};

            $('input[type="radio"]:checked').each(function() {
                const studentId = $(this).attr('name').split('_')[2];
                const status = $(this).data('estado');
                attendanceData[studentId] = status;
            });

            const postData = {
                course_id: $('#bootcamp').val(),
                modalidad: $('#modalidad').val(),
                sede: $('#sede').val(),
                class_date: $('#class_date').val(),
                attendance: attendanceData
            };

            $.ajax({
                url: 'components/attendance/guardar_asistencia.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(postData),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: 'Asistencias guardadas correctamente'
                        }).then((result) => {
                            // Recargar la página después de cerrar el alert
                            location.reload();
                        });;
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error || 'Error desconocido'
                        });
                    }
                },
                error: function(xhr) {
                    alert('Error en la solicitud: ' + xhr.responseText);
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

</html>