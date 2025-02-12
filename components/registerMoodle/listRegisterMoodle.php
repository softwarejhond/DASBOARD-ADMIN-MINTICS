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
$sql = "SELECT user_register.*, municipios.municipio, departamentos.departamento
        FROM user_register
        INNER JOIN municipios ON user_register.municipality = municipios.id_municipio
        INNER JOIN departamentos ON user_register.department = departamentos.id_departamento
        WHERE departamentos.id_departamento IN (15, 25)
          AND user_register.status = '1' 
          AND user_register.statusAdmin = '1'
          AND user_register.statusMoodle = '0'
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
?>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid px-2 mt-5">
    <div class="table-responsive">
        <button id="exportarExcel" class="btn btn-success mb-3"
            onclick="window.location.href='components/registerMoodle/export_excel_enrolled.php?action=export'">
            <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
        </button>
        <table id="listaInscritos" class="table table-hover table-bordered">
            <thead class="thead-dark text-center">
                <tr class="text-center">
                    <th>Tipo ID</th>
                    <th>Número</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Nuevo Email</th>
                    <th>BootCamp</th>
                    <th>Inglés Nivelatorio</th>
                    <th>English Code</th>
                    <th>Habilidades</th>
                    <th>Registrar</th>
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
                    $nuevoCorreo = strtolower(trim($row['first_name'])) . '.' . strtolower(trim($row['first_last'])) . '.' . substr(trim($row['number_id']), -4) . '.ut@poliandino.edu.co';
                ?>
                <tr data-type-id="<?php echo htmlspecialchars($row['typeID']); ?>"
                    data-number-id="<?php echo htmlspecialchars($row['number_id']); ?>"
                    data-full-name="<?php echo htmlspecialchars($fullName); ?>"
                    data-email="<?php echo htmlspecialchars($row['email']); ?>"
                    data-institutional-email="<?php echo htmlspecialchars($nuevoCorreo); ?>">
                    <td><?php echo htmlspecialchars($row['typeID']); ?></td>
                    <td><?php echo htmlspecialchars($row['number_id']); ?></td>
                    <td style="width: 300px; min-width: 300px; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($fullName); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($nuevoCorreo); ?></td>
                    <td>
                        <select name="bootcamp_<?php echo $row['number_id']; ?>" class="form-select">
                            <?php if (!empty($courses_data)): ?>
                                <?php foreach ($courses_data as $course): ?>
                                    <option value="<?php echo htmlspecialchars($course['id']); ?>">
                                        <?php echo htmlspecialchars($course['id'] . ' - ' . $course['fullname']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option>No hay cursos</option>
                            <?php endif; ?>
                        </select>
                    </td>
                    <td>
                        <select name="ingles_<?php echo $row['number_id']; ?>" class="form-select">
                            <?php if (!empty($courses_data)): ?>
                                <?php foreach ($courses_data as $course): ?>
                                    <option value="<?php echo htmlspecialchars($course['id']); ?>">
                                        <?php echo htmlspecialchars($course['id'] . ' - ' . $course['fullname']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option>No hay cursos</option>
                            <?php endif; ?>
                        </select>
                    </td>
                    <td>
                        <select name="english_code_<?php echo $row['number_id']; ?>" class="form-select">
                            <?php if (!empty($courses_data)): ?>
                                <?php foreach ($courses_data as $course): ?>
                                    <option value="<?php echo htmlspecialchars($course['id']); ?>">
                                        <?php echo htmlspecialchars($course['id'] . ' - ' . $course['fullname']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option>No hay cursos</option>
                            <?php endif; ?>
                        </select>
                    </td>
                    <td>
                        <select name="skills_<?php echo $row['number_id']; ?>" class="form-select">
                            <?php if (!empty($courses_data)): ?>
                                <?php foreach ($courses_data as $course): ?>
                                    <option value="<?php echo htmlspecialchars($course['id']); ?>">
                                        <?php echo htmlspecialchars($course['id'] . ' - ' . $course['fullname']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option>No hay cursos</option>
                            <?php endif; ?>
                        </select>
                    </td>
                    <td>
                        <button class="btn btn-primary" onclick="confirmEnrollment(event, '<?php echo $row['number_id']; ?>')">
                            Matricular
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function confirmEnrollment(event, studentId) {
    event.preventDefault();
    const button = event.target;
    const row = button.closest('tr');
    
    // Obtener datos del usuario
    const typeId = row.dataset.typeId;
    const numberId = row.dataset.numberId;
    const fullName = row.dataset.fullName;
    const email = row.dataset.email;
    const institutionalEmail = row.dataset.institutionalEmail;
    
    // Obtener cursos seleccionados
    const getCourseData = (select) => {
    const option = select.options[select.selectedIndex];
    const fullText = option.text;
    const id = option.value;
    // Obtener todo el texto después del primer guion
    const name = fullText.split(' - ').slice(1).join(' - ').trim();
    return { id: id, name: name };
};
    
    const bootcamp = getCourseData(row.querySelector(`select[name="bootcamp_${studentId}"]`));
    const ingles = getCourseData(row.querySelector(`select[name="ingles_${studentId}"]`));
    const englishCode = getCourseData(row.querySelector(`select[name="english_code_${studentId}"]`));
    const skills = getCourseData(row.querySelector(`select[name="skills_${studentId}"]`));
    
    // Generar contraseña (número de ID)
    const password = 'UTt@2025!';

    // Preparar datos para enviar
    const formData = {
        type_id: typeId,
        number_id: numberId,
        full_name: fullName,
        email: email,
        institutional_email: institutionalEmail,
        password: password,
        id_bootcamp: bootcamp.id,
        bootcamp_name: bootcamp.name,
        id_leveling_english: ingles.id,
        leveling_english_name: ingles.name,
        id_english_code: englishCode.id,
        english_code_name: englishCode.name,
        id_skills: skills.id,
        skills_name: skills.name
    };

    Swal.fire({
        title: 'Confirmar matrícula',
        text: "¿Está seguro que desea matricular este estudiante?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, matricular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('components/registerMoodle/enroll_user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: data.message,
                        showConfirmButton: true
                    }).then(() => {
                        // Recargar la página para actualizar la lista
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Error de conexión', 'error');
            });
        }
    });
}
</script>
</body>
</html>