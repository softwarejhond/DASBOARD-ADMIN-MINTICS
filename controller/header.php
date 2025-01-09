<nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top">
    <div class="container-fluid">
        <button class="btn btn-tertiary mr-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions">
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
                <li class="nav-item">
                    <a class="nav-link" href="empresa.php">Institución</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Perfil</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Filtros
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="listaReparaciones.php">#</a></li>
                        <li><a class="dropdown-item" href="#">#</a></li>
                        <li><a class="dropdown-item" href="#">#</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="#">Lista de administradores</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        IPC
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" data-toggle="modal" data-target="#actualizarIPC">#</a></li>
                        <li><a class="dropdown-item" href="#" data-toggle="modal" data-target="#actualizarIPCLocales">#</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">#</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">#</a>
                </li>
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
                    <li><a class="dropdown-item" href="perfil.php">Perfil</a></li>
                    <li><a class="dropdown-item" href="../controller/close.php">Cerrar sesión</a></li>
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