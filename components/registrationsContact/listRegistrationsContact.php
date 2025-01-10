<?php
// Mantén el código PHP para la conexión y demás operaciones

// Inicializar la variable de mensaje
$mensajeToast = '';

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
                <th><h2 type="button" 
                        data-bs-toggle="tooltip" data-bs-placement="top"
                        data-bs-custom-class="custom-tooltip"
                        data-bs-title="Cambiar medio de contacto">
                        <i class="bi bi-arrow-left-right"></i>
                    </h2></th>
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
                        $btnText = htmlspecialchars($row['contactMedium']); // El texto del botón
                        $icon = ''; // Variable para el ícono

                        if ($row['contactMedium'] == 'WhatsApp') {
                            $btnClass = 'bg-lime-dark'; // Verde para WhatsApp
                            $icon = '<i class="bi bi-whatsapp"></i>'; // Ícono de WhatsApp
                        } elseif ($row['contactMedium'] == 'Teléfono') {
                            $btnClass = 'bg-teal-dark'; // Azul para Teléfono
                            $icon = '<i class="bi bi-telephone"></i>'; // Ícono de Teléfono
                        } elseif ($row['contactMedium'] == 'Correo') {
                            $btnClass = 'bg-amber-light'; // Amarillo para Correo
                            $icon = '<i class="bi bi-envelope"></i>'; // Ícono de Correo
                        }

                        // Mostrar el botón con la clase y el ícono correspondiente
                        echo '<button class="btn ' . $btnClass . '">' . $icon . ' ' . $btnText . '</button>';
                        ?>
                    </td>

                    <td><?php echo htmlspecialchars($row['program']); ?></td>
                    <td><?php echo htmlspecialchars($row['departamento']); ?></td>
                    <td><?php echo htmlspecialchars($row['municipio']); ?></td>
                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                    <td>
                        <?php
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
                                echo 'Estado desconocido';
                                break;
                        }
                        ?>
                    </td>
                    <td>
                        <button class="btn btn-primary" onclick="mostrarModalActualizar(<?php echo $row['number_id']; ?>)">Actualizar Medio de Contacto</button>
                    </td>

                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
   function mostrarModalActualizar(id) {
    // Crear el modal dinámicamente con un select de opciones
    const modalHtml = `
    <div id="modalActualizar" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Actualizar Medio de Contacto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formActualizarMedio">
                        <div class="form-group">
                            <label for="nuevoMedio">Seleccionar nuevo medio de contacto:</label>
                            <select class="form-control" id="nuevoMedio" name="nuevoMedio" required>
                                <option value="Correo">Correo</option>
                                <option value="Teléfono">Teléfono</option>
                                <option value="WhatsApp">WhatsApp</option>
                            </select>
                        </div>
                        <input type="hidden" name="id" value="${id}">
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
`;

    // Añadir el modal al DOM
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Mostrar el modal
    $('#modalActualizar').modal('show');

    // Manejar el envío del formulario con confirmación
    $('#formActualizarMedio').on('submit', function(e) {
        e.preventDefault();

        if (confirm("¿Está seguro de que desea actualizar el medio de contacto?")) {
            const nuevoMedio = $('#nuevoMedio').val();
            actualizarMedioContacto(id, nuevoMedio);
            $('#modalActualizar').modal('hide');
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
                console.log("Respuesta del servidor: " + response); // Depuración

                if (response == "success") {
                    // Obtener el nuevo botón con clase e ícono
                    const result = getBtnClass(nuevoMedio); // Llamar a la función que devuelve la clase y el ícono
                    const botonHtml = `<button class="btn ${result.btnClass}">${result.icon} ${nuevoMedio}</button>`;

                    // Actualizar el contenido de la celda correspondiente en la tabla
                    document.getElementById("medioContacto_" + id).innerHTML = botonHtml; // Cambiar el HTML completo del botón
                    toastr.success("El medio de contacto se actualizó correctamente.");
                } else {
                    toastr.error("Hubo un error al actualizar el medio de contacto.");
                }
            }
        };
        xhr.send("id=" + id + "&nuevoMedio=" + nuevoMedio);
    }


    // Función para obtener la clase del botón según el medio de contacto
    function getBtnClass(medio) {
        let btnClass = '';
        let icon = '';

        if (medio == 'WhatsApp') {
            btnClass = 'bg-lime-dark'; // Verde para WhatsApp
            icon = '<i class="bi bi-whatsapp"></i>'; // Ícono de WhatsApp
        } else if (medio == 'Teléfono') {
            btnClass = 'bg-teal-dark'; // Azul para Teléfono
            icon = '<i class="bi bi-telephone"></i>'; // Ícono de Teléfono
        } else if (medio == 'Correo') {
            btnClass = 'bg-amber-light'; // Amarillo para Correo
            icon = '<i class="bi bi-envelope"></i>'; // Ícono de Correo
        }

        return {
            btnClass,
            icon
        }; // Devuelve tanto la clase como el ícono
    }
    function confirmarActualizacion() {
    // Mostrar un mensaje de confirmación
    if (confirm("¿Está seguro de que desea actualizar el medio de contacto?")) {
        return true; // Si el usuario confirma, se permite el envío del formulario
    } else {
        return false; // Si el usuario cancela, no se envía el formulario
    }
}

</script>


<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    <?php if ($mensajeToast): ?>
        toastr.success("<?php echo $mensajeToast; ?>");
    <?php endif; ?>
</script>