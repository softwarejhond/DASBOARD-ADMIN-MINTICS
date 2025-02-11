<?php
// Conexión a la base de datos ya establecida desde el main

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
          AND user_register.statusAdmin = '2'
        ORDER BY user_register.first_name ASC";

$result = $conn->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Procesar datos del usuario según sea necesario
        $data[] = $row;
    }
} else {
    echo '<div class="alert alert-info">No hay datos disponibles.</div>';
}
?>

<div class="table-responsive">
    <button id="exportarExcel" class="btn btn-success mb-3"
        onclick="window.location.href='components/listRegistrationsAcept/export_excel_admitted.php?action=export'">
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
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['typeID']); ?></td>
                    <td><?php echo htmlspecialchars($row['number_id']); ?></td>
                    <td>
                        <?php
                        // Convertir cada parte del nombre a minúsculas, quitar espacios y luego poner en mayúscula la primera letra de cada palabra
                        $firstName   = ucwords(strtolower(trim($row['first_name'])));
                        $secondName  = ucwords(strtolower(trim($row['second_name'])));
                        $firstLast   = ucwords(strtolower(trim($row['first_last'])));
                        $secondLast  = ucwords(strtolower(trim($row['second_last'])));
                        echo htmlspecialchars($firstName . " " . $secondName . " " . $firstLast . " " . $secondLast);
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    
                    <!-- Nuevo campo de correo generado automáticamente -->
                    <td>
                        <?php
                        $nombre  = strtolower(trim($row['first_name']));
                        $apellido = strtolower(trim($row['first_last']));
                        $idNumber = trim($row['number_id']);
                        $ultimosDigitos = substr($idNumber, -4);
                        $nuevoCorreo = $nombre . '.' . $apellido . '.' . $ultimosDigitos . '.ut@poliandino.edu.co';
                        echo htmlspecialchars($nuevoCorreo);
                        ?>
                    </td>

                    <!-- Columna BootCamp -->
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

                    <!-- Columna Inglés Nivelatorio (mismos datos que BootCamp) -->
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

                    <!-- Columna English Code (mismos datos que BootCamp) -->
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

                    <!-- Columna Habilidades (mismos datos que BootCamp) -->
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

                    <!-- Columna Registrar: botón para confirmar la matrícula -->
                    <td>
                        <button class="btn btn-primary" onclick="confirmEnrollment('<?php echo $row['number_id']; ?>')">
                            Matricular
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Cargar SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Función que muestra la confirmación de matrícula usando SweetAlert2
function confirmEnrollment(studentId) {
    Swal.fire({
        title: 'Confirmar matrícula',
        text: "¿Está seguro que desea matricular este estudiante en los cursos seleccionados?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, matricular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Aquí puedes agregar la lógica para matricular al estudiante, por ejemplo, mediante una llamada AJAX.
            Swal.fire(
                'Matriculado',
                'El estudiante ha sido matriculado en los cursos seleccionados.',
                'success'
            );
        }
    });
}
</script>

<!-- Notificación de actualización con SweetAlert2 (opcional) -->
<script>
    Swal.fire({
        icon: 'info',
        title: 'Actualizando información...',
        text: 'Por favor, espere un momento.',
        position: 'center',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
    });
</script>
