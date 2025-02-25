<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario
?>
<div class="offcanvas offcanvas-start" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions" aria-labelledby="offcanvasWithBothOptionsLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasWithBothOptionsLabel"><i class="bi bi-boxes"></i> SIVP Aplicaciones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>

    </div>
    <div class="offcanvas-body">
        <div class="container-fluid">
            <fieldset class="checkbox-group">
                <legend class="checkbox-group-legend">

                </legend>
                <?php if ($rol === 'Administrador'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Añadir usuario">
                        <label class="checkbox-wrapper" data-bs-target="#exampleModalNuevoAdmin" data-bs-toggle="modal">

                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <i class="bi bi-person-add icono"></i>
                                </span>
                                <span class="checkbox-label">Añadir</span>
                            </span>
                        </label>
                    </div>
                <?php endif; ?>
                <?php if ($rol === 'Administrador' || $rol === 'Asesor'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Contacto de registros">
                        <a href="registrarionsContact.php">
                            <label class="checkbox-wrapper" data-bs-target="#exampleModalNuevoReporte" data-bs-toggle="modal">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="bi bi-people-fill icono"></i>
                                    </span>
                                    <span class="checkbox-label ">Ingresar</span>
                                </span>
                            </label>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if ($rol === 'Administrador'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Añadir asesor">
                        <label class="checkbox-wrapper" data-bs-target="#exampleModalNuevoAsesor" data-bs-toggle="modal">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <i class="bi bi-people-fill icono"></i>
                                </span>
                                <span class="checkbox-label">Añadir</span>
                            </span>
                        </label>
                    </div>
                <?php endif; ?>
                <?php if ($rol === 'Administrador' || $rol === 'Asesor'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Usuarios verificados">
                        <a href="verifiedUsers.php">
                            <label class="checkbox-wrapper">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="bi bi-mortarboard-fill icono"></i>
                                    </span>
                                    <span class="checkbox-label">Ingresar</span>
                                </span>
                            </label>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if ($rol === 'Administrador' || $rol === 'Asesor'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Actualizar identificación">
                        <a href="updateDocument.php">
                            <label class="checkbox-wrapper">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="bi bi-person-vcard-fill icono"></i>
                                    </span>
                                    <span class="checkbox-label ">Actualizar ID</span>
                                </span>
                            </label>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if ($rol === 'Administrador' || $rol === 'Asesor'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Consulta individual">
                        <a href="individualSearch.php">
                            <label class="checkbox-wrapper">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="bi bi-person-bounding-box icono"></i>
                                    </span>
                                    <span class="checkbox-label">Ingresar</span>
                                </span>
                            </label>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if ($rol === 'Administrador' || $rol === 'Académico'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Regisrar usuarios en Moodle">
                        <a href="registerMoodle.php">
                            <label class="checkbox-wrapper">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="bi bi-robot icono"></i>
                                    </span>
                                    <span class="checkbox-label">Ingresar</span>
                                </span>
                            </label>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if ($rol === 'Administrador' || $rol === 'Académico'): ?>
                <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Perfil">
                    <a href="contactLogs.php"> <label class="checkbox-wrapper">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                <i class="fa-solid fa-address-book icono"></i>
                                </span>
                                <span class="checkbox-label">Registros</span>
                            </span>
                        </label>
                    </a>
                </div>
                <?php endif; ?>
                <?php if ($rol === 'Administrador'): ?>
                    <div class="checkbox me-3 text-center" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Exportar datos">
                        <a href="components/infoWeek/exportAll.php?action=export">
                            <label class="checkbox-wrapper">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="bi bi-file-earmark-excel-fill icono text-success-dark"></i>
                                    </span>
                                    <span class="checkbox-label">Exportar</span>
                                </span>
                            </label>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Visítanos">
                    <a href="https://agenciaeaglesoftware.com/" target="_blank">
                        <label class="checkbox-wrapper">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <img src="img/icons/eagle.png" alt="LogoEagle" width="60px">
                                </span>
                            </span>
                        </label>
                    </a>

                </div>
            </fieldset>
        </div>
    </div>
    <?php include("controller/footer.php"); ?>
</div>