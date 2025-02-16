<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Asegúrate de que la ruta sea correcta
require 'db_connection.php'; // Asegúrate de que la ruta sea correcta

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['destinatario']) || !isset($data['asunto']) || !isset($data['program']) || !isset($data['first_name']) || !isset($data['usuario']) || !isset($data['contraseña'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$destinatario = $data['destinatario'];
$asunto = $data['asunto'];
$program = $data['program'];
$first_name = $data['first_name'];
$usuario = $data['usuario'];
$contraseña = $data['contraseña'];

// Consulta la configuración SMTP desde la base de datos
$query = "SELECT * FROM smtpConfig WHERE id=1";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $smtpConfig = mysqli_fetch_assoc($result);
    $username = $smtpConfig['username'];
    $host = $smtpConfig['host'];
    $emailSmtp = $smtpConfig['email'];
    $dependence = $smtpConfig['dependence'];
    $password = $smtpConfig['password'];
    $port = $smtpConfig['port'];
    $nameBody = $smtpConfig['nameBody'];
    $subject = $smtpConfig['Subject'];
    $urlpicture = $smtpConfig['urlpicture'];

    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $port;

        // Remitente y destinatarios
        $mail->setFrom($emailSmtp, $dependence);
        $mail->addAddress($destinatario);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = $asunto;

        // Definir las URLs para cada programa
        $programUrls = [
            'Análisis de datos' => 'https://dashboard.uttalento.co/preKnowAnalysis.php',
            'Ciberseguridad' => 'https://dashboard.uttalento.co/preKnowCybersecurity.php',
            'Inteligencia Artificial' => 'https://dashboard.uttalento.co/preKnowIntelligence.php',
            'Programación' => 'https://dashboard.uttalento.co/preKnowPrograming.php',
            'BlockChain' => 'https://dashboard.uttalento.co/preKnowBlockchain.php',
            'Arquitectura en la nube' => 'https://dashboard.uttalento.co/preKnowArchitecture.php'
        ];

        // Obtener la URL correspondiente al programa seleccionado
        $programUrl = isset($programUrls[$program]) ? $programUrls[$program] : '#'; // Si no se encuentra el programa, usar un valor por defecto

        // Aquí va tu mensaje HTML
        $mensaje = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #f4f4f9;
                    color: #333;
                }
                .container {
                    max-width: 600px;
                    margin: 20px auto;
                    background: #ffffff;
                    border-radius: 10px;
                    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                    padding: 20px;
                }
                .header {
                    text-align: center;
                    background: #066aab;
                    color: #fff;
                    padding: 20px;
                    border-top-left-radius: 10px;
                    border-top-right-radius: 10px;
                }
                .header h1 {
                    margin: 0;
                    font-size: 24px;
                }
                .content {
                    line-height: 1.6;
                }
                .content p {
                    margin: 10px 0;
                }
                a.button {
                    display: inline-block;
                    margin: 20px 0;
                    padding: 10px 20px;
                    background: #066aab;
                    color: #fff;
                    text-decoration: none;
                    font-weight: bold;
                    border-radius: 5px;
                    text-align: center;
                }
                a.button:hover, 
                a.button:visited {
                    color: #fff;
                    text-decoration: none;
                }
                .footer {
                    text-align: center;
                    margin-top: 20px;
                    color: #777;
                    font-size: 12px;
                }
            </style>
        </head>

        <body>
            <div class='container'>
                <div class='header'>
                    <h1>¡Bienvenido a Talento Tech del MINTIC!</h1>
                </div>
                <div class='content'>
                    <p>Hola <b>$first_name</b>,</p>
                    <p>¡Estás un paso más cerca de alcanzar tus metas! 🎉</p>
                    <p>Queremos contarte que has sido admitido como beneficiario del programa <b>Talento Tech de MINTIC</b> en el <b>$program</b> como campista.</p>
                    
                    <h3>Acceso a la plataforma</h3>
                    <p>A continuación, encontrarás tu usuario y contraseña para formalizar tu matrícula en el programa y acceder a nuestra plataforma de formación:</p>
                    <p><b>Usuario:</b> $usuario</p>
                    <p><b>Contraseña:</b> $contraseña</p>
                    <p>Puedes iniciar sesión y completar tu registro haciendo clic en el siguiente botón:</p>
                    <a class='button' href='https://talento-tech.uttalento.co/login/index.php' target='_blank'>Acceder a la Plataforma</a>
                    
                    <p>O también puedes acceder manualmente copiando y pegando el siguiente enlace en tu navegador:</p>
                    <p><b>🔗 <a href='https://talento-tech.uttalento.co/login/index.php' target='_blank'>https://talento-tech.uttalento.co/login/index.php</a></b></p>

                    <p>Esperamos que este camino te acerque a tus objetivos y cuentes con nosotros hasta el final. Este es solo un paso más hacia la realización de tus sueños. 🚀</p>

                    <p>Si tienes dudas o inquietudes, puedes comunicarte con nuestro equipo de soporte a través de:</p>
                    <p>📞 <b>3008959859</b></p>
                    <p>📧 <b><a href='mailto:servicioalcliente.ut@poliandino.edu.co'>servicioalcliente.ut@poliandino.edu.co</a></b></p>
                </div>

                <div class='footer'>
                    <p>Equipo Talento Tech – MINTIC</p>
                </div>
            </div>
        </body>
        </html>";

        $mail->Body = $mensaje;

        // Enviar el correo
        $mail->send();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        error_log("Error al enviar el correo: {$mail->ErrorInfo}");
        echo json_encode(['success' => false, 'message' => $mail->ErrorInfo]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo obtener la configuración SMTP']);
}
?>