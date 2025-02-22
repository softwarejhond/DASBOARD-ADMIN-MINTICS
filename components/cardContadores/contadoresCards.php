<!-- Barra de progreso global -->
<div class="progress mt-3">
    <div id="progress-bar-global" class="progress-bar progress-bar-striped progress-bar-animated bg-indigo-dark" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
</div>
<div class="text-center">
    <small id="countdown-timer" class="text-muted">Actualización en tiempo real</small>
</div>
<!-- HTML de las tarjetas -->
<div class="row">
    <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
        <div class="row">
            <!-- Tarjeta Total Usuarios Registrados -->
            <div class="col-sm-12 col-lg-6 col-md- mb-3 mb-sm-0 mb-md-1">
                <div class="card bg-teal-dark text-white shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-container me-3">
                            <i class="bi bi-people-fill fa-3x text-white"></i>
                        </div>
                        <div class="text-container">
                            <h5 class="card-title">Total usuarios registrados</h5>
                            <h2>
                                <div class="spinner-grow text-light" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span id="usuers_registrados"></span>
                                <br>
                                <h6 id="countdown-timer" class="text-white">Registros</h6>
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tarjeta Usuarios por Verificar -->
            <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
                <div class="card bg-magenta-light text-white shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-container me-3">
                            <i class="fas fa-user-clock fa-3x text-gray-dark"></i>
                        </div>
                        <div class="text-container">
                            <h5 class="card-title">Usuarios por verificar</h5>
                            <h2>
                                <span id="total_sinVerificar"></span> |
                                <span id="porc_sinVerificar"></span>
                            </h2>
                            <a href="registrarionsContact.php" class="btn btn-light btn-sm">Ver detalles</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta Total de Usuarios -->
            <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
                <div class="card bg-amber-light text-dark shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-container me-3">
                            <i class="fas fa-users fa-3x text-gray-dark"></i>
                        </div>
                        <div class="text-container">
                            <h5 class="card-title">Total de Usuarios verificados</h5>
                            <h2><span id="total_usuarios"></span></h2>
                            <a href="verifiedUsers.php" class="btn btn-light btn-sm">Ver detalles</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta Usuarios en Cundinamarca -->
            <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
                <div class="card bg-indigo-light shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-container me-3">
                            <i class="fas fa-map-marker-alt fa-3x text-gray-dark"></i>
                        </div>
                        <div class="text-container">
                            <h5 class="card-title">Usuarios Cundinamarca verificados</h5>
                            <h2>
                                <span id="total_cundinamarca"></span> |
                                <span id="porc_cundinamarca"></span>
                            </h2>
                            <a href="#" class="btn btn-light btn-sm">Ver detalles</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta Usuarios en Boyacá -->
            <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
                <div class="card bg-teal-light shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-container me-3">
                            <i class="fas fa-map-marker-alt fa-3x text-gray-dark"></i>
                        </div>
                        <div class="text-container">
                            <h5 class="card-title">Usuarios en Boyacá verificados</h5>
                            <h2>
                                <span id="total_boyaca"></span> |
                                <span id="porc_boyaca"></span>
                            </h2>
                            <a href="#" class="btn btn-light btn-sm">Ver detalles</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta establecio contacto -->
            <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
                <div class="card bg-lime-light shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-container me-3">
                            <i class="bi bi-telephone-inbound fa-3x text-gray-dark"></i>
                        </div>
                        <div class="text-container">
                            <h5 class="card-title">Se estableció contacto</h5>
                            <h2>
                                Sí: <span id="total_contacto_si"></span> |
                                <span id="porc_contacto_si"></span><br>
                                No: <span id="total_contacto_no"></span> |
                                <span id="porc_contacto_no"></span>
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tarjeta establecio contacto de los verificados -->
            <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
                <div class="card bg-brown-light shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-container me-3">
                            <i class="bi bi-telephone-inbound fa-3x text-white"></i>
                        </div>
                        <div class="text-container text-white">
                            <h5 class="card-title">Se estableció contacto a verificados</h5>
                            <h2>
                                Sí: <span id="total_contacto_si_admin"></span> |
                                <span id="porc_contacto_si_admin"></span><br>
                                No: <span id="total_contacto_no_admin"></span> |
                                <span id="porc_contacto_no_admin"></span>
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
        <!-- Tarjeta establecio contacto de los verificados -->
        <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
            <div class="card bg-teal-light shadow">
                <div class="card-body d-flex align-items-center">

                    <div class="text-container text-black">
                        <h5 class="card-title"> <i class="bi bi-geo-alt-fill fa-2x text-black"></i> Registros por departamento</h5>
                        <?php include("components/graphics/registerDeparments.php");  ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<br>
<br>
<!-- Asegúrate de incluir jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
    $(document).ready(function() {
        function actualizarContadores() {
            $.ajax({
                url: 'components/cardContadores/actualizarContadores.php',
                method: 'GET',
                success: function(data) {
                    $('#usuers_registrados').text(data.total_registrados);
                    $('#total_usuarios').text(data.total_usuarios);
                    $('#total_boyaca').text(data.total_boyaca);
                    $('#porc_boyaca').text(data.porc_boyaca + '%');
                    $('#total_cundinamarca').text(data.total_cundinamarca);
                    $('#porc_cundinamarca').text(data.porc_cundinamarca + '%');
                    $('#total_sinVerificar').text(data.total_sinVerificar);
                    $('#porc_sinVerificar').text(data.porc_sinVerificar + '%');
                    $('#total_GobernacionBoyaca').text(data.total_GobernacionBoyaca);
                    $('#porc_GobernacionBoyaca').text(data.porc_GobernacionBoyaca + '%');
                    $('#total_contacto_si').text(data.total_contacto_si);
                    $('#porc_contacto_si').text(data.porc_contacto_si + '%');
                    $('#total_contacto_no').text(data.total_contacto_no);
                    $('#porc_contacto_no').text(data.porc_contacto_no + '%');
                    $('#total_contacto_si_admin').text(data.total_contacto_si_admin);
                    $('#porc_contacto_si_admin').text(data.porc_contacto_si_admin + '%');
                    $('#total_contacto_no_admin').text(data.total_contacto_no_admin);
                    $('#porc_contacto_no_admin').text(data.porc_contacto_no_admin + '%');
                },
                error: function(error) {
                    console.error('Error al obtener los datos:', error);
                }
            });
        }

        function actualizarBarraProgreso() {
            var progreso = 0;
            var intervalo = setInterval(function() {
                progreso += 20; // Incremento para completar 100% en 5 segundos
                $('#progress-bar-global').css('width', progreso + '%').attr('aria-valuenow', progreso);
                if (progreso >= 100) {
                    clearInterval(intervalo);
                    $('#progress-bar-global').css('width', '0%').attr('aria-valuenow', 0); // Reiniciar la barra de progreso
                }
            }, 1000); // Actualizar cada 1 segundo
        }

        // Ejecutar la función cada 5 segundos para actualizar en tiempo real
        function iniciarActualizacion() {
            actualizarContadores();
            actualizarBarraProgreso();
        }

        iniciarActualizacion();
        setInterval(iniciarActualizacion, 5000);
    });
</script>