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
                <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Usuarios berificados">
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
                <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Carousel de imagenes">

                    <a href="carusel.php">
                        <label class="checkbox-wrapper">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <i class="bi bi-images icono"></i>
                                </span>
                                <span class="checkbox-label ">Carusel</span>
                            </span>
                        </label>
                    </a>
                </div>
                <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Programar retiro">
                    <a href="registrarRetiro.php">
                        <label class="checkbox-wrapper">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <i class="bi bi-door-open icono"></i>
                                </span>
                                <span class="checkbox-label">Retiros</span>
                            </span>
                        </label>
                    </a>
                </div>
                <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Perfil">
                    <a href="perfil.php"> <label class="checkbox-wrapper">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <i class="bi bi-person-circle icono"></i>
                                </span>
                                <span class="checkbox-label">Perfil</span>
                            </span>
                        </label>
                    </a>
                </div>
                <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Configuracion">
                    <a href="actualizar-smtp.php"><label class="checkbox-wrapper">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <i class="bi bi-gear-wide-connected icono"></i>
                                </span>
                                <span class="checkbox-label">Configuración</span>
                            </span>
                        </label>
                    </a>
                </div>
                <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Añadir tipo de propiedad">
                    <label class="checkbox-wrapper" data-bs-target="#exampleModalToggle" data-bs-toggle="modal">
                        <span class="checkbox-tile">
                            <span class="checkbox-icon">
                                <i class="bi bi-database-fill-add icono"></i>
                            </span>
                            <span class="checkbox-label">Añadir</span>
                        </span>
                    </label>
                </div>
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