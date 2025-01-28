<?php
// Mantén el código PHP para la conexión y demás operaciones
$mensajeToast = ''; // Mensaje para el toast

// Procesamiento de la actualización de estado
if (isset($_POST['btnActualizarEstado'])) {
    $codigo = $_POST['codigo'];
    $nuevoEstado = $_POST['nuevoEstado'];

    // Obtener los datos del usuario para verificar las condiciones
    $userSql = "SELECT * FROM user_register WHERE number_id = ?";
    $stmtUser   = $conn->prepare($userSql);
    $stmtUser->bind_param('i', $codigo);
    $stmtUser->execute();
    $resultUser  = $stmtUser->get_result();

    if ($resultUser  && $resultUser->num_rows > 0) {
        $userData = $resultUser->fetch_assoc();

        // Calcular edad
        $birthday = new DateTime($userData['birthdate']);
        $now = new DateTime();
        $age = $now->diff($birthday)->y;

        // Verificar condiciones
        $isAccepted = false;
        if ($userData['mode'] === 'Presencial') {
            if (
                $userData['typeID'] === 'C.C' && $age > 17 &&
                (strtoupper($userData['departament']) === 25 || strtoupper($userData['departament']) === 15) &&
                $userData['internet'] === 'Sí'
            ) {
                $isAccepted = true;
            }
        } elseif ($userData['mode'] === 'Virtual') {
            if (
                $userData['typeID'] === 'C.C' && $age > 17 &&
                (strtoupper($userData['departament']) === 25 || strtoupper($userData['departament']) === 15) &&
                $userData['internet'] === 'Sí' &&
                $userData['technologies'] === 'computador'
            ) {
                $isAccepted = true;
            }
        }

        // Si se cumplen las condiciones, actualizar el estado a "ACEPTADO"
        if ($isAccepted) {
            $nuevoEstado = 'ACEPTADO'; // Cambiar el estado a "ACEPTADO"
        }

        // Actualización en la base de datos
        $updateSql = "UPDATE user_register SET status = ? WHERE number_id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param('si', $nuevoEstado, $codigo);

        if ($stmt->execute()) {
            $mensajeToast = 'Estado actualizado correctamente.';
        } else {
            $mensajeToast = "Error al actualizar el estado: {$stmt->error}"; // Muestra el error
        }
    } else {
        $mensajeToast = 'Usuario no encontrado.';
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

$sqlContactLog = "SELECT cl.*, a.name AS advisor_name
                  FROM contact_log cl
                  LEFT JOIN advisors a ON cl.idAdvisor = a.id
                  WHERE cl.number_id = ?";

$result = $conn->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Obtener los datos de contact_log para el número de ID actual
        $stmtContactLog = $conn->prepare($sqlContactLog);
        $stmtContactLog->bind_param('i', $row['number_id']);
        $stmtContactLog->execute();
        $resultContactLog = $stmtContactLog->get_result();
        $contactLogs = $resultContactLog->fetch_all(MYSQLI_ASSOC);

        // Si hay registros, asignar los valores
        if (!empty($contactLogs)) {
            $row['idAdvisor'] = $contactLogs[0]['idAdvisor'];
            $row['advisor_name'] = $contactLogs[0]['advisor_name']; // Nombre del asesor
            $row['detail'] = $contactLogs[0]['details'];
            $row['contact_established'] = $contactLogs[0]['contact_established'];
            $row['still_interested'] = $contactLogs[0]['continues_interested'];
            $row['observation'] = $contactLogs[0]['observation'];
        } else {
            // Si no hay registros, asignar valores por defecto
            $row['idAdvisor'] = 'No registrado';
            $row['advisor_name'] = 'Sin asignar';
            $row['detail'] = 'Sin detalles';
            $row['contact_established'] = 0; // Cambiado a 0
            $row['still_interested'] = 0; // Cambiado a 0
            $row['observation'] = 'Sin observaciones';
        }

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
                <th>Foto de ID</th>
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
                <th>Municipio</th>
                <th>Ocupación</th>
                <th>Tiempo de obligaciones</th>
                <th>Sede de elección</th>
                <th>Programa de interés</th>
                <th>Horario</th>
                <th>Dispositivo</th>
                <th>Internet</th>
                <th>Estado</th>
                <th>Medio de contacto</th>
                <th>Información de llamada</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['typeID']); ?></td>
                    <td><?php echo htmlspecialchars($row['number_id']); ?></td>
                    <td>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalID_<?php echo $row['number_id']; ?>">
                            <i class="bi bi-card-image"></i>
                        </button>

                        <!-- Modal para mostrar las imágenes -->
                        <div class="modal fade" id="modalID_<?php echo $row['number_id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-indigo-dark">
                                        <h5 class="modal-title">Imágenes de Identificación</h5>
                                        <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-12 mb-4">
                                                <h6>Frente del documento</h6>
                                                <img src="https://dashboard.uttalento.co/files/idFilesFront/<?php echo htmlspecialchars($row['file_front_id']); ?>"
                                                    class="img-fluid w-100"
                                                    style="max-height: 400px; object-fit: contain;"
                                                    alt="Frente ID">
                                            </div>
                                            <div class="col-12">
                                                <h6>Reverso del documento</h6>
                                                <img src="https://dashboard.uttalento.co/files/idFilesBack/<?php echo htmlspecialchars($row['file_back_id']); ?>"
                                                    class="img-fluid w-100"
                                                    style="max-height: 400px; object-fit: contain;"
                                                    alt="Reverso ID">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
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
                        echo '<button type="button" class="' . $btnClass . '" data-bs-toggle="tooltip" data-bs-placement="top" title="' . $btnText . '">'
                            . $icon .
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
                            echo "<button class='btn bg-lime-light w-100'><b>{$departamento}</b></button>"; // Botón verde para CUNDINAMARCA
                        } elseif ($departamento === 'BOYACÁ') {
                            echo "<button class='btn bg-indigo-light w-100'><b>{$departamento}</b></button>"; // Botón azul para BOYACÁ
                        } else {
                            echo "<span>{$departamento}</span>"; // Texto normal para otros valores
                        }
                        ?>
                    </td>

                    <td><b class="text-center"><?php echo htmlspecialchars($row['municipio']); ?></b></td>
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

                    <?php
                    $btnClass = '';
                    $btnText = htmlspecialchars($row['internet']); // El texto que aparecerá en la tooltip
                    $icon = ''; // Ícono correspondiente

                    // Mostrar el estado internet
                    if ($row['internet'] === 'Sí') {
                        $btnClass = 'bg-indigo-dark text-white'; // Clase para computador
                        $icon = '<i class="bi bi-router-fill"></i>'; // Ícono de computador
                    } elseif ($row['internet'] === 'No') {
                        $btnClass = 'bg-teal-dark text-white'; // Clase para smartphone
                        $icon = '<i class="bi bi-wifi-off"></i>'; // Ícono de smartphone
                    }
                    // Mostrar el botón con la clase, ícono y tooltip correspondientes
                    echo '<td class="text-center">
                    <button class="btn ' . $btnClass . '" data-bs-toggle="tooltip" data-bs-placement="top" 
                    data-bs-custom-class="custom-tooltip" data-bs-title="' . $btnText . '">
                        ' . $icon . '
                    </button>
                  </td>'
                    ?>

                    <td>
                        <?php
                        // Verificar condiciones para cada registro
                        $isAccepted = false;
                        if ($row['mode'] === 'Presencial') {
                            if (
                                $row['typeID'] === 'C.C' && $row['age'] > 17 &&
                                (strtoupper($row['departamento']) === 'CUNDINAMARCA' || strtoupper($row['departamento']) === 'BOYACÁ') &&
                                $row['internet'] === 'Sí'
                            ) {
                                $isAccepted = true;
                            }
                        } elseif ($row['mode'] === 'Virtual') {
                            if (
                                $row['typeID'] === 'C.C' && $row['age'] > 17 &&
                                (strtoupper($row['departamento']) === 'CUNDINAMARCA' || strtoupper($row['departamento']) === 'BOYACÁ') &&
                                $row['internet'] === 'Sí' &&
                                $row['technologies'] === 'computador'
                            ) {
                                $isAccepted = true;
                            }
                        }

                        if ($isAccepted) {
                            echo '<button class="btn bg-teal-dark w-100">CUMPLE</button>';
                        } else {
                            echo '<button class="btn bg-danger text-white w-100">NO CUMPLE</button>';
                        }
                        ?>
                    </td>
                    <td>
                        <button class="btn bg-magenta-dark text-white" onclick="mostrarModalActualizar(<?php echo $row['number_id']; ?>)" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-custom-class="custom-tooltip"
                            data-bs-title="Cambiar medio de contacto">
                            <i class="bi bi-arrow-left-right"></i></button>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalLlamada_<?php echo $row['number_id']; ?>">
                            <i class="bi bi-telephone"></i>
                        </button>
                    </td>
                </tr>

                <!-- Modal -->
                <div class="modal fade" id="modalLlamada_<?php echo $row['number_id']; ?>" tabindex="-1" aria-labelledby="modalLlamadaLabel_<?php echo $row['number_id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-indigo-dark">
                                <h5 class="modal-title" id="modalLlamadaLabel_<?php echo $row['number_id']; ?>"><i class="bi bi-telephone"></i> Información de Llamada</h5>
                                <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="formActualizarLlamada_<?php echo $row['number_id']; ?>" method="POST" onsubmit="return actualizarLlamada(<?php echo $row['number_id']; ?>)">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <p><strong>ID de asesor:</strong> <?php echo htmlspecialchars($row['idAdvisor']); ?></p>
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Asesor:</strong></label>
                                            <select class="form-control" name="idAdvisor" required disabled>
                                                <?php
                                                // Consulta para obtener todos los asesores
                                                $sqlAsesores = "SELECT idAdvisor, name FROM advisors ORDER BY name ASC";
                                                $resultAsesores = $conn->query($sqlAsesores);
                                                
                                                // Mostrar opción por defecto
                                                echo '<option value="">Seleccione un asesor</option>';
                                                
                                                // Mostrar cada asesor como una opción
                                                if ($resultAsesores && $resultAsesores->num_rows > 0) {
                                                    while ($asesor = $resultAsesores->fetch_assoc()) {
                                                        $selected = ($asesor['idAdvisor'] == $row['idAdvisor']) ? 'selected' : '';
                                                        echo '<option value="' . $asesor['id'] . '" ' . $selected . '>' . 
                                                            htmlspecialchars($asesor['name']) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <hr class="hr" />
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Detalle:</strong></label>
                                        <select class="form-control" name="details">
                                            <option value="Sin detalles" <?php if ($row['detail'] == 'Sin detalles') echo 'selected'; ?>>Sin detalles</option>
                                            <option value="Número equivocado" <?php if ($row['detail'] == 'Número equivocado') echo 'selected'; ?>>Número equivocado</option>
                                            <option value="Teléfono apagado" <?php if ($row['detail'] == 'Teléfono apagado') echo 'selected'; ?>>Teléfono apagado</option>
                                            <option value="Teléfono desconectado" <?php if ($row['detail'] == 'Teléfono desconectado') echo 'selected'; ?>>Teléfono desconectado</option>
                                            <option value="Sin señal" <?php if ($row['detail'] == 'Sin señal') echo 'selected'; ?>>Sin señal</option>
                                            <option value="No contestan" <?php if ($row['detail'] == 'No contestan') echo 'selected'; ?>>No contestan</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Estableció Contacto:</strong></label>
                                        <select class="form-control" name="contact_established">
                                            <option value="0" <?php if ($row['contact_established'] == 0) echo 'selected'; ?>>No</option>
                                            <option value="1" <?php if ($row['contact_established'] == 1) echo 'selected'; ?>>Sí</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Aún Interesado:</strong></label>
                                        <select class="form-control" name="continues_interested">
                                            <option value="0" <?php if ($row['still_interested'] == 0) echo 'selected'; ?>>No</option>
                                            <option value="1" <?php if ($row['still_interested'] == 1) echo 'selected'; ?>>Sí</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Observación:</strong></label>
                                        <textarea rows="3" class="form-control" name="observation"><?php echo htmlspecialchars($row['observation']); ?></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer position-relative d-flex justify-content-center">
                                    <button type="submit" class="btn bg-indigo-dark text-white">Actualizar Información</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

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

    function actualizarLlamada(id) {
        const form = document.getElementById('formActualizarLlamada_' + id);
        if (!form) {
            console.error('Formulario no encontrado');
            return false;
        }

        const formData = new FormData(form);
        formData.append('number_id', id);

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "components/registrationsContact/actualizar_llamada.php", true);

        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    const response = xhr.responseText;
                    if (response.trim() === "success") {
                        toastr.success("La información de la llamada se actualizó correctamente.");
                        location.reload();
                    } else {
                        toastr.error("Error: " + response);
                    }
                } else {
                    toastr.error("Error en la conexión con el servidor");
                }
            }
        };

        xhr.onerror = function() {
            console.error("Error de red");
            toastr.error("Error de conexión");
        };

        xhr.send(formData);
        return false;
    }

    xhr.onerror = function() {
        console.error("Error de red");
        toastr.error("Error de conexión");
    };

    xhr.send(formData);
    return false;
    
</script>

<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>