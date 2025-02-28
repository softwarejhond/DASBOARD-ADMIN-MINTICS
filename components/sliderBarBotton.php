<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario
?>
<div class="offcanvas offcanvas-bottom text-bg-dark" tabindex="-1" id="offcanvasBottom" aria-labelledby="offcanvasBottomLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasBottomLabel"><i class="bi bi-boxes"></i> Gestión de matriculados</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body small">
        <fieldset class="checkbox-group-bottom d-flex flex-wrap justify-content-center align-items-center">

            <!-- Botones e íconos organizados horizontalmente -->
            <?php if ($rol === 'Administrador' || $rol === 'Docente'|| $rol === 'Académico'): ?>
                <div class="checkbox me-3 text-center" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Tabla de asistencia">
                    <a href="attendance.php">
                        <label class="checkbox-wrapper">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <i class="bi bi-list-check icono text-indigo-dark "></i>
                                </span>
                                <span class="checkbox-label">Asistencia</span>
                            </span>
                        </label>
                    </a>
                </div>
            <?php endif; ?>
            <?php if ($rol === 'Administrador' || $rol === 'Académico'): ?>
            <div class="checkbox me-3 text-center" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Lista de matriculados">
                <a href="activeMoodle.php">
                    <label class="checkbox-wrapper">
                        <span class="checkbox-tile">
                            <span class="checkbox-icon icono text-indigo-dark">
                                <i class="bi bi-person-fill-check icono text-indigo-dark "></i>
                            </span>
                            <span class="checkbox-label">Matriculados</span>
                        </span>
                    </label>
                </a>
            </div>
            <?php endif; ?>
            <?php if ($rol === 'Administrador' || $rol === 'Académico'): ?>
            <div class="checkbox me-3 text-center" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Asignar docente a un grupo">
                <a href="asignarDocentes.php">
                    <label class="checkbox-wrapper">
                        <span class="checkbox-tile">
                            <span class="checkbox-icon">
                                <i class="bi bi-pencil-square icono text-indigo-dark "></i>
                            </span>
                            <span class="checkbox-label">Ingresar</span>
                        </span>
                    </label>
                </a>
            </div>
            <?php endif; ?>
            <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Docente'): ?>
            <div class="checkbox me-3 text-center" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Actualizar asistencia individual">
            <a href="individualAttendance.php">
                    <label class="checkbox-wrapper">
                        <span class="checkbox-tile">
                            <span class="checkbox-icon">
                                <i class="bi bi-person-lines-fill  icono text-indigo-dark"></i>
                            </span>
                            <span class="checkbox-label">Ingresar</span>
                        </span>
                    </label>
                </a>
            </div>
            <?php endif; ?>
            <?php if ($rol === 'Administrador' || $rol === 'Académico'): ?>
            <div class="checkbox me-3 text-center" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Asistencia grupal">    
                <a href="attendanceGroup.php">
                    <label class="checkbox-wrapper">
                        <span class="checkbox-tile">
                            <span class="checkbox-icon">
                            <i class="bi bi-list-task icono text-indigo-dark "></i>
                            </span>
                            <span class="checkbox-label">Ingresar</span>
                        </span>
                    </label>
                </a>
            </div>
            <?php endif; ?>
            <?php if ($rol === 'Administrador' || $rol === 'Académico'): ?>
                <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Matricula múltiple">
                        <a href="multipleMoodle.php"><label class="checkbox-wrapper">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="bi bi-robot icono text-indigo-dark "></i>
                                    </span>
                                    <span class="checkbox-label">Ingresar</span>
                                </span>
                            </label>
                        </a>
                    </div>
            <?php endif; ?>
            <?php if ($rol === 'Administrador' ):?>
                <div class="checkbox me-3 text-center" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Asignar mentores a grupos">
                    <a href="asignarMentores.php"><label class="checkbox-wrapper">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <i class="bi bi-pencil-square icono text-indigo-dark "></i>
                                </span>
                                <span class="checkbox-label">Ingresar</span>
                            </span>
                        </label>
                    </a>
                </div>
            <?php endif; ?>
            <?php if ($rol === 'Administrador' ):?>
                <div class="checkbox me-3 text-center" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Asignar monitores a grupos">
                    <a href="asignarMonitores.php"><label class="checkbox-wrapper">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <i class="bi bi-pencil-square icono text-indigo-dark "></i>
                                </span>
                                <span class="checkbox-label">Ingresar</span>
                            </span>
                        </label>
                    </a>
                </div>
            <?php endif; ?>
        </fieldset>

    </div>
</div>