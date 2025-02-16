<?php

$sql = "SELECT * FROM groups";
$result = $conn->query($sql);
$data = [];

// Llenar $data con los resultados de la consulta
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Reiniciar el puntero del resultado para usarlo después en la tabla
$result = $conn->query($sql);

// Obtener datos únicos para los filtros
$departamentos = ['BOYACÁ', 'CUNDINAMARCA'];
$programas = [];
$modalidades = [];
$sedes = [];

foreach ($data as $row) {
    $sede = $row['headquarters'];
    if (!in_array($sede, $sedes)) {
        $sedes[] = $sede;
    }
    if (!in_array($row['program'], $programas)) {
        $programas[] = $row['program'];
    }
    if (!in_array($row['mode'], $modalidades)) {
        $modalidades[] = $row['mode'];
    }
}

// Ordenar los arrays para mejor visualización
sort($sedes);
sort($programas);
sort($modalidades);

?>

<div class="row p-3 mb-1">
    <b class="text-left mb-1"><i class="bi bi-filter-circle"></i> Filtrar beneficiario</b>

    <div class="col-md-6 col-sm-12 col-lg-3">
        <div class="filter-title"><i class="bi bi-map"></i> Departamento</div>
        <div class="card filter-card card-department" data-icon="📍">
            <div class="card-body">
                <select id="filterDepartment" class="form-select">
                    <option value="">Todos los departamentos</option>
                    <option value="BOYACÁ">BOYACÁ</option>
                    <option value="CUNDINAMARCA">CUNDINAMARCA</option>
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-sm-12 col-lg-3">
        <div class="filter-title"><i class="bi bi-building"></i> Sede</div>
        <div class="card filter-card card-headquarters" data-icon="🏫">
            <div class="card-body">
                <select id="filterHeadquarters" class="form-select">
                    <option value="">Todas las sedes</option>
                    <?php foreach ($sedes as $sede): ?>
                        <option value="<?= htmlspecialchars($sede) ?>"><?= htmlspecialchars($sede) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-sm-12 col-lg-3">
        <div class="filter-title"><i class="bi bi-mortarboard"></i> Programa</div>
        <div class="card filter-card card-program" data-icon="🎓">
            <div class="card-body">
                <select id="filterProgram" class="form-select">
                    <option value="">Todos los programas</option>
                    <?php foreach ($programas as $programa): ?>
                        <option value="<?= htmlspecialchars($programa) ?>"><?= htmlspecialchars($programa) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-sm-12 col-lg-3">
        <div class="filter-title"><i class="bi bi-laptop"></i> Modalidad</div>
        <div class="card filter-card card-mode" data-icon="💻">
            <div class="card-body">
                <select id="filterMode" class="form-select">
                    <option value="">Todas las modalidades</option>
                    <option value="Virtual">Virtual</option>
                    <option value="Presencial">Presencial</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="table-responsive">
        <button id="exportarExcel" class="btn btn-success mb-3"
            onclick="window.location.href='components/registerMoodle/export_excel_enrolled.php?action=export'">
            <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
        </button>
        <table id="listaInscritos" class="table table-hover table-bordered">
            <thead class="thead-dark text-center">
                <tr class="text-center">
                    <th>Tipo ID</th>
                    <th>Numero de ID</th>
                    <th>Nombre completo</th>
                    <th>Correo personal</th>
                    <th>Correo institucional</th>
                    <th>Departamento</th>
                    <th>Sede</th>
                    <th>Modalidad</th>
                    <th>Bootcamp</th>
                    <th>Ingles Nivelatorio</th>
                    <th>English Code</th>
                    <th>Habilidades</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr data-department="<?= htmlspecialchars($row['department']) ?>"
                        data-headquarters="<?= htmlspecialchars($row['headquarters']) ?>"
                        data-program="<?= htmlspecialchars($row['program']) ?>"
                        data-mode="<?= htmlspecialchars($row['mode']) ?>">

                        
                        <td><?php echo htmlspecialchars($row['type_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['number_id']); ?></td>
                        <td style="width: 300px; min-width: 300px; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['institutional_email']); ?></td>
                        <td>
                            <?php
                            $departamento = htmlspecialchars($row['department']);
                            if ($departamento === 'CUNDINAMARCA') {
                                echo "<button class='btn bg-lime-light w-100'><b>{$departamento}</b></button>"; // Botón verde para CUNDINAMARCA
                            } elseif ($departamento === 'BOYACÁ') {
                                echo "<button class='btn bg-indigo-light w-100'><b>{$departamento}</b></button>"; // Botón azul para BOYACÁ
                            } else {
                                echo "<span>{$departamento}</span>"; // Texto normal para otros valores
                            }
                            ?>
                        </td>
                        <td><b class="text-center"><?php echo htmlspecialchars($row['headquarters']); ?></b></td>
                        <td><?php echo htmlspecialchars($row['mode']); ?></td>
                        <td><?php echo htmlspecialchars($row['id_bootcamp'] . ' - ' . $row['bootcamp_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['id_leveling_english'] . ' - ' . $row['leveling_english_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['id_english_code'] . ' - ' . $row['english_code_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['id_skills'] . ' - ' . $row['skills_name']); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function getFormDataFromRow(row) {
        const studentId = row.dataset.numberId;

        const getCourseData = (prefix) => {
            const select = document.getElementById(prefix);
            if (!select) {
                console.error(`Select no encontrado: ${prefix}`);
                return {
                    id: '0',
                    name: 'No seleccionado'
                };
            }
            const option = select.options[select.selectedIndex];
            const fullText = option.text;
            const id = option.value;
            const name = fullText.split(' - ').slice(1).join(' - ').trim();

            console.log(`${prefix}:`, {
                id,
                name
            }); // Debug
            return {
                id,
                name
            };
        };

        const formData = {
            type_id: row.dataset.typeId,
            number_id: studentId,
            full_name: row.dataset.fullName,
            email: row.dataset.email,
            institutional_email: row.dataset.institutionalEmail,
            department: row.dataset.department,
            headquarters: row.dataset.headquarters,
            program: row.dataset.program,
            mode: row.dataset.mode,
            password: 'UTt@2025!',
            id_bootcamp: getCourseData('bootcamp').id,
            bootcamp_name: getCourseData('bootcamp').name,
            id_leveling_english: getCourseData('ingles').id,
            leveling_english_name: getCourseData('ingles').name,
            id_english_code: getCourseData('english_code').id,
            english_code_name: getCourseData('english_code').name,
            id_skills: getCourseData('skills').id,
            skills_name: getCourseData('skills').name
        };

        console.log('FormData:', formData); // Debug
        return formData;
    }
</script>