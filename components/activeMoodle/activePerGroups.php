<?php
// Definir las variables globales para Moodle
$api_url = "https://talento-tech.uttalento.co/webservice/rest/server.php";
$token   = "3f158134506350615397c83d861c2104";
$format  = "json";

// Función para llamar a la API de Moodle
function callMoodleAPI($function, $params = [])
{
    global $api_url, $token, $format;
    $params['wstoken'] = $token;
    $params['wsfunction'] = $function;
    $params['moodlewsrestformat'] = $format;
    $url = $api_url . '?' . http_build_query($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error en la solicitud cURL: ' . curl_error($ch);
    }
    curl_close($ch);
    return json_decode($response, true);
}

// Función para obtener cursos desde Moodle
function getCourses()
{
    return callMoodleAPI('core_course_get_courses');
}

// Obtener cursos y almacenarlos en $courses_data
$courses_data = getCourses();



?>



<div class="container">
    <div class="row justify-content-between">
        <div class="col-lg-4 col-md-6">
            <div class="card" style="height: 230px; background: linear-gradient(to right, transparent, rgba(0, 0, 0, 0.9)), url('img/cards/programacion.png') center/cover;"
                data-bs-toggle="modal" data-bs-target="#cursoModal">
                <div class="card-body d-flex align-items-center justify-content-center">
                    <h3 class="card-title text-center text-white">Programación</h3>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card" style="height: 230px; background: linear-gradient(to right, transparent, rgba(0, 0, 0, 0.9)), url('img/cards/arquitectura_nube.png') center/cover;"
                data-bs-toggle="modal" data-bs-target="#cursoModal">
                <div class="card-body d-flex align-items-center justify-content-center">
                    <h3 class="card-title text-center text-white">Arquitectura en la nube</h3>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card" style="height: 230px; background: linear-gradient(to right, transparent, rgba(0, 0, 0, 0.9)), url('img/cards/analisis_de_datos.png') center/cover;"
                data-bs-toggle="modal" data-bs-target="#cursoModal">
                <div class="card-body d-flex align-items-center justify-content-center">
                    <h3 class="card-title text-center text-white">Análisis de datos</h3>
                </div>
            </div>
        </div>
    </div>

    <br>

    <div class="row justify-content-between">
        <div class="col-lg-4 col-md-6">
            <div class="card" style="height: 230px; background: linear-gradient(to right, transparent, rgba(0, 0, 0, 0.9)), url('img/cards/blockchain.png') center/cover;"
                data-bs-toggle="modal" data-bs-target="#cursoModal">
                <div class="card-body d-flex align-items-center justify-content-center">
                    <h3 class="card-title text-center text-white">BlockChain</h3>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card" style="height: 230px; background: linear-gradient(to right, transparent, rgba(0, 0, 0, 0.9)), url('img/cards/inteligencia_artificial.png') center/cover;"
                data-bs-toggle="modal" data-bs-target="#cursoModal">
                <div class="card-body d-flex align-items-center justify-content-center">
                    <h3 class="card-title text-center text-white">Inteligencia Artificial</h3>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card" style="height: 230px; background: linear-gradient(to right, transparent, rgba(0, 0, 0, 0.9)), url('img/cards/ciberseguridad.png') center/cover;"
                data-bs-toggle="modal" data-bs-target="#cursoModal">
                <div class="card-body d-flex align-items-center justify-content-center">
                    <h3 class="card-title text-center text-white">Ciberseguridad</h3>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal único -->
<div class="modal fade" id="cursoModal" tabindex="-1" aria-labelledby="cursoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cursoModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="modalContent"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.card');
        const modal = new bootstrap.Modal(document.getElementById('cursoModal'));

        cards.forEach(card => {
            card.addEventListener('click', function() {
                const title = this.querySelector('.card-title').textContent;
                document.getElementById('cursoModalLabel').textContent = `Ver listado de matriculados a: ${title}`;
                document.getElementById('modalContent').textContent = `Contenido relacionado con ${title}...`;
                modal.show();
            });
        });
    });
</script>