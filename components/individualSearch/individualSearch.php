<div class="p-3">
    <div class="row">
        <div class="col col-lg-12 col-md-12 col-sm-12 px-2 mt-1 mx-auto" style="max-width: 800px;">
            <div class="card text-center">
                <div class="card-header bg-indigo-dark text-white">
                    <i class="bi bi-person-badge"></i> BUSCAR ESTUDIANTE <i class="bi bi-person-badge"></i>
                </div>
                <br>
                <!-- Mostrar imagen solo si no hay búsqueda -->
                <form action="" method="GET" class="mx-3">
                    <div class="input-group  mb-3">
                        <input type="number" name="search" required
                            value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
                            class="form-control text-center"
                            placeholder="CODIGO DE LA PROPIEDAD">
                        <button type="submit" class="btn bg-indigo-dark text-white" title="Buscar estudiante">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (isset($_GET['search'])): ?>
            <div class="col col-lg-12 col-md-12 col-sm-12 px-2 mt-1">
                <?php
                $filtervalues = $_GET['search'];
                $query = "SELECT ur.*, m.municipio, d.departamento, 
                        TIMESTAMPDIFF(YEAR, ur.birthdate, CURDATE()) as age
                        FROM user_register ur
                        LEFT JOIN municipios m ON ur.municipality = m.id_municipio 
                        LEFT JOIN departamentos d ON ur.department = d.id_departamento
                        WHERE ur.number_id LIKE ? LIMIT 1";

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

                $stmt = $conn->prepare($query);
                $searchParam = "%$filtervalues%";
                $stmt->bind_param("s", $searchParam);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $number_id = $row['number_id'];
                    $nombre = htmlspecialchars($row['first_name'] . ' ' . $row['second_name'] . ' ' .
                        $row['first_last'] . ' ' . $row['second_last']);

                    // Consulta para obtener historial de contactos
                    $sqlContactLog = "SELECT cl.*, a.name AS advisor_name 
                                    FROM contact_log cl
                                    LEFT JOIN advisors a ON cl.idAdvisor = a.idAdvisor 
                                    WHERE cl.number_id = ?";

                    // Preparar y ejecutar consulta de contact_log
                    $stmtContactLog = $conn->prepare($sqlContactLog);
                    $stmtContactLog->bind_param('s', $number_id);
                    $stmtContactLog->execute();
                    $resultContactLog = $stmtContactLog->get_result();
                    $contactLogs = $resultContactLog->fetch_all(MYSQLI_ASSOC);

                    // Si hay registros de contact_log, asignar valores
                    if (!empty($contactLogs)) {
                        // Almacenar todo el historial
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

                        // Asignar último registro como valores actuales
                        $lastLog = end($contactLogs);
                        $row['idAdvisor'] = $lastLog['idAdvisor'];
                        $row['advisor_name'] = $lastLog['advisor_name'];
                        $row['details'] = $lastLog['details'];
                        $row['contact_established'] = $lastLog['contact_established'];
                        $row['continues_interested'] = $lastLog['continues_interested'];
                        $row['observation'] = $lastLog['observation'];
                    } else {
                        // Valores por defecto si no hay registros
                        $row['idAdvisor'] = 'No registrado';
                        $row['advisor_name'] = 'Sin asignar';
                        $row['details'] = 'Sin detalles';
                        $row['contact_established'] = 0;
                        $row['continues_interested'] = 0;
                        $row['observation'] = 'Sin observaciones';
                        $row['contact_logs'] = [];
                    }
                ?>
            </div>

            <form method="POST">
                <div class="card items-center mt-3 mx-auto" style="max-width: 800px;">
                    <div class="card-header bg-indigo-dark text-white text-center items-center">
                        <i class="bi bi-person-lines-fill"></i> INFORMACIÓN DEL ESTUDIANTE
                    </div>
                    <div class="d-flex card-body items-center text-center justify-content-center"></div>
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush text-center">
                                <li class="list-group-item">
                                    <strong>Nombre:</strong><br>
                                    <?= $nombre ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Tipo de identificación:</strong><br>
                                    <?= htmlspecialchars($row['typeID']) ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Numero de identificación:</strong><br>
                                    <?= htmlspecialchars($number_id) ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Fotos de identificación:</strong><br>
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
                                                                    src="../files/idFilesFront/<?php echo htmlspecialchars($row['file_front_id']); ?>"
                                                                    class="img-fluid w-100 zoomable"
                                                                    style="max-height: 400px; object-fit: contain; transition: transform 0.3s ease; position: relative; z-index: 1055;"
                                                                    alt="Frente ID"
                                                                    onclick="toggleZoom('idImageFront_<?php echo $row['number_id']; ?>')">
                                                            </div>
                                                            <div class="mt-2">
                                                                <button type="button" class="btn btn-primary" onclick="rotateImage('idImageFront_<?php echo $row['number_id']; ?>', -90)">↺ Rotar Izquierda</button>
                                                                <button type="button" class="btn btn-primary" onclick="rotateImage('idImageFront_<?php echo $row['number_id']; ?>', 90)">↻ Rotar Derecha</button>
                                                            </div>
                                                        </div>

                                                        <!-- Reverso del documento -->
                                                        <div class="col-12 text-center">
                                                            <h6>Reverso del documento</h6>
                                                            <div class="position-relative overflow-visible">
                                                                <img id="idImageBack_<?php echo $row['number_id']; ?>"
                                                                    src="../files/idFilesBack/<?php echo htmlspecialchars($row['file_back_id']); ?>"
                                                                    class="img-fluid w-100 zoomable"
                                                                    style="max-height: 400px; object-fit: contain; transition: transform 0.3s ease; position: relative; z-index: 1055;"
                                                                    alt="Reverso ID"
                                                                    onclick="toggleZoom('idImageBack_<?php echo $row['number_id']; ?>')">
                                                            </div>
                                                            <div class="mt-2">
                                                                <button type="button" class="btn btn-primary" onclick="rotateImage('idImageBack_<?php echo $row['number_id']; ?>', -90)">↺ Rotar Izquierda</button>
                                                                <button type="button" class="btn btn-primary" onclick="rotateImage('idImageBack_<?php echo $row['number_id']; ?>', 90)">↻ Rotar Derecha</button>
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
                                </li>

                                <li class="list-group-item">
                                    <strong>Edad:</strong><br>
                                    <?= htmlspecialchars($row['age']) ?> años
                                </li>

                                <li class="list-group-item">
                                    <strong>Correo:</strong><br>
                                    <?= htmlspecialchars($row['email']) ?>
                                </li>

                                <li class="list-group-item">
                                    <strong>Telefono 1:</strong><br>
                                    <?= htmlspecialchars($row['first_phone']) ?>
                                </li>

                                <li class="list-group-item">
                                    <strong>Telefono 2:</strong><br>
                                    <?= htmlspecialchars($row['second_phone']) ?>
                                </li>

                                <li class="list-group-item">
                                    <strong>Medio de contacto:</strong><br>
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
                                </li>

                                <li class="list-group-item">
                                    <strong>Actualizar medio de contacto:</strong><br>
                                    <button class="btn bg-indigo-dark text-white" type="button" onclick="mostrarModalActualizar(<?php echo $row['number_id']; ?>)" data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-custom-class="custom-tooltip"
                                        data-bs-title="Cambiar medio de contacto">
                                        <i class="bi bi-arrow-left-right"></i></button>
                                </li>

                                <li class="list-group-item">
                                    <strong>Contacto de emergencia:</strong><br>
                                    <?= htmlspecialchars($row['emergency_contact_name']) ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Numero de contacto:</strong><br>
                                    <?= htmlspecialchars($row['emergency_contact_number']) ?>
                                </li>

                                <li class="list-group-item">
                                    <strong>Dirección:</strong><br>
                                    <?= htmlspecialchars($row['address']) ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Nacionalidad:</strong><br>
                                    <?= htmlspecialchars($row['nationality']) ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Departamento:</strong><br>
                                    <?= htmlspecialchars($row['departamento']) ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Municipio:</strong><br>
                                    <?= htmlspecialchars($row['municipio']) ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Ocupación:</strong><br>
                                    <?= htmlspecialchars($row['occupation']) ?>
                                </li>

                                <li class="list-group-item">
                                    <strong>Tiempo para obligaciones:</strong><br>
                                    <?= htmlspecialchars($row['time_obligations']) ?>
                                </li>

                                <li class="list-group-item">
                                    <strong>Sede:</strong><br>
                                    <?= htmlspecialchars($row['headquarters']) ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Modalidad:</strong><br>
                                    <?= htmlspecialchars($row['mode']) ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Programa:</strong><br>
                                    <?= htmlspecialchars($row['program']) ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Horarios:</strong><br>
                                    <button type="button" class="btn bg-indigo-light"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-custom-class="custom-tooltip"
                                        data-bs-title="<?= htmlspecialchars($row['schedules']) ?>">
                                        <i class="bi bi-clock-history"></i>
                                    </button>
                                </li>

                                <li class="list-group-item">
                                    <strong>Dispositivo:</strong><br>
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
                                    echo '
                                    <button class="btn ' . $btnClass . '" data-bs-toggle="tooltip" data-bs-placement="top" 
                                    data-bs-custom-class="custom-tooltip" data-bs-title="' . $btnText . '">
                                        ' . $icon . '
                                    </button>';
                                    ?>
                                </li>

                                <li class="list-group-item">
                                    <strong>Internet:</strong><br>

                                    <?php
                                    $btnClass = '';
                                    $btnText = htmlspecialchars($row['internet']); // El texto que aparecerá en la tooltip
                                    $icon = ''; // Ícono correspondiente

                                    // Mostrar el estado internet
                                    if ($row['internet'] === 'Sí') {
                                        $btnClass = 'bg-indigo-dark text-white'; // Clase para internet
                                        $icon = '<i class="bi bi-router-fill"></i>'; // Ícono de internet
                                    } elseif ($row['internet'] === 'No') {
                                        $btnClass = 'bg-red-dark text-white'; // Clase para smartphone
                                        $icon = '<i class="bi bi-wifi-off"></i>'; // Ícono de wifi off
                                    }
                                    // Mostrar el botón con la clase, ícono y tooltip correspondientes
                                    echo '<button class="btn ' . $btnClass . '" data-bs-toggle="tooltip" data-bs-placement="top" 
                                    data-bs-custom-class="custom-tooltip" data-bs-title="' . $btnText . '">
                                        ' . $icon . '
                                    </button>'
                                    ?>
                                </li>

                                <li class="list-group-item">
                                    <strong>Estado:</strong><br>
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
                                        echo '<button class="btn bg-teal-dark w-100" style="max-width: 100px;" data-bs-toggle="tooltip" data-bs-placement="top" title="CUMPLE"><i class="bi bi-check-circle"></i></button>';
                                    } else {
                                        echo '<button class="btn bg-danger text-white w-100" style="max-width: 100px;" data-bs-toggle="tooltip" data-bs-placement="top" title="NO CUMPLE"><i class="bi bi-x-circle"></i></button>';
                                    }
                                    ?>
                                </li>

                                <li class="list-group-item">
                                    <strong>Estado de admisión:</strong><br>
                                    <?php
                                    if ($row['statusAdmin'] === 1) {
                                        echo '<button class="btn bg-teal-dark w-100" style="max-width: 100px;" data-bs-toggle="tooltip" data-bs-placement="top" title="ACEPTADO"><i class="bi bi-check-circle"></i></button>';
                                    } elseif ($row['statusAdmin'] === 0) {
                                        echo '<button class="btn bg-silver text-white w-100" style="max-width: 100px;" data-bs-toggle="tooltip" data-bs-placement="top" title="SIN ESTADO"><i class="bi bi-question-circle"></i></button>';
                                    }
                                    ?>
                                </li>

                                <li class="list-group-item">
                                    <strong>Puntaje de prueba:</strong><br>
                                    <?php
                                    if (isset($nivelesUsuarios[$row['number_id']])) {
                                        $puntaje = $nivelesUsuarios[$row['number_id']];
                                        if ($puntaje >= 1 && $puntaje <= 5) {
                                            echo '<button class="btn bg-magenta-dark w-100" style="max-width: 100px;" role="alert">' . htmlspecialchars($nivelesUsuarios[$row['number_id']]) . '</button>';
                                        } elseif ($puntaje >= 6 && $puntaje <= 10) {
                                            echo '<button class="btn bg-orange-dark w-100" style="max-width: 100px;" role="alert"role="alert">' . htmlspecialchars($nivelesUsuarios[$row['number_id']]) . '</button>';
                                        } elseif ($puntaje >= 11 && $puntaje <= 15) {
                                            echo '<button class="btn bg-teal-dark w-100" style="max-width: 100px;" role="alert" role="alert">' . htmlspecialchars($nivelesUsuarios[$row['number_id']]) . '</button>';
                                        }
                                    } else {
                                        echo '<button class="btn bg-silver w-100" style="max-width: 100px;" role="alert"role="alert data-bs-toggle="tooltip" data-bs-placement="top"
                                            data-bs-custom-class="custom-tooltip"
                                            data-bs-title="No ha presebtado la prueba" >
                                        <i class="bi bi-ban"></i>
                                            </button>';
                                    }
                                    ?>
                                </li>

                                <li class="list-group-item">
                                    <strong>Nivel de prueba:</strong><br>
                                    <?php
                                    if (isset($nivelesUsuarios[$row['number_id']])) {
                                        $puntaje = $nivelesUsuarios[$row['number_id']];
                                        if ($puntaje >= 1 && $puntaje <= 5) {
                                            echo '<button class="btn bg-magenta-dark w-100" style="max-width: 150px;" role="alert">Básico</div>';
                                        } elseif ($puntaje >= 6 && $puntaje <= 10) {
                                            echo '<button class="btn bg-orange-dark w-100" style="max-width: 150px;" role="alert"role="alert">Intermedio</div>';
                                        } elseif ($puntaje >= 11 && $puntaje <= 15) {
                                            echo '<button class="btn bg-teal-dark w-100" style="max-width: 150px;" role="alert" role="alert">Avanzado</div>';
                                        }
                                    } else {
                                        echo '<button class="btn bg-silver w-100" style="max-width: 150px;" role="alert"role="alert  data-bs-toggle="tooltip" data-bs-placement="top"
                                                data-bs-custom-class="custom-tooltip"
                                                data-bs-title="No ha presebtado la prueba" >
                                                <i class="bi bi-ban"></i></button>';
                                    }
                                    ?>
                                </li>


                                <li class="list-group-item text-center">
                                    <br>

                                    <strong>Actualizar registro de contacto:</strong><br>
                                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalLlamada_<?php echo $row['number_id']; ?>">
                                        <i class="bi bi-telephone"></i>
                                    </button>



                                </li>

                                <li class="list-group-item text-center">
                                    <br>

                                    <strong>Actualizar estado de admision:</strong><br>
                                    <button class="btn bg-indigo-dark text-white" type="button" onclick="mostrarModalActualizarAdmision(<?php echo $row['number_id']; ?>)" data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-custom-class="custom-tooltip"
                                        data-bs-title="Cambiar estado de admisión">
                                        <i class="bi bi-arrow-left-right"></i></button>
                                    <br>
                                    <br>

                                </li>


                            </ul>
                        </div>
                    </div>
                </div>
    </div>
    </form>
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
                <form id="formActualizarLlamada_<?php echo $row['number_id']; ?>" method="POST" onsubmit="event.preventDefault(); return actualizarLlamada(<?php echo $row['number_id']; ?>)">
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
</div>
</div>
</form>
</tr>

<?php } else { ?>
    <div class="alert alert-danger mt-3">
        No se encontró ningún estudiante con el código <?= htmlspecialchars($filtervalues) ?>
    </div>
<?php } ?>
</div>
<?php endif; ?>
</div>
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
                console.log("Respuesta:", xhr.responseText);

                if (xhr.status == 200) {
                    const response = xhr.responseText.trim();

                    if (response === "success") {
                        // Cerrar el modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalLlamada_' + id));
                        modal.hide();


                        Swal.fire({
                            title: '¡Exitoso! 🎉',
                            text: 'La información se ha guardado correctamente.',
                            toast: true,
                            position: 'center',
                        }).then(() => {
                            // Recargar la página después de 2 segundos
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        });

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

    function mostrarModalActualizarAdmision(id) {
        // Remover cualquier modal previo del DOM
        $('#modalActualizarAdmision_' + id).remove();

        // Crear el modal dinámicamente con un identificador único
        const modalHtml = `
        <div id="modalActualizarAdmision_${id}" class="modal fade" aria-hidden="true" aria-labelledby="modalActualizarAdmisionLabel" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-indigo-dark">
                        <h5 class="modal-title text-center"><i class="bi bi-arrow-left-right"></i> Actualizar Estado de Admisión</h5>
                        <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formActualizarAdmision_${id}">
                            <div class="form-group">
                                <label for="nuevoEstado_${id}">Seleccionar nuevo estado:</label>
                                <select class="form-control" id="nuevoEstado_${id}" name="nuevoEstado" required>
                                   <option value="">Seleccionar</option>
                                    <option value="1">Aceptado</option>
                                    <option value="2">Rechazado</option>
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
        $('#modalActualizarAdmision_' + id).modal('show');

        // Manejar el envío del formulario
        $('#formActualizarAdmision_' + id).on('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: '¿Está seguro?',
                text: "¿Desea actualizar el estado de admisión?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const nuevoEstado = $('#nuevoEstado_' + id).val();
                    actualizarEstadoAdmision(id, nuevoEstado);
                    $('#modalActualizarAdmision_' + id).modal('hide');
                }
            });
        });
    }

    function actualizarEstadoAdmision(id, nuevoEstado) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "components/registrationsContact/actualizar_admision.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    const response = xhr.responseText.trim();
                    if (response === "success") {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: 'El estado de admisión se ha actualizado correctamente.',
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al actualizar el estado de admisión.'
                        });
                    }
                }
            }
        };

        xhr.send("id=" + id + "&nuevoEstado=" + encodeURIComponent(nuevoEstado));
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.6/umd/popper.min.js"></script>