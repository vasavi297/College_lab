<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Experiment 9 - Verify Beer-Lambert's Law</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
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
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container">
  <form id="exp9-form" method="post"  class="form-section">
  <div class="exp-header">
    <div style="display:flex;flex-direction:column;">
      <label for="expNo">Experiment No.9</label>
      <input type="hidden" id="subject" name="subject" value="chemistry">
    <input type="hidden" id="experiment_number" name="experiment_number" value="9">
                </div>
    <div style="display:flex;flex-direction:column;">
      <label for="expDate">Date</label>
      <input type="date" id="expDate" name="expDate" required/>
    </div>
  </div>
   <h2> Verify Beer-Lambert's Law</h2>
    <label for="aim">Aim</label>
  <textarea id="aim" name="aim" rows="3" placeholder="Enter experiment aim" required></textarea>

    <!-- Apparatus Drag and Drop -->
    <label>Apparatus Used (Drag and Drop)</label>
  <div id="apparatus-dropbox" class="apparatus-dropbox" aria-label="Apparatus dropbox">
    <small id="apparatus-placeholder" style="color:#777;">Drag apparatus here</small>
  </div>
    <input type="hidden" id="apparatus_list" name="apparatus_list" value="">

   <label for="chemicals">Chemicals Required</label>
  <textarea id="chemicals" name="chemicals" rows="3" placeholder="List chemicals" required></textarea>

  <label for="theory">Theory</label>
  <textarea id="theory" name="theory" rows="3" placeholder="Explain theory" required></textarea>
   
     <!-- First Image -->
    
           <div style="display: flex; justify-content: center; align-items: center; margin: 12px;">
    <img src="../../../images/exp9_2.jpg" alt="Beer-Lambert's law image" style="max-width:600px; width: 100%; height: auto;" />
</div>

        
<div style="border:2px solid #223b71; border-radius:8px; width:420px; margin:18px auto 14px auto; background:#eef4fd;box-shadow:2px 4px 10px #eee;">
  <h3 style="text-align: center; margin-bottom: 7px; color: #223b71; letter-spacing:1px; font-size: 1.3em;">Formula</h3>
  <div style="text-align:center; font-size:1.7em; font-weight:bold; margin:14px 0;">
    A = ε c l
  </div>
  <hr style="border-top:1px solid #c6d7f7; width:78%; margin:8px auto 20px auto;">
  <div style="padding: 0 44px 12px 44px;">
    <div style="font-size:1.03em; text-align:left;">
      <b>A</b> = Absorbance<br><br>
      <b>ε</b> = Molar extinction coefficient<br><br>
      <b>c</b> = Concentration (mol/L)<br><br>
      <b>l</b> = Length of cuvette (1 cm)
    </div>
  </div>
</div>
<!-- Additional relationship -->
<div style="border:2px solid #223b71; border-radius:8px; width:420px; margin:18px auto 14px auto; background:#eef4fd;box-shadow:2px 4px 10px #eee;">
  <h3 style="text-align: center; margin-bottom: 7px; color: #223b71; letter-spacing:1px; font-size: 1.3em;">At constant length</h3>
  <div style="text-align:center; font-size:1.4em; font-weight:bold; margin:14px 0;">A ∝ c</div>
</div>


           <div style="display: flex; justify-content: center; align-items: center; margin: 12px;">
    <img src="../../../images/exp9_1.jpg" alt="Graph of Absorbance" style="max-width:300px; width: 100%; height: auto;" />
</div>

    <!-- Procedure -->
    <h3>Procedure :</h3>
  <textarea id="procedure" name="procedure" rows="4" placeholder="Enter Procedure " required></textarea>

    <!-- Table 1: Preparation of Standard KMnO4 Solution -->
   <h4>Table 1 : Preparation of Standard KMnO4 Solution</h4>
      <table>
        <tr>
          <th>S.No</th>
          <th>KMnO<sub>4</sub> (0.01M) </th>
          <th>Distilled H<sub>2</sub>O (ml)</th>
          <th>Concentration (M)</th>
        </tr>
        <tr>
  <td>1</td>
  <td><input type="text" name="std_v1" /></td>
  <td><input type="text" name="std_br_initial1" /></td>
  <td><input type="text" name="std_br_final1" /></td>
 
</tr>
<tr>
  <td>2</td>
  <td><input type="text" name="std_v2" /></td>
  <td><input type="text" name="std_br_initial2" /></td>
  <td><input type="text" name="std_br_final2" /></td>
  
</tr>
<tr>
  <td>3</td>
  <td><input type="text" name="std_v3" /></td>
  <td><input type="text" name="std_br_initial3" /></td>
  <td><input type="text" name="std_br_final3" /></td>
  
</tr>
<tr>
  <td>4</td>
  <td><input type="text" name="std_v4" /></td>
  <td><input type="text" name="std_br_initial4" /></td>
  <td><input type="text" name="std_br_final4" /></td>
 
</tr>
<tr>
  <td>5</td>
  <td><input type="text" name="std_v5" /></td>
  <td><input type="text" name="std_br_initial5" /></td>
  <td><input type="text" name="std_br_final5" /></td>
 
</tr>
<tr>
  <td>6</td>
  <td>Unknown</td>
  <td><input type="text" name="unk_br_initial" /></td>
  <td><input type="text" name="unk_br_final" /></td>
 
</tr>

    </table>
    <textarea id="process" name="process" rows="3" placeholder="Enter process"></textarea><br>

    <!-- Determining absorbance - text area -->
    <h3>Determining the Absorbance value of the unknown KMnO4 solution</h3>
    <textarea id="absorbance" name="absorbance" rows="3" placeholder="Describe absorbance measurement for unknown KMnO4" required></textarea>

    <!-- Dynamic Graph Section -->
    <div class="graph-section">
        <h3>Beer-Lambert's Law Graph</h3>
        <div style="width: 100%; max-width: 800px; margin: 20px auto;">
          <canvas id="beerLambertChart" width="800" height="400"></canvas>
        </div>
        <div id="graph-placeholder" style="text-align: center; padding: 40px; color: #666; background: #f9f9f9; border-radius: 5px;">
          <p><strong>Graph will appear here when you enter data in the table below</strong></p>
          <p>Enter Concentration and Absorbance values to generate the Beer-Lambert plot</p>
        </div>
        <div id="unknown-concentration-info" style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px; display: none;">
          <strong>Concentration of Unknown Solution:</strong> 
          <span id="unknown-concentration-value">0</span> M
        </div>
    </div>

    <!-- Table: Absorbance of Solution -->
    <h4> Absorbance of the Solution</h4>
<table id="absorbance-table">
  <tr>
    <th>S.No</th>
    <th>Concentration (mol/L)</th>
    <th>Absorbance</th>
  </tr>
  <tr>
    <td>1</td>
    <td><input type="text" name="tab2_conc_1" class="conc-input" step="0.001" onchange="updateBeerLambertGraph()" /></td>
    <td><input type="text" name="tab2_abs_1" class="abs-input" step="0.001" onchange="updateBeerLambertGraph()" /></td>
  </tr>
  <tr>
    <td>2</td>
    <td><input type="text" name="tab2_conc_2" class="conc-input" step="0.001" onchange="updateBeerLambertGraph()" /></td>
    <td><input type="text" name="tab2_abs_2" class="abs-input" step="0.001" onchange="updateBeerLambertGraph()" /></td>
  </tr>
  <tr>
    <td>3</td>
    <td><input type="text" name="tab2_conc_3" class="conc-input" step="0.001" onchange="updateBeerLambertGraph()" /></td>
    <td><input type="text" name="tab2_abs_3" class="abs-input" step="0.001" onchange="updateBeerLambertGraph()" /></td>
  </tr>
  <tr>
    <td>4</td>
    <td><input type="text" name="tab2_conc_4" class="conc-input" step="0.001" onchange="updateBeerLambertGraph()" /></td>
    <td><input type="text" name="tab2_abs_4" class="abs-input" step="0.001" onchange="updateBeerLambertGraph()" /></td>
  </tr>
  <tr>
    <td>5</td>
    <td><input type="text" name="tab2_conc_5" class="conc-input" step="0.001" onchange="updateBeerLambertGraph()" /></td>
    <td><input type="text" name="tab2_abs_5" class="abs-input" step="0.001" onchange="updateBeerLambertGraph()" /></td>
  </tr>
  <tr>
    <td>6</td>
    <td>Unknown Solution</td>
    <td><input type="text" name="tab2_abs_6" class="abs-input" step="0.001" onchange="updateBeerLambertGraph()" /></td>
  </tr>
 
</table>

    <!-- From graph sentence -->
   <div>From Graph we know the concentration of unknown KMnO₄ solution = <input type="text" name="concentration_unknown" id="concentration_unknown" style="width: 140px;" readonly /></div>

    <!-- Report line -->
    <label for="Report">Result</label>
  <textarea id="result" name="result" rows="3" placeholder="Write final conclusion" required></textarea>

   <div class="btn-group">
    <button type="button" onclick="previewExpWithGraph()" style="cursor:pointer; background:#007bff; color:#fff; font-weight:600; padding:8px 16px; border-radius:6px; width: fit-content;">Preview </button>
       <button type="button"  onclick="submitExperimentWithGraph()" style="cursor:pointer; background:#1a347a; color:#fff; font-weight:600; padding:8px 16px; border-radius:6px; width: fit-content;">Submit</button>
         
      </div>
    </form>

<aside class="sidebar">
      <div class="apparatus-box">
        <h3 style="font-size:22px;margin-bottom:12px;text-align:center;"> Select Apparatus</h3>
        <div class="apparatus-list">
          <button type="button" class="apparatus-btn">Calorimeter</button>
          <button type="button" class="apparatus-btn">Cuvette</button>
          <button type="button" class="apparatus-btn">Burette</button>
          <button type="button" class="apparatus-btn">100 ml Beakers</button>
          <button type="button" class="apparatus-btn">Glass rod</button>
          <button type="button" class="apparatus-btn">Volumetric Flask</button>
          <button type="button" class="apparatus-btn">Conical Flask</button>
          <button type="button" class="apparatus-btn">Tissues</button>
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
let beerLambertChart = null;

// ---------- Calculator Functions ----------
        let hasDecimal = false;

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

// ---------- Beer-Lambert Graph Functions ----------
function updateBeerLambertGraph() {
    const concentrations = [];
    const absorbances = [];
    let unknownAbsorbance = null;
    
    // Collect data from table
    for (let i = 1; i <= 5; i++) {
        const concInput = document.querySelector(`input[name="tab2_conc_${i}"]`);
        const absInput = document.querySelector(`input[name="tab2_abs_${i}"]`);
        
        if (concInput && concInput.value && absInput && absInput.value) {
            const concentration = parseFloat(concInput.value);
            const absorbance = parseFloat(absInput.value);
            
            concentrations.push(concentration);
            absorbances.push(absorbance);
        }
    }
    
    // Get unknown absorbance
    const unknownAbsInput = document.querySelector('input[name="tab2_abs_6"]');
    if (unknownAbsInput && unknownAbsInput.value) {
        unknownAbsorbance = parseFloat(unknownAbsInput.value);
    }
    
    // Only generate graph if we have at least 2 data points
    if (concentrations.length >= 2) {
        generateBeerLambertPlot(concentrations, absorbances, unknownAbsorbance);
    } else {
        // Hide graph and show placeholder if not enough data
        const canvas = document.getElementById('beerLambertChart');
        const placeholder = document.getElementById('graph-placeholder');
        const unknownInfo = document.getElementById('unknown-concentration-info');
        
        if (beerLambertChart) {
            beerLambertChart.destroy();
            beerLambertChart = null;
        }
        
        canvas.style.display = 'none';
        placeholder.style.display = 'block';
        unknownInfo.style.display = 'none';
    }
}

function generateBeerLambertPlot(concentrations, absorbances, unknownAbsorbance = null) {
    const ctx = document.getElementById('beerLambertChart').getContext('2d');
    const placeholder = document.getElementById('graph-placeholder');
    
    // Show canvas and hide placeholder
    document.getElementById('beerLambertChart').style.display = 'block';
    placeholder.style.display = 'none';
    
    // Destroy existing chart
    if (beerLambertChart) {
        beerLambertChart.destroy();
    }
    
    // Calculate linear regression
    const regression = linearRegression(concentrations, absorbances);
    const slope = regression.slope;
    const intercept = regression.intercept;
    const rSquared = regression.rSquared;
    
    // Calculate unknown concentration if absorbance is provided
    let unknownConcentration = null;
    if (unknownAbsorbance !== null) {
        unknownConcentration = (unknownAbsorbance - intercept) / slope;
        document.getElementById('concentration_unknown').value = unknownConcentration.toFixed(4);
        
        // Show unknown concentration info
        const unknownInfo = document.getElementById('unknown-concentration-info');
        const unknownValue = document.getElementById('unknown-concentration-value');
        unknownValue.textContent = unknownConcentration.toFixed(4);
        unknownInfo.style.display = 'block';
    }
    
    // Generate points for the regression line
    const minConc = Math.min(...concentrations);
    const maxConc = Math.max(...concentrations);
    const lineConcentrations = [minConc, maxConc];
    const lineAbsorbances = lineConcentrations.map(conc => slope * conc + intercept);
    
    beerLambertChart = new Chart(ctx, {
        type: 'scatter',
        data: {
            datasets: [
                {
                    label: 'Experimental Data',
                    data: concentrations.map((conc, index) => ({x: conc, y: absorbances[index]})),
                    backgroundColor: '#000000',
                    borderColor: '#000000',
                    pointRadius: 6,
                    pointHoverRadius: 8
                },
                {
                    label: 'Linear Fit (A = εcl)',
                    data: lineConcentrations.map((conc, index) => ({x: conc, y: lineAbsorbances[index]})),
                    borderColor: '#ff0000',
                    backgroundColor: 'rgba(255, 0, 0, 0.1)',
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: false,
                    showLine: true,
                    tension: 0
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Beer-Lambert Law: Absorbance vs Concentration',
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
                    mode: 'point',
                    intersect: true,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff'
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Concentration (mol/L)',
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
                        text: 'Absorbance',
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

function linearRegression(x, y) {
    const n = x.length;
    let sumX = 0;
    let sumY = 0;
    let sumXY = 0;
    let sumXX = 0;
    
    for (let i = 0; i < n; i++) {
        sumX += x[i];
        sumY += y[i];
        sumXY += x[i] * y[i];
        sumXX += x[i] * x[i];
    }
    
    const slope = (n * sumXY - sumX * sumY) / (n * sumXX - sumX * sumX);
    const intercept = (sumY - slope * sumX) / n;
    
    // Calculate R-squared
    let ssTot = 0;
    let ssRes = 0;
    const yMean = sumY / n;
    
    for (let i = 0; i < n; i++) {
        const yPred = slope * x[i] + intercept;
        ssTot += Math.pow(y[i] - yMean, 2);
        ssRes += Math.pow(y[i] - yPred, 2);
    }
    
    const rSquared = 1 - (ssRes / ssTot);
    
    return { slope, intercept, rSquared };
}

// Function to create print-style graph for preview/submission
function createPrintStyleBeerLambertGraph(concentrations, absorbances, unknownAbsorbance = null, width = 600, height = 400) {
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
    const minConc = Math.min(...concentrations);
    const maxConc = Math.max(...concentrations);
    const minAbs = Math.min(...absorbances);
    const maxAbs = Math.max(...absorbances);
    
    // Scales
    const xScale = (conc) => margin.left + ((conc - minConc) / (maxConc - minConc)) * chartWidth;
    const yScale = (abs) => margin.top + chartHeight - ((abs - minAbs) / (maxAbs - minAbs)) * chartHeight;
    
    // Draw grid lines
    ctx.strokeStyle = '#e0e0e0';
    ctx.lineWidth = 1;
    
    // Vertical grid lines
    const concSteps = 5;
    for (let i = 0; i <= concSteps; i++) {
        const conc = minConc + (i / concSteps) * (maxConc - minConc);
        const x = xScale(conc);
        ctx.beginPath();
        ctx.moveTo(x, margin.top);
        ctx.lineTo(x, margin.top + chartHeight);
        ctx.stroke();
    }
    
    // Horizontal grid lines
    const absSteps = 5;
    for (let i = 0; i <= absSteps; i++) {
        const abs = minAbs + (i / absSteps) * (maxAbs - minAbs);
        const y = yScale(abs);
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
    
    // Calculate linear regression
    const regression = linearRegression(concentrations, absorbances);
    const slope = regression.slope;
    const intercept = regression.intercept;
    
    // Draw regression line
    ctx.strokeStyle = '#ff0000';
    ctx.lineWidth = 2;
    ctx.beginPath();
    const startX = xScale(minConc);
    const startY = yScale(slope * minConc + intercept);
    const endX = xScale(maxConc);
    const endY = yScale(slope * maxConc + intercept);
    ctx.moveTo(startX, startY);
    ctx.lineTo(endX, endY);
    ctx.stroke();
    
    // Draw data points
    ctx.fillStyle = '#000000';
    for (let i = 0; i < concentrations.length; i++) {
        const x = xScale(concentrations[i]);
        const y = yScale(absorbances[i]);
        ctx.beginPath();
        ctx.arc(x, y, 4, 0, 2 * Math.PI);
        ctx.fill();
    }
    
    // Draw unknown point if available
    if (unknownAbsorbance !== null) {
        const unknownConcentration = (unknownAbsorbance - intercept) / slope;
        const x = xScale(unknownConcentration);
        const y = yScale(unknownAbsorbance);
        
        // Draw cross for unknown
        ctx.strokeStyle = '#0000ff';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(x - 6, y - 6);
        ctx.lineTo(x + 6, y + 6);
        ctx.moveTo(x + 6, y - 6);
        ctx.lineTo(x - 6, y + 6);
        ctx.stroke();
    }
    
    // Draw title
    ctx.fillStyle = '#000000';
    ctx.font = 'bold 16px Arial';
    ctx.textAlign = 'center';
    ctx.fillText('Beer-Lambert Law: Absorbance vs Concentration', width / 2, 30);
    
    // Draw X-axis label
    ctx.font = 'bold 12px Arial';
    ctx.fillText('Concentration (mol/L)', width / 2, height - 15);
    
    // Draw Y-axis label
    ctx.save();
    ctx.translate(20, height / 2);
    ctx.rotate(-Math.PI / 2);
    ctx.fillText('Absorbance', 0, 0);
    ctx.restore();
    
    // Draw X-axis ticks and labels
    ctx.font = '11px Arial';
    ctx.textAlign = 'center';
    for (let i = 0; i <= concSteps; i++) {
        const conc = minConc + (i / concSteps) * (maxConc - minConc);
        const x = xScale(conc);
        ctx.beginPath();
        ctx.moveTo(x, margin.top + chartHeight);
        ctx.lineTo(x, margin.top + chartHeight + 5);
        ctx.stroke();
        ctx.fillText(conc.toFixed(3), x, margin.top + chartHeight + 20);
    }
    
    // Draw Y-axis ticks and labels
    ctx.textAlign = 'right';
    for (let i = 0; i <= absSteps; i++) {
        const abs = minAbs + (i / absSteps) * (maxAbs - minAbs);
        const y = yScale(abs);
        ctx.beginPath();
        ctx.moveTo(margin.left, y);
        ctx.lineTo(margin.left - 5, y);
        ctx.stroke();
        ctx.fillText(abs.toFixed(3), margin.left - 10, y + 4);
    }
    
    // Draw equation and R-squared
    ctx.fillStyle = '#000000';
    ctx.font = 'bold 12px Arial';
    ctx.textAlign = 'left';
    ctx.fillText(`A = ${slope.toFixed(3)}c + ${intercept.toFixed(3)}`, margin.left + 10, margin.top + 20);
    ctx.fillText(`R² = ${regression.rSquared.toFixed(4)}`, margin.left + 10, margin.top + 40);
    
    // Draw unknown concentration if available
    if (unknownAbsorbance !== null) {
        const unknownConcentration = (unknownAbsorbance - intercept) / slope;
        ctx.fillText(`Unknown Concentration = ${unknownConcentration.toFixed(4)} M`, margin.left + 10, margin.top + 60);
    }
    
    return canvas.toDataURL('image/png');
}

// Initialize with no graph
document.addEventListener('DOMContentLoaded', function() {
    // Initially hide the canvas and show placeholder
    document.getElementById('beerLambertChart').style.display = 'none';
    document.getElementById('graph-placeholder').style.display = 'block';
});

// ---------- Drag & Drop ----------
document.addEventListener('DOMContentLoaded', () => {
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

    function addApparatusToDropbox(name) {
        const placeholder = document.getElementById('apparatus-placeholder');
        if (placeholder) placeholder.style.display = 'none';
        const dropZone = document.getElementById('apparatus-dropbox');
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
});

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('exp9-form');
    
    form.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            if (e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
                return false;
            }
        }
    });
});

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

function generateAbsorbanceTableRowsForPreview() {
    let rows = '';
    for (let i = 1; i <= 6; i++) {
        const concentration = document.querySelector(`input[name="tab2_conc_${i}"]`)?.value || '';
        const absorbance = document.querySelector(`input[name="tab2_abs_${i}"]`)?.value || '';
        
        const concLabel = i === 6 ? 'Unknown Solution' : escapeHtml(concentration);
        
        rows += `<tr>
            <td>${i}</td>
            <td>${concLabel}</td>
            <td>${escapeHtml(absorbance)}</td>
        </tr>`;
    }
    return rows;
}

function previewExpWithGraph() {
    const form = document.getElementById('exp9-form');
    const apparatusList = Array.from(document.querySelectorAll("#apparatus-dropbox .tool-item"))
        .map(el => el.textContent.trim());

    // Collect graph data
    const concentrations = [];
    const absorbances = [];
    let unknownAbsorbance = null;
    
    for (let i = 1; i <= 5; i++) {
        const concInput = document.querySelector(`input[name="tab2_conc_${i}"]`);
        const absInput = document.querySelector(`input[name="tab2_abs_${i}"]`);
        
        if (concInput && concInput.value && absInput && absInput.value) {
            concentrations.push(parseFloat(concInput.value));
            absorbances.push(parseFloat(absInput.value));
        }
    }
    
    // Get unknown absorbance
    const unknownAbsInput = document.querySelector('input[name="tab2_abs_6"]');
    if (unknownAbsInput && unknownAbsInput.value) {
        unknownAbsorbance = parseFloat(unknownAbsInput.value);
    }

    // Create print-style graph
    let graphDataURL = '';
    if (concentrations.length >= 2) {
        graphDataURL = createPrintStyleBeerLambertGraph(concentrations, absorbances, unknownAbsorbance);
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
    .formula-box {
        border: 2px solid #223b71;
        border-radius: 8px;
        width: 420px;
        margin: 18px auto 14px auto;
        background: #eef4fd;
        box-shadow: 2px 4px 10px #eee;
        padding: 10px;
    }
</style>
<div class="header-row">
    <div><b>Experiment No.:9</b> </div>
    <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
</div>
<h2 style="text-align:center; margin-top: 0;">Verify Beer-Lambert's Law</h2>

<p><b>Aim:</b> ${formatTextWithBreaks(form.aim.value || '')}</p>
<p><b>Apparatus Used:</b> ${apparatusList.length ? escapeHtml(apparatusList.join(", ")) : '—'}</p>
<p><b>Chemicals Required:</b> ${formatTextWithBreaks(form.chemicals.value || '')}</p>
<p><b>Theory:</b> ${formatTextWithBreaks(form.theory.value || '')}</p>

<div style="display: flex; justify-content: center; align-items: center; margin: 12px;">
    <img src="../../../images/exp9_2.jpg" alt="Beer-Lambert's law image" style="max-width:600px; width: 100%; height: auto;" />
</div>

<div style="text-align:center; margin: 20px 0;">
    <p style="font-size:1.2em; font-weight:bold; margin:10px 0;">Formula: A = ε c l</p>
    <div style="display: inline-block; text-align: left;">
        <p style="margin:2px 0;">Where</p>
        <p style="margin:2px 0;"><b>A</b> = Absorbance</p>
        <p style="margin:2px 0;"><b>ε</b> = Molar extinction coefficient</p>
        <p style="margin:2px 0;"><b>c</b> = Concentration (mol/L)</p>
        <p style="margin:2px 0;"><b>l</b> = Length of cuvette (1 cm)</p>
    </div>
</div>

<div style="text-align:center; margin: 20px 0;">
    <p style="font-size:1.2em; font-weight:bold;">At constant length: A ∝ c</p>
</div>

<div style="display: flex; justify-content: center; align-items: center; margin: 12px;">
    <img src="../../../images/exp9_1.jpg" alt="Graph of Absorbance" style="max-width:300px; width: 100%; height: auto;" />
</div>

<h3>Procedure:</h3>
<p>${formatTextWithBreaks(form.procedure.value || '')}</p>

<h4>Table 1: Preparation of Standard KMnO₄ Solution</h4>
<table>
    <tr>
        <th>S.No</th>
        <th>KMnO₄ (0.01M) (ml)</th>
        <th>Distilled H₂O (ml)</th>
        <th>Concentration (M)</th>
    </tr>
    <tr>
        <td>1</td>
        <td>${escapeHtml(form.std_v1.value || '')}</td>
        <td>${escapeHtml(form.std_br_initial1.value || '')}</td>
        <td>${escapeHtml(form.std_br_final1.value || '')}</td>
    </tr>
    <tr>
        <td>2</td>
        <td>${escapeHtml(form.std_v2.value || '')}</td>
        <td>${escapeHtml(form.std_br_initial2.value || '')}</td>
        <td>${escapeHtml(form.std_br_final2.value || '')}</td>
    </tr>
    <tr>
        <td>3</td>
        <td>${escapeHtml(form.std_v3.value || '')}</td>
        <td>${escapeHtml(form.std_br_initial3.value || '')}</td>
        <td>${escapeHtml(form.std_br_final3.value || '')}</td>
    </tr>
    <tr>
        <td>4</td>
        <td>${escapeHtml(form.std_v4.value || '')}</td>
        <td>${escapeHtml(form.std_br_initial4.value || '')}</td>
        <td>${escapeHtml(form.std_br_final4.value || '')}</td>
    </tr>
    <tr>
        <td>5</td>
        <td>${escapeHtml(form.std_v5.value || '')}</td>
        <td>${escapeHtml(form.std_br_initial5.value || '')}</td>
        <td>${escapeHtml(form.std_br_final5.value || '')}</td>
    </tr>
    <tr>
        <td>6</td>
        <td>Unknown</td>
        <td>${escapeHtml(form.unk_br_initial.value || '')}</td>
        <td>${escapeHtml(form.unk_br_final.value || '')}</td>
    </tr>
</table>

<p><b>Process:</b> ${formatTextWithBreaks(form.process.value || '')}</p>

<h3>Determining the Absorbance value of the unknown KMnO₄ solution</h3>
<p>${formatTextWithBreaks(form.absorbance.value || '')}</p>

<div style="margin: 25px 0; text-align: center;">
    <h3>Beer-Lambert's Law Graph</h3>
    ${graphDataURL ? `<img src="${graphDataURL}" alt="Beer-Lambert's Law Graph" style="max-width: 100%; height: auto; border: 1px solid #000;" />` : '<p><em>No graph data available - Enter data in the table to generate the graph</em></p>'}
    ${document.getElementById('concentration_unknown').value ? `<div style="margin-top: 15px; padding: 10px; background: #f0f0f0; border: 1px solid #000; border-radius: 0;">
        <b>Concentration of Unknown Solution:</b> ${document.getElementById('concentration_unknown').value} M
    </div>` : ''}
</div>

<h4>Absorbance of the Solution</h4>
<table>
    <tr>
        <th>S.No</th>
        <th>Concentration (mol/L)</th>
        <th>Absorbance</th>
    </tr>
    ${generateAbsorbanceTableRowsForPreview()}
</table>

<p><b>From Graph we know the concentration of unknown KMnO₄ solution:</b> ${escapeHtml(form.concentration_unknown.value || '')}</p>

<h3>Result:</h3>
<p>${formatTextWithBreaks(form.result.value || '')}</p>`;

    const win = window.open('', '_blank', 'width=900,height=800');
    win.document.write('<!DOCTYPE html><html><head><title>Preview</title><meta charset="utf-8"></head><body style="font-family:Arial,sans-serif; padding:20px;">');
    win.document.write(previewHtml);
    win.document.write('</body></html>');
    win.document.close();
}

async function submitExperimentWithGraph() {
   
        const form = document.getElementById('exp9-form');
         const subject = 'chemistry';
            const experiment_number = 9; // From your database
            const employee_id = '123';

    if (!form.aim.value.trim() || !form.chemicals.value.trim() || 
        !form.theory.value.trim() || !form.result.value.trim()) {
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
    const concentrations = [];
    const absorbances = [];
    let unknownAbsorbance = null;
    
    for (let i = 1; i <= 5; i++) {
        const concInput = document.querySelector(`input[name="tab2_conc_${i}"]`);
        const absInput = document.querySelector(`input[name="tab2_abs_${i}"]`);
        
        if (concInput && concInput.value && absInput && absInput.value) {
            concentrations.push(parseFloat(concInput.value));
            absorbances.push(parseFloat(absInput.value));
        }
    }
    
    // Get unknown absorbance
    const unknownAbsInput = document.querySelector('input[name="tab2_abs_6"]');
    if (unknownAbsInput && unknownAbsInput.value) {
        unknownAbsorbance = parseFloat(unknownAbsInput.value);
    }

    // Create print-style graph for submission
    let graphDataURL = '';
    if (concentrations.length >= 2) {
        graphDataURL = createPrintStyleBeerLambertGraph(concentrations, absorbances, unknownAbsorbance);
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
    .formula-box {
        border: 2px solid #223b71;
        border-radius: 8px;
        width: 420px;
        margin: 18px auto 14px auto;
        background: #eef4fd;
        box-shadow: 2px 4px 10px #eee;
        padding: 10px;
    }
</style>
<div class="header-row">
    <div><b>Experiment No.9:</b> </div>
    <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
</div>
<h2 style="text-align:center; margin-top: 0;">Verify Beer-Lambert's Law</h2>

<p><b>Aim:</b> ${formatTextWithBreaks(form.aim.value || '')}</p>
<p><b>Apparatus Used:</b> ${apparatusList.length ? escapeHtml(apparatusList.join(", ")) : '—'}</p>
<p><b>Chemicals Required:</b> ${formatTextWithBreaks(form.chemicals.value || '')}</p>
<p><b>Theory:</b> ${formatTextWithBreaks(form.theory.value || '')}</p>

<div style="display: flex; justify-content: center; align-items: center; margin: 12px;">
    <img src="../../../images/exp9_2.jpg" alt="Beer-Lambert's law image" style="max-width:600px; width: 100%; height: auto;" />
</div>

<div style="text-align:center; margin: 20px 0;">
    <p style="font-size:1.2em; font-weight:bold; margin:10px 0;">Formula: A = ε c l</p>
    <div style="display: inline-block; text-align: left;">
        <p style="margin:2px 0;">Where</p>
        <p style="margin:2px 0;"><b>A</b> = Absorbance</p>
        <p style="margin:2px 0;"><b>ε</b> = Molar extinction coefficient</p>
        <p style="margin:2px 0;"><b>c</b> = Concentration (mol/L)</p>
        <p style="margin:2px 0;"><b>l</b> = Length of cuvette (1 cm)</p>
    </div>
</div>

<div style="text-align:center; margin: 20px 0;">
    <p style="font-size:1.2em; font-weight:bold;">At constant length: A ∝ c</p>
</div>

<div style="display: flex; justify-content: center; align-items: center; margin: 12px;">
    <img src="../../../images/exp9_1.jpg" alt="Graph of Absorbance" style="max-width:300px; width: 100%; height: auto;" />
</div>

<h3>Procedure:</h3>
<p>${formatTextWithBreaks(form.procedure.value || '')}</p>

<h4>Table 1: Preparation of Standard KMnO₄ Solution</h4>
<table>
    <tr>
        <th>S.No</th>
        <th>KMnO₄ (0.01M) (ml)</th>
        <th>Distilled H₂O (ml)</th>
        <th>Concentration (M)</th>
    </tr>
    <tr>
        <td>1</td>
        <td>${escapeHtml(form.std_v1.value || '')}</td>
        <td>${escapeHtml(form.std_br_initial1.value || '')}</td>
        <td>${escapeHtml(form.std_br_final1.value || '')}</td>
    </tr>
    <tr>
        <td>2</td>
        <td>${escapeHtml(form.std_v2.value || '')}</td>
        <td>${escapeHtml(form.std_br_initial2.value || '')}</td>
        <td>${escapeHtml(form.std_br_final2.value || '')}</td>
    </tr>
    <tr>
        <td>3</td>
        <td>${escapeHtml(form.std_v3.value || '')}</td>
        <td>${escapeHtml(form.std_br_initial3.value || '')}</td>
        <td>${escapeHtml(form.std_br_final3.value || '')}</td>
    </tr>
    <tr>
        <td>4</td>
        <td>${escapeHtml(form.std_v4.value || '')}</td>
        <td>${escapeHtml(form.std_br_initial4.value || '')}</td>
        <td>${escapeHtml(form.std_br_final4.value || '')}</td>
    </tr>
    <tr>
        <td>5</td>
        <td>${escapeHtml(form.std_v5.value || '')}</td>
        <td>${escapeHtml(form.std_br_initial5.value || '')}</td>
        <td>${escapeHtml(form.std_br_final5.value || '')}</td>
    </tr>
    <tr>
        <td>6</td>
        <td>Unknown</td>
        <td>${escapeHtml(form.unk_br_initial.value || '')}</td>
        <td>${escapeHtml(form.unk_br_final.value || '')}</td>
    </tr>
</table>

<p><b>Process:</b> ${formatTextWithBreaks(form.process.value || '')}</p>

<h3>Determining the Absorbance value of the unknown KMnO₄ solution</h3>
<p>${formatTextWithBreaks(form.absorbance.value || '')}</p>

<div style="margin: 25px 0; text-align: center;">
    <h3>Beer-Lambert's Law Graph</h3>
    ${graphDataURL ? `<img src="${graphDataURL}" alt="Beer-Lambert's Law Graph" style="max-width: 100%; height: auto; border: 1px solid #000;" />` : '<p>No graph data available</p>'}
    ${document.getElementById('concentration_unknown').value ? `<div style="margin-top: 15px; padding: 10px; background: #f0f0f0; border: 1px solid #000; border-radius: 0;">
        <b>Concentration of Unknown Solution:</b> ${document.getElementById('concentration_unknown').value} M
    </div>` : ''}
</div>

<h4>Absorbance of the Solution</h4>
<table>
    <tr>
        <th>S.No</th>
        <th>Concentration (mol/L)</th>
        <th>Absorbance</th>
    </tr>
    ${generateAbsorbanceTableRowsForPreview()}
</table>

<p><b>From Graph we know the concentration of unknown KMnO₄ solution:</b> ${escapeHtml(form.concentration_unknown.value || '')}</p>

<h3>Result:</h3>
<p>${formatTextWithBreaks(form.result.value || '')}</p>`;

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
</script>
</body>
</html>