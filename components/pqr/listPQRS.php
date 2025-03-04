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

        <!-- ******* NUEVO FILTRO DE TIPO ******** -->
        <div class="col-auto">
            <label for="tipo" class="form-label subTitle">Filtrar por Tipo:</label>
            <select class="form-select" id="tipo" name="tipo" onchange="this.form.submit()">
                <option value="">Todos los tipos</option>
                <option value="Petición" <?php if ($tipo_seleccionado == 'Petición') echo 'selected'; ?>>Petición</option>
                <option value="Queja" <?php if ($tipo_seleccionado == 'Queja') echo 'selected'; ?>>Queja</option>
                <option value="Reclamo" <?php if ($tipo_seleccionado == 'Reclamo') echo 'selected'; ?>>Reclamo</option>
                <option value="Sugerencia" <?php if ($tipo_seleccionado == 'Sugerencia') echo 'selected'; ?>>Sugerencia</option>
            </select>
        </div>
        <!-- ******* FIN NUEVO FILTRO DE TIPO ******** -->

        <!-- ******** BOTÓN DE EXPORTACIÓN ******** -->
        <div class="col-auto">
            <button type="button" class="btn btn-success mt-4" onclick="exportData()"><i class="fas fa-file-excel"></i> Exportar a Excel</button>
        </div>

        <script>
            function exportData() {
                // Crea un formulario dinámicamente
                var form = document.createElement('form');
                form.action = 'components/pqr/exportPQRS_process.php'; // URL del script de exportación
                form.method = 'POST'; // Usa POST para enviar datos

                // Agrega los filtros al formulario (si es necesario)
                var estado = document.getElementById('estado').value;
                var tipo = document.getElementById('tipo').value;

                // Crea un campo oculto para el estado
                var estadoInput = document.createElement('input');
                estadoInput.type = 'hidden';
                estadoInput.name = 'estado';
                estadoInput.value = estado;
                form.appendChild(estadoInput);

                // Crea un campo oculto para el tipo
                var tipoInput = document.createElement('input');
                tipoInput.type = 'hidden';
                tipoInput.name = 'tipo';
                tipoInput.value = tipo;
                form.appendChild(tipoInput);

                // Agrega el formulario al body y lo envía
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form); // Limpia el formulario después de enviar
            }
        </script>
        <!-- ******** FIN BOTÓN DE EXPORTACIÓN ******** -->

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
                    <button type='button' class='btn bg-indigo-dark btn-sm' data-bs-toggle='modal' data-bs-target='#detallePQRModal-" . htmlspecialchars($fila["id"]) . "' title='Ver Detalles'><i class='fas fa-eye'></i></button>
                    <button type='button' class='btn bg-orange-dark btn-sm' data-bs-toggle='modal' data-bs-target='#editarPQRModal-" . htmlspecialchars($fila["id"]) . "' title='Editar'><i class='fas fa-edit'></i></button>
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