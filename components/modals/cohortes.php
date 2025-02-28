<?php
include '../../controller/conexion.php';

// Query to fetch all cohorts
$query = "SELECT * FROM cohorts ORDER BY cohort_number";
$result = mysqli_query($conn, $query);
?>

<!-- Modal -->
<div class="modal fade" id="cohortModal" tabindex="-1" aria-labelledby="cohortModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cohortModalLabel">Información de Cohortes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Date display section -->
                <div id="dateInfo" class="mb-3" style="display: none;">
                    <div class="alert alert-info">
                        <p><strong>Fecha de inicio:</strong> <span id="startDate"></span></p>
                        <p><strong>Fecha de finalización:</strong> <span id="endDate"></span></p>
                    </div>
                </div>

                <!-- Cohort selection -->
                <div class="form-group">
                    <label for="cohortSelect">Seleccione una cohorte:</label>
                    <select class="form-control" id="cohortSelect">
                        <option value="">Seleccione una cohorte</option>
                        <?php while($row = mysqli_fetch_assoc($result)) { ?>
                            <option value="<?php echo $row['cohort_number']; ?>" 
                                    data-start="<?php echo $row['start_date']; ?>"
                                    data-end="<?php echo $row['finish_date']; ?>">
                                Cohorte <?php echo $row['cohort_number']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('cohortSelect').addEventListener('change', function() {
    const dateInfo = document.getElementById('dateInfo');
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    
    if (this.value) {
        const selectedOption = this.options[this.selectedIndex];
        startDate.textContent = selectedOption.dataset.start;
        endDate.textContent = selectedOption.dataset.end;
        dateInfo.style.display = 'block';
    } else {
        dateInfo.style.display = 'none';
    }
});
</script>