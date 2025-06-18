<?php
$servername = "localhost";
$username = "root";
$password = "";
$database_name = "final_project";

// Create connection
$conn = new mysqli($servername, $username, $password, $database_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query for table (no LIMIT now)
$sql = "SELECT id, temperature, humidity, status, timestamp FROM dht_data ORDER BY timestamp DESC";
$result = $conn->query($sql);

// Begin HTML output
echo "<style>
  body {
    font-family: 'Rajdhani', sans-serif;
    background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
    color: #f0f0f0;
    margin: 0;
    padding: 20px;
  }

  a {
    padding: 10px 20px;
    background-color: #00f7ff;
    color: #0f2027;
    text-decoration: none;
    font-weight: bold;
    border-radius: 8px;
    transition: background-color 0.3s ease;
  }

  a:hover {
    background-color: #00d5e0;
  }

  h2 {
    text-align: center;
    font-size: 1.8rem;
    color:rgb(255, 255, 255);
    margin: 20px 0;
  }

  .table-container {
    max-height: 600px;
    overflow-y: auto;
    border: 2px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    background-color: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(4px);
    box-shadow: 0 8px 20px rgba(0, 255, 255, 0.05);
  }
    

  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 1rem;
  }

  th, td {
    padding: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
  }

  th {
    background-color: #00f7ff;
    color: #0f2027;
    position: sticky;
    top: 0;
    z-index: 1;
    font-weight: bold;
  }

  tr:hover {
    background-color: rgba(0, 247, 255, 0.2);
  }

  .chart-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 30px;
    margin-top: 40px;
  }

  .chart-box {
    flex: 1;
    min-width: 300px;
    max-width: 800px;
    background-color: rgba(255, 255, 255, 0.05);
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 6px 12px rgba(0, 255, 255, 0.1);
  }

  canvas {
    width: 100% !important;
    height: auto !important;
  }

  @media (max-width: 600px) {
    h2 {
      font-size: 1.4rem;
    }

    th, td {
      font-size: 0.85rem;
      padding: 8px;
    }

    a {
      font-size: 0.9rem;
      padding: 8px 16px;
    }
  }
</style>";


echo "<div style='display: flex; justify-content: space-between; align-items: center; margin: 20px;'>
        <a href='index.php' style='
            padding: 8px 16px;
            background-color: #00F7FF;
            color: black;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        '>← Back to Dashboard</a>
        <h2 style='margin: 0; flex-grow: 1; text-align: center; align-items:center; color: white;'>Temperature Monitoring</h2>
      </div>";

// Scrollable table
echo "<div class='table-container'><table>
        <tr>
            <th>Temperature ID</th>
            <th>Temperature Value (°C)</th>
            <th>Humidity Value (%)</th>
            <th>Temperature Status</th>
            <th>Date Collected</th>
        </tr>";


if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['temperature']}</td>
                <td>{$row['humidity']}</td>
                <td>{$row['status']}</td>
                <td>{$row['timestamp']}</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='4'>No results found.</td></tr>";
}
echo "</table></div>";

// Prepare chart data
$conn2 = new mysqli($servername, $username, $password, $database_name);
$sql2 = "SELECT temperature, humidity, status, timestamp FROM dht_data ORDER BY timestamp ASC";
$result2 = $conn2->query($sql2);

$timestamps = [];
$temperatures = [];
$humidities = [];
$temp_max = 0;
$hum_max = 0;

while ($row = $result2->fetch_assoc()) {
    $time = date('H:i:s', strtotime($row['timestamp']));
    $timestamps[] = $time;
    $temperatures[] = $row['temperature'];
    $humidities[] = $row['humidity'];

    if ($row['temperature'] > $temp_max) $temp_max = $row['temperature'];
    if ($row['humidity'] > $hum_max) $hum_max = $row['humidity'];
}
$conn2->close();
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Chart Containers -->
<div class="chart-container">
  <div class="chart-box">
    <canvas id="tempChart" height="400"></canvas>
  </div>
  <div class="chart-box">
    <canvas id="humChart" height="400"></canvas>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let tempChart, humChart;

function fetchAndUpdate() {
  fetch('get_dht_history.php')
    .then(response => response.json())
    .then(data => {
      if (!data.length) return;

      const tableBody = data.map(row => `
        <tr>
          <td>${row.id}</td>
          <td>${row.temperature}</td>
          <td>${row.humidity}</td>
          <td>${row.status}</td>
          <td>${row.timestamp}</td>
        </tr>
      `).join('');

      document.querySelector("table").innerHTML = `
        <tr>
          <th>Temperature ID</th>
          <th>Temperature Value (°C)</th>
          <th>Humidity Value (%)</th>
          <th>Temperature Status</th>
          <th>Date Collected</th>
        </tr>` + tableBody;

      const timestamps = data.map(row => row.timestamp.split(" ")[1]); // Time only
      const temperatures = data.map(row => parseFloat(row.temperature));
      const humidities = data.map(row => parseFloat(row.humidity));

      const tempMax = Math.max(...temperatures) + 5;
      const humMax = Math.max(...humidities) + 5;

      // Update charts
      if (tempChart && humChart) {
        tempChart.data.labels = timestamps;
        tempChart.data.datasets[0].data = temperatures;
        tempChart.options.scales.y.suggestedMax = tempMax;
        tempChart.update();

        humChart.data.labels = timestamps;
        humChart.data.datasets[0].data = humidities;
        humChart.options.scales.y.suggestedMax = humMax;
        humChart.update();
      } else {
        const tempCtx = document.getElementById('tempChart').getContext('2d');
        tempChart = new Chart(tempCtx, {
          type: 'line',
          data: {
            labels: timestamps,
            datasets: [{
              label: 'Temperature (°C)',
              data: temperatures,
              borderColor: 'rgba(255, 99, 132, 1)',
              backgroundColor: 'rgba(255, 99, 132, 0.2)',
              fill: false,
              tension: 0.1
            }]
          },
          options: {
            responsive: true,
          scales: {
            y: {
              suggestedMin: 0,
              suggestedMax: tempMax, // or humMax
              title: {
                display: true,
                text: 'Temperature (°C)', // or ''
                color: 'white'
              },
              ticks: {
                color: 'white'
              },
              grid: {
                color: 'white'
              }
            },
            x: {
              title: {
                display: true,
                text: 'Time',
                color: 'white'
              },
              ticks: {
                color: 'white'
              },
              grid: {
                color: 'white'
              }
            }
          }
          }
        });

        const humCtx = document.getElementById('humChart').getContext('2d');
        humChart = new Chart(humCtx, {
          type: 'line',
          data: {
            labels: timestamps,
            datasets: [{
              label: 'Humidity (%)',
              color:'white',
              data: humidities,
              borderColor: 'rgba(54, 162, 235, 1)',
              backgroundColor: 'rgba(54, 162, 235, 0.2)',
              fill: false,
              tension: 0.1
            }]
          },
          options: {
            responsive: true,
          scales: {
            y: {
              suggestedMin: 0,
              suggestedMax: tempMax, // or humMax
              title: {
                display: true,
                text: 'Humidity (%)',
                color: 'white'
              },
              ticks: {
                color: 'white'
              },
              grid: {
                color: 'white'
              }
            },
            x: {
              title: {
                display: true,
                text: 'Time',
                color: 'white'
              },
              ticks: {
                color: 'white'
              },
              grid: {
                color: 'white'
              }
            }
          }
          }
        });
      }
    });
}

// Initial fetch
fetchAndUpdate();

// Refresh every 10 seconds
setInterval(fetchAndUpdate, 10000);
</script>
