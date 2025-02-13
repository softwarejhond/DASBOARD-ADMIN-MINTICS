<?php
// Conexión a la base de datos ya establecida desde el main
require_once 'conexion.php'; // Asegúrate de incluir la conexión a la BD

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
}

?>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid px-2 mt-5">
    <div class="table-responsive">

        <div class="row mb-3 g-3">
            <h3><i class="bi bi-card-checklist"></i> Seleccionar cursos</h3>

            <div class="col-md-3">
                <h5>Bootcamp:</h5>
                <select id="bootcamp" class="form-select course-select">
                    <?php if (!empty($courses_data)): ?>
                        <?php foreach ($courses_data as $course): ?>
                            <option value="<?php echo htmlspecialchars($course['id']); ?>">
                                <?php echo htmlspecialchars($course['id'] . ' - ' . $course['fullname']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-md-3">
                <h5>Ingles nivelatorio:</h5>
                <select id="ingles" class="form-select course-select">
                    <?php if (!empty($courses_data)): ?>
                        <?php foreach ($courses_data as $course): ?>
                            <option value="<?php echo htmlspecialchars($course['id']); ?>">
                                <?php echo htmlspecialchars($course['id'] . ' - ' . $course['fullname']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-md-3">
                <h5>English code:</h5>
                <select id="english_code" class="form-select course-select">
                    <?php if (!empty($courses_data)): ?>
                        <?php foreach ($courses_data as $course): ?>
                            <option value="<?php echo htmlspecialchars($course['id']); ?>">
                                <?php echo htmlspecialchars($course['id'] . ' - ' . $course['fullname']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-md-3">
                <h5>Habilidades:</h5>
                <select id="skills" class="form-select course-select">
                    <?php if (!empty($courses_data)): ?>
                        <?php foreach ($courses_data as $course): ?>
                            <option value="<?php echo htmlspecialchars($course['id']); ?>">
                                <?php echo htmlspecialchars($course['id'] . ' - ' . $course['fullname']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <hr>

        <div class="row mb-3 g-3">
            <h3><i class="bi bi-filter-circle"></i> Filtrar</h3>
            <div class="col-md-3">
                <select id="filterDepartment" class="form-select">
                    <option value="">Todos los departamentos</option>
                    <option value="BOYACÁ">BOYACÁ</option>
                    <option value="CUNDINAMARCA">CUNDINAMARCA</option>
                </select>
            </div>

            <div class="col-md-3">
                <select id="filterHeadquarters" class="form-select">
                    <option value="">Todas las sedes</option>
                    <?php foreach ($sedes as $sede): ?>
                        <option value="<?= htmlspecialchars($sede) ?>"><?= htmlspecialchars($sede) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <select id="filterProgram" class="form-select">
                    <option value="">Todos los programas</option>
                    <?php foreach ($programas as $programa): ?>
                        <option value="<?= htmlspecialchars($programa) ?>"><?= htmlspecialchars($programa) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <select id="filterMode" class="form-select">
                    <option value="">Todas las modalidades</option>
                    <option value="Virtual">Virtual</option>
                    <option value="Presencial">Presencial</option>
                </select>
            </div>
        </div>

        <table id="listaInscritos" class="table table-hover table-bordered">

            <button id="matricularSeleccionados" class="btn btn-primary mb-3">
                <i class="bi bi-people-fill"></i> Matricular Seleccionados
            </button>
            <br>
            <button id="exportarExcel" class="btn btn-success mb-3"
                onclick="window.location.href='components/registerMoodle/export_excel_enrolled.php?action=export'">
                <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
            </button>

            <thead class="thead-dark text-center">
                <tr class="text-center">
                    <th>Tipo ID</th>
                    <th>Número</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Nuevo Email</th>
                    <th>Departamento</th>
                    <th>Sede</th>
                    <th>Programa</th>
                    <th>Modalidad</th>
                    <th>Seleccionar</th>
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
                        data-mode="<?= htmlspecialchars($row['mode']) ?>">
                        <td><?php echo htmlspecialchars($row['typeID']); ?></td>
                        <td><?php echo htmlspecialchars($row['number_id']); ?></td>
                        <td style="width: 300px; min-width: 300px; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($fullName); ?></td>
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
                        <td><?php echo htmlspecialchars($row['mode']); ?></td>
                        <td>
                            <input type="checkbox" class="form-check-input" style="width: 25px; height: 25px;" name="" id="">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.getElementById('matricularSeleccionados').addEventListener('click', confirmBulkEnrollment);

    function confirmBulkEnrollment(event) {
        const selectedCheckboxes = document.querySelectorAll('#listaInscritos tbody input[type="checkbox"]:checked');
        if (selectedCheckboxes.length === 0) {
            Swal.fire('Error', 'Por favor seleccione al menos un estudiante', 'error');
            return;
        }

        Swal.fire({
            title: 'Confirmar matrícula',
            text: `¿Está seguro que desea matricular a ${selectedCheckboxes.length} estudiantes seleccionados?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, matricular',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const promises = [];
                const errors = [];
                let successes = 0;

                selectedCheckboxes.forEach(checkbox => {
                    const row = checkbox.closest('tr');
                    const formData = getFormDataFromRow(row);

                    const promise = fetch('components/registerMoodle/enroll_user.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(formData)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                successes++;
                            } else {
                                errors.push({
                                    student: formData.number_id,
                                    message: data.message || 'Error desconocido'
                                });
                            }
                        })
                        .catch(error => {
                            errors.push({
                                student: formData.number_id,
                                message: 'Error de conexión o servidor'
                            });
                        });

                    promises.push(promise);
                });

                Promise.all(promises).then(() => {
                    let message = `Matrículas completadas: ${successes} exitosas.`;
                    if (errors.length > 0) {
                        message += `<br>Errores (${errors.length}):<br>`;
                        message += errors.map(err => `• ${err.student}: ${err.message}`).join('<br>');
                    }
                    Swal.fire({
                        title: 'Resultado',
                        html: message,
                        icon: errors.length ? 'warning' : 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        if (successes > 0) {
                            location.reload();
                        }
                    });
                });
            }
        });
    }

    function getFormDataFromRow(row) {
        const studentId = row.dataset.numberId;

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

            console.log(`${prefix}:`, {
                id,
                name
            }); // Debug
            return {
                id,
                name
            };
        };

        const formData = {
            type_id: row.dataset.typeId,
            number_id: studentId,
            full_name: row.dataset.fullName,
            email: row.dataset.email,
            institutional_email: row.dataset.institutionalEmail,
            department: row.dataset.department,
            headquarters: row.dataset.headquarters,
            program: row.dataset.program,
            mode: row.dataset.mode,
            password: 'UTt@2025!',
            id_bootcamp: getCourseData('bootcamp').id,
            bootcamp_name: getCourseData('bootcamp').name,
            id_leveling_english: getCourseData('ingles').id,
            leveling_english_name: getCourseData('ingles').name,
            id_english_code: getCourseData('english_code').id,
            english_code_name: getCourseData('english_code').name,
            id_skills: getCourseData('skills').id,
            skills_name: getCourseData('skills').name
        };

        console.log('FormData:', formData); // Debug
        return formData;
    }
</script>
</body>

</html>