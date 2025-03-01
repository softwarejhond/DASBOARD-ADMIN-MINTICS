<?php
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

// Consulta para obtener usuarios
$sql = "SELECT user_register.*, departamentos.departamento
        FROM user_register
        INNER JOIN departamentos ON user_register.department = departamentos.id_departamento
        WHERE departamentos.id_departamento IN (15, 25)
          AND user_register.status = '1' 
          AND user_register.statusAdmin = '1'
        ORDER BY user_register.first_name ASC";

$result = $conn->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    echo '<div class="alert alert-info">No hay datos disponibles.</div>';
}

// Obtener datos únicos para los filtros
$departamentos = ['BOYACÁ', 'CUNDINAMARCA'];
$programas = [];
$modalidades = [];
$sedes = []; // Agregar array para sedes
$niveles = ['Explorador', 'Integrador', 'Innovador'];
$horarios = [];

foreach ($data as $row) {
    $depto = $row['departamento'];
    $sede = $row['headquarters'];


    // Obtener sedes únicas
    if (!in_array($sede, $sedes) && !empty($sede)) {
        $sedes[] = $sede;
    }

    // Obtener programas únicos
    if (!in_array($row['program'], $programas)) {
        $programas[] = $row['program'];
    }

    // Obtener modalidades únicas
    if (!in_array($row['mode'], $modalidades)) {
        $modalidades[] = $row['mode'];
    }

    // Obtener horarios únicos
    if (!in_array($row['schedules'], $horarios)) {
        $horarios[] = $row['schedules'];
    }
}

?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid px-2">
    <div class="table-responsive">

        <div class="row p-3">
            <b class="text-left mb-1"><i class="bi bi-card-checklist"></i> Seleccionar cursos</b>

            <div class="col-md-6 col-sm-12 col-lg-3">
                <div class="course-title text-indigo-dark "><i class="bi bi-laptop"></i> Bootcamp</div>
                <div class="card course-card card-bootcamp" data-icon="💻">
                    <div class="card-body">
                        <select id="bootcamp" class="form-select course-select">
                            <?php if (!empty($courses_data)): ?>
                                <?php foreach ($courses_data as $course): ?>
                                    <?php 
                                    $categoryAllowed = in_array($course['categoryid'], [14, 11, 10, 7, 6, 5]);
                                    if ($categoryAllowed):
                                    ?>
                                        <option value="<?php echo htmlspecialchars($course['id']); ?>">
                                            <?php echo htmlspecialchars($course['id'] . ' - ' . $course['fullname']); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-sm-12 col-lg-3">
                <div class="course-title text-indigo-dark"><i class="bi bi-translate"></i> Inglés nivelatorio</div>
                <div class="card course-card card-ingles" data-icon="🌍">
                    <div class="card-body">
                        <select id="ingles" class="form-select course-select">
                            <?php if (!empty($courses_data)): ?>
                                <?php foreach ($courses_data as $course): ?>
                                    <?php if ($course['categoryid'] == 4): ?>
                                        <option value="<?php echo htmlspecialchars($course['id']); ?>">
                                            <?= htmlspecialchars($course['id'] . ' - ' . $course['fullname']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-sm-12 col-lg-3">
                <div class="course-title text-indigo-dark"><i class="bi bi-code-slash"></i> English Code</div>
                <div class="card course-card card-english-code" data-icon="👨‍💻">
                    <div class="card-body">
                        <select id="english_code" class="form-select course-select">
                            <?php if (!empty($courses_data)): ?>
                                <?php foreach ($courses_data as $course): ?>
                                    <?php if ($course['categoryid'] == 12): ?>
                                        <option value="<?php echo htmlspecialchars($course['id']); ?>">
                                            <?= htmlspecialchars($course['id'] . ' - ' . $course['fullname']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-sm-12 col-lg-3">
                <div class="course-title text-indigo-dark"><i class="bi bi-lightbulb"></i> Habilidades</div>
                <div class="card course-card card-skills" data-icon="💡">
                    <div class="card-body">
                        <select id="skills" class="form-select course-select">
                            <?php if (!empty($courses_data)): ?>
                                <?php foreach ($courses_data as $course): ?>
                                    <?php if ($course['categoryid'] == 13): ?>
                                        <option value="<?php echo htmlspecialchars($course['id']); ?>">
                                            <?= htmlspecialchars($course['id'] . ' - ' . $course['fullname']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row p-3 mb-1">
            <b class="text-left mb-1"><i class="bi bi-filter-circle"></i> Filtrar beneficiario</b>

            <div class="col-md-6 col-sm-12 col-lg-3">
                <div class="filter-title"><i class="bi bi-map"></i> Departamento</div>
                <div class="card filter-card card-department" data-icon="📍">
                    <div class="card-body">
                        <select id="filterDepartment" class="form-select">
                            <option value="">Todos los departamentos</option>
                            <option value="BOYACÁ">BOYACÁ</option>
                            <option value="CUNDINAMARCA">CUNDINAMARCA</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-sm-12 col-lg-3">
                <div class="filter-title"><i class="bi bi-building"></i> Sede</div>
                <div class="card filter-card card-headquarters" data-icon="🏫">
                    <div class="card-body">
                        <select id="filterHeadquarters" class="form-select">
                            <option value="">Todas las sedes</option>
                            <?php foreach ($sedes as $sede): ?>
                                <option value="<?= htmlspecialchars($sede) ?>"><?= htmlspecialchars($sede) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-sm-12 col-lg-3">
                <div class="filter-title"><i class="bi bi-mortarboard"></i> Programa</div>
                <div class="card filter-card card-program" data-icon="🎓">
                    <div class="card-body">
                        <select id="filterProgram" class="form-select">
                            <option value="">Todos los programas</option>
                            <?php foreach ($programas as $programa): ?>
                                <option value="<?= htmlspecialchars($programa) ?>"><?= htmlspecialchars($programa) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-sm-12 col-lg-3">
                <div class="filter-title"><i class="bi bi-laptop"></i> Modalidad</div>
                <div class="card filter-card card-mode" data-icon="💻">
                    <div class="card-body">
                        <select id="filterMode" class="form-select">
                            <option value="">Todas las modalidades</option>
                            <option value="Virtual">Virtual</option>
                            <option value="Presencial">Presencial</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-sm-12 col-lg-3">
            </div>

            <div class="col-md-6 col-sm-12 col-lg-3"><br>
                <div class="filter-title"><i class="bi bi-layers"></i> Preferencia</div>
                <div class="card filter-card card-level" data-icon="⭐">
                    <div class="card-body">
                        <select id="filterLevel" class="form-select">
                            <option value="">Todos los niveles</option>
                            <option value="Explorador">Explorador</option>
                            <option value="Integrador">Integrador</option>
                            <option value="Innovador">Innovador</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-sm-12 col-lg-3"><br>
                <div class="filter-title"><i class="bi bi-clock"></i> Horario</div>
                <div class="card filter-card card-schedule" data-icon="⏰">
                    <div class="card-body">
                        <select id="filterSchedule" class="form-select">
                            <option value="">Todos los horarios</option>
                            <?php foreach ($horarios as $horario): ?>
                                <option value="<?= htmlspecialchars($horario) ?>"><?= htmlspecialchars($horario) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-sm-12 col-lg-3">
            </div>


        </div>


        <table id="listaInscritos" class="table table-hover table-bordered">
            <div class="d-flex justify-content-between align-items-center p-2 mb-2">
                <!-- Botón para exportar a Excel -->
                <button id="exportarExcel" class="btn btn-success"
                    onclick="window.location.href='components/registerMoodle/export_excel_enrolled.php?action=export'">
                    <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
                </button>

                <!-- Botón para mostrar usuarios seleccionados -->
                <button class="btn bg-magenta-dark text-white d-flex align-items-center gap-2"
                    type="button"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#selectedUsersList">
                    <i class="bi bi-list-check"></i>
                    <span>Gestionar seleccionados (<span id="floatingSelectedCount">0</span>)</span>
                </button>
                <!-- Contador de usuarios seleccionados -->
                <span id="contador" style="display: none;">0</span>
            </div>

            <thead class="thead-dark text-center">
                <tr class="text-center">
                    <th>Tipo ID</th>
                    <th>Número</th>
                    <th>Nombre</th>
                    <th>Modalidad</th>
                    <th class="text-center">
                        <i class="bi bi-patch-check-fill"></i>
                    </th>
                    <th>Email</th>
                    <th>Nuevo Email</th>
                    <th>Departamento</th>
                    <th>Sede</th>
                    <th>Programa</th>
                    <th>Preferencia</th>
                    <th>Horario</th>

                </tr>
            </thead>
            <tbody class="text-center">
                <?php foreach ($data as $row):
                    // Procesar datos del usuario
                    $firstName   = ucwords(strtolower(trim($row['first_name'])));
                    $secondName  = ucwords(strtolower(trim($row['second_name'])));
                    $firstLast   = ucwords(strtolower(trim($row['first_last'])));
                    $secondLast  = ucwords(strtolower(trim($row['second_last'])));
                    $fullName = $firstName . " " . $secondName . " " . $firstLast . " " . $secondLast;
                    $nuevoCorreo = strtolower(substr(trim($row['first_name']), 0, 1))
                        . strtolower(substr(trim($row['second_name']), 0, 1))
                        . substr(trim($row['number_id']), -4)
                        . strtolower(substr(trim($row['first_last']), 0, 1))
                        . strtolower(substr(trim($row['second_last']), 0, 1))
                        . '.ut@poliandino.edu.co';
                ?>
                    <tr data-type-id="<?php echo htmlspecialchars($row['typeID']); ?>"
                        data-number-id="<?php echo htmlspecialchars($row['number_id']); ?>"
                        data-full-name="<?php echo htmlspecialchars($fullName); ?>"
                        data-email="<?php echo htmlspecialchars($row['email']); ?>"
                        data-institutional-email="<?php echo htmlspecialchars($nuevoCorreo); ?>"
                        data-department="<?= htmlspecialchars($row['departamento']) ?>"
                        data-headquarters="<?= htmlspecialchars($row['headquarters']) ?>"
                        data-program="<?= htmlspecialchars($row['program']) ?>"
                        data-level="<?= htmlspecialchars($row['level']) ?>"
                        data-schedule="<?= htmlspecialchars($row['schedules']) ?>"
                        data-mode="<?= htmlspecialchars($row['mode']) ?>">
                        <td><?php echo htmlspecialchars($row['typeID']); ?></td>
                        <td><?php echo htmlspecialchars($row['number_id']); ?></td>
                        <td style="width: 300px; min-width: 300px; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($fullName); ?></td>
                        <td><?php echo htmlspecialchars($row['mode']); ?></td>
                        <td>
                            <input type="checkbox" class="form-check-input usuario-checkbox"
                                style="width: 25px; height: 25px; appearance: none; background-color: white; border: 2px solid #ec008c; cursor: pointer; position: relative;"
                                onclick="this.style.backgroundColor = this.checked ? 'magenta' : 'white'">
                        </td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($nuevoCorreo); ?></td>

                        <td>
                            <?php
                            $departamento = htmlspecialchars($row['departamento']);
                            if ($departamento === 'CUNDINAMARCA') {
                                echo "<button class='btn bg-lime-light w-100'><b>{$departamento}</b></button>"; // Botón verde para CUNDINAMARCA
                            } elseif ($departamento === 'BOYACÁ') {
                                echo "<button class='btn bg-indigo-light w-100'><b>{$departamento}</b></button>"; // Botón azul para BOYACÁ
                            } else {
                                echo "<span>{$departamento}</span>"; // Texto normal para otros valores
                            }
                            ?>
                        </td>
                        <td><b class="text-center"><?php echo htmlspecialchars($row['headquarters']); ?></b></td>
                        <td><?php echo htmlspecialchars($row['program']); ?></td>
                        <td><?php echo htmlspecialchars($row['level']); ?></td>
                        <td class="text-center">
                            <a class="btn bg-indigo-light"
                                tabindex="0" role="button" data-toggle="popover" data-trigger="focus" data-placement="top"
                                title="<?php echo empty($row['schedules']) ? 'Sin horario asignado' : htmlspecialchars($row['schedules']); ?>">
                                <i class="bi bi-clock-history"></i>
                            </a>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <!-- Agregar después de la tabla -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="selectedUsersList" aria-labelledby="selectedUsersListLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="selectedUsersListLabel">
                    <i class="bi bi-person-check"></i> Beneficiarios seleccionados (<span id="selectedCount">0</span>)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>

            </div>


            <div class="offcanvas-body">
                <div class="m-3">
                    <button id="enrollSelectedUsers" class="btn bg-magenta-dark text-white w-100">
                        <i class="bi bi-patch-check-fill"></i> Matricular Seleccionados
                    </button>
                </div>
                <div id="selectedUsersContainer"></div>

            </div>
        </div>


    </div>
</div>
<script>
    const selectedUsers = new Map();

    document.addEventListener("DOMContentLoaded", function() {
        // Selecciona todos los checkboxes con la clase 'usuario-checkbox'
        const checkboxes = document.querySelectorAll(".usuario-checkbox");
        const contador = document.getElementById("contador");

        function actualizarContador() {
            // Cuenta los checkboxes que están marcados
            const seleccionados = document.querySelectorAll(".usuario-checkbox:checked").length;
            contador.textContent = seleccionados;
        }

        // Agrega un evento a cada checkbox para actualizar el contador
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener("change", function() {
                const row = this.closest('tr');
                toggleUserSelection(this, row);
                actualizarContador();
            });
        });

        // Agregar evento para el checkbox "Seleccionar todos"
        const selectAllCheckbox = document.getElementById('selectAll');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.usuario-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                    const row = checkbox.closest('tr');
                    toggleUserSelection(checkbox, row);
                });
                actualizarContador();
            });
        }
    });

    // No necesitamos este evento ya que está duplicado más abajo con el ID correcto 'enrollSelectedUsers'

    function confirmBulkEnrollment(usersToEnroll) {
        if (usersToEnroll.length === 0) {
            Swal.fire('Error', 'Por favor seleccione al menos un estudiante', 'error');
            return;
        }

        Swal.fire({
            title: 'Confirmar matrícula',
            text: `¿Está seguro que desea matricular a ${usersToEnroll.length} estudiantes seleccionados?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, matricular',
            cancelButtonText: 'Cancelar'
        }).then(async (result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando matrículas',
                    html: `<div>Procesando: <b>0</b> de ${usersToEnroll.length}<br>Por favor espere...</div>`,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const result = await processEnrollments(usersToEnroll);

                Swal.fire({
                    title: 'Be',
                    html: result.message,
                    icon: result.icon,
                    confirmButtonText: 'Entendido'
                }).then(() => {
                    updateSelectedUsersList();
                });
            }
        });
    }

    async function processEnrollments(usersToEnroll) {
        const errors = [];
        let successes = 0;
        let processed = 0;
        let emailSuccesses = 0;

        for (const formData of usersToEnroll) {
            try {
                // Matrícula
                const enrollResponse = await fetch('components/registerMoodle/enroll_user_multiple.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });

                const enrollData = await enrollResponse.json();
                processed++;

                // Actualizar progreso
                const content = Swal.getHtmlContainer();
                if (content) {
                    const b = content.querySelector('b');
                    if (b) {
                        b.textContent = processed;
                    }
                }

                // Si la matrícula fue exitosa
                if (enrollData.success) {
                    try {
                        // Intentar enviar el correo
                        const emailResponse = await sendEnrollmentEmail(formData);
                        if (emailResponse.success) {
                            emailSuccesses++;
                            successes++;
                        } else {
                            // Solo registrar error de correo
                            errors.push({
                                student: formData.number_id,
                                message: `Matrícula exitosa, pero error al enviar correo: ${emailResponse.message}`,
                                type: 'email'
                            });
                            successes++; // Aún consideramos exitosa la matrícula
                        }
                    } catch (emailError) {
                        // Error al enviar correo pero matrícula exitosa
                        errors.push({
                            student: formData.number_id,
                            message: `Matrícula exitosa, pero error al enviar correo: ${emailError.message}`,
                            type: 'email'
                        });
                        successes++; // Aún consideramos exitosa la matrícula
                    }

                    // Eliminar usuario de la lista
                    selectedUsers.delete(formData.number_id);
                    updateSelectedUsersList();
                } else {
                    // Error en la matrícula
                    errors.push({
                        student: formData.number_id,
                        message: enrollData.message || 'Error desconocido en la matrícula',
                        type: 'enroll'
                    });
                }
            } catch (error) {
                processed++;
                errors.push({
                    student: formData.number_id,
                    message: 'Error de conexión o servidor',
                    type: 'server'
                });
            }
        }

        // Mensaje final más detallado
        let message = `<div class="text-start">
            <p><b>Resultados:</b></p>
            <ul>
            <li>Matrículas exitosas: ${successes} de ${usersToEnroll.length}</li>
            <li>Correos enviados: ${emailSuccesses} de ${successes}</li>
            </ul>`;

        if (errors.length > 0) {
            const enrollErrors = errors.filter(e => e.type === 'enroll').length;
            const emailErrors = errors.filter(e => e.type === 'email').length;
            const serverErrors = errors.filter(e => e.type === 'server').length;

            message += `<p><b>Resumen de errores:</b></p>
            <ul>
            ${enrollErrors > 0 ? `<li>Errores de matrícula: ${enrollErrors}</li>` : ''}
            ${emailErrors > 0 ? `<li>Errores de correo: ${emailErrors}</li>` : ''}
            ${serverErrors > 0 ? `<li>Errores de servidor: ${serverErrors}</li>` : ''}
            </ul>
            <p><b>Detalles:</b></p>`;

            errors.forEach(err => {
                message += `<p>• ${err.student}: ${err.message}</p>`;
            });
        }

        message += '</div>';

        // Mostrar alerta de matrículas
        Swal.fire({
            title: 'Resultados de Matrícula',
            html: `<div class="text-start">
            <p><b>Resultados:</b></p>
            <ul>
                <li>Matrículas exitosas: ${successes} de ${usersToEnroll.length}</li>
            </ul>
            </div>`,
            icon: successes > 0 ? 'success' : 'error',
            confirmButtonText: 'Entendido'
        }).then(() => {
            // Mostrar alerta de correos
            Swal.fire({
                title: 'Usuarios matriculados exitosamente',
                html: `<div class="text-center">
                <p><b>Resultados:</b></p>
                <ul>
                <li>Correos enviados: ${emailSuccesses} de ${successes}</li>
                </ul>
                ${errors.length > 0 ? message : ''}
            </div>`,
                icon: emailSuccesses > 0 ? 'success' : 'error',
                confirmButtonText: 'Entendido'
            }).then(() => {
                updateSelectedUsersList();
            });
        });

        if (errors.length > 0) {
            const enrollErrors = errors.filter(e => e.type === 'enroll').length;
            const emailErrors = errors.filter(e => e.type === 'email').length;
            const serverErrors = errors.filter(e => e.type === 'server').length;

            message += `<p><b>Resumen de errores:</b></p>
            <ul>
                ${enrollErrors > 0 ? `<li>Errores de matrícula: ${enrollErrors}</li>` : ''}
                ${emailErrors > 0 ? `<li>Errores de correo: ${emailErrors}</li>` : ''}
                ${serverErrors > 0 ? `<li>Errores de servidor: ${serverErrors}</li>` : ''}
            </ul>
            <p><b>Detalles:</b></p>`;

            errors.forEach(err => {
                message += `<p>• ${err.student}: ${err.message}</p>`;
            });
        }

        message += '</div>';

        return {
            success: successes > 0,
            message: message,
            icon: errors.length > 0 ? (successes > 0 ? 'warning' : 'error') : 'success'
        };
    }

    // Modificar la función getFormDataFromRow en multipleRegistrations.php
    async function getFormDataFromRow(row) {
        // Obtener datos del usuario desde el Map de usuarios seleccionados
        const numberId = row.dataset.numberId;
        const userData = selectedUsers.get(numberId) || {};

        const getCourseData = (prefix) => {
            const select = document.getElementById(prefix);
            if (!select) {
                console.error(`Select no encontrado: ${prefix}`);
                return {
                    id: '0',
                    name: 'No seleccionado'
                };
            }
            const option = select.options[select.selectedIndex];
            const fullText = option.text;
            const id = option.value;
            const name = fullText.split(' - ').slice(1).join(' - ').trim();

            return {
                id,
                name
            };
        };

        const formData = {
            type_id: userData.type_id || row.dataset.typeId,
            number_id: numberId,
            full_name: userData.full_name || row.dataset.fullName,
            email: userData.email || row.dataset.email,
            institutional_email: userData.institutional_email || row.dataset.institutionalEmail,
            department: userData.department || row.dataset.department,
            headquarters: userData.headquarters || row.dataset.headquarters,
            program: userData.program || row.dataset.program,
            mode: userData.mode || row.dataset.mode,
            password: userData.password || 'UTt@2025!',
            id_bootcamp: getCourseData('bootcamp').id,
            bootcamp_name: getCourseData('bootcamp').name,
            id_leveling_english: getCourseData('ingles').id,
            leveling_english_name: getCourseData('ingles').name,
            id_english_code: getCourseData('english_code').id,
            english_code_name: getCourseData('english_code').name,
            id_skills: getCourseData('skills').id,
            skills_name: getCourseData('skills').name
        };

        return formData;
    }

    // Modificar el evento de matrícula
    document.getElementById('enrollSelectedUsers').addEventListener('click', function() {
        if (selectedUsers.size === 0) {
            Swal.fire('Error', 'No hay usuarios seleccionados', 'error');
            return;
        }

        try {
            const usersToEnroll = Array.from(selectedUsers.values()).map(userData => {
                const row = document.querySelector(`tr[data-number-id="${userData.number_id}"]`);
                if (!row) {
                    throw new Error(`No se encontraron los datos completos para el usuario ${userData.full_name}`);
                }
                return getFormDataFromRow(row);
            });

            confirmBulkEnrollment(usersToEnroll);
        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        }
    });

    // Modificar la función toggleUserSelection para guardar todos los campos necesarios
    function toggleUserSelection(checkbox, row) {
        const numberId = row.dataset.numberId;

        if (checkbox.checked) {
            // Obtener datos de los cursos seleccionados
            const getCourseData = (prefix) => {
                const select = document.getElementById(prefix);
                if (!select) {
                    console.error(`Select no encontrado: ${prefix}`);
                    return {
                        id: '0',
                        name: 'No seleccionado'
                    };
                }
                const option = select.options[select.selectedIndex];
                return {
                    id: option.value,
                    name: option.text.split(' - ').slice(1).join(' - ').trim()
                };
            };

            const bootcamp = getCourseData('bootcamp');
            const ingles = getCourseData('ingles');
            const englishCode = getCourseData('english_code');
            const skills = getCourseData('skills');

            // Agregar usuario a la lista con todos los campos requeridos
            selectedUsers.set(numberId, {
                type_id: row.dataset.typeId,
                number_id: numberId,
                full_name: row.dataset.fullName,
                email: row.dataset.email,
                institutional_email: row.dataset.institutionalEmail,
                department: row.dataset.department,
                headquarters: row.dataset.headquarters,
                program: row.dataset.program,
                mode: row.dataset.mode,
                password: 'UTt@2025!',
                id_bootcamp: bootcamp.id,
                bootcamp_name: bootcamp.name,
                id_leveling_english: ingles.id,
                leveling_english_name: ingles.name,
                id_english_code: englishCode.id,
                english_code_name: englishCode.name,
                id_skills: skills.id,
                skills_name: skills.name
            });
        } else {
            selectedUsers.delete(numberId);
        }

        updateSelectedUsersList();
    }

    function updateSelectedUsersList() {
        const container = document.getElementById('selectedUsersContainer');
        const selectedCount = document.getElementById('selectedCount');
        const floatingSelectedCount = document.getElementById('floatingSelectedCount');
        const mainCounter = document.getElementById('contador');

        container.innerHTML = '';
        selectedUsers.forEach((userData, numberId) => {
            const userCard = document.createElement('div');
            userCard.className = 'card mb-2';
            userCard.innerHTML = `
            <div class="card-body d-flex flex-column text-center">
                <h6 class="card-title mb-2"><b>${userData.full_name}</b></h6>
                <p class="card-text mb-1">
                    <strong>ID:</strong> ${numberId}
                </p>
                <p class="card-text mb-1">
                    <strong>Email:</strong> ${userData.institutional_email}
                </p>
                <button class="btn border-0" type="button" disabled>
                        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                        <span role="status">En espera para matricular</span>
                </button>
                <button class="btn btn-danger btn-sm mt-auto">
                    <i class="bi bi-trash"></i> Eliminar selección
                </button>
            </div>
        `;
            container.appendChild(userCard);
        });

        const count = selectedUsers.size;
        selectedCount.textContent = count;
        floatingSelectedCount.textContent = count;
        mainCounter.textContent = count;
    }

    document.getElementById('enrollSelectedUsers').addEventListener('click', function() {
        if (selectedUsers.size === 0) {
            Swal.fire('Error', 'No hay usuarios seleccionados', 'error');
            return;
        }

        // Convertir el Map a un array de usuarios para procesar
        const usersToEnroll = Array.from(selectedUsers.values());
        confirmBulkEnrollment(usersToEnroll);
    });

    function removeSelectedUser(numberId) {
        selectedUsers.delete(numberId);
        // Desmarcar el checkbox en la tabla si está visible
        const checkbox = document.querySelector(`tr[data-number-id="${numberId}"] input[type="checkbox"]`);
        if (checkbox) {
            checkbox.checked = false;
        }
        updateSelectedUsersList();
    }

    // Agregar esta nueva función para enviar el correo
    async function sendEnrollmentEmail(userData) {
        try {
            const response = await fetch('components/registerMoodle/send_email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: userData.email,
                    program: userData.program,
                    first_name: userData.full_name.split(' ')[0], // Toma el primer nombre
                    usuario: userData.number_id,
                    password: userData.password
                })
            });

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error enviando email:', error);
            return {
                success: false,
                message: 'Error enviando el correo electrónico'
            };
        }
    }

    $(document).ready(function() {
        $('[data-toggle="popover"]').popover({
            placement: 'top',
            trigger: 'focus',
            html: true
        });
    });
</script>
</body>

</html>