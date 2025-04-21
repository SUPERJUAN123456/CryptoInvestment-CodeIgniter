<!DOCTYPE html>
<html lang="es">
<head>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/styles.css') ?>">
    <meta charset="UTF-8">
    <title>Criptomonedas en Tiempo Real</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
    </style>
</head>
<body>
    <h1>Criptomonedas IGNIWEB
        (Observa el valor de tus criptomonedas favoritas, en tiempo real)
    </h1>
    
    <input type="text" id="search" placeholder="Buscar criptomoneda..." oninput="filterTable()">

    <p>Última actualización: <span id="last-update">Nunca</span></p>
    <div id="crypto-table-container">
        Cargando datos...
    </div>
    <button id="back-button" style="display:none; margin-top: 10px;" onclick="showFullTable()">Volver a la vista general</button>
    <div id="date-filter" style="display: none; margin-top: 1rem;">
    <label>Desde: <input type="date" id="start-date"></label>
    <label>Hasta: <input type="date" id="end-date"></label>
    <button onclick="applyDateFilter()">Filtrar</button>
</div>

    <canvas id="crypto-chart" width="800" height="400" style="display: none; margin-top: 2rem;"></canvas>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-trendline@2.0.0"></script>
    <script>
        function applyDateFilter() {
    generateChart();
}

        let previousPrices = {};
        let historicalPrices = {};
        let selectedCrypto = null;
        let chart = null;
        let latestCryptoData = [];
        let originalorder = [];
        let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');

        function toggleFavorite(name) {
    const index = favorites.indexOf(name);
    if (index >= 0) {
        favorites.splice(index, 1); // Quitar de favoritos
    } else {
        favorites.push(name); // Agregar a favoritos
    }

    localStorage.setItem('favorites', JSON.stringify(favorites));

    // Restaurar el orden original
    const restoredData = [...originalOrder];

    // Reconstruir el arreglo original con datos actualizados
    const reordered = restoredData.map(original => {
        return latestCryptoData.find(c => c.name === original.name);
    }).filter(Boolean);

    renderTable(reordered);
}
        fetchCryptoData();

        let originalOrder = [];

async function fetchCryptoData() {
    try {
        const response = await fetch("<?= base_url('data') ?>");
        const json = await response.json();

        latestCryptoData = json.data;
        originalOrder = [...latestCryptoData];  // Guardar el orden original
        renderTable(latestCryptoData);
        updateHistoricalPrices(json.data);

        if (selectedCrypto) generateChart(); // Solo actualiza si hay una cripto seleccionada
        document.getElementById('last-update').textContent = new Date().toLocaleTimeString();
    } catch (error) {
        document.getElementById('crypto-table-container').innerHTML = '<p>Error al cargar los datos.</p>';
        console.error(error);
    } finally {
        setTimeout(fetchCryptoData, 30000);
    }
}


        function updateHistoricalPrices(data) {
            data.forEach(crypto => {
                const name = crypto.name;
                const price = crypto.quote.USD.price;

                if (!historicalPrices[name]) {
                    historicalPrices[name] = [];
                }

                historicalPrices[name].push({
                    time: new Date().toLocaleTimeString(),
                    price: price
                });

                let arrow = '';
                let direction = null;

                if (previousPrices[name]) {
                    const previous = previousPrices[name].price;
                    if (price > previous) {
                        direction = 'up'; arrow = '🔼';
                    } else if (price < previous) {
                        direction = 'down'; arrow = '🔽';
                    } else {
                        direction = previousPrices[name].direction;
                        arrow = direction === 'up' ? '🔼' : direction === 'down' ? '🔽' : '';
                    }
                }

                previousPrices[name] = { price, direction, arrow };
            });
        }

        function renderTable(data) {
    // Guardamos el orden original de las criptos
    originalorder = data.map(crypto => crypto.name);

    let html = `
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Precio (USD)</th>
                    <th>Capitalización</th>
                    <th>Cambio (24h)</th>
                </tr>
            </thead>
            <tbody>
    `;

    // Primero ordenamos las favoritas
    data.sort((a, b) => {
        const aFav = favorites.includes(a.name);
        const bFav = favorites.includes(b.name);
        if (aFav && !bFav) return -1;
        if (!aFav && bFav) return 1;
        return 0;
    });

    data.forEach(crypto => {
        const name = crypto.name;
        const logoUrl = crypto.logo || 'https://via.placeholder.com/20';
        const currentPrice = crypto.quote.USD.price;
        const marketCap = crypto.quote.USD.market_cap;
        const change24h = crypto.quote.USD.percent_change_24h;
        const arrow = previousPrices[name]?.arrow || '';
        const isFavorite = favorites.includes(name);

        // Si hay una cripto seleccionada, solo renderizamos esa fila
        if (selectedCrypto && selectedCrypto !== name) return;

        html += `
        <tr onclick="selectCrypto('${name}', this)" ${selectedCrypto === name ? 'class="selected-row"' : ''}>
            <td>
                <img src="${logoUrl}" alt="${name}" style="width: 20px; height: 20px; margin-right: 8px;">
                ${name}
                <button 
                    onclick="event.stopPropagation(); toggleFavorite('${name}')" 
                    style="margin-left: 10px; font-size: 12px; padding: 2px 5px; border: none; background: none; cursor: pointer;">
                    ${isFavorite ? '★' : '☆'}
                </button>
            </td>
            <td>${currentPrice.toFixed(6)} USD ${arrow}</td>
            <td>${Number(marketCap).toLocaleString()} USD</td>
            <td>${change24h.toFixed(2)}%</td>
        </tr>
        `;
    });

    html += `</tbody></table>`;
    document.getElementById('crypto-table-container').innerHTML = html;
}



        function selectCrypto(name, rowElement) {
            document.getElementById('date-filter').style.display = 'block';
            selectedCrypto = name;
            const allRows = document.querySelectorAll('#crypto-table-container tbody tr');
            allRows.forEach(row => {
                row.classList.remove('selected-row');
                row.style.display = 'none'; // ocultar todo
            });

            rowElement.classList.add('selected-row');
            rowElement.style.display = ''; // mostrar la fila seleccionada

            document.getElementById('crypto-chart').style.display = 'block';
            document.getElementById('back-button').style.display = 'inline-block';

            generateChart();
        }

        function showFullTable() {
    document.getElementById('date-filter').style.display = 'none';
    document.getElementById('search').value = ''; // Limpiar búsqueda

    selectedCrypto = null;
    renderTable(latestCryptoData); // Renderizar de nuevo la tabla completa

    document.getElementById('crypto-chart').style.display = 'none';
    document.getElementById('back-button').style.display = 'none';
}




        function generateChart() {
    if (!selectedCrypto) return;

    const startDate = document.getElementById('start-date')?.value;
    const endDate = document.getElementById('end-date')?.value;

    // Validación de fechas
    if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
        alert('La fecha de inicio no puede ser posterior a la fecha de fin.');
        return; // Detener la ejecución si las fechas no son válidas
    }

    // Usar 'let' en vez de 'const' para evitar redeclarar la variable 'url'
    let url = new URL("<?= base_url('historical-data') ?>", window.location.origin);
    url.searchParams.append('name', selectedCrypto);
    if (startDate) url.searchParams.append('start', startDate);
    if (endDate) url.searchParams.append('end', endDate);

    fetch(url)
        .then(response => response.json())
        .then(json => {
            if (!json || !json.data || json.data.length === 0) {
                console.warn("No se encontraron datos históricos para el filtro aplicado.");
                return;
            }

            const data = json.data;

            const labels = data.map(d => d.time);
            const prices = data.map(d => d.price);

            const ctx = document.getElementById('crypto-chart').getContext('2d');
            if (chart) chart.destroy();

            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: `${selectedCrypto} - Precio USD`,
                        data: prices,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        pointRadius: 4,
                        pointBackgroundColor: prices.map((value, index, arr) => {
                            if (index === 0) return 'gray';
                            return value > arr[index - 1] ? 'green' : value < arr[index - 1] ? 'red' : 'gray';
                        }),
                        fill: true,
                        tension: 0.1,
                        trendlineLinear: {
                            style: 'rgb(255, 99, 132)', // Color de la línea de tendencia
                            lineStyle: 'dotted',         // Estilo de la línea (dotted, solid, etc.)
                            width: 2                     // Grosor de la línea
                        }
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: true },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    },
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error al obtener datos históricos:', error);
        });
}
    



function filterTable() {
    const input = document.getElementById('search').value.toLowerCase().trim();
    const rows = document.querySelectorAll('#crypto-table-container tbody tr');
    let found = false;

    rows.forEach(row => {
        const name = row.querySelector('td')?.textContent.toLowerCase() || '';
        const match = name.includes(input);
        row.style.display = match ? '' : 'none';
        if (match) found = true;
    });

    // Mostrar u ocultar mensaje de error fuera de la tabla
    let errorMsg = document.getElementById('no-results');
    if (!errorMsg) {
        errorMsg = document.createElement('p');
        errorMsg.id = 'no-results';
        errorMsg.style.color = '#ef4444';
        errorMsg.style.marginTop = '1rem';
        document.getElementById('crypto-table-container').appendChild(errorMsg);
    }
    errorMsg.style.display = found ? 'none' : 'block';
    errorMsg.textContent = 'No se encontraron criptomonedas con ese nombre.';
}


    </script>

</body>
</html>
