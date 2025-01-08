<?php
session_start();
include("./conexion.php");
// Habilitar la visualización de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);  // Mostrar todos los errores

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Si no está logueado, redirigir a la página de inicio de sesión
    header('Location: index.php');
    exit;
}

//$rol = $infoUsuario['rol'];

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../css/estilo.css?v=0.0.2">
    <link rel="stylesheet" href="../css/slidebar.css?v=0.0.3">
    <link rel="stylesheet" href="../css/contadores.css?v=0.7">
    <link rel="stylesheet" href="../css/animacion.css?v=0.15">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Dashboard</title>
    <link rel="icon" href="https://css.mintic.gov.co/mt/mintic/new/img/logo_mintic_24_dark.svg" type="image/x-icon">
</head>

<body class="" style="background-color:white">
<?php include("../controller/header.php"); ?>
<?php include("./sliderBar.php"); ?>
    <div id="mt-3">
        <div class="mt-3">
            <br><br><br><br>
            <div id="dashboard">
                <div class="position-relative bg-transparent">
                    <h2 class="position-absolute top-4 start-0 "><i class="bi bi-speedometer2 "></i> Dashboard</h2>

                </div>
                <br><br>
                <hr>
                <?php include("./contadoresCards.php"); ?>
                <div class="row">
                    <div class=" col-sm-12 col-md-8 col-lg-6">

                    </div>
                    <div class="col-sm-12 col-md-12 col-lg-6">

                        <br>
                    </div>


                </div>
            </div>

        </div>
    </div>
    <?php include("../controller/footer.php"); ?>
    <?php include("../controller/botonFlotanteDerecho.php"); ?>
    <?php include("../components/sliderBarBotton.php"); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
 
    <script src="../js/real-time-update-contadores.js?v=0.3"></script>
    <script>
        $('#link-dashboard').addClass('pagina-activa');
    </script>
</body>

</html>