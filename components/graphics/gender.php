<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<h4><i class="bi bi-gender-trans"></i> Género</h4>
<canvas id="graficaGenero" width="100%" height="200"></canvas>

<script>
    const ctx = document.getElementById('graficaGenero').getContext('2d');
    let miGrafica;

    async function cargarDatos() {
        try {
            // Realizamos la petición a la URL
            const respuesta = await fetch('components/graphics/genderQuery.php?json=1');
            // Verificamos que la respuesta sea válida
            if (!respuesta.ok) {
                throw new Error(`HTTP error! Status: ${respuesta.status}`);
            }
            // Obtenemos los datos en formato JSON
            const datos = await respuesta.json();
            console.log('Datos recibidos:', datos);  // Muestra los datos para verificar la estructura
            if (!datos.labels || !datos.data) {
                throw new Error('Formato de datos incorrecto');
            }
            
            const config = {
                type: 'pie', // Tipo de gráfica
                data: {
                    labels: datos.labels, // Etiquetas de los segmentos
                    datasets: [{
                        label: 'Cantidad',
                        data: datos.data, // Datos numéricos
                        backgroundColor: ['#36A2EB', '#bf6900', '#ec008c'], // Colores de los segmentos
                        borderColor: ['#36A2EB', '#bf6900', '#ec008c'], // Colores del borde
                        borderWidth: 1,
                        datalabels: {
                            display: true,  // Hacer visibles las etiquetas siempre
                            color: 'white', // Color del texto
                            font: {
                                weight: 'bold',
                                size: 14
                            },
                            align: 'center',  // Centrar el texto dentro de cada segmento
                            anchor: 'center', // Asegura que el texto esté centrado en cada segmento
                            formatter: (value, ctx) => {
                                // Calcular el total de los datos
                                let total = ctx.chart.data.datasets[0].data.reduce((sum, value) => sum + value, 0);
                                let percentage = Math.round((value / total) * 100);
                                return `${percentage}%\n${value}`;  // Mostrar porcentaje y valor
                            }
                        }
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
                            display: true,  // Asegura que las etiquetas siempre estén visibles
                            color: 'white', // Color de las etiquetas
                            font: {
                                weight: 'bold',
                                size: 14
                            },
                            align: 'center',  // Centrar el texto dentro de cada segmento
                            anchor: 'center', // Asegura que el texto esté centrado en cada segmento
                            formatter: (value, ctx) => {
                                // Calcular el total de los datos
                                let total = ctx.chart.data.datasets[0].data.reduce((sum, value) => sum + value, 0);
                                let percentage = Math.round((value / total) * 100);
                                return `${percentage}%\n${value}`;  // Mostrar porcentaje y valor
                            }
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

    // Cargar los datos al inicio y actualizar cada 30 segundos
    cargarDatos();
    setInterval(cargarDatos, 30000);  // Actualización cada 30 segundos
</script>