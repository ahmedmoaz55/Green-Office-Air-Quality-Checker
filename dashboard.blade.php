<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OxyGuard | Command Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #0a0a0a; color: #e0e0e0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { background-color: #1a1a1a; border: 1px solid #333; border-radius: 8px; }
        .text-neon { color: #00e676; }
        .btn-neon { background-color: transparent; border: 1px solid #00e676; color: #00e676; }
        .btn-neon:hover { background-color: #00e676; color: #000; }
        
        /* Gauge Container relative positioning to center the text */
        .gauge-container { position: relative; height: 160px; display: flex; justify-content: center; margin-top: 10px; }
        .gauge-text { position: absolute; bottom: 10px; text-align: center; width: 100%; }
        .data-readout { font-size: 2.2rem; font-weight: bold; font-family: monospace; line-height: 1; }
        
        /* Massive Alert Overlay Styles */
        #critical-overlay {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background-color: rgba(220, 38, 38, 0.85); z-index: 9999;
            display: flex; justify-content: center; align-items: center;
            backdrop-filter: blur(6px);
        }
        .alert-box {
            background-color: #b91c1c; color: white; padding: 4rem;
            border-radius: 12px; text-align: center; box-shadow: 0 0 50px rgba(0,0,0,0.8);
            border: 3px solid #f87171; animation: alertPulse 1.5s infinite;
        }
        @keyframes alertPulse {
            0% { transform: scale(1); box-shadow: 0 0 30px rgba(220, 38, 38, 0.5); }
            50% { transform: scale(1.02); box-shadow: 0 0 60px rgba(220, 38, 38, 0.9); }
            100% { transform: scale(1); box-shadow: 0 0 30px rgba(220, 38, 38, 0.5); }
        }
    </style>
</head>
<body>

<div id="critical-overlay" class="d-none">
    <div class="alert-box">
        <h1 class="display-3 fw-bold mb-3">⚠️ CRITICAL CO2 LEVEL ⚠️</h1>
        <h2 class="display-6" id="overlay-room-name">in Meeting Room A</h2>
        <p class="fs-4 mt-4 mb-5">System threshold exceeded. Increase ventilation immediately.</p>
        <button class="btn btn-light btn-lg px-5 text-danger fw-bold" onclick="acknowledgeAlert()">
            Acknowledge & Silence
        </button>
    </div>
</div>
<form action="/logout" method="POST" class="d-inline">
    @csrf
    <button type="submit" class="btn btn-outline-danger btn-sm ms-3">Disconnect (Logout)</button>
</form>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
        <h2 class="mb-0"><span class="text-neon">OxyGuard::</span> Command Center</h2>
        <div><span class="badge bg-success fs-6" id="system-status">SYSTEM SECURE</span></div>
    </div>

    <div class="row g-4">
        <div class="col-md-3">
            <div class="card shadow-sm mb-4">
                <div class="card-body pb-0">
                    <h6 class="text-secondary text-center">Live CO2 Concentration</h6>
                    <div class="gauge-container">
                        <canvas id="co2Gauge"></canvas>
                        <div class="gauge-text">
                            <div class="data-readout text-neon" id="live-co2">---</div>
                            <small class="text-muted">PPM</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title border-bottom border-secondary pb-2 mb-3">Simulation Controls</h5>
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Monitored Zone</label>
                        <select class="form-select bg-dark text-light border-secondary" id="zone-selector">
                            <option value="Main Floor - Zone A">Main Floor - Zone A</option>
                            <option value="Meeting Room B">Meeting Room B</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Alert Threshold: <span id="threshold-val" class="text-white">1000</span> ppm</label>
                        <input type="range" class="form-range" id="threshold-slider" min="600" max="1500" value="1000">
                    </div>
                    <div class="d-grid gap-2 mt-4">
                        <button class="btn btn-outline-danger btn-sm" onclick="triggerAnomaly('hvac')">Simulate HVAC Failure</button>
                        <button class="btn btn-neon btn-sm mt-2" onclick="resetSimulation()">Reset Systems</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <h5 class="card-title text-secondary">Historical Telemetry</h5>
                        <div id="chart-alert" class="badge bg-danger d-none animate__animated animate__flash">CRITICAL WARNING</div>
                    </div>
                    <canvas id="co2LineChart" height="220"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title text-secondary border-bottom border-secondary pb-2">Event Logs</h5>
                    <ul class="list-group list-group-flush small" id="event-log">
                        <li class="list-group-item bg-transparent text-secondary border-secondary">System initialized...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // --- STATE VARIABLES ---
    let simulationMode = 'normal';
    let currentCo2 = 650;
    let thresholdPpm = 1000;
    let alertAcknowledged = false;
    const maxGaugeValue = 2000; // Maximum value for the meter

    // --- UI ELEMENTS ---
    const thresholdSlider = document.getElementById('threshold-slider');
    const thresholdVal = document.getElementById('threshold-val');
    const zoneSelector = document.getElementById('zone-selector');
    const liveCo2Display = document.getElementById('live-co2');
    const systemStatus = document.getElementById('system-status');
    const chartAlert = document.getElementById('chart-alert');
    const criticalOverlay = document.getElementById('critical-overlay');
    const overlayRoomName = document.getElementById('overlay-room-name');

    // --- EVENT LISTENERS ---
    thresholdSlider.addEventListener('input', function() {
        thresholdPpm = this.value;
        thresholdVal.innerText = thresholdPpm;
    });

    // --- ACTIONS ---
    function triggerAnomaly(type) {
        simulationMode = type;
        systemStatus.className = 'badge bg-warning text-dark';
        systemStatus.innerText = 'SIMULATING ANOMALY';
        logEvent('Manual Override: HVAC Failure simulated', 'text-warning');
        alertAcknowledged = false; // Reset acknowledgment state for new anomaly
    }

    function resetSimulation() {
        simulationMode = 'normal';
        currentCo2 = 650;
        systemStatus.className = 'badge bg-success';
        systemStatus.innerText = 'SYSTEM SECURE';
        chartAlert.classList.add('d-none');
        criticalOverlay.classList.add('d-none');
        alertAcknowledged = false;
        logEvent('Systems reset to normal parameters', 'text-neon');
    }

    function acknowledgeAlert() {
        criticalOverlay.classList.add('d-none');
        alertAcknowledged = true;
        logEvent('Critical alert acknowledged by operator', 'text-warning');
    }

    // --- 1. GAUGE CHART SETUP (Analog Meter) ---
    const gaugeCtx = document.getElementById('co2Gauge').getContext('2d');
    const co2Gauge = new Chart(gaugeCtx, {
        type: 'doughnut',
        data: {
            labels: ['CO2', 'Remaining'],
            datasets: [{
                data: [650, maxGaugeValue - 650], // Initial state
                backgroundColor: ['#00e676', '#333'], // Green and Dark Gray
                borderWidth: 0,
                cutout: '80%', // Makes it thin
                circumference: 180, // Half circle
                rotation: -90 // Start from left side
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { tooltip: { enabled: false }, legend: { display: false } },
            animation: { animateRotate: false, animateScale: true }
        }
    });

    // --- 2. LINE CHART SETUP ---
   // --- 2. LINE CHART SETUP ---
    const lineCtx = document.getElementById('co2LineChart').getContext('2d');
    const co2LineChart = new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: [], 
            datasets: [{
                label: 'CO2 Level (ppm)',
                data: [],
                borderColor: '#00e676', backgroundColor: 'rgba(0, 230, 118, 0.1)',
                borderWidth: 2, fill: true, tension: 0.3, pointRadius: 2
            }]
        },
        options: {
            responsive: true, 
            maintainAspectRatio: true, /* <-- THIS FIXES THE INVISIBLE CHART */
            aspectRatio: 2, /* Keeps it wide and proportionate */
            scales: { 
                y: { min: 400, max: 1800, grid: { color: '#333' } }, 
                x: { grid: { display: false } } 
            },
            animation: { duration: 300 }
        }
    });

    // --- DATA GENERATOR ---
    function generateData() {
        if (simulationMode === 'normal') {
            currentCo2 = currentCo2 + (Math.floor(Math.random() * 21) - 10);
            if(currentCo2 < 500) currentCo2 = 500;
            if(currentCo2 > 800) currentCo2 = 800;
        } else if (simulationMode === 'hvac') {
            currentCo2 += Math.floor(Math.random() * 60) + 40; // Fast climb
        }
        return { zone_name: zoneSelector.value, co2_ppm: currentCo2, humidity_percent: 45 };
    }

    // --- MAIN LOOP ---
    setInterval(() => {
        const payload = generateData();
        const now = new Date().toLocaleTimeString();

        // 1. Update Gauge & Text
        liveCo2Display.innerText = payload.co2_ppm;
        let meterColor = payload.co2_ppm > thresholdPpm ? '#ff1744' : '#00e676';
        liveCo2Display.className = `data-readout ${payload.co2_ppm > thresholdPpm ? 'text-danger' : 'text-neon'}`;
        
        co2Gauge.data.datasets[0].data = [payload.co2_ppm, Math.max(0, maxGaugeValue - payload.co2_ppm)];
        co2Gauge.data.datasets[0].backgroundColor = [meterColor, '#333'];
        co2Gauge.update();

        // 2. Send to Laravel (Optional: uncomment fetch block if your backend is running)
        /*
        fetch('/sensor-data', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify(payload)
        });
        */

        // 3. Update Line Chart & Alerts
        co2LineChart.data.labels.push(now);
        co2LineChart.data.datasets[0].data.push(payload.co2_ppm);
        if (co2LineChart.data.labels.length > 25) {
            co2LineChart.data.labels.shift(); co2LineChart.data.datasets[0].data.shift();
        }
        
        if (payload.co2_ppm > thresholdPpm) {
            co2LineChart.data.datasets[0].borderColor = '#ff1744';
            co2LineChart.data.datasets[0].backgroundColor = 'rgba(255, 23, 68, 0.1)';
            chartAlert.classList.remove('d-none');
            
            // Trigger Massive Overlay
            if (!alertAcknowledged) {
                overlayRoomName.innerText = `in ${payload.zone_name}`;
                criticalOverlay.classList.remove('d-none');
            }
        } else {
            co2LineChart.data.datasets[0].borderColor = '#00e676';
            co2LineChart.data.datasets[0].backgroundColor = 'rgba(0, 230, 118, 0.1)';
            chartAlert.classList.add('d-none');
            criticalOverlay.classList.add('d-none'); // Hide if it drops naturally
            alertAcknowledged = false;
        }
        co2LineChart.update();

    }, 2000); // 2 seconds update

    // --- LOGGING HELPER ---
    function logEvent(message, colorClass) {
        const logUl = document.getElementById('event-log');
        const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'});
        const li = document.createElement('li');
        li.className = `list-group-item bg-transparent ${colorClass} border-secondary py-1`;
        li.innerText = `[${time}] ${message}`;
        logUl.prepend(li);
        if (logUl.children.length > 10) logUl.removeChild(logUl.lastChild);
    }
</script>

</body>
</html>