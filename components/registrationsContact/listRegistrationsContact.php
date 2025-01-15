<?php
// Mantén el código PHP para la conexión y demás operaciones
$mensajeToast = ''; // Mensaje para el toast

// Procesamiento de la actualización de estado
if (isset($_POST['btnActualizarEstado'])) {
    $codigo = $_POST['codigo'];
    $nuevoEstado = $_POST['nuevoEstado'];

    // Actualización en la base de datos
    $updateSql = "UPDATE user_register SET status = ? WHERE number_id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param('si', $nuevoEstado, $codigo);

    if ($stmt->execute()) {
        $mensajeToast = 'Estado actualizado correctamente.';
    } else {
        $mensajeToast = 'Error al actualizar el estado.';
    }
}

// Obtener los datos
$sql = "SELECT user_register.*, municipios.municipio, departamentos.departamento
        FROM user_register
        INNER JOIN municipios ON user_register.municipality = municipios.id_municipio
        INNER JOIN departamentos ON user_register.department = departamentos.id_departamento
        WHERE departamentos.id_departamento IN (15, 25)
        AND user_register.status = '1' 
        ORDER BY user_register.first_name ASC";

$result = $conn->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['acciones'] = '
    <td>
    <form id="formActualizacionEstado' . $row["number_id"] . '" class="d-inline" method="POST" onsubmit="return actualizarEstado(' . $row["number_id"] . ', event);">
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

        // Calcular edad
        $birthday = new DateTime($row['birthdate']);
        $now = new DateTime();
        $age = $now->diff($birthday)->y;
        $row['age'] = $age;

        $data[] = $row;
    }
} else {
    echo '<div class="alert alert-info">No hay datos disponibles.</div>';
}

?>

<div class="table-responsive">
    <table id="listaInscritos" class="table table-hover table-bordered">
        <thead class="thead-dark">
            <tr class="text-center">
                <th>Tipo ID</th>
                <th>Número</th>
                <th>Nombre Completo</th>
                <th>Edad</th>
                <th>Correo</th>
                <th>Teléfono principal</th>
                <th>Teléfono secundario</th>
                <th>Medio de contacto</th>
                <th>Contacto de emergencia</th>
                <th>Teléfono del contacto</th>
                <th>Nacionalidad</th>
                <th>Departamento</th>
                <th>municipio</th>
                <th>Ocupación</th>
                <th>Tiempo de obligaciones</th>
                <th>Sede de elección</th>
                <th>Programa de interes</th>
                <th>Horario</th>
                <th>Dispositivo</th>
                <th>Estado</th>
                <th>
                    <button type="button" class="btn bg-magenta-dark text-white" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Cambiar medio de contacto">
                        <i class="bi bi-toggles"></i>
                    </button>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['typeID']); ?></td>
                    <td><?php echo htmlspecialchars($row['number_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['second_name']) . ' ' . htmlspecialchars($row['first_last']) . ' ' . htmlspecialchars($row['second_last']); ?></td>
                    <td><?php echo $row['age']; ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['first_phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['second_phone']); ?></td>
                    <td id="medioContacto_<?php echo $row['number_id']; ?>">
                    <?php
// Asigna la clase y el ícono según el valor de 'contactMedium'
$btnClass = '';
$btnText = htmlspecialchars($row['contactMedium']); // El texto que aparecerá en la tooltip
$icon = ''; // Ícono correspondiente

if ($row['contactMedium'] === 'WhatsApp') {
    $btnClass = 'btn bg-lime-dark text-white'; // Verde para WhatsApp
    $icon = '<i class="bi bi-whatsapp"></i>'; // Ícono de WhatsApp
} elseif ($row['contactMedium'] === 'Teléfono') {
    $btnClass = 'btn bg-teal-dark text-white'; // Azul para Teléfono
    $icon = '<i class="bi bi-telephone"></i>'; // Ícono de Teléfono
} elseif ($row['contactMedium'] === 'Correo') {
    $btnClass = 'btn bg-orange-light'; // Amarillo para Correo
    $icon = '<i class="bi bi-envelope"></i>'; // Ícono de Correo
} else {
    $btnClass = 'btn btn-secondary'; // Clase genérica si no coincide
    $icon = '<i class="bi bi-question-circle"></i>'; // Ícono genérico
    $btnText = 'Desconocido'; // Texto genérico
}

// Mostrar el botón con la clase, ícono y tooltip correspondientes
echo '<button type="button" class="'.$btnClass.'" data-bs-toggle="tooltip" data-bs-placement="top" title="'.$btnText.'">'
    .$icon.
    '</button>';
?>




                    </td>

                    <td><?php echo htmlspecialchars($row['emergency_contact_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['emergency_contact_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['nationality']); ?></td>
                    <td>
                        <?php
                        $departamento = htmlspecialchars($row['departamento']);
                        if ($departamento === 'CUNDINAMARCA') {
                            echo '<button class="btn btn-info w-100">' . $departamento . '</button>'; // Botón verde para CUNDINAMARCA
                        } elseif ($departamento === 'BOYACÁ') {
                            echo '<button class="btn bg-indigo-light w-100">' . $departamento . '</button>'; // Botón azul para BOYACÁ
                        } else {
                            echo '<span>' . $departamento . '</span>'; // Texto normal para otros valores
                        }
                        ?>
                    </td>

                    <td><?php echo htmlspecialchars($row['municipio']); ?></td>
                    <td><?php echo htmlspecialchars($row['occupation']); ?></td>
                    <td><?php echo htmlspecialchars($row['time_obligations']); ?></td>
                    <td><?php echo htmlspecialchars($row['headquarters']); ?></td>
                    <td><?php echo htmlspecialchars($row['program']); ?></td>
                    <td class="text-center">
                        <button type="button" class="btn bg-indigo-light"
                            data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-custom-class="custom-tooltip"
                            data-bs-title="<?php echo htmlspecialchars($row['schedules']); ?>">
                            <i class="bi bi-clock-history"></i>
                        </button>
                    </td>
                    <?php
                    // Asigna la clase, ícono y texto del tooltip según el valor de 'technologies'
                    $btnClass = '';
                    $btnText = htmlspecialchars($row['technologies']); // El texto que aparecerá en la tooltip
                    $icon = ''; // Ícono correspondiente

                    if ($row['technologies'] === 'computador') {
                        $btnClass = 'bg-indigo-dark text-white'; // Clase para computador
                        $icon = '<i class="bi bi-laptop"></i>'; // Ícono de computador
                    } elseif ($row['technologies'] === 'smartphone') {
                        $btnClass = 'bg-teal-dark text-white'; // Clase para smartphone
                        $icon = '<i class="bi bi-phone"></i>'; // Ícono de smartphone
                    } elseif ($row['technologies'] === 'tablet') {
                        $btnClass = 'bg-amber-light text-white'; // Clase para tablet
                        $icon = '<i class="bi bi-tablet"></i>'; // Ícono de tablet
                    } else {
                        $btnClass = 'btn-secondary'; // Clase genérica si no coincide
                        $icon = '<i class="bi bi-question-circle"></i>'; // Ícono genérico
                    }

                    // Mostrar el botón con la clase, ícono y tooltip correspondientes
                    echo '<td class="text-center">
        <button class="btn ' . $btnClass . '" data-bs-toggle="tooltip" data-bs-placement="top" 
        data-bs-custom-class="custom-tooltip" data-bs-title="' . $btnText . '">
            ' . $icon . '
        </button>
      </td>';
                    ?>

                    <td>
                        <?php
                        // Mostrar el estado
                        switch ($row['status']) {
                            case '1':
                                echo '<button class="btn bg-orange-dark w-100"> NUEVO</button>';
                                break;
                            case '2':
                                echo '<button class="btn bg-teal-dark text-white w-100">ACEPTADO</button>';
                                break;
                            case '3':
                                echo '<button class="btn bg-red-dark text-white w-100">DENEGADO</button>';
                                break;
                            default:
                                echo 'Estado desconocido';
                                break;
                        }
                        ?>
                    </td>
                    <td>
                        <button class="btn bg-magenta-dark text-white" onclick="mostrarModalActualizar(<?php echo $row['number_id']; ?>)" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-custom-class="custom-tooltip"
                            data-bs-title="Cambiar medio de contacto">
                            <i class="bi bi-arrow-left-right"></i></button>
                    </td>

                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function mostrarModalActualizar(id) {
        // Remover cualquier modal previo del DOM
        $('#modalActualizar_' + id).remove();

        // Crear el modal dinámicamente con un identificador único
        const modalHtml = `
    <div id="modalActualizar_${id}" class="modal fade"  aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-indigo-dark">
                    <h5 class="modal-title text-center"><i class="bi bi-arrow-left-right"></i> Actualizar Medio de Contacto</h5>
                      <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
            
                </div>
                <div class="modal-body">
                    <form id="formActualizarMedio_${id}">
                        <div class="form-group">
                            <label for="nuevoMedio_${id}">Seleccionar nuevo medio de contacto:</label>
                            <select class="form-control" id="nuevoMedio_${id}" name="nuevoMedio" required>
                                <option value="Correo">Correo</option>
                                <option value="Teléfono">Teléfono</option>
                                <option value="WhatsApp">WhatsApp</option>
                            </select>
                        </div>
                        <br>
                        <input type="hidden" name="id" value="${id}">
                        <button type="submit" class="btn bg-indigo-dark text-white">Actualizar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    `;

        // Añadir el modal al DOM
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Mostrar el modal
        $('#modalActualizar_' + id).modal('show');

        // Manejar el envío del formulario con confirmación
        $('#formActualizarMedio_' + id).on('submit', function(e) {
            e.preventDefault();

            if (confirm("¿Está seguro de que desea actualizar el medio de contacto?")) {
                const nuevoMedio = $('#nuevoMedio_' + id).val();
                actualizarMedioContacto(id, nuevoMedio);
                $('#modalActualizar_' + id).modal('hide');
            } else {
                toastr.info("La actualización ha sido cancelada.");
            }
        });
    }


    function actualizarMedioContacto(id, nuevoMedio) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "components/registrationsContact/actualizar_medio_contacto.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                const response = xhr.responseText;
                console.log("Respuesta del servidor: " + response);

                if (response == "success") {
                    const result = getBtnClass(nuevoMedio);
                    const botonHtml = `<button class="btn ${result.btnClass}">${result.icon} ${nuevoMedio}</button>`;

                    // Actualizar solo el botón específico
                    document.querySelector("#medioContacto_" + id).innerHTML = botonHtml;

                    toastr.success("El medio de contacto se actualizó correctamente.");
                } else {
                    toastr.error("Hubo un error al actualizar el medio de contacto.");
                }
            }
        };
        xhr.send("id=" + id + "&nuevoMedio=" + encodeURIComponent(nuevoMedio));
    }


    // Función para obtener la clase del botón según el medio de contacto
    function getBtnClass(medio) {
        let btnClass = '';
        let icon = '';

        if (medio == 'WhatsApp') {
            btnClass = 'bg-lime-dark w-100';
            icon = '<i class="bi bi-whatsapp"></i>';
        } else if (medio == 'Teléfono') {
            btnClass = 'bg-teal-dark w-100';
            icon = '<i class="bi bi-telephone"></i>';
        } else if (medio == 'Correo') {
            btnClass = 'bg-amber-light w-100';
            icon = '<i class="bi bi-envelope"></i>';
        }

        return {
            btnClass,
            icon
        };
    }

    <?php if ($mensajeToast): ?>
        toastr.success("<?php echo $mensajeToast; ?>");
    <?php endif; ?>
</script>
<script></script>


<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>