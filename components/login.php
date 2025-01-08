<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inicializar la sesión
session_start();

// Establecer tiempo de vida de la sesión en segundos
$inactividad = 86400;

// Comprobar si $_SESSION["timeout"] está establecida
if (isset($_SESSION["timeout"])) {
    // Calcular el tiempo de vida de la sesión (TTL = Time To Live)
    $sessionTTL = time() - $_SESSION["timeout"];
    if ($sessionTTL > $inactividad) {
        session_unset();
        session_destroy();
        header("location: login.php"); // Redirigir a la página de inicio de sesión
        exit;
    }
}

// El siguiente key se crea cuando se inicia sesión
$_SESSION["timeout"] = time();

// Incluir el archivo de conexión
require_once "../controller/conexion.php";

// Definir variables y inicializar con valores vacíos
$username = $password = "";
$username_err = $password_err = "";

// Procesar datos del formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validar que el nombre de usuario no esté vacío
    if (empty(trim($_POST["username"]))) {
        $username_err = "Por favor ingrese su usuario.";
    } else if (!filter_var(trim($_POST["username"]), FILTER_VALIDATE_INT)) {
        $username_err = "El usuario debe ser un número.";
    } else {
         $username = trim($_POST["username"]);
    }
    

    // Validar que la contraseña no esté vacía
    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor ingrese su contraseña.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validar credenciales
    if (empty($username_err) && empty($password_err)) {
        // Preparar una declaración SQL
       $sql = "SELECT id, username, password, nombre, rol, foto FROM users WHERE username = ?";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Vincular variables a la declaración preparada como parámetros
            mysqli_stmt_bind_param($stmt, "i", $param_username); // "i" indica que $username es un entero
            $param_username = $username;

            // Intentar ejecutar la declaración preparada
            if (mysqli_stmt_execute($stmt)) {
                // Almacenar resultado
                mysqli_stmt_store_result($stmt);

                // Verificar si el nombre de usuario existe, si sí, verificar la contraseña
                if (mysqli_stmt_num_rows($stmt) === 1) {
                    // Vincular variables de resultado
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $nombre, $rol, $foto);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // La contraseña es correcta, iniciar una nueva sesión
                            // Ya hemos comenzado la sesión anteriormente

                            // Almacenar datos del usuario en la sesión
                            $_SESSION['loggedin'] = true; // Indica que el usuario ha iniciado sesión
                            $_SESSION['nombre'] = htmlspecialchars($nombre); // Establecer el nombre real del usuario
                            $_SESSION['rol'] = $rol; // Asignar un rol real basado en tu base de datos
                            $_SESSION['username'] = htmlspecialchars($username); // Asignar nombre de usuario
                            $_SESSION['foto'] = htmlspecialchars($foto); // Ruta de la foto del usuario

                            // Redirigir al usuario a la página principal
                            header("location: index.php");
                            exit;
                        } else {
                            $password_err = "Contraseña incorrecta.";
                        }
                    }
                } else {
                    $username_err = "Usuario no existe.";
                }
            } else {
                echo "Algo salió mal, por favor vuelve a intentarlo.";
            }
        }
        // Cerrar la declaración
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="css/animacion.css?v=0.9">
    <title>SIVP - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <div class="page">
        <div class="container">
            <div class="left">
                <div class="login">Iniciar sesión</div>
                <img src="https://css.mintic.gov.co/mt/mintic/new/img/logo_mintic_24_dark.svg" alt="Logo MinTIC" width="70" style="display: block; margin: 20px auto;">
                <div class="eula">Inicia sesión con usuario y contraseña</div>
            </div>
            <div class="right">
                <svg viewBox="0 0 320 300">
                    <defs>
                        <linearGradient inkscape:collect="always" id="linearGradient" x1="13" y1="193.49992" x2="307"
                            y2="193.49992" gradientUnits="userSpaceOnUse">
                            <stop style="stop-color:#ff00ff;" offset="0" id="stop876" />
                            <stop style="stop-color:#ff0000;" offset="1" id="stop878" />
                        </linearGradient>
                    </defs>
                    <path
                        d="m 40,120.00016 239.99984,-3.2e-4 c 0,0 24.99263,0.79932 25.00016,35.00016 0.008,34.20084 -25.00016,35 -25.00016,35 h -239.99984 c 0,-0.0205 -25,4.01348 -25,38.5 0,34.48652 25,38.5 25,38.5 h 215 c 0,0 20,-0.99604 20,-25 0,-24.00396 -20,-25 -20,-25 h -190 c 0,0 -20,1.71033 -20,25 0,24.00396 20,25 20,25 h 168.57143" />
                </svg>
                <div class="form">
                    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" class="form-login">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                        <input type="submit" id="submit" value="Iniciar" name="iniciar">

                        <!-- Mensaje que se mostrará´cuando se haya procesado la solicitud en el servidor -->
                        <?php if (isset($_POST['iniciar'])) : ?>
                            <span class="msj-error-input"> <?php echo $password_err ?></span>
                        <?php endif ?>
                        <?php if (!empty($username_err)) : ?>
                            <span class="msj-error-input"><?php echo $username_err ?></span>
                        <?php endif ?>
                    </form>
                </div>
            </div>
        </div>
        <footer class="login-footer">
            <div class="copyright-info">
                <p class="text-center login__forgot">
                    SIVP © Copyright <?php echo date("Y"); ?>
                    <br>
                    <a href="https://agenciaeaglesoftware.com/" target="_blank" class="linkEagle">Made by Agencia de Desarrollo Eagle Software</a>
                    <br>
                </p>

                <div class="social-icons">
                    <button onclick="window.open('https://api.whatsapp.com/send/?phone=573015606006&text&type=phone_number&app_absent=0', '_blank')" class="linkEagle">
                         <img src="../assets/img/whatsapp_logo.svg" alt="Whatsapp logo" class="social-icon">
                    </button>
                    <button onclick="window.open('https://www.instagram.com/eaglesoftwares/#', '_blank')" class="linkEagle">
                        <img src="../assets/img/instagram_logo.svg" alt="Instagram logo" class="social-icon">
                    </button>
                    <button onclick="window.open('https://www.facebook.com/eaglesoftwares/', '_blank')" class="linkEagle">
                        <img src="../assets/img/facebook_logo.svg" alt="Facebook logo" class="social-icon">
                    </button>
                </div>
            </div>
        </footer>
    </div>
    <script src="js/tooglePassword.js"></script>
    <script src="../components/hooks/lineLogin.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>