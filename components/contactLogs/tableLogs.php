<?php

try {
    $query = "SELECT 
        cl.*,
        CONCAT(ur.first_name, ' ', ur.second_name, ' ', ur.first_last, ' ', ur.second_last) as student_name,
        a.name as advisor_name
    FROM contact_log cl
    LEFT JOIN user_register ur ON cl.number_id = ur.number_id
    LEFT JOIN advisors a ON cl.idAdvisor = a.idAdvisor
    ORDER BY cl.contact_date DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $logs = $result->fetch_all(MYSQLI_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<table id="listaInscritos" class="table table-hover table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Estudiante</th>
            <th>Asesor</th>
            <th>Contacto<br>Establecido</th>
            <th>Fecha y hora<br>de Contacto</th>
            <th>Detalles</th>
            <th>Continúa<br>Interesado</th>
            <th>Observaciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td><?php echo htmlspecialchars($log['number_id']); ?></td>
                <td><?php echo htmlspecialchars($log['student_name']); ?></td>
                <td><?php echo htmlspecialchars($log['advisor_name']); ?></td>
                <td class="text-center"><?php echo $log['contact_established'] == 1 ? 'Sí' : 'No'; ?></td>
                <td><?php echo htmlspecialchars($log['contact_date']); ?></td>
                <td><?php echo htmlspecialchars($log['details']); ?></td>
                <td class="text-center" ><?php echo $log['continues_interested'] ? 'Sí' : 'No'; ?></td>
                <td>
                    <!-- Button trigger modal -->
                    <div class="text-center">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#observationModal<?php echo htmlspecialchars($log['id']); ?>"  data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-custom-class="custom-tooltip"
                            data-bs-title="Observaciones">
                            <i class="bi bi-person-lines-fill"></i>
                        </button>
                    </div>

                    <!-- Modal -->
                    <div class="modal fade" id="observationModal<?php echo htmlspecialchars($log['id']); ?>" tabindex="-1" role="dialog" aria-labelledby="observationModalLabel<?php echo htmlspecialchars($log['id']); ?>" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="observationModalLabel<?php echo htmlspecialchars($log['id']); ?>">Observaciones</h5>
                                </div>
                                <div class="modal-body">
                                    <?php echo htmlspecialchars($log['observation'] ?? ''); ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="bi bi-x"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.bundle.min.js"></script>