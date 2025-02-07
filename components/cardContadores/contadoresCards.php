<?php
//NO SE REQUIERE IMPORTAR LA CONEXIÓN PORQUE DESDE EL MAIN YA ESTÁ CONECTADA

// Obtener total de usuarios
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

// Obtener total de usuarios con programa
$sql_sin_verificar = "SELECT COUNT(*) AS total_sinVerificar FROM user_register WHERE status = '1' AND statusAdmin = '0'";
$result_sinVerificar= mysqli_query($conn, query: $sql_sin_verificar);
$total_sinVerificar= mysqli_fetch_assoc($result_sinVerificar)['total_sinVerificar'];

// Calcular porcentajes
$porc_boyaca = ($total_usuarios > 0) ? round(($total_boyaca / $total_usuarios) * 100, 2) : 0;
$porc_cundinamarca = ($total_usuarios > 0) ? round(($total_cundinamarca / $total_usuarios) * 100, 2) : 0;
$porc_sinVerificar = ($total_usuarios > 0) ? round(($total_sinVerificar / $total_usuarios) * 100, 2) : 0;
?>

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
                    <h2><span id="con_programa"><?php echo $total_sinVerificar; ?></span> | <span id="porc_con_programa"><?php echo $porc_sinVerificar; ?></span>%</h2>
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
                    <h5 class="card-title">Usuarios verificados</h5>
                    <h2><span id="total_usuarios"><?php echo $total_usuarios; ?></span></h2>
                    <a href="verifiedUsers.php" class="btn btn-light btn-sm">Ver detalles</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta Usuarios en Cundinamarca -->
    <div class="col-sm-12 col-lg-3 col-md-6 mb-3 mb-sm-0 mb-md-1">
        <div class="card bg-indigo-light text-dark shadow">
            <div class="card-body d-flex align-items-center">
                <div class="icon-container me-3">
                    <i class="fas fa-map-marker-alt fa-3x text-gray-dark"></i>
                </div>
                <div class="text-container">
                    <h5 class="card-title">Usuarios Cundinamarca</h5>
                    <h2><span id="cundinamarca"><?php echo $total_cundinamarca; ?></span> | <span id="porc_cundinamarca"><?php echo $porc_cundinamarca; ?></span>%</h2>
                    <a href="#" class="btn btn-light btn-sm">Ver detalles</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta Usuarios en Boyacá -->
    <div class="col-sm-12 col-lg-3 col-md-6 mb-3 mb-sm-0 mb-md-1">
        <div class="card bg-teal-light text-dark shadow">
            <div class="card-body d-flex align-items-center">
                <div class="icon-container me-3">
                    <i class="fas fa-map-marker-alt fa-3x text-gray-dark"></i>
                </div>
                <div class="text-container">
                    <h5 class="card-title">Usuarios Boyacá</h5>
                    <h2><span id="boyaca"><?php echo $total_boyaca; ?></span> | <span id="porc_boyaca"><?php echo $porc_boyaca; ?></span>%</h2>
                    <a href="#" class="btn btn-light btn-sm">Ver detalles</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    function actualizarDatos() {
        $.ajax({
            url: "?ajax=1",
            method: "GET",
            dataType: "json",
            success: function (response) {
                $("#total_usuarios").text(response.total_usuarios);
                $("#boyaca").text(response.total_boyaca);
                $("#porc_boyaca").text(response.porc_boyaca);
                $("#cundinamarca").text(response.total_cundinamarca);
                $("#porc_cundinamarca").text(response.porc_cundinamarca);
                $("#con_programa").text(response.con_programa);
                $("#porc_con_programa").text(response.porc_con_programa);
            },
            error: function () {
                console.error("No se pudieron cargar los datos.");
            }
        });
    }

    // Cargar datos al inicio y actualizar cada 10 segundos
    actualizarDatos();
    setInterval(actualizarDatos, 10000);
});
</script>
