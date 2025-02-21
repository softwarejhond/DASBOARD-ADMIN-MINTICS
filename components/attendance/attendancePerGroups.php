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

// consulta para obtener los profesoresa 

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

        #historialContainer {
            margin-top: 20px;
            padding: 15px;
            border-top: 1px solid #dee2e6;
        }

        #historialContainer table {
            font-size: 0.9em;
        }

        #historialContainer th {
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>

    <div class="container-fluid mt-4">

        <div class="card shadow mb-3">
            <div class="card-body rounded-0">
                <div class="container-fluid">
                    <div class="row align-items-end">

                        <!-- Seleccionar docente -->
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

                        <!-- Agregar después del select de bootcamp -->
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                            <label class="form-label">Tipo de Curso</label>
                            <select id="courseType" class="form-select">
                                <option value="">Seleccione tipo de curso</option>
                                <option value="bootcamp">Tecnico</option>
                                <option value="leveling_english">Inglés Nivelatorio</option>
                                <option value="english_code">English Code</option>
                                <option value="skills">Habilidas de poder</option>
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
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 d-flex gap-3">
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
                        <th style="width: 5%">ID</th>
                        <th style="width: 10%">Número de ID</th>
                        <th style="width: 20%">Nombre completo</th>
                        <th style="width: 20%; word-wrap: break-word; max-width: 200px;">Correo institucional</th>
                        <th style="width: 8%">Presente</th>
                        <th style="width: 8%">Tarde</th>
                        <th style="width: 8%">Ausente</th>
                        <th style="width: 8%">Festivo</th>
                        <th style="width: 13%">Registro</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Se llenará dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para registro de ausencia -->
    <div class="modal fade" id="ausenciaModal" tabindex="-1" aria-labelledby="ausenciaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ausenciaModalLabel">Registro de Ausencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="ausenciaForm">
                        <input type="hidden" id="studentId" name="studentId">
                        <input type="hidden" id="classId" name="classId">

                        <div class="row mb-3">
                            <div class="col-12">
                                <h6>Estudiante</h6>
                                <h3 class="text-magenta-dark" id="studentName"></h3>
                                <h6 class="text-muted">C.C: <span id="studentId_display"></span></h6>
                                <button type="button" class="btn btn-info btn-sm" id="verHistorial" title="Ver historial de registros">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">¿Se estableció contacto?</label>
                                <select class="form-select" id="contactEstablished" name="contactEstablished" required>
                                    <option value="">Seleccione una opción</option>
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Compromiso</label>
                                <select class="form-select" id="compromiso" name="compromiso">
                                    <option value="">Seleccione una opción</option>
                                    <option value="Asistirá a la siguiente clase">Asistirá a la siguiente clase</option>
                                    <option value="Cambio de horario">Cambio de horario</option>
                                    <option value="Cambio de programa">Cambio de programa</option>
                                    <option value="Tutoría Virtual">Tutoría Virtual</option>
                                    <option value="Tutoria Presencial">Tutoria Presencial</option>
                                    <option value="Clase grabada Virtual">Clase grabada Virtual</option>
                                    <option value="Autoreporte">Autoreporte</option>
                                    <option value="Maratón de Retos">Maratón de Retos</option>
                                    <option value="Auxilio de Transporte">Auxilio de Transporte</option>
                                    <option value="Auxilio de conectividad">Auxilio de conectividad</option>
                                    <option value="No se estableció contacto">No se establecio contacto</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Seguimiento de Compromiso</label>
                                <select class="form-select" id="seguimientoCompromiso" name="seguimientoCompromiso">
                                    <option value="">Seleccione una opción</option>
                                    <option value="Cumplió">Cumplió</option>
                                    <option value="Requiere acompañamiento / Alerta">Requiere acompañamiento / Alerta</option>
                                    <option value="Estratégia Psicosocial">Estratégia Psicosocial</option>
                                    <option value="No se estableció contacto">No se estableció contacto</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Retiro</label>
                                <select class="form-select" id="retiro" name="retiro">
                                    <option value="">Seleccione una opción</option>
                                    <option value="Seguimiento detallado">Seguimiento detallado</option>
                                    <option value="Retiro">Retiro</option>
                                    <option value="No aplica">No aplica</option>
                                    <option value="No se estableció contacto">No se establecio contacto</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Motivo de Retiro</label>
                                <select class="form-select" id="motivoRetiro" name="motivoRetiro">
                                    <option value="">Seleccione una opción</option>
                                    <option value="Laboral">Laboral</option>
                                    <option value="Psicosocial">Psicosocial</option>
                                    <option value="Académico">Académico</option>
                                    <option value="Tiempo de destinación">Tiempo de destinación</option>
                                    <option value="No aplica">No aplica</option>
                                    <option value="Otro">Otro</option>
                                    <option value="No se estableció contacto">No se establecio contacto</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observacion" name="observacion" rows="3"></textarea>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="historialContainer" style="display: none;">
                    <hr>
                    <h4>Historial de Seguimiento</h4>
                    <button type="button" class="btn btn-success mb-3" id="exportarHistorial">
                        <i class="bi bi-file-earmark-excel"></i> Exportar Historial
                    </button>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Contacto</th>
                                    <th>Compromiso</th>
                                    <th>Seguimiento</th>
                                    <th>Retiro</th>
                                    <th>Motivo</th>
                                    <th>Observación</th>
                                    <th>Fecha de registro</th>
                                </tr>
                            </thead>
                            <tbody id="historialBody">
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="guardarAusencia">Guardar</button>
                </div>
            </div>
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
                    courseType: $('#courseType').val(),
                    modalidad: $('#modalidad').val(),
                    sede: $('#sede').val(),
                    class_date: $('#class_date').val()
                };

                // Verificar que todos los campos requeridos tengan valor
                if (!data.bootcamp || !data.courseType || !data.modalidad || !data.sede || !data.class_date) {
                    console.log('Por favor, complete todos los campos');
                    return;
                }

                $.ajax({
                    url: 'components/attendance/buscar_datos_grupales.php', // Se usa el archivo independiente para la solicitud AJAX
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
            $('#bootcamp, #courseType, #sede, #class_date').change(updateTable);

            // Ejecutar toggleSede al cargar la página
            toggleSede();

            // Función para manejar la selección de contacto
            $('#contactEstablished').change(function() {
                const noContacto = $(this).val() === '0';
                const noContactoText = 'No se estableció contacto';

                if (noContacto) {
                    $('#compromiso').val(noContactoText);
                    $('#seguimientoCompromiso').val(noContactoText);
                    $('#retiro').val(noContactoText);
                    $('#motivoRetiro').val(noContactoText);

                    // Deshabilitar los demás selects
                    $('#compromiso, #seguimientoCompromiso, #retiro, #motivoRetiro').prop('disabled', true);
                } else {
                    // Limpiar y habilitar los selects
                    $('#compromiso, #seguimientoCompromiso, #retiro, #motivoRetiro').val('').prop('disabled', false);
                }
            });

            // Reemplaza la función existente del historial
            $('#verHistorial').click(function() {
                const studentId = $('#studentId').val();
                const studentName = $('#studentName').text();

                // Validar que exista un ID de estudiante
                if (!studentId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo identificar el estudiante seleccionado'
                    });
                    return;
                }

                $.ajax({
                    url: 'components/attendance/historial_ausencias.php',
                    type: 'POST',
                    data: {
                        studentId: studentId
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        // Mostrar loading
                        Swal.fire({
                            title: 'Cargando historial',
                            text: `Consultando registros de ${studentName}`,
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(response) {
                        Swal.close();

                        if (response.success) {
                            let html = '';
                            if (response.data && response.data.length > 0) {
                                response.data.forEach(registro => {
                                    html += `
                                        <tr>
                                            <td>${registro.class_date || 'N/A'}</td>
                                            <td>${registro.contact_established == 1 ? 'Sí' : 'No'}</td>
                                            <td>${registro.compromiso || '-'}</td>
                                            <td>${registro.seguimiento_compromiso || '-'}</td>
                                            <td>${registro.retiro || '-'}</td>
                                            <td>${registro.motivo_retiro || '-'}</td>
                                            <td>${registro.observacion || '-'}</td>
                                            <td>${registro.creation_date || '-'}</td>
                                        </tr>
                                    `;
                                });
                            } else {
                                html = '<tr><td colspan="7" class="text-center">No hay registros previos para este estudiante</td></tr>';
                            }

                            $('#historialBody').html(html);
                            $('#historialContainer').slideDown();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al cargar el historial',
                                html: `
                                    <div style="text-align: left">
                                        <p><strong>Tipo de error:</strong> ${response.error.type || 'Desconocido'}</p>
                                        <p><strong>Mensaje:</strong> ${response.error.message || 'No hay detalles disponibles'}</p>
                                    </div>
                                `
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();

                        let errorMessage = 'Error desconocido al cargar el historial';
                        let errorDetail = '';

                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMessage = response.error || errorMessage;
                            errorDetail = response.detail || '';
                        } catch (e) {
                            errorDetail = error;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error al cargar el historial',
                            html: `
                                <div style="text-align: left">
                                    <p><strong>Estado de la solicitud:</strong> ${status}</p>
                                    <p><strong>Mensaje de error:</strong> ${errorMessage}</p>
                                    ${errorDetail ? `<p><strong>Detalle:</strong> ${errorDetail}</p>` : ''}
                                    <p><small>Si el problema persiste, contacte al administrador</small></p>
                                </div>
                            `
                        });

                        // Log para debugging
                        console.error('Error en la solicitud de historial:', {
                            status: status,
                            error: error,
                            response: xhr.responseText,
                            xhr: xhr
                        });
                    },
                    complete: function() {
                        // Asegurar que el loading se cierre
                        if (Swal.isLoading()) {
                            Swal.close();
                        }
                    }
                });
            });

            $('#exportarHistorial').click(function() {
                const studentId = $('#studentId').val();
                const studentName = $('#studentName').text();

                if (!studentId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo identificar el estudiante'
                    });
                    return;
                }

                // Crear la URL para la exportación
                const exportUrl = `components/attendance/exportar_historial.php?studentId=${encodeURIComponent(studentId)}&studentName=${encodeURIComponent(studentName)}`;

                // Abrir en una nueva ventana/pestaña
                window.open(exportUrl, '_blank');
            });
        });



        // Manejo del modal de ausencia
        $('#ausenciaModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const studentId = button.data('student-id');
            const studentName = button.data('student-name');
            const classId = $('#bootcamp').val();

            const modal = $(this);
            modal.find('#studentId').val(studentId);
            modal.find('#studentId_display').text(studentId);
            modal.find('#classId').val(classId);
            modal.find('#studentName').text(studentName);

            // Limpiar el formulario
            modal.find('form')[0].reset();

            // Habilitar todos los selects al abrir el modal
            $('#compromiso, #seguimientoCompromiso, #retiro, #motivoRetiro').prop('disabled', false);
            // Ocultar el historial
            $('#historialContainer').hide();
        });

        // Manejo del guardado de ausencia
        $('#guardarAusencia').click(function() {
            const formData = {
                studentId: $('#studentId').val(),
                classId: $('#classId').val(),
                contactEstablished: $('#contactEstablished').val(),
                compromiso: $('#compromiso').val(),
                seguimientoCompromiso: $('#seguimientoCompromiso').val(),
                retiro: $('#retiro').val(),
                motivoRetiro: $('#motivoRetiro').val(),
                observacion: $('#observacion').val(),
                classDate: $('#class_date').val()
            };

            // Validar campos requeridos
            if (!formData.contactEstablished) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor, indique si se estableció contacto'
                });
                return;
            }

            $.ajax({
                url: 'components/attendance/guardar_ausencia.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Registro guardado correctamente'
                    }).then(() => {
                        $('#ausenciaModal').modal('hide');
                        updateTable();
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al guardar el registro'
                    });
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

</html>