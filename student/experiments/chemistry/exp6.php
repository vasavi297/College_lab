<?php
session_start();
require_once __DIR__ . '/../../device_guard.php';
ensure_desktop_only();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Experiment 6: CONDUCTOMETRIC TITRATION (WEAK ACID Vs STRONG BASE)</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 <link rel="stylesheet" href="../../../css/experiments.css">
 <style>
    textarea {
    
  width: 100%;
        display: block;
        overflow: auto;
        resize: none;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-family: 'Poppins', sans-serif;
        margin-bottom: 15px;
}
</style>
</head>
<body>
  <div class="container">
    <!-- Main Form -->
    <form id="exp6-form" method="post"  class="form-section">

      <!-- Experiment Header -->
      <div class="exp-header">
        <div style="display:flex;flex-direction:column;">
          <label for="expNo">Experiment No.6</label>
          <input type="hidden" id="subject" name="subject" value="Chemistry">
    <input type="hidden" id="experiment_number" name="experiment_number" value="6">
                </div>
        <div style="display:flex;flex-direction:column;">
          <label for="expDate">Date</label>
          <input type="date" id="expDate" name="expDate" />
        </div>
         <button type="button" id="fullscreenBtn" title="Full Screen" class="fullscreen-btn"style="position:absolute; right:70px;top:70px" onclick="toggleFullScreen()">Full Screen</button>
      </div>

      <?php
// Check if this is a retake
$is_retake = isset($_GET['is_retake']) && $_GET['is_retake'] == '1';
$retake_count = isset($_GET['retake_count']) ? intval($_GET['retake_count']) : 0;
$attempt_number = $retake_count + 1;
?>

<?php if ($is_retake): ?>
<div style="background: #fef3c7; padding: 12px; border-radius: 6px; border-left: 4px solid #f59e0b; margin-bottom: 20px;">
    <strong>⚠️ Retake Submission - Attempt <?php echo $attempt_number; ?></strong>
    <p style="margin: 5px 0 0 0; font-size: 0.9rem;">
        Please correct your previous submission based on the feedback provided.
        <?php if ($retake_count > 0): ?>
            This is your <?php echo ($retake_count == 1 ? 'second' : ($retake_count == 2 ? 'third' : ($retake_count+1).'th')); ?> attempt.
        <?php endif; ?>
    </p>
</div>
<?php endif; ?>

      <h2 style="font-size: 30px;">CONDUCTOMETRIC TITRATION (WEAK ACID Vs STRONG BASE)</h2>

      <label for="aim">Aim</label>
      <textarea id="aim" name="aim" rows="3" placeholder="Enter experiment aim"></textarea>

      <label>Apparatus Used (Drag and Drop)</label>
      <div id="apparatus-dropbox" class="apparatus-dropbox" aria-label="Apparatus dropbox">
        <small id="apparatus-placeholder" style="color:#777;">Drag apparatus here</small>
      </div>
      <input type="hidden" id="apparatus_list" name="apparatus_list" value="">

      <label for="chemicals">Chemicals Required:-</label>
      <textarea id="chemicals" name="chemicals" rows="3" placeholder="List chemicals"></textarea>

      <label for="principle">Principle</label>
      <textarea id="principle" name="principle" rows="3" placeholder="Explain principle"></textarea>

      <h3>Procedure - Part A: Standardization of NaOH</h3>
      <textarea id="procedure_a" name="procedure_a" rows="4" placeholder="Enter Procedure Part A"></textarea>

      <h4>Standardization of NaOH Solution</h4>
      <table>
        <tr>
          <th rowspan="2">S.No</th>
          <th rowspan="2">Volume of CH<sub>3</sub>COOH Solution (ml)</th>
          <th colspan="2">Burette Reading (ml)</th>
          <th rowspan="2">Volume of NaOH solution (ml)</th>
        </tr>
        <tr><th>Initial</th><th>Final</th></tr>
        <tr><td>1</td><td><input type="text" name="std_v1" /></td><td><input type="text" name="std_br_initial1" /></td><td><input type="text" name="std_br_final1" /></td><td><input type="text" name="std_vol_k2_1" /></td></tr>
        <tr><td>2</td><td><input type="text" name="std_v2" /></td><td><input type="text" name="std_br_initial2" /></td><td><input type="text" name="std_br_final2" /></td><td><input type="text" name="std_vol_k2_2" /></td></tr>
        <tr><td>3</td><td><input type="text" name="std_v3" /></td><td><input type="text" name="std_br_initial3" /></td><td><input type="text" name="std_br_final3" /></td><td><input type="text" name="std_vol_k2_3" /></td></tr>
      </table>

      <h3>Calculations</h3>
      <div class="calculation-box">
        <div class="calc-row">
          <label for="calc_std_n1">N₁ = Normality of Oxalic Acid solution =</label>
          <input type="text" id="calc_std_n1" name="calc_std_n1" /><p><b>N</b></p>
        </div>
        <div class="calc-row">
          <label for="calc_std_n2">N₂ = Normality of NaOH solution =</label>
          <input type="text" id="calc_std_n2" name="calc_std_n2" />
        </div>
        <div class="calc-row">
          <label for="calc_std_v1">V₁ = Volume of Oxalic Acid solution =</label>
          <input type="text" id="calc_std_v1" name="calc_std_v1" />
        </div>
        <div class="calc-row">
          <label for="calc_std_v2">V₂ = Volume of NaOH solution =</label>
          <input type="text" id="calc_std_v2" name="calc_std_v2" />
        </div>

        <div class="formula-text">Formula: N₁V₁ = N₂V₂</div>

        <div class="calc-row">
          <label for="calc_std_n2_calc">N₂ = (N₁V₁) / V₂ = </label>
          <input type="text" id="calc_std_n2_calc" name="calc_std_n2_calc" />
        </div>
        <div class="result-text">Normality of NaOH solution (N₂) is <input type="text" id="calc_std_n2_final" name="calc_std_n2_final" style="width: 110px;" /> N.</div>
      </div>

      <h3>Procedure - Part B: Estimation of CH<sub>3</sub>COOH</h3>
      <textarea id="procedure_b" name="procedure_b" rows="4" placeholder="Enter Procedure Part B"></textarea>

      <h4>Estimation Table</h4>
<table>
  <tr>
    <th rowspan="2">S.No</th>
    <th rowspan="2">Volume of NaOH Added (ml)</th>
    <th rowspan="2">Observed Conductance (µS/cm)</th>
    <th rowspan="2">Corrected Conductance = <br>G = ((u + v)/u)c</th>
  </tr>
  <tr></tr>
  <tr><td>1</td><td><input type="text" name="est_v1" class="volume-input" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_conductance1" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_vol_k2_1" onchange="updateGraph()" /></td></tr>
  <tr><td>2</td><td><input type="text" name="est_v2" class="volume-input" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_conductance2" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_vol_k2_2" onchange="updateGraph()" /></td></tr>
  <tr><td>3</td><td><input type="text" name="est_v3" class="volume-input" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_conductance3" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_vol_k2_3" onchange="updateGraph()" /></td></tr>
  <tr><td>4</td><td><input type="text" name="est_v4" class="volume-input" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_conductance4" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_vol_k2_4" onchange="updateGraph()" /></td></tr>
  <tr><td>5</td><td><input type="text" name="est_v5" class="volume-input" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_conductance5" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_vol_k2_5" onchange="updateGraph()" /></td></tr>
  <tr><td>6</td><td><input type="text" name="est_v6" class="volume-input" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_conductance6" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_vol_k2_6" onchange="updateGraph()" /></td></tr>
  <tr><td>7</td><td><input type="text" name="est_v7" class="volume-input" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_conductance7" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_vol_k2_7" onchange="updateGraph()" /></td></tr>
  <tr><td>8</td><td><input type="text" name="est_v8" class="volume-input" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_conductance8" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_vol_k2_8" onchange="updateGraph()" /></td></tr>
  <tr><td>9</td><td><input type="text" name="est_v9" class="volume-input" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_conductance9" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_vol_k2_9" onchange="updateGraph()" /></td></tr>
  <tr><td>10</td><td><input type="text" name="est_v10" class="volume-input" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_conductance10" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_vol_k2_10" onchange="updateGraph()" /></td></tr>
  <tr><td>11</td><td><input type="text" name="est_v11" class="volume-input" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_conductance11" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_vol_k2_11" onchange="updateGraph()" /></td></tr>
  <tr><td>12</td><td><input type="text" name="est_v12" class="volume-input" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_conductance12" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_vol_k2_12" onchange="updateGraph()" /></td></tr>
  <tr><td>13</td><td><input type="text" name="est_v13" class="volume-input" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_conductance13" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_vol_k2_13" onchange="updateGraph()" /></td></tr>
  <tr><td>14</td><td><input type="text" name="est_v14" class="volume-input" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_conductance14" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_vol_k2_14" onchange="updateGraph()" /></td></tr>
  <tr><td>15</td><td><input type="text" name="est_v15" class="volume-input" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_conductance15" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_vol_k2_15" onchange="updateGraph()" /></td></tr>
  <tr><td>16</td><td><input type="text" name="est_v16" class="volume-input" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_conductance16" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_vol_k2_16" onchange="updateGraph()" /></td></tr>
  <tr><td>17</td><td><input type="text" name="est_v17" class="volume-input" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_conductance17" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_vol_k2_17" onchange="updateGraph()" /></td></tr>
  <tr><td>18</td><td><input type="text" name="est_v18" class="volume-input" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_conductance18" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_vol_k2_18" onchange="updateGraph()" /></td></tr>
  <tr><td>19</td><td><input type="text" name="est_v19" class="volume-input" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_conductance19" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_vol_k2_19" onchange="updateGraph()" /></td></tr>
  <tr><td>20</td><td><input type="text" name="est_v20" class="volume-input" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_conductance20" onchange="calculateCorrectedConductance()" /></td><td><input type="text" name="est_vol_k2_20" onchange="updateGraph()" /></td></tr>
</table>

      <!-- Graph Section -->
      <div class="graph-section">
        <h3>Conductometric Titration Curve</h3>
        <div style="width: 100%; max-width: 800px; margin: 20px auto;">
          <canvas id="titrationChart" width="800" height="400"></canvas>
        </div>
        <div id="graph-placeholder" style="text-align: center; padding: 40px; color: #666; background: #f9f9f9; border-radius: 5px;">
          <p><strong>Graph will appear here when you enter data in the table above</strong></p>
          <p>Enter Volume of NaOH and Corrected Conductance values to generate the titration curve</p>
        </div>
        <div id="endpoint-info" style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px; display: none;">
          <strong>Equivalence Point:</strong> 
          <span id="endpoint-volume">0</span> ml of NaOH
        </div>
      </div>

      
      <h3>Calculations</h3>
      <div class="calculation-box">
        <div class="calc-row">
          <label for="calc_est_n1">N₂ = Normality of NaOH solution</label>
          <input type="text" id="calc_est_n1" name="calc_est_n1" /><p><b>N</b></p>
        </div>
        <div class="calc-row">
          <label for="calc_est_n2">N₃ = Normality of CH<sub>3</sub>COOH solution</label>
          <input type="text" id="calc_est_n2" name="calc_est_n2" />
        </div>
        <div class="calc-row">
          <label for="calc_est_v1">V₂ = Volume of NaOH solution</label>
          <input type="text" id="calc_est_v1" name="calc_est_v1" /><P><B>ml</B>(From Graph)</P>
        </div>
        <div class="calc-row">
          <label for="calc_est_v2">V₃ = Volume of CH<sub>3</sub>COOH solution</label>
          <input type="text" id="calc_est_v2" name="calc_est_v2" /><p><b>ml</b></p>
        </div>

        <div class="formula-text">Formula: N₂V₂ = N₃V₃</div>

        <div class="calc-row">
          <label for="calc_est_n3_calc">N₃ = (N₂V₂) / V₃ = </label>
          <input type="text" id="calc_est_n3_calc" name="calc_est_n3_calc" />
        </div>
        <div class="result-text">Normality of CH<sub>3</sub>COOH solution (N₃) is <input type="text" id="calc_est_n3_final" name="calc_est_n3_final" style="width: 110px;" /></div>

        <div class="calc-row">
          <label for="calc_est_amount">Amount of CH<sub>3</sub>COOH solution present in given solution is </label>
        </div>

        <div class="calc-row">
          <label for="calc_est_q_calc">Q = (E × N₃ × Volume in ml) / 1000 = </label>
          <input type="text" id="calc_est_q" name="calc_est_q" /><p><b>g/1000ml</b></p>
        </div>

        <div class="result-text" style="display:block;">
          Where: E = Gram equivalent weight of CH<sub>3</sub>COOH = <input type="text" id="calc_est_e" name="calc_est_e" readonly value="60" style="width: 80px;" /> grams<br />
          N = Normality of CH<sub>3</sub>COOH solution (N₃) = <input type="text" id="calc_est_n_val" name="calc_est_n_val" style="width: 100px;" /><br />
          V = Volume of CH<sub>3</sub>COOH solution = <input type="text" id="calc_est_v_val" name="calc_est_v_val" style="width: 100px;" />
        </div>
      </div>

      <label for="result">Result</label>
      <textarea id="result" name="result" rows="3" placeholder="Write final conclusion"></textarea>
<div class="btn-group">
   
    
       <button type="button" onclick="previewExp()" style="cursor:pointer; background:#007bff; color:#fff; font-weight:600; padding:8px 16px; border-radius:6px; width: fit-content;">Preview </button>
       <button type="button" id="submitBtn" onclick="submitExperiment()" style="cursor:pointer; background:#1a347a; color:#fff; font-weight:600; padding:8px 16px; border-radius:6px; width: fit-content;">Submit</button>
            </div>
    </form>

    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="apparatus-box">
        <h3 style="font-size:22px;margin-bottom:12px;text-align:center;">Select Apparatus </h3>
        <div class="apparatus-list">
          <button type="button" class="apparatus-btn">Conductivity meter</button>
          <button type="button" class="apparatus-btn">Conductivity Cell</button>
          <button type="button" class="apparatus-btn">Burette</button>
          <button type="button" class="apparatus-btn">Pipette Beaker</button>
          <button type="button" class="apparatus-btn">Conical Flask</button>
          <button type="button" class="apparatus-btn">Measuring Cylinder</button>
          <button type="button" class="apparatus-btn">Micro Burette</button>
          <button type="button" class="apparatus-btn">Magnetic Stirrer </button>
        </div>
      </div>
      <div class="calculator-box">
        <h3 style="font-size:22px;margin-bottom:16px;">Calculator</h3>
        <input type="text" id="calc-display" readonly />
        <div class="calc-buttons">
          <button class="calc-btn calc-red" onclick="clearCalc()">C</button>
          <button class="calc-btn" onclick="press('+')">+</button>
          <button class="calc-btn" onclick="press('*')">×</button>
          <button class="calc-btn" onclick="press('/')">÷</button>
          <button class="calc-btn" onclick="press('7')">7</button>
          <button class="calc-btn" onclick="press('8')">8</button>
          <button class="calc-btn" onclick="press('9')">9</button>
          <button class="calc-btn" onclick="press('-')">−</button>
          <button class="calc-btn" onclick="press('4')">4</button>
          <button class="calc-btn" onclick="press('5')">5</button>
          <button class="calc-btn" onclick="press('6')">6</button>
          <button class="calc-btn calc-equal" onclick="calculate()">=</button>
          <button class="calc-btn" onclick="press('1')">1</button>
          <button class="calc-btn" onclick="press('2')">2</button>
          <button class="calc-btn" onclick="press('3')">3</button>
          <button class="calc-btn" onclick="press('0')">0</button>
           <button class="calc-btn" onclick="addDecimal()">.</button>
        </div>
      </div>
    </aside>
  </div>

  <script>
    // -------- Fullscreen Toggle --------
function toggleFullScreen() {
    const elem = document.documentElement;
    const btn = document.getElementById('fullscreenBtn');
    if (!document.fullscreenElement) {
        elem.requestFullscreen().then(() => {
            btn.textContent = 'Exit Full Screen';
            btn.title = 'Exit Full Screen';
        });
    } else {
        document.exitFullscreen().then(() => {
            btn.textContent = 'Full Screen';
            btn.title = 'Full Screen';
        });
    }
}

document.addEventListener('fullscreenchange', function() {
    const btn = document.getElementById('fullscreenBtn');
    if (!document.fullscreenElement) {
        btn.textContent = 'Full Screen';
        btn.title = 'Full Screen';
    } else {
        btn.textContent = 'Exit Full Screen';
        btn.title = 'Exit Full Screen';
    }
});
    let titrationChart = null;

    
        function press(value) {
            const display = document.getElementById('calc-display');
            
            // Reset decimal flag when operator is pressed
            if (['+', '-', '*', '/'].includes(value)) {
                hasDecimal = false;
            }
            
            display.value += value;
        }

        function addDecimal() {
            const display = document.getElementById('calc-display');
            
            // If no decimal in current number, add it
            if (!hasDecimal) {
                // If display is empty or last character is operator, add "0." first
                if (display.value === '' || ['+', '-', '*', '/'].includes(display.value.slice(-1))) {
                    display.value += '0.';
                } else {
                    display.value += '.';
                }
                hasDecimal = true;
            }
        }

        function clearCalc() {
            document.getElementById('calc-display').value = "";
            hasDecimal = false;
        }

        function calculate() {
            const display = document.getElementById('calc-display');
            try {
                // Handle trailing decimal
                if (display.value.slice(-1) === '.') {
                    display.value += '0';
                }
                
                display.value = eval(display.value);
                hasDecimal = display.value.includes('.');
            } catch (e) {
                display.value = "Error";
                hasDecimal = false;
            }
        }

    // ---------- Graph Functions ----------
    function updateGraph() {
      const volumes = [];
      const conductances = [];
      
      // Collect data from table
      for (let i = 1; i <= 20; i++) {
        
        const volumeInput = document.querySelector(`input[name="est_v${i}"]`);
        const conductanceInput = document.querySelector(`input[name="est_vol_k2_${i}"]`);
        
        if (volumeInput && volumeInput.value && conductanceInput && conductanceInput.value) {
          const volume = parseFloat(volumeInput.value);
          const conductance = parseFloat(conductanceInput.value);
          
          volumes.push(volume);
          conductances.push(conductance);
        }
      }
      
      // Only generate graph if we have at least 2 data points
      if (volumes.length >= 2) {
        generateTitrationCurve(volumes, conductances);
      } else {
        // Hide graph and show placeholder if not enough data
        const canvas = document.getElementById('titrationChart');
        const placeholder = document.getElementById('graph-placeholder');
        const endpointInfo = document.getElementById('endpoint-info');
        
        if (titrationChart) {
          titrationChart.destroy();
          titrationChart = null;
        }
        
        canvas.style.display = 'none';
        placeholder.style.display = 'block';
        endpointInfo.style.display = 'none';
      }
    }

    function generateTitrationCurve(volumes, conductances) {
      const ctx = document.getElementById('titrationChart').getContext('2d');
      const placeholder = document.getElementById('graph-placeholder');
      
      // Show canvas and hide placeholder
      document.getElementById('titrationChart').style.display = 'block';
      placeholder.style.display = 'none';
      
      // Destroy existing chart
      if (titrationChart) {
        titrationChart.destroy();
      }
      
      // Generate curve data based on user input
      const data = generateCurveFromUserData(volumes, conductances);
      
      titrationChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: data.volumes,
          datasets: [{
            label: 'Corrected Conductance (µS/cm)',
            data: data.conductances,
            borderColor: '#000000',
            backgroundColor: 'rgba(0, 0, 0, 0.1)',
            borderWidth: 2,
            fill: false,
            tension: 0.4,
            pointBackgroundColor: '#000000',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 1,
            pointRadius: 3,
            pointHoverRadius: 5
          }]
        },
        options: {
          responsive: true,
          plugins: {
            title: {
              display: true,
              text: 'Conductometric Titration: CH₃COOH vs NaOH',
              font: {
                size: 16,
                weight: 'bold'
              },
              color: '#000000'
            },
            legend: {
              display: true,
              labels: {
                color: '#000000',
                font: {
                  size: 12,
                  weight: 'bold'
                }
              }
            },
            tooltip: {
              mode: 'index',
              intersect: false,
              backgroundColor: 'rgba(0, 0, 0, 0.8)',
              titleColor: '#ffffff',
              bodyColor: '#ffffff'
            }
          },
          scales: {
            x: {
              title: {
                display: true,
                text: 'Volume of NaOH Added (ml)',
                color: '#000000',
                font: {
                  size: 12,
                  weight: 'bold'
                }
              },
              grid: {
                color: '#cccccc',
                lineWidth: 1
              },
              ticks: {
                color: '#000000',
                font: {
                  size: 11
                }
              }
            },
            y: {
              title: {
                display: true,
                text: 'Corrected Conductance (µS/cm)',
                color: '#000000',
                font: {
                  size: 12,
                  weight: 'bold'
                }
              },
              grid: {
                color: '#cccccc',
                lineWidth: 1
              },
              ticks: {
                color: '#000000',
                font: {
                  size: 11
                }
              }
            }
          },
          interaction: {
            intersect: false,
            mode: 'nearest'
          },
          elements: {
            line: {
              tension: 0.4
            }
          }
        }
      });
      
      // Update endpoint information
      updateEndpointInfo(data.equivalencePoint);
    }

    function generateCurveFromUserData(inputVolumes, inputConductances) {
      // Use only the user's actual data points
      const volumes = [...inputVolumes];
      const conductances = [...inputConductances];
      let equivalencePoint = 0;
      
      if (volumes.length >= 2) {
        // Find the equivalence point (minimum point for weak acid-strong base)
        let minConductance = Math.min(...conductances);
        let minIndex = conductances.indexOf(minConductance);
        equivalencePoint = volumes[minIndex];
      }
      
      // Update endpoint volume in calculations
      if (equivalencePoint > 0) {
        document.getElementById('calc_est_v1').value = equivalencePoint.toFixed(2);
      }
      
      return { volumes, conductances, equivalencePoint };
    }

    function updateEndpointInfo(equivalencePoint) {
      const endpointInfo = document.getElementById('endpoint-info');
      const endpointVolume = document.getElementById('endpoint-volume');
      
      if (equivalencePoint > 0) {
        endpointVolume.textContent = equivalencePoint.toFixed(2);
        endpointInfo.style.display = 'block';
      } else {
        endpointInfo.style.display = 'none';
      }
    }
    function calculateCorrectedConductance() {
    for (let i = 1; i <= 20; i++) {
        const volumeInput = document.querySelector(`input[name="est_v${i}"]`);
        const observedInput = document.querySelector(`input[name="est_conductance${i}"]`);
        const correctedInput = document.querySelector(`input[name="est_vol_k2_${i}"]`);
        
        if (volumeInput && volumeInput.value && observedInput && observedInput.value) {
            const volume = parseFloat(volumeInput.value);
            const observed = parseFloat(observedInput.value);
            const u = 25; // Initial volume of CH₃COOH solution
            const corrected = ((u + volume) / u) * observed;
            
            correctedInput.value = corrected.toFixed(2);
        }
    }
    updateGraph(); // Update graph after calculation
}

    // Function to create print-style graph for preview/submission
    function createPrintStyleGraph(volumes, conductances, width = 600, height = 400) {
      const canvas = document.createElement('canvas');
      canvas.width = width;
      canvas.height = height;
      const ctx = canvas.getContext('2d');
      
      // Set white background
      ctx.fillStyle = '#ffffff';
      ctx.fillRect(0, 0, width, height);
      
      // Chart dimensions with margins
      const margin = { top: 60, right: 40, bottom: 60, left: 60 };
      const chartWidth = width - margin.left - margin.right;
      const chartHeight = height - margin.top - margin.bottom;
      
      // Find min and max values
      const minVol = Math.min(...volumes);
      const maxVol = Math.max(...volumes);
      const minCond = Math.min(...conductances);
      const maxCond = Math.max(...conductances);
      
      // Scales
      const xScale = (vol) => margin.left + ((vol - minVol) / (maxVol - minVol)) * chartWidth;
      const yScale = (cond) => margin.top + chartHeight - ((cond - minCond) / (maxCond - minCond)) * chartHeight;
      
      // Draw grid lines
      ctx.strokeStyle = '#e0e0e0';
      ctx.lineWidth = 1;
      
      // Vertical grid lines
      const volSteps = 5;
      for (let i = 0; i <= volSteps; i++) {
        const vol = minVol + (i / volSteps) * (maxVol - minVol);
        const x = xScale(vol);
        ctx.beginPath();
        ctx.moveTo(x, margin.top);
        ctx.lineTo(x, margin.top + chartHeight);
        ctx.stroke();
      }
      
      // Horizontal grid lines
      const condSteps = 5;
      for (let i = 0; i <= condSteps; i++) {
        const cond = minCond + (i / condSteps) * (maxCond - minCond);
        const y = yScale(cond);
        ctx.beginPath();
        ctx.moveTo(margin.left, y);
        ctx.lineTo(margin.left + chartWidth, y);
        ctx.stroke();
      }
      
      // Draw axes
      ctx.strokeStyle = '#000000';
      ctx.lineWidth = 2;
      
      // X-axis
      ctx.beginPath();
      ctx.moveTo(margin.left, margin.top + chartHeight);
      ctx.lineTo(margin.left + chartWidth, margin.top + chartHeight);
      ctx.stroke();
      
      // Y-axis
      ctx.beginPath();
      ctx.moveTo(margin.left, margin.top);
      ctx.lineTo(margin.left, margin.top + chartHeight);
      ctx.stroke();
      
      // Draw data points and line
      ctx.strokeStyle = '#000000';
      ctx.lineWidth = 2;
      ctx.fillStyle = '#000000';
      
      // Draw line
      ctx.beginPath();
      for (let i = 0; i < volumes.length; i++) {
        const x = xScale(volumes[i]);
        const y = yScale(conductances[i]);
        if (i === 0) {
          ctx.moveTo(x, y);
        } else {
          ctx.lineTo(x, y);
        }
      }
      ctx.stroke();
      
      // Draw points
      for (let i = 0; i < volumes.length; i++) {
        const x = xScale(volumes[i]);
        const y = yScale(conductances[i]);
        ctx.beginPath();
        ctx.arc(x, y, 3, 0, 2 * Math.PI);
        ctx.fill();
      }
      
      // Draw title
      ctx.fillStyle = '#000000';
      ctx.font = 'bold 16px Arial';
      ctx.textAlign = 'center';
      ctx.fillText('Conductometric Titration: CH₃COOH vs NaOH', width / 2, 30);
      
      // Draw X-axis label
      ctx.font = 'bold 12px Arial';
      ctx.fillText('Volume of NaOH Added (ml)', width / 2, height - 15);
      
      // Draw Y-axis label
      ctx.save();
      ctx.translate(20, height / 2);
      ctx.rotate(-Math.PI / 2);
      ctx.fillText('Corrected Conductance (µS/cm)', 0, 0);
      ctx.restore();
      
      // Draw X-axis ticks and labels
      ctx.font = '11px Arial';
      ctx.textAlign = 'center';
      for (let i = 0; i <= volSteps; i++) {
        const vol = minVol + (i / volSteps) * (maxVol - minVol);
        const x = xScale(vol);
        ctx.beginPath();
        ctx.moveTo(x, margin.top + chartHeight);
        ctx.lineTo(x, margin.top + chartHeight + 5);
        ctx.stroke();
        ctx.fillText(vol.toFixed(1), x, margin.top + chartHeight + 20);
      }
      
      // Draw Y-axis ticks and labels
      ctx.textAlign = 'right';
      for (let i = 0; i <= condSteps; i++) {
        const cond = minCond + (i / condSteps) * (maxCond - minCond);
        const y = yScale(cond);
        ctx.beginPath();
        ctx.moveTo(margin.left, y);
        ctx.lineTo(margin.left - 5, y);
        ctx.stroke();
        ctx.fillText(cond.toFixed(0), margin.left - 10, y + 4);
      }
      
      // Draw equivalence point if available
      if (volumes.length >= 2) {
        const minConductance = Math.min(...conductances);
        const minIndex = conductances.indexOf(minConductance);
        const eqVolume = volumes[minIndex];
        
        const eqX = xScale(eqVolume);
        const eqY = yScale(minConductance);
        
        // Draw vertical line at equivalence point
        ctx.strokeStyle = '#ff0000';
        ctx.lineWidth = 1;
        ctx.setLineDash([5, 3]);
        ctx.beginPath();
        ctx.moveTo(eqX, margin.top);
        ctx.lineTo(eqX, margin.top + chartHeight);
        ctx.stroke();
        
        // Draw horizontal line at equivalence point
        ctx.beginPath();
        ctx.moveTo(margin.left, eqY);
        ctx.lineTo(margin.left + chartWidth, eqY);
        ctx.stroke();
        ctx.setLineDash([]);
        
        // Draw equivalence point marker
        ctx.fillStyle = '#ff0000';
        ctx.beginPath();
        ctx.arc(eqX, eqY, 5, 0, 2 * Math.PI);
        ctx.fill();
        
        // Draw equivalence point label
        ctx.fillStyle = '#ff0000';
        ctx.font = 'bold 12px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(`Equivalence Point: ${eqVolume.toFixed(2)} ml`, width / 2, 50);
      }
      
      return canvas.toDataURL('image/png');
    }

    // ---------- Calculation Functions ----------
    function calculateNormality() {
      const N2 = parseFloat(document.getElementById('calc_est_n1').value) || 0;
      const V2 = parseFloat(document.getElementById('calc_est_v1').value) || 0;
      const V3 = parseFloat(document.getElementById('calc_est_v2').value) || 25;
      
      if (N2 > 0 && V2 > 0 && V3 > 0) {
        const N3 = (N2 * V2) / V3;
        document.getElementById('calc_est_n3_calc').value = N3.toFixed(4);
        document.getElementById('calc_est_n3_final').value = N3.toFixed(4);
        document.getElementById('calc_est_n_val').value = N3.toFixed(4);
        
        // Calculate amount
        const E = 60; // Gram equivalent weight of CH₃COOH
        const Q = (E * N3 * V3) / 1000;
        document.getElementById('calc_est_q').value = Q.toFixed(4);
      }
    }

    // ---------- Drag & Drop Functions ----------
    function addApparatusToDropbox(name) {
      const placeholder = document.getElementById('apparatus-placeholder');
      if (placeholder) placeholder.style.display = 'none';
      const dropZone = document.getElementById('apparatus-dropbox');
      
      const existingItems = Array.from(dropZone.querySelectorAll('.tool-item'));
      if (existingItems.some(item => item.textContent === name)) {
        return;
      }
      
      const item = document.createElement('div');
      item.className = 'tool-item';
      item.textContent = name;
      item.title = 'Click to remove';
      item.setAttribute('role','button');
      item.setAttribute('tabindex','0');
      item.setAttribute('draggable', 'false');

      item.addEventListener('click', () => {
        item.remove();
        if (dropZone.children.length === 0 && placeholder) {
          placeholder.style.display = 'inline';
        }
      });
      
      item.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          item.remove();
          if (dropZone.children.length === 0 && placeholder) {
            placeholder.style.display = 'inline';
          }
        }
      });

      dropZone.appendChild(item);
    }

    // ---------- Utility Functions ----------
    function escapeHtml(str) {
      if (!str) return '';
      return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }

        function formatTextWithBreaks(text) {
            if (!text) return '';
            const escaped = escapeHtml(text);
            return escaped.replace(/\n/g, '<br>');
        }

    function generateTableRowsForPreview() {
      let rows = '';
      for (let i = 1; i <= 20; i++) {
        const volume = document.querySelector(`input[name="est_v${i}"]`)?.value || '';
        const conductance = document.querySelector(`input[name="est_conductance${i}"]`)?.value || '';
        const correctedCond = document.querySelector(`input[name="est_vol_k2_${i}"]`)?.value || '';
        
        if (volume || conductance) {
          rows += `<tr>
            <td>${i}</td>
            <td>${escapeHtml(volume)}</td>
            <td>${escapeHtml(conductance)}</td>
            <td>${escapeHtml(correctedCond)}</td>
          </tr>`;
        }
      }
      return rows;
    }
    document.addEventListener("cheking tab switces", () => {
  if (document.hidden) { console.log("tab_switched");
  }
});



document.addEventListener("fullscreen", () => {
  if (!document.fullscreenElement) {console.log("exit full screen");
  }
});
    // ---------- Preview Function ----------
    function previewExp() {
      const form = document.getElementById('exp6-form');
      const apparatusList = Array.from(document.querySelectorAll("#apparatus-dropbox .tool-item"))
        .map(el => el.textContent.trim());

      // Collect graph data
      const volumes = [];
      const conductances = [];
      
      for (let i = 1; i <= 20; i++) {
        const volumeInput = document.querySelector(`input[name="est_v${i}"]`);
        const conductanceInput = document.querySelector(`input[name="est_vol_k2_${i}"]`);
        
        if (volumeInput && volumeInput.value && conductanceInput && conductanceInput.value) {
          volumes.push(parseFloat(volumeInput.value));
          conductances.push(parseFloat(conductanceInput.value));
        }
      }

      // Create print-style graph
      let graphDataURL = '';
      if (volumes.length >= 2) {
        graphDataURL = createPrintStyleGraph(volumes, conductances);
      }

      const previewHtml = `
    <style>
      .header-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        font-weight: 400;
        font-size: 1rem;
        color: #000000;
      }
      table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
      }
      table, th, td {
        border: 1px solid #000;
      }
      th, td {
        padding: 8px 10px;
        text-align: center;
      }
    </style>
    <div class="header-row">
       <div><b>Experiment No.:</b> 6</div>
      <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
    </div>
    <h2 style="text-align:center; margin-top: 0;">CONDUCTOMETRIC TITRATION (WEAK ACID Vs STRONG BASE)</h2>

    <p><b>Aim:</b> ${formatTextWithBreaks(form.aim.value || '')}</p>
    <p><b>Apparatus Used:</b> ${apparatusList.length ? escapeHtml(apparatusList.join(", ")) : '—'}</p>
    <p><b>Chemicals Required:</b> ${formatTextWithBreaks(form.chemicals.value || '')}</p>
    <p><b>Principle:</b> ${formatTextWithBreaks(form.principle.value || '')}</p>

    <h3>Procedure - Part A: Standardization of NaOH</h3>
    <p>${formatTextWithBreaks(form.procedure_a.value || '')}</p>

    <table border="1" cellspacing="0" cellpadding="5" style="width:100%; border-collapse: collapse;">
      <tr><th rowspan="2">S.No</th><th rowspan="2">Volume of CH₃COOH Solution (ml)</th><th colspan="2">Burette Reading (ml)</th><th rowspan="2">Volume of NaOH solution (ml)</th></tr>
      <tr><th>Initial</th><th>Final</th></tr>
      <tr><td>1</td><td>${escapeHtml(form.std_v1.value || '')}</td><td>${escapeHtml(form.std_br_initial1.value || '')}</td><td>${escapeHtml(form.std_br_final1.value || '')}</td><td>${escapeHtml(form.std_vol_k2_1.value || '')}</td></tr>
      <tr><td>2</td><td>${escapeHtml(form.std_v2.value || '')}</td><td>${escapeHtml(form.std_br_initial2.value || '')}</td><td>${escapeHtml(form.std_br_final2.value || '')}</td><td>${escapeHtml(form.std_vol_k2_2.value || '')}</td></tr>
      <tr><td>3</td><td>${escapeHtml(form.std_v3.value || '')}</td><td>${escapeHtml(form.std_br_initial3.value || '')}</td><td>${escapeHtml(form.std_br_final3.value || '')}</td><td>${escapeHtml(form.std_vol_k2_3.value || '')}</td></tr>
    </table>

    <h3>Calculations :</h3>
    <div>
      N₁: Normality of CH₃COOH solution = ${escapeHtml(form.calc_std_n1.value || '')} N<br><br>
      N₂: Normality of NaOH solution = ${escapeHtml(form.calc_std_n2.value || '')}<br><br>
      V₁: Volume of CH₃COOH solution = ${escapeHtml(form.calc_std_v1.value || '')}<br><br>
      V₂: Volume of NaOH solution = ${escapeHtml(form.calc_std_v2.value || '')}<br><br>
      Formula: N₁V₁ = N₂V₂<br><br>
      N₂ = (N₁V₁) / V₂ = ${escapeHtml(form.calc_std_n2_calc.value || '')}<br><br>
      Normality of NaOH solution (N₂) is ${escapeHtml(form.calc_std_n2_final.value || '')} N.
    </div>

    <h3>Procedure - Part B: Estimation of CH₃COOH</h3>
    <p>${formatTextWithBreaks(form.procedure_b.value || '')}</p>

    <table border="1" cellspacing="0" cellpadding="5" style="width:100%; border-collapse: collapse;">
      <tr><th>S.No</th><th>Volume of NaOH Added (ml)</th><th>Observed Conductance (µS/cm)</th><th>Corrected Conductance<br>G = ((u + v)/u)c</th></tr>
      ${generateTableRowsForPreview()}
    </table>

    <div style="margin: 25px 0; text-align: center;">
      <h3>Conductometric Titration Curve</h3>
      ${graphDataURL ? `<img src="${graphDataURL}" alt="Conductometric Titration Curve" style="max-width: 100%; height: auto; border: 1px solid #000;" />` : '<p><em>No graph data available - Enter data in the table to generate the graph</em></p>'}
      ${document.getElementById('calc_est_v1').value ? `<div style="margin-top: 15px; padding: 10px; background: #f0f0f0; border: 1px solid #000; border-radius: 0;">
        <b>Equivalence Point:</b> ${document.getElementById('calc_est_v1').value} ml of NaOH
      </div>` : ''}
    </div>

    <h3>Calculations :</h3>
    <div>
      N₂: Normality of NaOH solution = ${escapeHtml(form.calc_est_n1.value || '')} N<br><br>
      N₃: Normality of CH₃COOH solution = ${escapeHtml(form.calc_est_n2.value || '')}<br><br>
      V₂: Volume of NaOH solution at endpoint = ${escapeHtml(form.calc_est_v1.value || '')} ml<br><br>
      V₃: Volume of CH₃COOH solution = ${escapeHtml(form.calc_est_v2.value || '25')} ml<br><br>
      Formula: N₂V₂ = N₃V₃<br><br>
      N₃ = (N₂V₂) / V₃ = ${escapeHtml(form.calc_est_n3_calc.value || '')}<br><br>
      Normality of CH₃COOH solution (N₃) is ${escapeHtml(form.calc_est_n3_final.value || '')} N<br><br>
      Amount of CH₃COOH present in given solution is ${escapeHtml(form.calc_est_amount?.value || '')}<br><br>
      Q = (E × N₃ × Volume in ml) / 1000 = ${escapeHtml(form.calc_est_q.value || '')} g/1000ml<br><br>
      Where:<br><br>
      E = Gram equivalent weight of CH₃COOH = ${escapeHtml(form.calc_est_e.value || '60')} grams<br><br>
      N = Normality of CH₃COOH solution (N₃) = ${escapeHtml(form.calc_est_n_val.value || '')} N<br><br>
      V = Volume of CH₃COOH solution = ${escapeHtml(form.calc_est_v_val.value || '25')} ml
    </div>

    <h3>Result :</h3><p>${formatTextWithBreaks(form.result.value || '')}</p>`;

      const win = window.open('', '_blank', 'width=900,height=800');
      win.document.write('<!DOCTYPE html><html><head><title>Preview</title><meta charset="utf-8"></head><body style="font-family:Arial,sans-serif; padding:20px;">');
      win.document.write(previewHtml);
      win.document.write('</body></html>');
      win.document.close();
    }

    // ---------- Confirmation Dialog ----------
    async function confirmSubmit() {
        return new Promise((resolve) => {
            const confirmed = confirm("Do you really want to submit this experiment?\nPlease review all your answers before submitting.\nClick OK to submit ");
            resolve(confirmed);
        });
    }

    // ---------- Submit Experiment Function ----------
    async function submitExperiment() {
      console.log('Submit button clicked - starting submission');
      
      // Show confirmation dialog
      const shouldSubmit = await confirmSubmit();
      if (!shouldSubmit) {
          console.log('Submission cancelled by user');
          return;
      }
      
      const form = document.getElementById('exp6-form');
      if (!form) {
          alert('Error: Form not found!');
          return;
      }

      const subject = 'Chemistry';
      const experiment_number = 6;

      // Get retake parameters if this is a retake
      const urlParams = new URLSearchParams(window.location.search);
      const retakeId = urlParams.get('retake_id');
      const isRetake = urlParams.get('is_retake');
      const retakeCount = urlParams.get('retake_count') || 0;


      // Validation
      if (!form.aim.value.trim() || !form.chemicals.value.trim() || 
          !form.principle.value.trim() || !form.result.value.trim() ||
          !form.procedure_a.value.trim() || !form.procedure_b.value.trim()) {
        alert("Please fill all required fields.");
        return;
      }

      const apparatusList = Array.from(document.querySelectorAll("#apparatus-dropbox .tool-item"))
        .map(el => el.textContent.trim());

      if (apparatusList.length === 0) {
        alert("Please add at least one apparatus.");
        return;
      }

      const submissionHtml = `
    <style>
      .header-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        font-weight: 400;
        font-size: 1rem;
        color: #000000;
      }
      table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
      }
      table, th, td {
        border: 1px solid #000;
      }
      th, td {
        padding: 8px 10px;
        text-align: center;
      }
    </style>
    <div class="header-row">
      <div><b>Experiment No.:</b> 6</div>
      <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
    </div>
    <h2 style="text-align:center; margin-top: 0;">DETERMINATION OF STRENGTH OF AN ACID (ACETIC ACID) BY CONDUCTOMETRIC TITRATION</h2>

    <p><b>Aim:</b> ${formatTextWithBreaks(form.aim.value || '')}</p>
    <p><b>Apparatus Used:</b> ${apparatusList.length ? escapeHtml(apparatusList.join(", ")) : '—'}</p>
    <p><b>Chemicals Required:</b> ${formatTextWithBreaks(form.chemicals.value || '')}</p>
    <p><b>Principle:</b> ${formatTextWithBreaks(form.principle.value || '')}</p>

    <h3>Procedure:</h3>
    <p><b>Part A: Standardization of NaOH Solution</b></p>
    <p>${formatTextWithBreaks(form.procedure_a.value || '')}</p>

    <h3>Part B: Determination of Strength of Acetic Acid</h3>
    <p>${formatTextWithBreaks(form.procedure_b.value || '')}</p>

    <h3>Result:</h3>
    <p>${formatTextWithBreaks(form.result.value || '')}</p>

    <h3>Calculations:</h3>
    <div>
      N₁: Normality of NaOH = ${escapeHtml(form.calc_est_n1.value || '')} N<br><br>
      N₂: Normality of Acetic Acid = ${escapeHtml(form.calc_est_n2.value || '')}<br><br>
      V₁: Volume of NaOH = ${escapeHtml(form.calc_est_v1.value || '')} ml<br><br>
      V₂: Volume of Acetic Acid = ${escapeHtml(form.calc_est_v2.value || '')} ml<br><br>
      Formula: N₁V₁ = N₂V₂<br><br>
      N₂ = (N₁V₁) / V₂ = ${escapeHtml(form.calc_est_n3_calc.value || '')}<br><br>
      E = Gram equivalent weight of CH₃COOH = ${escapeHtml(form.calc_est_e.value || '60')} grams<br><br>
      Q = (E × N × V) / 1000 = ${escapeHtml(form.calc_est_q.value || '')} grams
    </div>
      `;

      // Prepare POST data
      const postData = new URLSearchParams();
      postData.append('subject', subject);
      postData.append('experiment_number', experiment_number);
      postData.append('submission_data', submissionHtml);

      // Add retake parameters
      if (isRetake === '1' && retakeId) {
          postData.append('is_retake', '1');
          postData.append('retake_id', retakeId);
          postData.append('retake_count', retakeCount);
          console.log('Submitting retake:', { retakeId, retakeCount });
      }

      // Show loading state
      const submitBtn = document.querySelector('button[onclick="submitExperiment()"]');
      const originalText = submitBtn.textContent;
      submitBtn.textContent = 'Submitting...';
      submitBtn.disabled = true;
      
      console.log('Sending fetch request...');
      
      fetch('../../submit_experiment.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: postData.toString()
      })
      .then(res => {
          console.log('Response received, status:', res.status);
          if (!res.ok) {
              throw new Error('Network response was not ok: ' + res.status);
          }
          return res.json();
      })
      .then(data => {
          console.log('Response data:', data);
          
          // Reset button
          submitBtn.textContent = originalText;
          submitBtn.disabled = false;
          
          if (data.success) {
              alert('✅ ' + data.message);
              window.location.href = '../updated_exp.php';
          } else {
              alert('❌ ' + (data.message || 'Submission failed'));
          }
      })
      .catch(error => {
          console.error('Fetch error:', error);
          submitBtn.textContent = originalText;
          submitBtn.disabled = false;
          alert('Error submitting experiment. Please try again.');
      });
    }

    // ---------- DOM Content Loaded ----------
    document.addEventListener('DOMContentLoaded', function() {
      // Initially hide the canvas and show placeholder
      document.getElementById('titrationChart').style.display = 'none';
      document.getElementById('graph-placeholder').style.display = 'block';
      
      // Add event listeners for automatic calculations
      document.getElementById('calc_est_n1').addEventListener('input', calculateNormality);
      document.getElementById('calc_est_v1').addEventListener('input', calculateNormality);
      document.getElementById('calc_est_v2').addEventListener('input', calculateNormality);

      // Drag & Drop setup
      const tools = document.querySelectorAll('.apparatus-btn');
      tools.forEach(tool => {
        tool.setAttribute('draggable', 'true');
        
        tool.addEventListener('dragstart', (e) => {
          const name = tool.textContent.trim();
          e.dataTransfer.setData('text/plain', name);
          e.dataTransfer.effectAllowed = 'copy';
        });

        tool.addEventListener('keydown', (e) => {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            addApparatusToDropbox(tool.textContent.trim());
          }
        });

        tool.addEventListener('click', () => {
          addApparatusToDropbox(tool.textContent.trim());
        });
      });

      const dropZone = document.getElementById('apparatus-dropbox');
      dropZone.addEventListener('dragover', (e) => { 
        e.preventDefault(); 
        e.dataTransfer.dropEffect = 'copy'; 
        dropZone.style.borderColor = '#3460d1';
        dropZone.style.backgroundColor = '#e8edff';
      });
      
      dropZone.addEventListener('dragenter', (e) => { 
        e.preventDefault(); 
        dropZone.style.borderColor = '#3460d1';
        dropZone.style.backgroundColor = '#e8edff';
      });
      
      dropZone.addEventListener('dragleave', (e) => { 
        dropZone.style.borderColor = '#ccd6ec';
        dropZone.style.backgroundColor = '#f8fafd';
      });
      
      dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#ccd6ec';
        dropZone.style.backgroundColor = '#f8fafd';
        const data = e.dataTransfer.getData('text/plain');
        if (!data) return;
        addApparatusToDropbox(data);
      });

      // Form event handling
      const form = document.getElementById('exp6-form');
      
      form.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
          if (e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
            return false;
          }
        }
      });
      
      
    });
  </script>
</body>
</html>