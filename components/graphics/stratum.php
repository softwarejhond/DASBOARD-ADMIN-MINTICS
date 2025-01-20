<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<h4><i class="bi bi-123"></i> Estrato</h4>
<canvas id="graficaEstrato" width="100%" height="200"></canvas>

<script>
    const stratum = document.getElementById('graficaEstrato').getContext('2d');
    let stratumGrafica;

    async function cargarDatos() {
        try {
            // Realizamos la petición a la URL
            const respuesta = await fetch('components/graphics/stratumQuery.php?json=1');
            // Verificamos que la respuesta sea válida
            if (!respuesta.ok) {
                throw new Error(`HTTP error! Status: ${respuesta.status}`);
            }
            // Obtenemos los datos en formato JSON
            const datos = await respuesta.json();
            console.log('Datos recibidos:', datos); // Muestra los datos para verificar la estructura

            // Verificar si los datos contienen las propiedades necesarias
            if (!datos.labels || !datos.data) {
                throw new Error('Formato de datos incorrecto');
            }

            // Configuración de la gráfica
            const config = {
                type: 'pie', // Tipo de gráfica
                data: {
                    labels: datos.labels, // Etiquetas de los segmentos (Estrato 1, Estrato 2, etc.)
                    datasets: [{
                        label: 'Cantidad',
                        data: datos.data, // Datos numéricos (cantidad de usuarios por estrato)
                        backgroundColor: ['#1e88e5', '#ff7043', '#8e24aa', '#ffd54f', '#43a047', '#e53935'], // Colores de los segmentos
                        borderColor: ['#1e88e5', '#ff7043', '#8e24aa', '#ffd54f', '#43a047', '#e53935'], // Colores del borde
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,  // Que sea adaptable al tamaño de la pantalla
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            enabled: true // Habilitar tooltips para cuando se pase el mouse
                        },
                        datalabels: {
                            color: 'white', // Color del texto
                            font: {
                                weight: 'bold',
                                size: 14
                            },
                            align: 'center',  // Centrar el texto dentro de cada segmento
                            anchor: 'center', // Asegura que el texto esté centrado en cada segmento
                            formatter: function(value, context) {
                                // Calcular el total de los datos
                                let total = context.chart.data.datasets[0].data.reduce((sum, value) => sum + value, 0);
                                let percentage = Math.round((value / total) * 100);
                                return `${value}\n(${percentage}%)`; // Mostrar valor y porcentaje
                            }
                        }
                    }
                }
            };

            // Si ya existe una gráfica, actualiza sus datos
            if (stratumGrafica) {
                stratumGrafica.data = config.data;
                stratumGrafica.update();
            } else {
                stratumGrafica = new Chart(stratum, config);
            }
        } catch (error) {
            console.error('Error al cargar los datos:', error);
        }
    }

    // Cargar los datos al inicio y actualizar cada 30 segundos
    cargarDatos();
    setInterval(cargarDatos, 30000); // Actualización cada 30 segundos
</script>
