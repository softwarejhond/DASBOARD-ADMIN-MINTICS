<?php
// Verifica la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Consulta para obtener cursos con su tipo
$sql = 'SELECT 
    id_bootcamp AS id, 
    bootcamp_name AS nombre, 
    "bootcamp_teacher_id" AS tipocampo,
    bootcamp_teacher_id AS docente_actual 
FROM groups WHERE id_bootcamp IS NOT NULL
UNION
SELECT 
    id_leveling_english, 
    leveling_english_name, 
    "le_teacher_id",
    le_teacher_id AS docente_actual 
FROM groups WHERE id_leveling_english IS NOT NULL
UNION
SELECT 
    id_english_code, 
    english_code_name, 
    "ec_teacher_id",
    ec_teacher_id AS docente_actual 
FROM groups WHERE id_english_code IS NOT NULL
UNION
SELECT 
    id_skills, 
    skills_name, 
    "skills_teacher_id",
    skills_teacher_id AS docente_actual 
FROM groups WHERE id_skills IS NOT NULL';

$sql_docentes = "SELECT id, username, nombre FROM users WHERE rol = 5";


$resultado = $conn->query($sql);

// Obtener lista de docentes
$docentes = [];
$sql_docentes = "SELECT username, nombre FROM users WHERE rol = 5";
$resultado_docentes = $conn->query($sql_docentes);
if ($resultado_docentes->num_rows > 0) {
    while ($docente = $resultado_docentes->fetch_assoc()) {
        $docentes[] = $docente;
    }
}

// Generar formulario
if ($resultado->num_rows > 0) {
    echo '<div class="container mt-4">';
    echo '<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">';

    while ($row = $resultado->fetch_assoc()) {
        echo '
        <div class="col">
            <div class="card h-100 shadow-sm">
                <img src="https://via.placeholder.com/300x150" class="card-img-top" alt="Curso">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">' . htmlspecialchars($row["nombre"]) . '</h5>
                    <p class="card-text text-muted small">ID: ' . htmlspecialchars($row["id"]) . ' | Tipo: ' . htmlspecialchars($row["tipocampo"]) . '</p>
                    
                    <div class="mt-auto">';

                    if (!empty($docentes)) {
                        echo '<label class="form-label">Seleccionar Docente</label>
                              <select class="form-select docente-select" 
                                  data-idcurso="' . htmlspecialchars($row["id"]) . '" 
                                  data-tipocampo="' . htmlspecialchars($row["tipocampo"]) . '">
                              <option value="" ' . ($row["docente_actual"] == 0 ? 'selected' : '') . '>Seleccione...</option>';
                    
                        foreach ($docentes as $docente) {
                            $selected = $docente["id"] == $row["docente_actual"] ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($docente["username"]) . '" ' . $selected . '>'
                                . htmlspecialchars($docente["nombre"]) . '</option>';
                        }
                        
                        echo '</select>
                              <button class="btn btn-primary mt-2 actualizar-docente">Actualizar</button>';
                    }

        echo '      </div>
                </div>
            </div>
        </div>';
    }

    echo '</div>';
    echo '</div>';
} else {
    echo '<div class="alert alert-info text-center mt-4">No hay cursos disponibles.</div>';
}

// Cerrar conexión
$conn->close();
?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".actualizar-docente").forEach(btn => {
            btn.addEventListener("click", function() {
                let select = this.previousElementSibling;
                let idCurso = select.dataset.idcurso;
                let tipoCampo = select.dataset.tipocampo;
                let docente = select.value;

                if (!docente) {
                    alert("Por favor, seleccione un docente.");
                    return;
                }

                fetch("components/setTeachers/update_teacher.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: new URLSearchParams({
                        id_curso: idCurso,
                        docente: docente,
                        tipo_campo: tipoCampo
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        alert("Docente actualizado correctamente.");
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("Error en la comunicación con el servidor");
                });
            });
        });
    });
</script>