   <!-- Sección de Filtro y Búsqueda -->
    <?php include 'components/pqr/filterPQRS.php' ?>
   <div class="card card-radius mb-4 p-3">
            <form method="GET" action="" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="estado" class="form-label subTitle">Filtrar por Estado:</label>
                    <select class="form-select" id="estado" name="estado" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        <?php
                        // Consultar los estados desde la base de datos
                        $sql_estados = "SELECT id, nombre FROM estados";
                        $resultado_estados = $conn->query($sql_estados);

                        if ($resultado_estados->num_rows > 0) {
                            while ($fila_estado = $resultado_estados->fetch_assoc()) {
                                $selected = ($fila_estado["id"] == $estado_seleccionado) ? "selected" : "";
                                echo "<option value='" . htmlspecialchars($fila_estado["id"]) . "' " . $selected . ">" . htmlspecialchars($fila_estado["nombre"]) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

            </form>
        </div>

        <!-- Tabla de PQRS -->
        <div class="table-responsive">
            <table id="tablaPQRS" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Radicado</th>
                        <th>Tipo</th>
                        <th>Asunto</th>
                        <th>Fecha de Creación</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($pqrs as $fila) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($fila["numero_radicado"]) . "</td>";
                        echo "<td>" . htmlspecialchars($fila["tipo"]) . "</td>";
                        echo "<td>" . htmlspecialchars($fila["asunto"]) . "</td>";
                        echo "<td>" . htmlspecialchars($fila["fecha_creacion"]) . "</td>";
                        echo "<td class='text-center'>";
                        $estado_nombre = htmlspecialchars($fila["estado_nombre"]);
                        $clase_estado = '';

                        switch ($estado_nombre) {
                            case 'Pendiente':
                                $clase_estado = 'bg-danger text-white';
                                break;
                            case 'En Proceso':
                                $clase_estado = 'bg-warning';
                                break;
                            case 'Atendido':
                                $clase_estado = 'bg-success text-white';
                                break;
                            case 'Cerrado':
                                $clase_estado = 'bg-dark text-white';
                                break;
                            default:
                                $clase_estado = '';
                                break;
                        }
                        echo "<span class='badge " . $clase_estado . "'>" . $estado_nombre . "</span>"; // Badge con la clase de color
                        echo "</td>";
                        echo "<td>
    <button type='button' class='btn btn-primary btn-sm' data-bs-toggle='modal' data-bs-target='#detallePQRModal-" . htmlspecialchars($fila["id"]) . "' title='Ver Detalles'><i class='fas fa-eye'></i></button>
     <button type='button' class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editarPQRModal-" . htmlspecialchars($fila["id"]) . "' title='Editar'><i class='fas fa-edit'></i></button>
</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>

            <?php
            // Generar los modales después de la tabla
            foreach ($pqrs as $fila) {
                $id_pqr_actual = $fila['id'];
                include 'components/modals/detalle_pqr.php';
                include('components/modals/editar_pqr.php');
            }
            ?>

            <div style="min-height: 100px;"> </div> <!-- Espacio abajo de la tabla -->
        </div>