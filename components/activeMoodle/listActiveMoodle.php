<?php

$sql = "SELECT * FROM groups";

$result = $conn->query($sql);
$data = [];

?>

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
                    <th>Bootcamp</th>
                    <th>Ingles Nivelatorio</th>
                    <th>English Code</th>
                    <th>Habilidades</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['type_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['number_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['institutional_email']); ?></td>
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