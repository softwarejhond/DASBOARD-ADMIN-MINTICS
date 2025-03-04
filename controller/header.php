<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario
require_once __DIR__ . '/../components/modals/cohortes.php';
?>

<nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top">
    <div class="container-fluid">
        <button class="btn btn-tertiary mr-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptionsLabel">
            <i class="bi bi-list"></i>
        </button>
        <a class="navbar-brand" href="#"><img src="https://css.mintic.gov.co/mt/mintic/new/img/logo_mintic_24_dark.svg" alt="logo" width="50px"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="main.php">Inicio</a>
                </li>
                <?php if ($rol === 'Administrador'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#cohortModal">
                       Cohortes
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if ($rol === 'Administrador' || $rol==='Empleabilidad'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="encuestas.php">Empleabilidad</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="asistencias.php">Asistencia Talleres</a>
                </li>
                <?php endif; ?>
                <?php if ($rol === 'Administrador'): ?>
                <!-- Sistema PQRS -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownPQRS" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        PQRS
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownPQRS">
                        <li><a class="dropdown-item" href="seguimiento_pqr.php">Seguimiento PQRS</a></li>
                    
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">Perfil</a>
                </li>
                <?php endif; ?>
            </ul>
            <!-- Mostrar el nombre del usuario logueado -->
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo htmlspecialchars($infoUsuario['foto']); ?>" alt="Perfil" class="rounded-circle" width="40" height="40">
                    <?php echo htmlspecialchars($infoUsuario['nombre']); ?>
                    <div class="spinner-grow spinner-grow-sm" role="status" style="color:#00976a">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <button type="button" class="btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Usuario: <?php echo htmlspecialchars($infoUsuario['rol']); ?>">
                        <i class="bi bi-info-circle-fill colorVerde" style="color: #00976a;"></i>
                    </button>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="profile.php">Perfil</a></li>
                    <li><a class="dropdown-item" href="close.php">Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
<script>
    // Inicializa todos los tooltips en la página
    $(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>