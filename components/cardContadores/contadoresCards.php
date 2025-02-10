<?php
// NO SE REQUIERE IMPORTAR LA CONEXIÓN PORQUE DESDE EL MAIN YA ESTÁ CONECTADA

// Obtener total de usuarios verificados
$sql_total = "SELECT COUNT(*) AS total FROM user_register WHERE status = '1' AND statusAdmin = '1'";
$result_total = mysqli_query($conn, $sql_total);
$total_usuarios = mysqli_fetch_assoc($result_total)['total'];

// Obtener total de usuarios en Boyacá
$sql_boyaca = "SELECT COUNT(*) AS total_boyaca FROM user_register WHERE status = '1' AND statusAdmin = '1' AND department = 15";
$result_boyaca = mysqli_query($conn, $sql_boyaca);
$total_boyaca = mysqli_fetch_assoc($result_boyaca)['total_boyaca'];

// Obtener total de usuarios en Cundinamarca
$sql_cundinamarca = "SELECT COUNT(*) AS total_cundinamarca FROM user_register WHERE status = '1' AND statusAdmin = '1' AND department = 25";
$result_cundinamarca = mysqli_query($conn, $sql_cundinamarca);
$total_cundinamarca = mysqli_fetch_assoc($result_cundinamarca)['total_cundinamarca'];

// Obtener total de usuarios sin verificar
$sql_sin_verificar = "SELECT COUNT(*) AS total_sinVerificar FROM user_register WHERE status = '1' AND statusAdmin = '0'";
$result_sinVerificar = mysqli_query($conn, $sql_sin_verificar);
$total_sinVerificar = mysqli_fetch_assoc($result_sinVerificar)['total_sinVerificar'];

// Obtener total de Gobernación de Boyacá
$sql_GobernacionBoyaca = "SELECT COUNT(*) AS total_GobernacionBoyaca FROM user_register WHERE status = '1' AND statusAdmin = '0' AND institution = 'Gobernación de Boyacá'";
$result_GobernacionBoyaca = mysqli_query($conn, $sql_GobernacionBoyaca);
$total_GobernacionBoyaca = mysqli_fetch_assoc($result_GobernacionBoyaca)['total_GobernacionBoyaca'];


// Calcular porcentajes
$porc_boyaca = ($total_usuarios > 0) ? round(($total_boyaca / $total_usuarios) * 100, 2) : 0;
$porc_cundinamarca = ($total_usuarios > 0) ? round(($total_cundinamarca / $total_usuarios) * 100, 2) : 0;
$porc_sinVerificar = ($total_usuarios > 0) ? round(($total_sinVerificar / $total_usuarios) * 100, 2) : 0;
$porc_GobernacionBoyaca = ($total_usuarios > 0) ? round(($total_GobernacionBoyaca / $total_usuarios) * 100, 2) : 0;

// Si la petición es AJAX, devolver JSON
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    echo json_encode([
        "total_usuarios" => $total_usuarios,
        "total_boyaca" => $total_boyaca,
        "porc_boyaca" => $porc_boyaca,
        "total_cundinamarca" => $total_cundinamarca,
        "porc_cundinamarca" => $porc_cundinamarca,
        "total_sinVerificar" => $total_sinVerificar,
        "porc_sinVerificar" => $porc_sinVerificar,
        "total_GobernacionBoyaca" => $total_GobernacionBoyaca,
        "porc_GobernacionBoyaca" => $porc_GobernacionBoyaca
    ]);
    exit;
}
?>

<!-- HTML de las tarjetas -->
<div class="row">
    <!-- Tarjeta Usuarios por Verificar -->
    <div class="col-sm-12 col-lg-3 col-md-6 mb-3 mb-sm-0 mb-md-1">
        <div class="card bg-magenta-light text-white shadow">
            <div class="card-body d-flex align-items-center">
                <div class="icon-container me-3">
                    <i class="fas fa-user-clock fa-3x text-gray-dark"></i>
                </div>
                <div class="text-container">
                    <h5 class="card-title">Usuarios por verificar</h5>
                    <h2>
                        <span id="con_programa"><?php echo $total_sinVerificar; ?></span> | 
                        <span id="porc_con_programa"><?php echo $porc_sinVerificar; ?></span>%
                    </h2>
                    <a href="registrarionsContact.php" class="btn btn-light btn-sm">Ver detalles</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta Total de Usuarios -->
    <div class="col-sm-12 col-lg-3 col-md-6 mb-3 mb-sm-0 mb-md-1">
        <div class="card bg-amber-light text-dark shadow">
            <div class="card-body d-flex align-items-center">
                <div class="icon-container me-3">
                    <i class="fas fa-users fa-3x text-gray-dark"></i>
                </div>
                <div class="text-container">
                    <h5 class="card-title">Total de Usuarios verificados</h5>
                    <h2><span id="total_usuarios"><?php echo $total_usuarios; ?></span></h2>
                    <a href="verifiedUsers.php" class="btn btn-light btn-sm">Ver detalles</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta Usuarios en Cundinamarca -->
    <div class="col-sm-12 col-lg-3 col-md-6 mb-3 mb-sm-0 mb-md-1">
        <div class="card bg-indigo-light shadow">
            <div class="card-body d-flex align-items-center">
                <div class="icon-container me-3">
                    <i class="fas fa-map-marker-alt fa-3x text-gray-dark"></i>
                </div>
                <div class="text-container">
                    <h5 class="card-title">Usuarios en Cundinamarca</h5>
                    <h2>
                        <span id="total_cundinamarca"><?php echo $total_cundinamarca; ?></span> | 
                        <span id="porc_cundinamarca"><?php echo $porc_cundinamarca; ?></span>%
                    </h2>
                    <a href="#" class="btn btn-light btn-sm">Ver detalles</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta Usuarios en Boyacá -->
    <div class="col-sm-12 col-lg-3 col-md-6 mb-3 mb-sm-0 mb-md-1">
        <div class="card bg-teal-light shadow">
            <div class="card-body d-flex align-items-center">
                <div class="icon-container me-3">
                    <i class="fas fa-map-marker-alt fa-3x text-gray-dark"></i>
                </div>
                <div class="text-container">
                    <h5 class="card-title">Usuarios en Boyacá</h5>
                    <h2>
                        <span id="total_boyaca"><?php echo $total_boyaca; ?></span> | 
                        <span id="porc_boyaca"><?php echo $porc_boyaca; ?></span>%
                    </h2>
                    <a href="#" class="btn btn-light btn-sm">Ver detalles</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tarjeta Gobernación Boyacá -->
    <div class="col-sm-12 col-lg-3 col-md-6 mb-3 mb-sm-0 mb-md-1">
        <div class="card bg-lime-light shadow">
            <div class="card-body d-flex align-items-center">
                <div class="icon-container me-3">
                    <i class="fas fa-university fa-3x text-gray-dark"></i>
                </div>
                <div class="text-container">
                    <h5 class="card-title">Gobernación Boyacá</h5>
                    <h2>
                        <span id="total_GobernacionBoyaca"><?php echo $total_GobernacionBoyaca; ?></span> | 
                        <span id="porc_GobernacionBoyaca"><?php echo $porc_GobernacionBoyaca; ?></span>%
                    </h2>
                    <a href="#" class="btn btn-light btn-sm">Registrados por el formulario de la Gobernación</a>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
$(document).ready(function () {
    function actualizarContadores() {
        $.ajax({
            url: window.location.href.split('?')[0] + "?ajax=1",
            method: "GET",
            dataType: "json",
            success: function(data) {
                $('#total_usuarios').text(data.total_usuarios);
                $('#total_boyaca').text(data.total_boyaca);
                $('#porc_boyaca').text(data.porc_boyaca + "%");
                $('#total_cundinamarca').text(data.total_cundinamarca);
                $('#porc_cundinamarca').text(data.porc_cundinamarca + "%");
                $('#con_programa').text(data.total_sinVerificar);
                $('#porc_con_programa').text(data.porc_sinVerificar + "%");
                $('#total_GobernacionBoyaca').text(data.total_GobernacionBoyaca);
                $('#porc_GobernacionBoyaca').text(data.porc_GobernacionBoyaca + "%");
            },
            error: function() {
                console.error("No se pudieron cargar los datos.");
            }
        });
    }

    // Actualizar los contadores cada 5 segundos
    actualizarContadores();
    setInterval(actualizarContadores, 5000);
});
</script>
