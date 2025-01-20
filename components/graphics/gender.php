<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<canvas id="graficaGenero" width="200" height="200"></canvas>
<script>
    const ctx = document.getElementById('graficaGenero').getContext('2d');
    let miGrafica;

    async function cargarDatos() {
        try {
            const respuesta = await fetch('components/graphics/genderQuery.php?json=1'); // Ajusta la URL si es necesario
            if (!respuesta.ok) {
                throw new Error(`HTTP error! Status: ${respuesta.status}`);
            }
            const datos = await respuesta.json();
            console.log('Datos recibidos:', datos);

            if (!datos.labels || !datos.data) {
                throw new Error('Formato de datos incorrecto');
            }

            const config = {
                type: 'pie', // Cambia el tipo si es necesario (pie, bar, etc.)
                data: {
                    labels: datos.labels,
                    datasets: [{
                        label: 'Cantidad',
                        data: datos.data,
                        backgroundColor: ['#36A2EB', '#FF6384', '#ec008c'],
                        borderColor: ['#36A2EB', '#FF6384', '#ec008c'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            enabled: true
                        }
                    }
                }
            };

            // Si ya existe una gráfica, actualiza sus datos
            if (miGrafica) {
                miGrafica.data = config.data;
                miGrafica.update();
            } else {
                miGrafica = new Chart(ctx, config);
            }
        } catch (error) {
            console.error('Error al cargar los datos:', error);
        }
    }

    // Cargar al inicio y actualizar cada 5 segundos
    cargarDatos();
    setInterval(cargarDatos, 5000);
</script>
