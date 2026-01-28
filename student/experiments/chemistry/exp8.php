<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Experiment 8: Estimation of Iron Using Potentiometry</title>
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
    <form id="exp8-form" method="post" class="form-section">

      <!-- Experiment Header -->
      <div class="exp-header">
        <div style="display:flex;flex-direction:column;">
          <label for="experiment_id">Experiment No.8</label>
          <input type="hidden" id="subject" name="subject" value="chemistry">
    <input type="hidden" id="experiment_number" name="experiment_number" value="8">
                </div>
        <div style="display:flex;flex-direction:column;">
          <label for="expDate">Date</label>
          <input type="date" id="expDate" name="expDate" />
        </div>
      </div>

      <h2 style="font-size: 30px;">Estimation of Iron Using Potentiometry</h2>

      <label for="aim">Aim</label>
      <textarea id="aim" name="aim" rows="3" placeholder="Enter experiment aim"></textarea>

      <label>Apparatus Used (Drag and Drop)</label>
      <div id="apparatus-dropbox" class="apparatus-dropbox" aria-label="Apparatus dropbox">
        <small id="apparatus-placeholder" style="color:#777;">Drag apparatus here</small>
      </div>
      <input type="hidden" id="apparatus_list" name="apparatus_list" value="">

      <label for="chemicals">Chemicals Required</label>
      <textarea id="chemicals" name="chemicals" rows="3" placeholder="List chemicals"></textarea>

      <label for="principle">Principle</label>
      <textarea id="principle" name="principle" rows="3" placeholder="Explain principle"></textarea>

      <label for="cell_setup" style="font-size: small;">The Cell Set up</label>
      <textarea id="cell_setup" name="cell_setup" rows="3" placeholder="Set your cell here"></textarea>

      <label for="chemical_reaction" style="font-size: small;">The Chemical reaction</label>
      <textarea id="chemical_reaction" name="chemical_reaction" rows="3" placeholder="Explain chemical reaction"></textarea>
      
      <label for="ionic_form" style="font-size: small;">Ionic Form</label>
      <textarea id="ionic_form" name="ionic_form" rows="3" placeholder="Explain ionic form"></textarea>

      <h3>Procedure - Part A: Preparation of Standard K<sub>2</sub>Cr<sub>2</sub>O<sub>7</sub> Solution</h3>
      <textarea id="procedure_a" name="procedure_a" rows="4" placeholder="Enter Procedure Part A"></textarea>
      
      <h4>Preparation of Standard K<sub>2</sub>Cr<sub>2</sub>O<sub>7</sub> Solution</h4>
      <div class="calc-row">
        <label for="calc_std_n1">W₁ = Weight of weighing bottle + substance = </label>
        <input type="text" id="calc_std_n1" name="calc_std_n1" /><p><b>grams</b></p>
      </div>
      <div class="calc-row">
        <label for="calc_std_n2">W₂ = Weight of empty weighing bottle = </label>
        <input type="text" id="calc_std_n2" name="calc_std_n2" /><p><b>grams</b></p>
      </div>
      <div class="calc-row">
        <label for="calc_std_v1">W₁ - W₂ = Weight of substance transferred =</label>
        <input type="text" id="calc_std_v1" name="calc_std_v1" /><p><b>grams</b></p>
      </div>
      <h4><b>Formula:</b></h4>
      <div class="calc-row">
        <label>Normality of K<sub>2</sub>Cr<sub>2</sub>O<sub>7</sub> = Weight of substance in grams / (Molecular weight × Volume in liters)</label>
      </div>
      <div class="calc-row">
        <label for="calc_std_v2">W₁ - W₂ / (49.03 × 0.25) =</label>
        <input type="text" id="calc_std_v2" name="calc_std_v2" /><p><b>N</b></p>
      </div>

      <h3>Procedure - Part B: Estimation of Ferrous ion by Potentiometry</h3>
      <textarea id="procedure_b" name="procedure_b" rows="4" placeholder="Enter Procedure Part B"></textarea>
      <div style="text-align:center; margin-top:20px;">
        <h4 style="display:inline-block; border:2px solid black; padding:10px; margin:0; border-radius: 4px;">
          Model Graph
        </h4>
      </div>
      <div style="text-align:center; margin-top:10px;">
        <img src="../../../images/exp8.jpg" alt="Graph Image" style="max-width:100%; height:auto; border:1px solid #ccc; padding:5px;" />
      </div>

      <!-- Potentiometric Titration Table -->
      <h4>Potentiometric Titration Data</h4>
      <table>
        <tr>
          <th>S.No</th>
          <th>Volume of K<sub>2</sub>Cr<sub>2</sub>O<sub>7</sub> (ml)<br>(V)</th>
          <th>E.M.F of the Cell (mv)<br>(E)</th>
          <th>ΔV</th>
          <th>ΔE</th>
          <th>ΔE/ΔV</th>
        </tr>
        <tr><td>1</td><td><input type="text" name="v1" /></td><td><input type="text" name="e1" /></td><td><input type="text" name="dv1" /></td><td><input type="text" name="de1" /></td><td><input type="text" name="dedv1" onchange="updateGraphs()" /></td></tr>
        <tr><td>2</td><td><input type="text" name="v2" /></td><td><input type="text" name="e2" /></td><td><input type="text" name="dv2" /></td><td><input type="text" name="de2" /></td><td><input type="text" name="dedv2" onchange="updateGraphs()" /></td></tr>
        <tr><td>3</td><td><input type="text" name="v3" /></td><td><input type="text" name="e3" /></td><td><input type="text" name="dv3" /></td><td><input type="text" name="de3" /></td><td><input type="text" name="dedv3" onchange="updateGraphs()" /></td></tr>
        <tr><td>4</td><td><input type="text" name="v4" /></td><td><input type="text" name="e4" /></td><td><input type="text" name="dv4" /></td><td><input type="text" name="de4" /></td><td><input type="text" name="dedv4" onchange="updateGraphs()" /></td></tr>
        <tr><td>5</td><td><input type="text" name="v5" /></td><td><input type="text" name="e5" /></td><td><input type="text" name="dv5" /></td><td><input type="text" name="de5" /></td><td><input type="text" name="dedv5" onchange="updateGraphs()" /></td></tr>
        <tr><td>6</td><td><input type="text" name="v6" /></td><td><input type="text" name="e6" /></td><td><input type="text" name="dv6" /></td><td><input type="text" name="de6" /></td><td><input type="text" name="dedv6" onchange="updateGraphs()" /></td></tr>
        <tr><td>7</td><td><input type="text" name="v7" /></td><td><input type="text" name="e7" /></td><td><input type="text" name="dv7" /></td><td><input type="text" name="de7" /></td><td><input type="text" name="dedv7" onchange="updateGraphs()" /></td></tr>
        <tr><td>8</td><td><input type="text" name="v8" /></td><td><input type="text" name="e8" /></td><td><input type="text" name="dv8" /></td><td><input type="text" name="de8" /></td><td><input type="text" name="dedv8" onchange="updateGraphs()" /></td></tr>
        <tr><td>9</td><td><input type="text" name="v9" /></td><td><input type="text" name="e9" /></td><td><input type="text" name="dv9" /></td><td><input type="text" name="de9" /></td><td><input type="text" name="dedv9" onchange="updateGraphs()" /></td></tr>
        <tr><td>10</td><td><input type="text" name="v10" /></td><td><input type="text" name="e10" /></td><td><input type="text" name="dv10" /></td><td><input type="text" name="de10" /></td><td><input type="text" name="dedv10" onchange="updateGraphs()" /></td></tr>
        <tr><td>11</td><td><input type="text" name="v11" /></td><td><input type="text" name="e11" /></td><td><input type="text" name="dv11" /></td><td><input type="text" name="de11" /></td><td><input type="text" name="dedv11" onchange="updateGraphs()" /></td></tr>
        <tr><td>12</td><td><input type="text" name="v12" /></td><td><input type="text" name="e12" /></td><td><input type="text" name="dv12" /></td><td><input type="text" name="de12" /></td><td><input type="text" name="dedv12" onchange="updateGraphs()" /></td></tr>
        <tr><td>13</td><td><input type="text" name="v13" /></td><td><input type="text" name="e13" /></td><td><input type="text" name="dv13" /></td><td><input type="text" name="de13" /></td><td><input type="text" name="dedv13" onchange="updateGraphs()" /></td></tr>
        <tr><td>14</td><td><input type="text" name="v14" /></td><td><input type="text" name="e14" /></td><td><input type="text" name="dv14" /></td><td><input type="text" name="de14" /></td><td><input type="text" name="dedv14" onchange="updateGraphs()" /></td></tr>
        <tr><td>15</td><td><input type="text" name="v15" /></td><td><input type="text" name="e15" /></td><td><input type="text" name="dv15" /></td><td><input type="text" name="de15" /></td><td><input type="text" name="dedv15" onchange="updateGraphs()" /></td></tr>
        <tr><td>16</td><td><input type="text" name="v16" /></td><td><input type="text" name="e16" /></td><td><input type="text" name="dv16" /></td><td><input type="text" name="de16" /></td><td><input type="text" name="dedv16" onchange="updateGraphs()" /></td></tr>
        <tr><td>17</td><td><input type="text" name="v17" /></td><td><input type="text" name="e17" /></td><td><input type="text" name="dv17" /></td><td><input type="text" name="de17" /></td><td><input type="text" name="dedv17" onchange="updateGraphs()" /></td></tr>
        <tr><td>18</td><td><input type="text" name="v18" /></td><td><input type="text" name="e18" /></td><td><input type="text" name="dv18" /></td><td><input type="text" name="de18" /></td><td><input type="text" name="dedv18" onchange="updateGraphs()" /></td></tr>
        <tr><td>19</td><td><input type="text" name="v19" /></td><td><input type="text" name="e19" /></td><td><input type="text" name="dv19" /></td><td><input type="text" name="de19" /></td><td><input type="text" name="dedv19" onchange="updateGraphs()" /></td></tr>
        <tr><td>20</td><td><input type="text" name="v20" /></td><td><input type="text" name="e20" /></td><td><input type="text" name="dv20" /></td><td><input type="text" name="de20" /></td><td><input type="text" name="dedv20" onchange="updateGraphs()" /></td></tr>
      </table>

      <!-- Graph Section -->
      <div class="graph-section">
        <h3>Potentiometric Titration Curves</h3>
        
        <!-- Graph 1: Titration Curve -->
        <div style="width: 100%; max-width: 800px; margin: 20px auto;">
          <h4>Titration Curve: E.M.F vs Volume of K₂Cr₂O₇</h4>
          <canvas id="titrationChart" width="800" height="400"></canvas>
        </div>
        
        <!-- Graph 2: First Derivative Curve -->
        <div style="width: 100%; max-width: 800px; margin: 20px auto;">
          <h4>First Derivative Curve: ΔE/ΔV vs Volume of K₂Cr₂O₇</h4>
          <canvas id="derivativeChart" width="800" height="400"></canvas>
        </div>
        
        <div id="graph-placeholder" style="text-align: center; padding: 40px; color: #666; background: #f9f9f9; border-radius: 5px;">
          <p><strong>Graphs will appear here when you enter data in the table above</strong></p>
          <p>Enter Volume and E.M.F values for the titration curve, and ΔE/ΔV values for the derivative curve</p>
        </div>
        
        <div id="endpoint-info" style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px; display: none;">
          <strong>Equivalence Point:</strong> 
          <span id="endpoint-volume">0</span> ml of K₂Cr₂O₇
        </div>
      </div>

      <h3>Calculations</h3>
      <div class="calculation-box">
        <div class="calc-row">
          <label for="calc_est_n1">N₁ - Normality of K₂Cr₂O₇ solution</label>
          <input type="text" id="calc_est_n1" name="calc_est_n1" /><p><b>N</b></p>
        </div>
        <div class="calc-row">
          <label for="calc_est_n2">N₂ - Normality of Ferrous Ion solution</label>
          <input type="text" id="calc_est_n2" name="calc_est_n2" />
        </div>
        <div class="calc-row">
          <label for="calc_est_v1">V₁ - Volume of K₂Cr₂O₇ solution (From Graph)</label>
          <input type="text" id="calc_est_v1" name="calc_est_v1" /><p><b>ml</b></p>
        </div>
        <div class="calc-row">
          <label for="calc_est_v2">V₂ - Volume of Ferrous Ion solution</label>
          <input type="text" id="calc_est_v2" name="calc_est_v2" /><p><b>ml</b></p>
        </div>

        <div class="formula-text">Formula: N₁V₁ = N₂V₂</div>

        <div class="calc-row">
          <label for="calc_est_n3_calc">N₂ = (N₁V₁) / V₂ = </label>
          <input type="text" id="calc_est_n3_calc" name="calc_est_n3_calc" />
        </div>
        <div class="result-text">Normality of Ferrous ion solution (N₂) is <input type="text" id="calc_est_n3_final" name="calc_est_n3_final" style="width: 110px;" /></div>

        <div class="calc-row">
          <label for="calc_est_amount">Amount of Ferrous Ion present in the given 100ml solution = </label>
          <input type="text" id="calc_est_amount" name="calc_est_amount" />
        </div>

        <div class="calc-row">
          <label for="calc_est_q">Q = (E × N₃ × Volume in ml) / 1000 = </label>
          <input type="text" id="calc_est_q" name="calc_est_q" /><p>g/100ml</p>
        </div>

        <div class="result-text" style="display:block;">
          Where: E = Gram equivalent weight of Ferrous ion = <input type="text" id="calc_est_e" name="calc_est_e" readonly value="55.85" style="width: 80px;" /> grams<br />
          N = Normality of Ferrous ion solution (N₃) = <input type="text" id="calc_est_n_val" name="calc_est_n_val" style="width: 100px;" /><br />
          V = Volume of Ferrous ion solution = <input type="text" id="calc_est_v_val" name="calc_est_v_val" style="width: 100px;" />
        </div>
      </div>

      <label for="result">Result</label>
      <textarea id="result" name="result" rows="3" placeholder="Write final conclusion"></textarea>

      <div class="btn-group">
       
        
        <button type="button" onclick="previewExp()" style="cursor:pointer; background:#007bff; color:#fff; font-weight:600; padding:8px 16px; border-radius:6px; width: fit-content;">Preview</button>
        <button type="button" onclick="submitExperiment()" style="cursor:pointer; background:#1a347a; color:#fff; font-weight:600; padding:8px 16px; border-radius:6px; width: fit-content;">Submit</button>
            </div>
    </form>

    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="apparatus-box">
        <h3 style="font-size:22px;margin-bottom:12px;text-align:center;">Apparatus Select</h3>
        <div class="apparatus-list">
          <button type="button" class="apparatus-btn">250ml Beaker</button>
          <button type="button" class="apparatus-btn">Burette</button>
          <button type="button" class="apparatus-btn">20ml Pipette</button>
          <button type="button" class="apparatus-btn">Standard Flask</button>
          <button type="button" class="apparatus-btn">Micro Burette</button>
          <button type="button" class="apparatus-btn">Test Tube</button>
          <button type="button" class="apparatus-btn">Volumetric Flask</button>
          <button type="button" class="apparatus-btn">Potentiometer</button>
          <button type="button" class="apparatus-btn">Calomel Electrode</button>
          <button type="button" class="apparatus-btn">Platinum Electrode</button>
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
    let titrationChart = null;
    let derivativeChart = null;

    
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
    function updateGraphs() {
      const volumes = [];
      const emfValues = [];
      const dedvValues = [];
      
      // Collect data from table - only use manually entered values
      for (let i = 1; i <= 20; i++) {
        const volumeInput = document.querySelector(`input[name="v${i}"]`);
        const emfInput = document.querySelector(`input[name="e${i}"]`);
        const dedvInput = document.querySelector(`input[name="dedv${i}"]`);
        
        // For titration curve: use Volume and EMF
        if (volumeInput && volumeInput.value && emfInput && emfInput.value) {
          const volume = parseFloat(volumeInput.value);
          const emf = parseFloat(emfInput.value);
          
          volumes.push(volume);
          emfValues.push(emf);
        }
        
        // For derivative curve: use Volume and manually entered ΔE/ΔV
        if (volumeInput && volumeInput.value && dedvInput && dedvInput.value) {
          const volume = parseFloat(volumeInput.value);
          const dedv = parseFloat(dedvInput.value);
          
          // We need to match volumes with dedv values, so we'll push to separate arrays
          // and ensure they align properly
          if (i <= volumes.length) {
            dedvValues.push(dedv);
          }
        }
      }
      
      // Only generate graphs if we have at least 2 data points
      if (volumes.length >= 2) {
        generateTitrationCurve(volumes, emfValues);
        
        // For derivative curve, we need volumes that match the dedv values
        const derivativeVolumes = volumes.slice(0, dedvValues.length);
        if (derivativeVolumes.length >= 2 && dedvValues.length >= 2) {
          generateDerivativeCurve(derivativeVolumes, dedvValues);
        }
      } else {
        // Hide graphs and show placeholder if not enough data
        const canvas1 = document.getElementById('titrationChart');
        const canvas2 = document.getElementById('derivativeChart');
        const placeholder = document.getElementById('graph-placeholder');
        const endpointInfo = document.getElementById('endpoint-info');
        
        if (titrationChart) {
          titrationChart.destroy();
          titrationChart = null;
        }
        if (derivativeChart) {
          derivativeChart.destroy();
          derivativeChart = null;
        }
        
        canvas1.style.display = 'none';
        canvas2.style.display = 'none';
        placeholder.style.display = 'block';
        endpointInfo.style.display = 'none';
      }
    }

    function generateTitrationCurve(volumes, emfValues) {
      const ctx = document.getElementById('titrationChart').getContext('2d');
      const placeholder = document.getElementById('graph-placeholder');
      
      // Show canvas and hide placeholder
      document.getElementById('titrationChart').style.display = 'block';
      placeholder.style.display = 'none';
      
      // Destroy existing chart
      if (titrationChart) {
        titrationChart.destroy();
      }
      
      titrationChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: volumes,
          datasets: [{
            label: 'E.M.F (mV)',
            data: emfValues,
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
              text: 'Potentiometric Titration Curve: E.M.F vs Volume of K₂Cr₂O₇',
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
                text: 'Volume of K₂Cr₂O₇ (ml)',
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
                text: 'E.M.F (mV)',
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
          }
        }
      });
    }

    function generateDerivativeCurve(volumes, dedvValues) {
      const ctx = document.getElementById('derivativeChart').getContext('2d');
      
      // Show canvas
      document.getElementById('derivativeChart').style.display = 'block';
      
      // Destroy existing chart
      if (derivativeChart) {
        derivativeChart.destroy();
      }
      
      // Find equivalence point (maximum of first derivative)
      let maxDedv = Math.max(...dedvValues);
      let maxIndex = dedvValues.indexOf(maxDedv);
      let equivalencePoint = volumes[maxIndex];
      
      derivativeChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: volumes,
          datasets: [{
            label: 'ΔE/ΔV',
            data: dedvValues,
            borderColor: '#ff0000',
            backgroundColor: 'rgba(255, 0, 0, 0.1)',
            borderWidth: 2,
            fill: false,
            tension: 0.4,
            pointBackgroundColor: '#ff0000',
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
              text: 'First Derivative Curve: ΔE/ΔV vs Volume of K₂Cr₂O₇',
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
            },
            annotation: {
              annotations: {
                line1: {
                  type: 'line',
                  mode: 'vertical',
                  scaleID: 'x',
                  value: equivalencePoint,
                  borderColor: '#0000ff',
                  borderWidth: 2,
                  borderDash: [5, 5],
                  label: {
                    content: 'Equivalence Point',
                    enabled: true,
                    position: 'top'
                  }
                }
              }
            }
          },
          scales: {
            x: {
              title: {
                display: true,
                text: 'Volume of K₂Cr₂O₇ (ml)',
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
                text: 'ΔE/ΔV (mV/ml)',
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
          }
        }
      });
      
      // Update endpoint information
      updateEndpointInfo(equivalencePoint);
    }

    function updateEndpointInfo(equivalencePoint) {
      const endpointInfo = document.getElementById('endpoint-info');
      const endpointVolume = document.getElementById('endpoint-volume');
      
      if (equivalencePoint > 0) {
        endpointVolume.textContent = equivalencePoint.toFixed(2);
        endpointInfo.style.display = 'block';
        // Update calculation field
        document.getElementById('calc_est_v1').value = equivalencePoint.toFixed(2);
      } else {
        endpointInfo.style.display = 'none';
      }
    }

    // ---------- Print Style Graph Functions ----------
    function createPrintStyleGraphs(volumes, emfValues, dedvValues, width = 600, height = 400) {
      const canvas1 = document.createElement('canvas');
      canvas1.width = width;
      canvas1.height = height;
      const ctx1 = canvas1.getContext('2d');
      
      // Draw titration curve
      drawPrintGraph(ctx1, width, height, volumes, emfValues, 
        'Potentiometric Titration Curve: E.M.F vs Volume of K₂Cr₂O₇',
        'Volume of K₂Cr₂O₇ (ml)', 'E.M.F (mV)', '#000000');
      
      const titrationGraphURL = canvas1.toDataURL('image/png');
      
      // Draw derivative curve
      const canvas2 = document.createElement('canvas');
      canvas2.width = width;
      canvas2.height = height;
      const ctx2 = canvas2.getContext('2d');
      
      drawPrintGraph(ctx2, width, height, volumes, dedvValues,
        'First Derivative Curve: ΔE/ΔV vs Volume of K₂Cr₂O₇',
        'Volume of K₂Cr₂O₇ (ml)', 'ΔE/ΔV (mV/ml)', '#ff0000');
      
      const derivativeGraphURL = canvas2.toDataURL('image/png');
      
      return { titrationGraphURL, derivativeGraphURL };
    }

    function drawPrintGraph(ctx, width, height, xValues, yValues, title, xLabel, yLabel, color) {
      // Set white background
      ctx.fillStyle = '#ffffff';
      ctx.fillRect(0, 0, width, height);
      
      // Chart dimensions with margins
      const margin = { top: 60, right: 40, bottom: 60, left: 60 };
      const chartWidth = width - margin.left - margin.right;
      const chartHeight = height - margin.top - margin.bottom;
      
      // Find min and max values
      const minX = Math.min(...xValues);
      const maxX = Math.max(...xValues);
      const minY = Math.min(...yValues);
      const maxY = Math.max(...yValues);
      
      // Scales
      const xScale = (val) => margin.left + ((val - minX) / (maxX - minX)) * chartWidth;
      const yScale = (val) => margin.top + chartHeight - ((val - minY) / (maxY - minY)) * chartHeight;
      
      // Draw grid lines
      ctx.strokeStyle = '#e0e0e0';
      ctx.lineWidth = 1;
      
      // Vertical grid lines
      const xSteps = 5;
      for (let i = 0; i <= xSteps; i++) {
        const xVal = minX + (i / xSteps) * (maxX - minX);
        const x = xScale(xVal);
        ctx.beginPath();
        ctx.moveTo(x, margin.top);
        ctx.lineTo(x, margin.top + chartHeight);
        ctx.stroke();
      }
      
      // Horizontal grid lines
      const ySteps = 5;
      for (let i = 0; i <= ySteps; i++) {
        const yVal = minY + (i / ySteps) * (maxY - minY);
        const y = yScale(yVal);
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
      ctx.strokeStyle = color;
      ctx.lineWidth = 2;
      ctx.fillStyle = color;
      
      // Draw line
      ctx.beginPath();
      for (let i = 0; i < xValues.length; i++) {
        const x = xScale(xValues[i]);
        const y = yScale(yValues[i]);
        if (i === 0) {
          ctx.moveTo(x, y);
        } else {
          ctx.lineTo(x, y);
        }
      }
      ctx.stroke();
      
      // Draw points
      for (let i = 0; i < xValues.length; i++) {
        const x = xScale(xValues[i]);
        const y = yScale(yValues[i]);
        ctx.beginPath();
        ctx.arc(x, y, 3, 0, 2 * Math.PI);
        ctx.fill();
      }
      
      // Draw title
      ctx.fillStyle = '#000000';
      ctx.font = 'bold 16px Arial';
      ctx.textAlign = 'center';
      ctx.fillText(title, width / 2, 30);
      
      // Draw X-axis label
      ctx.font = 'bold 12px Arial';
      ctx.fillText(xLabel, width / 2, height - 15);
      
      // Draw Y-axis label
      ctx.save();
      ctx.translate(20, height / 2);
      ctx.rotate(-Math.PI / 2);
      ctx.fillText(yLabel, 0, 0);
      ctx.restore();
      
      // Draw X-axis ticks and labels
      ctx.font = '11px Arial';
      ctx.textAlign = 'center';
      for (let i = 0; i <= xSteps; i++) {
        const xVal = minX + (i / xSteps) * (maxX - minX);
        const x = xScale(xVal);
        ctx.beginPath();
        ctx.moveTo(x, margin.top + chartHeight);
        ctx.lineTo(x, margin.top + chartHeight + 5);
        ctx.stroke();
        ctx.fillText(xVal.toFixed(1), x, margin.top + chartHeight + 20);
      }
      
      // Draw Y-axis ticks and labels
      ctx.textAlign = 'right';
      for (let i = 0; i <= ySteps; i++) {
        const yVal = minY + (i / ySteps) * (maxY - minY);
        const y = yScale(yVal);
        ctx.beginPath();
        ctx.moveTo(margin.left, y);
        ctx.lineTo(margin.left - 5, y);
        ctx.stroke();
        ctx.fillText(yVal.toFixed(0), margin.left - 10, y + 4);
      }
    }

    // ---------- Calculation Functions ----------
    function calculateNormality() {
      const N1 = parseFloat(document.getElementById('calc_est_n1').value) || 0;
      const V1 = parseFloat(document.getElementById('calc_est_v1').value) || 0;
      const V2 = parseFloat(document.getElementById('calc_est_v2').value) || 25;
      
      if (N1 > 0 && V1 > 0 && V2 > 0) {
        const N2 = (N1 * V1) / V2;
        document.getElementById('calc_est_n3_calc').value = N2.toFixed(4);
        document.getElementById('calc_est_n3_final').value = N2.toFixed(4);
        document.getElementById('calc_est_n_val').value = N2.toFixed(4);
        
        // Calculate amount
        const E = 55.85; // Gram equivalent weight of Ferrous ion
        const Q = (E * N2 * V2) / 1000;
        document.getElementById('calc_est_q').value = Q.toFixed(4);
        document.getElementById('calc_est_amount').value = Q.toFixed(4) + ' g/100ml';
      }
    }

    // ---------- Drag & Drop Functions ----------
    function updateApparatusList() {
      const apparatusItems = Array.from(document.querySelectorAll("#apparatus-dropbox .tool-item"))
        .map(el => el.textContent.trim());
      document.getElementById('apparatus_list').value = apparatusItems.join(', ');
    }

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

      // Update apparatus list
      updateApparatusList();

      item.addEventListener('click', () => {
        item.remove();
        updateApparatusList();
        if (dropZone.children.length === 0 && placeholder) {
          placeholder.style.display = 'inline';
        }
      });
      
      item.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          item.remove();
          updateApparatusList();
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
        const volume = document.querySelector(`input[name="v${i}"]`)?.value || '';
        const emf = document.querySelector(`input[name="e${i}"]`)?.value || '';
        const dv = document.querySelector(`input[name="dv${i}"]`)?.value || '';
        const de = document.querySelector(`input[name="de${i}"]`)?.value || '';
        const dedv = document.querySelector(`input[name="dedv${i}"]`)?.value || '';
        
        if (volume || emf) {
          rows += `<tr>
            <td>${i}</td>
            <td>${escapeHtml(volume)}</td>
            <td>${escapeHtml(emf)}</td>
            <td>${escapeHtml(dv)}</td>
            <td>${escapeHtml(de)}</td>
            <td>${escapeHtml(dedv)}</td>
          </tr>`;
        }
      }
      return rows;
    }

    // ---------- Preview Function ----------
    function previewExp() {
      const form = document.getElementById('exp8-form');
      const apparatusList = Array.from(document.querySelectorAll("#apparatus-dropbox .tool-item"))
        .map(el => el.textContent.trim());

      // Collect graph data
      const volumes = [];
      const emfValues = [];
      const dedvValues = [];
      
      for (let i = 1; i <= 20; i++) {
        const volumeInput = document.querySelector(`input[name="v${i}"]`);
        const emfInput = document.querySelector(`input[name="e${i}"]`);
        const dedvInput = document.querySelector(`input[name="dedv${i}"]`);
        
        if (volumeInput && volumeInput.value && emfInput && emfInput.value) {
          volumes.push(parseFloat(volumeInput.value));
          emfValues.push(parseFloat(emfInput.value));
          // Use manually entered ΔE/ΔV values
          dedvValues.push(dedvInput && dedvInput.value ? parseFloat(dedvInput.value) : 0);
        }
      }

      // Create print-style graphs
      let graphDataURLs = { titrationGraphURL: '', derivativeGraphURL: '' };
      if (volumes.length >= 2) {
        graphDataURLs = createPrintStyleGraphs(volumes, emfValues, dedvValues);
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
      <div><b>Experiment No.:</b></div>
      <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
    </div>
    <h2 style="text-align:center; margin-top: 0;">Estimation of Iron Using Potentiometry</h2>

    <p><b>Aim:</b> ${formatTextWithBreaks(form.aim.value || '')}</p>
    <p><b>Apparatus Used:</b> ${apparatusList.length ? escapeHtml(apparatusList.join(", ")) : '—'}</p>
    <p><b>Chemicals Required:</b> ${formatTextWithBreaks(form.chemicals.value || '')}</p>
    <p><b>Principle:</b> ${formatTextWithBreaks(form.principle.value || '')}</p>
    <p><b>The Cell Set up:</b> ${formatTextWithBreaks(form.cell_setup.value || '')}</p>
    <p><b>The Chemical reaction:</b> ${formatTextWithBreaks(form.chemical_reaction.value || '')}</p>
    <p><b>Ionic Form:</b> ${formatTextWithBreaks(form.ionic_form.value || '')}</p>

    <h3>Procedure - Part A: Preparation of Standard K<sub>2</sub>Cr<sub>2</sub>O<sub>7</sub> Solution</h3>
    <p>${formatTextWithBreaks(form.procedure_a.value || '')}</p>

    <div>
      W₁ = Weight of weighing bottle + substance = ${escapeHtml(form.calc_std_n1.value || '')} grams<br><br>
      W₂ = Weight of empty weighing bottle = ${escapeHtml(form.calc_std_n2.value || '')} grams<br><br>
      W₁ - W₂ = Weight of substance transferred = ${escapeHtml(form.calc_std_v1.value || '')} grams<br><br>
      <b>Formula:</b> Normality of K<sub>2</sub>Cr<sub>2</sub>O<sub>7</sub> = Weight of substance in grams / (Molecular weight × Volume in liters)<br><br>
      W₁ - W₂ / (49.03 × 0.25) = ${escapeHtml(form.calc_std_v2.value || '')} N
    </div>

    <h3>Procedure - Part B: Estimation of Ferrous ion by Potentiometry</h3>
    <p>${formatTextWithBreaks(form.procedure_b.value || '')}</p>
    <div style="text-align:center; margin-top:20px;">
        <h4 style="display:inline-block; border:2px solid black; padding:10px; margin:0; border-radius: 4px;">
          Model Graph
        </h4>
      </div>
      <div style="text-align:center; margin-top:10px;">
        <img src="../../../images/exp8.jpg" alt="Graph Image" style="max-width:100%; height:auto; border:1px solid #ccc; padding:5px;" />
      </div>

    <table border="1" cellspacing="0" cellpadding="5" style="width:100%; border-collapse: collapse;">
      <tr>
        <th>S.No</th>
        <th>Volume of K<sub>2</sub>Cr<sub>2</sub>O<sub>7</sub> (ml)<br>(V)</th>
        <th>E.M.F of the Cell (mv)<br>(E)</th>
        <th>ΔV</th>
        <th>ΔE</th>
        <th>ΔE/ΔV</th>
      </tr>
      ${generateTableRowsForPreview()}
    </table>

    <div style="margin: 25px 0; text-align: center;">
      <h3>Potentiometric Titration Curves</h3>
      ${graphDataURLs.titrationGraphURL ? `
        <div style="margin-bottom: 30px;">
          <h4>Titration Curve: E.M.F vs Volume of K₂Cr₂O₇</h4>
          <img src="${graphDataURLs.titrationGraphURL}" alt="Titration Curve" style="max-width: 100%; height: auto; border: 1px solid #000;" />
        </div>
      ` : ''}
      ${graphDataURLs.derivativeGraphURL ? `
        <div>
          <h4>First Derivative Curve: ΔE/ΔV vs Volume of K₂Cr₂O₇</h4>
          <img src="${graphDataURLs.derivativeGraphURL}" alt="First Derivative Curve" style="max-width: 100%; height: auto; border: 1px solid #000;" />
        </div>
      ` : ''}
      ${!graphDataURLs.titrationGraphURL && !graphDataURLs.derivativeGraphURL ? '<p><em>No graph data available - Enter data in the table to generate the graphs</em></p>' : ''}
      ${document.getElementById('calc_est_v1').value ? `<div style="margin-top: 15px; padding: 10px; background: #f0f0f0; border: 1px solid #000; border-radius: 0;">
        <b>Equivalence Point:</b> ${document.getElementById('calc_est_v1').value} ml of K₂Cr₂O₇
      </div>` : ''}
    </div>

    <h3>Calculations :</h3>
    <div>
      N₁: Normality of K₂Cr₂O₇ solution = ${escapeHtml(form.calc_est_n1.value || '')} N<br><br>
      N₂: Normality of Ferrous Ion solution = ${escapeHtml(form.calc_est_n2.value || '')}<br><br>
      V₁: Volume of K₂Cr₂O₇ solution at endpoint = ${escapeHtml(form.calc_est_v1.value || '')} ml<br><br>
      V₂: Volume of Ferrous Ion solution = ${escapeHtml(form.calc_est_v2.value || '25')} ml<br><br>
      Formula: N₁V₁ = N₂V₂<br><br>
      N₂ = (N₁V₁) / V₂ = ${escapeHtml(form.calc_est_n3_calc.value || '')}<br><br>
      Normality of Ferrous ion solution (N₂) is ${escapeHtml(form.calc_est_n3_final.value || '')} N<br><br>
      Amount of Ferrous Ion present in the given 100ml solution = ${escapeHtml(form.calc_est_amount.value || '')}<br><br>
      Q = (E × N₃ × Volume in ml) / 1000 = ${escapeHtml(form.calc_est_q.value || '')} g/100ml<br><br>
      Where:<br><br>
      E = Gram equivalent weight of Ferrous ion = ${escapeHtml(form.calc_est_e.value || '55.85')} grams<br><br>
      N = Normality of Ferrous ion solution (N₃) = ${escapeHtml(form.calc_est_n_val.value || '')} N<br><br>
      V = Volume of Ferrous ion solution = ${escapeHtml(form.calc_est_v_val.value || '25')} ml
    </div>

    <h3>Result :</h3><p>${formatTextWithBreaks(form.result.value || '')}</p>`;

      const win = window.open('', '_blank', 'width=900,height=800');
      win.document.write('<!DOCTYPE html><html><head><title>Preview - Experiment 8</title><meta charset="utf-8"></head><body style="font-family:Arial,sans-serif; padding:20px;">');
      win.document.write(previewHtml);
      win.document.write('</body></html>');
      win.document.close();
    }

    // ---------- Submit Experiment Function ----------
    function submitExperiment() { 
        const form = document.getElementById('exp8-form');
         const subject = 'chemistry';
            const experiment_number = 8; // From your database
            const employee_id = '123';

      
      
      if (!form.aim.value.trim() || !form.chemicals.value.trim() || 
          !form.principle.value.trim() || !form.result.value.trim()) {
        alert("Please fill all required fields.");
        return;
      }

      const apparatusList = Array.from(document.querySelectorAll("#apparatus-dropbox .tool-item"))
        .map(el => el.textContent.trim());

      if (apparatusList.length === 0) {
        alert("Please add at least one apparatus.");
        return;
      }

      // Collect graph data
      const volumes = [];
      const emfValues = [];
      const dedvValues = [];
      
      for (let i = 1; i <= 20; i++) {
        const volumeInput = document.querySelector(`input[name="v${i}"]`);
        const emfInput = document.querySelector(`input[name="e${i}"]`);
        const dedvInput = document.querySelector(`input[name="dedv${i}"]`);
        
        if (volumeInput && volumeInput.value && emfInput && emfInput.value) {
          volumes.push(parseFloat(volumeInput.value));
          emfValues.push(parseFloat(emfInput.value));
          // Use manually entered ΔE/ΔV values
          dedvValues.push(dedvInput && dedvInput.value ? parseFloat(dedvInput.value) : 0);
        }
      }

      // Create print-style graphs for submission
      let graphDataURLs = { titrationGraphURL: '', derivativeGraphURL: '' };
      if (volumes.length >= 2) {
        graphDataURLs = createPrintStyleGraphs(volumes, emfValues, dedvValues);
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
      <div><b>Experiment No.:8</b> </div>
      <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
    </div>
    <h2 style="text-align:center; margin-top: 0;">Estimation of Iron Using Potentiometry</h2>

    <p><b>Aim:</b> ${formatTextWithBreaks(form.aim.value || '')}</p>
    <p><b>Apparatus Used:</b> ${apparatusList.length ? escapeHtml(apparatusList.join(", ")) : '—'}</p>
    <p><b>Chemicals Required:</b> ${formatTextWithBreaks(form.chemicals.value || '')}</p>
    <p><b>Principle:</b> ${formatTextWithBreaks(form.principle.value || '')}</p>
    <p><b>The Cell Set up:</b> ${formatTextWithBreaks(form.cell_setup.value || '')}</p>
    <p><b>The Chemical reaction:</b> ${formatTextWithBreaks(form.chemical_reaction.value || '')}</p>
    <p><b>Ionic Form:</b> ${formatTextWithBreaks(form.ionic_form.value || '')}</p>

    <h3>Procedure - Part A: Preparation of Standard K<sub>2</sub>Cr<sub>2</sub>O<sub>7</sub> Solution</h3>
    <p>${formatTextWithBreaks(form.procedure_a.value || '')}</p>

    <div>
      W₁ = Weight of weighing bottle + substance = ${escapeHtml(form.calc_std_n1.value || '')} grams<br><br>
      W₂ = Weight of empty weighing bottle = ${escapeHtml(form.calc_std_n2.value || '')} grams<br><br>
      W₁ - W₂ = Weight of substance transferred = ${escapeHtml(form.calc_std_v1.value || '')} grams<br><br>
      <b>Formula:</b> Normality of K<sub>2</sub>Cr<sub>2</sub>O<sub>7</sub> = Weight of substance in grams / (Molecular weight × Volume in liters)<br><br>
      W₁ - W₂ / (49.03 × 0.25) = ${escapeHtml(form.calc_std_v2.value || '')} N
    </div>

    <h3>Procedure - Part B: Estimation of Ferrous ion by Potentiometry</h3>
    <p>${formatTextWithBreaks(form.procedure_b.value || '')}</p>
    <div style="text-align:center; margin-top:20px;">
        <h4 style="display:inline-block; border:2px solid black; padding:10px; margin:0; border-radius: 4px;">
          Model Graph
        </h4>
      </div>
      <div style="text-align:center; margin-top:10px;">
        <img src="../../../images/exp8.jpg" alt="Graph Image" style="max-width:100%; height:auto; border:1px solid #ccc; padding:5px;" />
      </div>

    <table border="1" cellspacing="0" cellpadding="5" style="width:100%; border-collapse: collapse;">
      <tr>
        <th>S.No</th>
        <th>Volume of K<sub>2</sub>Cr<sub>2</sub>O<sub>7</sub> (ml)<br>(V)</th>
        <th>E.M.F of the Cell (mv)<br>(E)</th>
        <th>ΔV</th>
        <th>ΔE</th>
        <th>ΔE/ΔV</th>
      </tr>
      ${generateTableRowsForPreview()}
    </table>

    <div style="margin: 25px 0; text-align: center;">
      <h3>Potentiometric Titration Curves</h3>
      ${graphDataURLs.titrationGraphURL ? `
        <div style="margin-bottom: 30px;">
          <h4>Titration Curve: E.M.F vs Volume of K₂Cr₂O₇</h4>
          <img src="${graphDataURLs.titrationGraphURL}" alt="Titration Curve" style="max-width: 100%; height: auto; border: 1px solid #000;" />
        </div>
      ` : ''}
      ${graphDataURLs.derivativeGraphURL ? `
        <div>
          <h4>First Derivative Curve: ΔE/ΔV vs Volume of K₂Cr₂O₇</h4>
          <img src="${graphDataURLs.derivativeGraphURL}" alt="First Derivative Curve" style="max-width: 100%; height: auto; border: 1px solid #000;" />
        </div>
      ` : ''}
      ${!graphDataURLs.titrationGraphURL && !graphDataURLs.derivativeGraphURL ? '<p>No graph data available</p>' : ''}
      ${document.getElementById('calc_est_v1').value ? `<div style="margin-top: 15px; padding: 10px; background: #f0f0f0; border: 1px solid #000; border-radius: 0;">
        <b>Equivalence Point:</b> ${document.getElementById('calc_est_v1').value} ml of K₂Cr₂O₇
      </div>` : ''}
    </div>

    <h3>Calculations :</h3>
    <div>
      N₁: Normality of K₂Cr₂O₇ solution = ${escapeHtml(form.calc_est_n1.value || '')} N<br><br>
      N₂: Normality of Ferrous Ion solution = ${escapeHtml(form.calc_est_n2.value || '')}<br><br>
      V₁: Volume of K₂Cr₂O₇ solution at endpoint = ${escapeHtml(form.calc_est_v1.value || '')} ml<br><br>
      V₂: Volume of Ferrous Ion solution = ${escapeHtml(form.calc_est_v2.value || '25')} ml<br><br>
      Formula: N₁V₁ = N₂V₂<br><br>
      N₂ = (N₁V₁) / V₂ = ${escapeHtml(form.calc_est_n3_calc.value || '')}<br><br>
      Normality of Ferrous ion solution (N₂) is ${escapeHtml(form.calc_est_n3_final.value || '')} N<br><br>
      Amount of Ferrous Ion present in the given 100ml solution = ${escapeHtml(form.calc_est_amount.value || '')}<br><br>
      Q = (E × N₃ × Volume in ml) / 1000 = ${escapeHtml(form.calc_est_q.value || '')} g/100ml<br><br>
      Where:<br><br>
      E = Gram equivalent weight of Ferrous ion = ${escapeHtml(form.calc_est_e.value || '55.85')} grams<br><br>
      N = Normality of Ferrous ion solution (N₃) = ${escapeHtml(form.calc_est_n_val.value || '')} N<br><br>
      V = Volume of Ferrous ion solution = ${escapeHtml(form.calc_est_v_val.value || '25')} ml
    </div>

    <h3>Result :</h3><p>${formatTextWithBreaks(form.result.value || '')}</p>`;

      const postData = new URLSearchParams();
            postData.append('subject', subject);
            postData.append('experiment_number', experiment_number);
            postData.append('employee_id', employee_id);
            postData.append('submission_data', submissionHtml);

            fetch('../../../submit_experiment.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: postData.toString()
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Optional: clear form on success
                    // form.reset();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error submitting experiment. Please check console for details.');
            });
        }

    // ---------- DOM Content Loaded ----------
    document.addEventListener('DOMContentLoaded', function() {
      // Initially hide the canvases and show placeholder
      document.getElementById('titrationChart').style.display = 'none';
      document.getElementById('derivativeChart').style.display = 'none';
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
      const form = document.getElementById('exp8-form');
      
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