<?php
// Habilitar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Asegurarse de que la conexión a la base de datos esté configurada
if (!isset($conn) || !$conn) {
    die('Error: La conexión a la base de datos no está configurada.');
}

// Inicializar la variable de mensaje
$mensajeToast = '';

// Actualizar estado de la propiedad si se envió el formulario
if (isset($_POST['btnActualizarEstado'])) {
    $codigo = $_POST['codigo']; // Obtener el código de la propiedad desde el formulario
    $nuevoEstado = $_POST['nuevoEstado']; // Obtener el nuevo estado

    // Consulta SQL para actualizar el estado
    $updateSql = "UPDATE user_register SET status = ? WHERE number_id = ?";
    $stmt = $conn->prepare($updateSql);

    // Usar bind_param correctamente con tipos: 's' para string y 'i' para entero
    $stmt->bind_param('si', $nuevoEstado, $codigo); // Preparar la consulta para ejecutar

    if ($stmt->execute()) {
        $mensajeToast = 'Estado actualizado correctamente.';
    } else {
        $mensajeToast = 'Error al actualizar el estado.';
    }
}

// Consulta SQL para obtener los datos
$sql = "SELECT user_register.*, municipios.municipio, departamentos.departamento
        FROM user_register
        INNER JOIN municipios ON user_register.municipality = municipios.id_municipio
        INNER JOIN departamentos ON user_register.department = departamentos.id_departamento
        WHERE departamentos.id_departamento IN (15, 25)
        AND user_register.status = '1' 
        ORDER BY user_register.first_name ASC";

$result = $conn->query($sql);

// Si la consulta tiene resultados, generar los datos
$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Agregar acciones a cada fila
        $row['acciones'] = '
        <td><button class="btn bg-lime-dark "><i class="bi bi-eye-fill"></i></button></td>
        <td>
            <form method="POST" class="d-inline" onsubmit="return confirmarActualizacion();">
                <input type="hidden" name="codigo" value="' . htmlspecialchars($row["number_id"]) . '">
                <div class="input-group">
                    <select class="form-control" name="nuevoEstado" required>
                        <option value="1" ' . ($row["status"] == 'NUEVO' ? 'selected' : '') . '>NUEVO</option>
                        <option value="2" ' . ($row["status"] == 'ACEPTADO' ? 'selected' : '') . '>ACEPTADO</option>
                        <option value="3" ' . ($row["status"] == 'DENEGADO' ? 'selected' : '') . '>DENEGADO</option>
                    </select>
                    <div class="input-group-append">
                        <button type="submit" name="btnActualizarEstado" class="btn bg-indigo-dark text-white ">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                    </div>
                </div>
            </form>
        </td>';

        // Concatenar el nombre y el apellido
        $fullName = $row['first_name'] . ' ' . $row['second_name'] . ' ' . $row['first_last'] . ' ' . $row['second_last'];
        // Calcular la edad
        $birthday = new DateTime($row['birthdate']);
        $now = new DateTime();
        $age = $now->diff($birthday)->y; // Obtener la diferencia de años

        // Guardar fila de datos
        $row['age'] = $age; // Agregar la edad al array de datos

        // Guardar fila de datos
        $data[] = $row;
    }
} else {
    // Si no hay datos, mostrar mensaje
    echo '<div class="alert alert-info">No hay datos disponibles.</div>';
}
?>

<!-- Tabla -->
<div class="table-responsive">
    <table id="listaInscritos" class="table table-hover table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Tipo ID</th>
                <th>Número ID</th>
                <th>Nombre Completo</th>
                <th>Edad</th>
                <th>Correo</th>
                <th>Teléfono 1</th>
                <th>Teléfono 2</th>
                <th>Medio de contacto</th>
                <th>Programa</th>
                <th>Departamento</th>
                <th>Municipio</th>
                <th>Dirección</th>
                <th>Estado actual</th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['typeID']); ?></td>
                    <td><?php echo htmlspecialchars($row['number_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['second_name']) . ' ' . htmlspecialchars($row['first_last']) . ' ' . htmlspecialchars($row['second_last']); ?></td>
                    <td>
                        <?php
                        // Si la edad es mayor a 17, mostrar el botón con la edad
                        if ($row['age'] > 17) {
                            echo '<button class="btn bg-magenta-dark text-white">' . $row['age'] . '</button>';
                        } else {
                            // Si no, solo mostrar la edad como texto
                            echo htmlspecialchars($row['age']);
                        }
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['first_phone']) . ' / '; ?></td>
                    <td><?php echo htmlspecialchars($row['second_phone']); ?></td>
                    <?php include("components/registrationsContact/updateContactMedium.php"); ?>
                    <td><?php echo htmlspecialchars($row['program']); ?></td>
                    <td><?php echo htmlspecialchars($row['departamento']); ?></td>
                    <td><?php echo htmlspecialchars($row['municipio']); ?></td>
                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                    <td>
                        <?php
                        // Mapeo de los valores del estado
                        switch ($row['status']) {
                            case '1':
                                echo '<button class="btn bg-orange-dark"><i class="bi bi-star-fill"></i> NUEVO</button>';
                                break;
                            case '2':
                                echo '<button class="btn bg-teal-dark text-white"><i class="bi bi-check2-circle"></i> ACEPTADO</button>';
                                break;
                            case '3':
                                echo '<button class="btn bg-red-dark text-white"><i class="bi bi-x-circle"></i> DENEGADO</button>';
                                break;
                            default:
                                echo 'Estado desconocido'; // En caso de que haya un valor inesperado
                                break;
                        }
                        ?>
                    </td>
                    <?php echo $row['acciones']; ?>
                </tr>


            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Toastr CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    $(document).ready(function() {
        // Inicialización de la tabla
        $('#propiedadesVenta').DataTable({
            responsive: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            }
        });

        // Mostrar mensaje de toast si existe
        <?php if ($mensajeToast): ?>
            toastr.success("<?php echo $mensajeToast; ?>");
        <?php endif; ?>
    });

    // Función de confirmación de actualización
    function confirmarActualizacion() {
        return confirm("¿Está seguro de que desea actualizar el estado de este usuario?");
    }
</script>