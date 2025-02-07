<script>
$(document).ready(function () {
    function actualizarDatos() {
        $.ajax({
            url: "dashboard.php?ajax=1",
            method: "GET",
            dataType: "json",
            success: function (response) {
                $("#total_usuarios").text(response.total_usuarios);
                $("#cundinamarca").text(response.cundinamarca);
                $("#porc_cundinamarca").text(response.porc_cundinamarca);
                $("#boyaca").text(response.boyaca);
                $("#porc_boyaca").text(response.porc_boyaca);
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
