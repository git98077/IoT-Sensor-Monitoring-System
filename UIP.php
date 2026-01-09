<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>即時溫濕度監控</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: "Microsoft JhengHei", sans-serif;
            background: #f7f9fb;
            padding: 20px;
            color: #333;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        .stats {
            display: flex;
            justify-content: space-around;
            font-size: 1.5em;
            flex-wrap: wrap;
            gap: 10px;
        }

        canvas {
            width: 100% !important;
            max-width: 800px;
            margin: 20px auto;
            display: block;
        }
    </style>
</head>
<body>

    <h1>🌡️ 即時溫濕度監控儀表板</h1>

    <div class="card">
        <h2>目前最新數值</h2>
        <div class="stats" id="latestData">
            <div>🌡️ 溫度: <span id="temperature">--</span>°C</div>
            <div>💧 濕度: <span id="humidity">--</span>%</div>
            <div>📏 距離: <span id="distance">--</span> cm</div>
            <div>🕒 時間: <span id="datetime">--</span></div>
        </div>
    </div>

    <div class="card">
        <h2>最近10筆溫度資料</h2>
        <canvas id="tempChart"></canvas>
    </div>

    <div class="card">
        <h2>最近10筆濕度資料</h2>
        <canvas id="humChart"></canvas>
    </div>

    <div class="card">
        <h2>最近10筆距離資料</h2>
        <canvas id="distChart"></canvas>
    </div>

    <script>
        let tempChart, humChart, distChart;

        async function fetchData() {
            const response = await fetch('datap.php');
            const json = await response.json();

            // 更新最新資料
            document.getElementById('temperature').textContent = json.latest.temperature;
            document.getElementById('humidity').textContent = json.latest.humidity;
            document.getElementById('distance').textContent = json.latest.distance;
            document.getElementById('datetime').textContent = json.latest.datetime;

            const labels = json.chart.map(row => row.datetime);
            const tempData = json.chart.map(row => row.temperature);
            const humData = json.chart.map(row => row.humidity);
            const distData = json.chart.map(row => row.distance);

            // 更新圖表資料
            if (tempChart && humChart && distChart) {
                tempChart.data.labels = labels;
                tempChart.data.datasets[0].data = tempData;
                tempChart.update();

                humChart.data.labels = labels;
                humChart.data.datasets[0].data = humData;
                humChart.update();

                distChart.data.labels = labels;
                distChart.data.datasets[0].data = distData;
                distChart.update();
            }
        }

        // 初始化溫度圖表
        const tempCtx = document.getElementById('tempChart').getContext('2d');
        tempChart = new Chart(tempCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: '溫度 (°C)',
                    data: [],
                    borderColor: 'rgba(255,99,132,1)',
                    backgroundColor: 'rgba(255,99,132,0.2)',
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: { display: true, text: '時間' }
                    },
                    y: {
                        title: { display: true, text: '溫度 (°C)' }
                    }
                }
            }
        });

        // 初始化濕度圖表
        const humCtx = document.getElementById('humChart').getContext('2d');
        humChart = new Chart(humCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: '濕度 (%)',
                    data: [],
                    borderColor: 'rgba(54,162,235,1)',
                    backgroundColor: 'rgba(54,162,235,0.2)',
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: { display: true, text: '時間' }
                    },
                    y: {
                        title: { display: true, text: '濕度 (%)' }
                    }
                }
            }
        });

        // 初始化距離圖表
        const distCtx = document.getElementById('distChart').getContext('2d');
        distChart = new Chart(distCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: '距離 (cm)',
                    data: [],
                    borderColor: 'rgba(75,192,192,1)',
                    backgroundColor: 'rgba(75,192,192,0.2)',
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: { display: true, text: '時間' }
                    },
                    y: {
                        title: { display: true, text: '距離 (cm)' }
                    }
                }
            }
        });

        // 初次載入
        fetchData();
        // 每5秒更新一次
        setInterval(fetchData, 5000);
    </script>
</body>
</html>