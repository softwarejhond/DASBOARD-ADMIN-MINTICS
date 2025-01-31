<?php

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

// Función para obtener los niveles de los usuarios 
function obtenerNivelesUsuarios($conn)
{
    $sql = "SELECT cedula, nivel FROM usuarios";
    $result = $conn->query($sql);

    $niveles = array();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $niveles[$row['cedula']] = $row['nivel'];
        }
    }

    return $niveles;
}



// Obtener los niveles de usuarios
$nivelesUsuarios = obtenerNivelesUsuarios($conn);

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
            // Crear un array para almacenar todos los registros de contact_log
            $row['contact_logs'] = [];

            foreach ($contactLogs as $log) {
                $row['contact_logs'][] = [
                    'idAdvisor' => $log['idAdvisor'],
                    'advisor_name' => $log['advisor_name'],
                    'details' => $log['details'],
                    'contact_established' => $log['contact_established'],
                    'continues_interested' => $log['continues_interested'],
                    'observation' => $log['observation']
                ];
            }

            // Asignar el último registro como valores por defecto
            $lastLog = end($contactLogs);
            $row['idAdvisor'] = $lastLog['idAdvisor'];
            $row['advisor_name'] = $lastLog['advisor_name'];
            $row['details'] = $lastLog['details'];
            $row['contact_established'] = $lastLog['contact_established'];
            $row['continues_interested'] = $lastLog['continues_interested'];
            $row['observation'] = $lastLog['observation'];
        } else {
            // Si no hay registros, asignar valores por defecto
            $row['idAdvisor'] = 'No registrado';
            $row['advisor_name'] = 'Sin asignar';
            $row['details'] = 'Sin detalles';
            $row['contact_established'] = 0; // Cambiado a 0
            $row['continues_interested'] = 0; // Cambiado a 0
            $row['observation'] = 'Sin observaciones';
            $row['contact_logs'] = []; // Array vacío para contact_logs
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
    <button id="exportarExcel" class="btn btn-success mb-3"
        onclick="window.location.href='components/registrationsContact/export_to_excel.php?action=export'">
        <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
    </button>
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
                <th>Modalidad</th>
                <th>Programa de interés</th>
                <th>Horario</th>
                <th>Dispositivo</th>
                <th>Internet</th>
                <th>Estado</th>
                <th>Medio de contacto</th>
                <th>Puntaje de prueba</th>
                <th>Estado de prueba</th>
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
                                    <div class="modal-body position-relative" style="overflow: visible;">
                                        <div class="row">
                                            <!-- Frente del documento -->
                                            <div class="col-12 mb-4 text-center">
                                                <h6>Frente del documento</h6>
                                                <div class="position-relative overflow-visible">
                                                    <img id="idImageFront_<?php echo $row['number_id']; ?>"
                                                        src="https://dashboard.uttalento.co/files/idFilesFront/<?php echo htmlspecialchars($row['file_front_id']); ?>"
                                                        class="img-fluid w-100 zoomable"
                                                        style="max-height: 400px; object-fit: contain; transition: transform 0.3s ease; position: relative; z-index: 1055;"
                                                        alt="Frente ID"
                                                        onclick="toggleZoom('idImageFront_<?php echo $row['number_id']; ?>')">
                                                </div>
                                                <div class="mt-2">
                                                    <button class="btn btn-primary" onclick="rotateImage('idImageFront_<?php echo $row['number_id']; ?>', -90)">↺ Rotar Izquierda</button>
                                                    <button class="btn btn-primary" onclick="rotateImage('idImageFront_<?php echo $row['number_id']; ?>', 90)">↻ Rotar Derecha</button>
                                                </div>
                                            </div>

                                            <!-- Reverso del documento -->
                                            <div class="col-12 text-center">
                                                <h6>Reverso del documento</h6>
                                                <div class="position-relative overflow-visible">
                                                    <img id="idImageBack_<?php echo $row['number_id']; ?>"
                                                        src="https://dashboard.uttalento.co/files/idFilesBack/<?php echo htmlspecialchars($row['file_back_id']); ?>"
                                                        class="img-fluid w-100 zoomable"
                                                        style="max-height: 400px; object-fit: contain; transition: transform 0.3s ease; position: relative; z-index: 1055;"
                                                        alt="Reverso ID"
                                                        onclick="toggleZoom('idImageBack_<?php echo $row['number_id']; ?>')">
                                                </div>
                                                <div class="mt-2">
                                                    <button class="btn btn-primary" onclick="rotateImage('idImageBack_<?php echo $row['number_id']; ?>', -90)">↺ Rotar Izquierda</button>
                                                    <button class="btn btn-primary" onclick="rotateImage('idImageBack_<?php echo $row['number_id']; ?>', 90)">↻ Rotar Derecha</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            // Verificar si la variable ya existe en el ámbito global
                            if (typeof window.imageTransforms === 'undefined') {
                                window.imageTransforms = {};
                            }

                            function rotateImage(imageId, degrees) {
                                if (!window.imageTransforms[imageId]) {
                                    window.imageTransforms[imageId] = {
                                        rotation: 0,
                                        scale: 1
                                    };
                                }
                                window.imageTransforms[imageId].rotation += degrees;
                                applyTransform(imageId);
                            }

                            function toggleZoom(imageId) {
                                if (!window.imageTransforms[imageId]) {
                                    window.imageTransforms[imageId] = {
                                        rotation: 0,
                                        scale: 1
                                    };
                                }
                                window.imageTransforms[imageId].scale = window.imageTransforms[imageId].scale === 1 ? 2 : 1;
                                applyTransform(imageId);
                            }

                            function applyTransform(imageId) {
                                let imgElement = document.getElementById(imageId);
                                if (imgElement) {
                                    let {
                                        rotation,
                                        scale
                                    } = window.imageTransforms[imageId];
                                    imgElement.style.transform = `rotate(${rotation}deg) scale(${scale})`;
                                }
                            }
                        </script>

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
                    <td><?php echo htmlspecialchars($row['mode']); ?></td>
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
                            echo '<button class="btn bg-teal-dark w-100" data-bs-toggle="tooltip" data-bs-placement="top" title="CUMPLE"><i class="bi bi-check-circle"></i></button>';
                        } else {
                            echo '<button class="btn bg-danger text-white w-100" data-bs-toggle="tooltip" data-bs-placement="top" title="NO CUMPLE"><i class="bi bi-x-circle"></i></button>';
                        }
                        ?>
                    </td>
                    <td>
                        <button class="btn bg-magenta-dark text-white" onclick="mostrarModalActualizar(<?php echo $row['number_id']; ?>)" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-custom-class="custom-tooltip"
                            data-bs-title="Cambiar medio de contacto">
                            <i class="bi bi-arrow-left-right"></i></button>
                    </td>
                    <td><?php
                        if (isset($nivelesUsuarios[$row['number_id']])) {
                            echo htmlspecialchars($nivelesUsuarios[$row['number_id']]);
                        } else {
                            echo "No presento prueba";
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if (isset($nivelesUsuarios[$row['number_id']])) {
                            $puntaje = $nivelesUsuarios[$row['number_id']];
                            if ($puntaje >= 1 && $puntaje <= 5) {
                                echo '<button class="btn bg-orange-light w-100">Basico</button>';
                            } elseif ($puntaje >= 6 && $puntaje <= 10) {
                                echo '<button class="btn btn-info w-100">Intermedio</button>';
                            } elseif ($puntaje >= 11 && $puntaje <= 15) {
                                echo '<button class="btn bg-lime-light w-100">Avanzado</button>';
                            }
                        } else {
                            echo '<button class="btn btn-secondary w-100" data-bs-toggle="tooltip" data-bs-placement="top" title="NO PRESENTO PRUEBA"><i class="bi bi-question-circle"></i></button>';
                        }
                        ?>
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
                                <h5 class="modal-title" id="modalLlamadaLabel_<?php echo $row['number_id']; ?>">
                                    <i class="bi bi-telephone"></i> Información de Llamada
                                </h5>
                                <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="formActualizarLlamada_<?php echo $row['number_id']; ?>" method="POST" onsubmit="return actualizarLlamada(<?php echo $row['number_id']; ?>)">
                                <div class="modal-body">
                                    <!-- Contenedor para asesor actual y anterior -->
                                    <div class="row">
                                        <!-- Columna para el asesor actual -->
                                        <div class="col-md-6">
                                            <div class="mb-3"><u><strong>Asesor actual:</strong></u></div>
                                            <hr class="hr" />
                                            <div class="mb-3">
                                                <label class="form-label"><strong>ID de asesor:</strong></label>
                                                <input type="text" class="form-control" name="idAdvisor" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label"><strong>Nombre:</strong></label>
                                                <input type="text" class="form-control" readonly
                                                    value="<?php
                                                            // Consulta para obtener todos los asesores
                                                            $sqlAsesores = "SELECT idAdvisor, name FROM advisors ORDER BY name ASC";
                                                            $resultAsesores = $conn->query($sqlAsesores);

                                                            // Buscar y mostrar el nombre del asesor correspondiente
                                                            if ($resultAsesores && $resultAsesores->num_rows > 0) {
                                                                while ($asesor = $resultAsesores->fetch_assoc()) {
                                                                    if ($asesor['idAdvisor'] == $_SESSION['username']) {
                                                                        echo htmlspecialchars($asesor['name']);
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                            ?>">
                                            </div>
                                        </div>

                                        <!-- Columna para el asesor anterior -->
                                        <div class="col-md-6">
                                            <div class="mb-3"><u><strong>Asesor anterior:</strong></u></div>
                                            <hr class="hr" />
                                            <div class="mb-3">
                                                <label class="form-label"><strong>ID de asesor:</strong></label>
                                                <input type="text" class="form-control" readonly value="<?php echo htmlspecialchars($row['idAdvisor']); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label"><strong>Nombre:</strong></label>
                                                <input type="text" class="form-control" readonly
                                                    value="<?php
                                                            // Consulta para obtener todos los asesores
                                                            $sqlAsesores = "SELECT idAdvisor, name FROM advisors ORDER BY name ASC";
                                                            $resultAsesores = $conn->query($sqlAsesores);

                                                            // Buscar y mostrar el nombre del asesor correspondiente
                                                            if ($resultAsesores && $resultAsesores->num_rows > 0) {
                                                                while ($asesor = $resultAsesores->fetch_assoc()) {
                                                                    if ($asesor['idAdvisor'] == $row['idAdvisor']) {
                                                                        echo htmlspecialchars($asesor['name']);
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                            ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Resto del formulario -->
                                    <hr class="hr" />
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Detalle:</strong></label>
                                        <select class="form-control" name="details">
                                            <option value="Sin detalles" <?php if ($row['details'] == 'Sin detalles') echo 'selected'; ?>>Sin detalles</option>
                                            <option value="Número equivocado" <?php if ($row['details'] == 'Número equivocado') echo 'selected'; ?>>Número equivocado</option>
                                            <option value="Teléfono apagado" <?php if ($row['details'] == 'Teléfono apagado') echo 'selected'; ?>>Teléfono apagado</option>
                                            <option value="Teléfono desconectado" <?php if ($row['details'] == 'Teléfono desconectado') echo 'selected'; ?>>Teléfono desconectado</option>
                                            <option value="Sin señal" <?php if ($row['details'] == 'Sin señal') echo 'selected'; ?>>Sin señal</option>
                                            <option value="No contestan" <?php if ($row['details'] == 'No contestan') echo 'selected'; ?>>No contestan</option>
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
                                            <option value="0" <?php if ($row['continues_interested'] == 0) echo 'selected'; ?>>No</option>
                                            <option value="1" <?php if ($row['continues_interested'] == 1) echo 'selected'; ?>>Sí</option>
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
                    const response = xhr.responseText.trim();

                    if (response === "success") {
                        // Cerrar el modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalLlamada_' + id));
                        modal.hide();


                        Swal.fire({
                            title: '¡Exitoso! 🎉',
                            text: 'La información se ha guardado correctamente.',
                            icon: 'success',
                            toast: true,
                            position: 'center',
                            showConfirmButton: false,
                            timer: 4000
                        });

                        // Recargar la página después de 2 segundos
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        // Mostrar notificación de error

                        Swal.fire({
                            title: 'Error! ❌',
                            text: 'Hubo un problema al guardar la información: ' + response,

                            toast: true,
                            position: 'center',

                            icon: 'error',

                            showConfirmButton: false,
                            timer: 4000,
                        });
                    }
                } else {
                    console.error("Error en la conexión con el servidor");
                }
            }
        };

        xhr.onerror = function() {

            Swal.fire({
                title: 'Error! ❌',
                text: 'No se pudo conectar con el servidor.',

                toast: true,
                position: 'center',


                icon: 'error',

                showConfirmButton: false,
                timer: 4000,
            });
        };

        xhr.send(formData);
        return false;
    }

    // Muestra una notificación de actualización con SweetAlert2
    Swal.fire({
        icon: 'info',
        title: 'Actualizando información...',
        text: 'Por favor, espere un momento.',
        position: 'center',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
    })
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

//cambio minimo para probar git