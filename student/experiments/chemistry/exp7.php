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
  <title>Experiment 7: Determination of Cell Constant and Conductance of Solution</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  
  <!-- External CSS -->
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
    <form id="exp7-form" method="post"  class="form-section">
      <div class="exp-header">
        <div>
          <label for="expNo">Experiment No.7</label>
          <input type="hidden" id="subject" name="subject" value="Chemistry">
    <input type="hidden" id="experiment_number" name="experiment_number" value="7">
                </div>
        <div>
          <label for="expDate">Date</label>
          <input type="date" id="expDate" name="expDate" required/>
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

      <h2>Determination of Cell Constant and Conductance of Solution</h2>

      <label for="aim">Aim</label>
      <textarea id="aim" name="aim" rows="3" required></textarea>

      <label>Apparatus Used (Drag and Drop)</label>
      <div id="apparatus-dropbox" class="apparatus-dropbox">
        <small id="apparatus-placeholder">Drag apparatus here</small>
      </div>
      <input type="hidden" id="apparatus_list" name="apparatus_list" value="">

      <label for="chemicals">Chemicals Required</label>
      <textarea id="chemicals" name="chemicals" rows="3" required></textarea>

      <h3>Preparation of Solutions</h3>
      
      <label for="preparation_01n">Preparation of 0.1 N KCl solution</label>
      <textarea id="preparation_01n" name="preparation_01n" rows="2" required></textarea>
      
      <label for="preparation_001n">Preparation of 0.01 N KCl solution</label>
      <textarea id="preparation_001n" name="preparation_001n" rows="2" required></textarea>

      <h3>Definitions</h3>
      
      <label for="conductivity_def">Conductivity</label>
      <textarea id="conductivity_def" name="conductivity_def" rows="2" required></textarea>
      
      <label for="cell_constant_def">Cell Constant</label>
      <textarea id="cell_constant_def" name="cell_constant_def" rows="2" required></textarea>
      
      <label for="specific_conductance_def">Specific Conductance</label>
      <textarea id="specific_conductance_def" name="specific_conductance_def" rows="2" required></textarea>
      
      <p><strong>Cell Constant = Specific Conductance (ohm⁻¹Cm⁻¹) or Conductivity / Measured conductance (ohm⁻¹)</strong></p>
      <p>Thus, the unit of cell constant is Cm⁻¹.</p>

      <h3>Procedure</h3>
      <textarea id="procedure" name="procedure" rows="5" required></textarea>

      <h3>Observations</h3>
      <div class="observation-box">
        <div>
          <label for="conductance_water">Conductance of distilled water</label>
          <input type="text" id="conductance_water" name="conductance_water" />
        </div>
        <div>
          <label for="conductance_01n">Conductance of 0.1 N KCl</label>
          <input type="text" id="conductance_01n" name="conductance_01n" />
        </div>
        <div>
          <label for="conductance_001n">Conductance of 0.01 N KCl</label>
          <input type="text" id="conductance_001n" name="conductance_001n" />
        </div>
      </div>

      <h3>Data Table</h3>
      <table>
        <tr>
          <th>Concentration of KCl</th>
          <th>Specific Conductance (Ohm⁻¹ cm⁻¹)</th>
          <th>Observed Conductance (Ohm⁻¹)</th>
        </tr>
        <tr>
          <td>0.1 N</td>
          <td><input type="text" name="spec_cond_01n" /></td>
          <td><input type="text" name="obs_cond_01n" /></td>
        </tr>
        <tr>
          <td>0.01 N</td>
          <td><input type="text" name="spec_cond_001n" /></td>
          <td><input type="text" name="obs_cond_001n" /></td>
        </tr>
      </table>

       <h3>Calculations</h3>
        <div style="font-family:Arial, sans-serif; border:1px solid #333; padding:15px; width:90%; margin:10px auto; background:#f9f9f9; border-radius:6px;">
          <h3 style="text-align:left; margin:0; color:#222;">Formulas:</h3>      
          
          <div style="font-size:16px; margin-top:20px;">
            
            <!-- Left aligned -->
            <b style="display:block; text-align:left; font-size:16px; margin-top:20px;">Cell constant of 0.1N KCL =</b>
            
            <div style="text-align:center; margin-top:10px;">
              <span style="display:inline-block; border-bottom:2px solid #000; padding:3px 12px;">
                Cell Constant of 0.1N KCL
              </span>
              <br>
              <span style="display:inline-block; margin-top:5px;">
                Observed Conductance of 0.1N KCL – Observed Conductance of Distilled Water
              </span>
            </div>
            
            <br>
            <b style="text-align:center; font-size:16px; margin-top:20px;">Cell constant of 0.01N KCL =</b>
            <input type="text" name="cell_constant_0.1N" style="margin-left:10px; width:150px;" />
            <br><br>
            
            <!-- Second formula remains centered -->
            <b style="display:block; text-align:left; font-size:16px; margin-top:20px;">Cell constant of 0.01N KCL =</b>
            <div style="text-align:center; margin-top:10px;">
              <span style="display:inline-block; border-bottom:2px solid #000; padding:3px 12px;">
                Specific Conductance of 0.01N KCL
              </span>
              <br>
              <span style="display:inline-block; margin-top:5px;">
                Observed Conductance of 0.01N KCL – Observed Conductance of Distilled Water
              </span>
            </div>
            
            <br>
            <b style="text-align:center; font-size:16px; margin-top:20px;">Cell constant of 0.01N KCL =</b>
            <input type="text" name="cell_constant_0.01N" style="margin-left:10px; width:150px;" />
          
          </div>
        </div>
        <div >
             <h3>Result</h3>
          1. Conductance of 0.1N KCL = <input type="text" name="result_1" style="margin-left:10px; width:150px;" /><br>
          2. Conductance of 0.01N KCL = <input type="text" name="result_2" style="margin-left:10px; width:150px;" /><br>
          3. Cell constant of 0.1N KCL = <input type="text" name="result_3" style="margin-left:10px; width:150px;" /><br>
          4. Cell constant of 0.01N KCL = <input type="text" name="result_4" style="margin-left:10px; width:150px;" /><br>
        </div>
        <div class="btn-group">
    
    
       <button type="button" onclick="previewExp()" style="cursor:pointer; background:#007bff; color:#fff; font-weight:600; padding:8px 16px; border-radius:6px; width: fit-content;">Preview </button>
       <button type="button" id="submitBtn" onclick="submitExperiment()" style="cursor:pointer; background:#1a347a; color:#fff; font-weight:600; padding:8px 16px; border-radius:6px; width: fit-content;">Submit</button>
            </div>
    
    </form>

    <aside class="sidebar">
      <div class="apparatus-box">
        <h3 style="font-size:22px;margin-bottom:12px;text-align:center;">Select Apparatus</h3>
        <div class="apparatus-list">
          <button type="button" class="apparatus-btn">Conductometer</button>
          <button type="button" class="apparatus-btn">Conductivity cell</button>
          <button type="button" class="apparatus-btn">Beaker</button>
          <button type="button" class="apparatus-btn">Standard flask</button>
          <button type="button" class="apparatus-btn">Measuring Cylinder</button>
          <button type="button" class="apparatus-btn">Weighing balance</button>
          <button type="button" class="apparatus-btn">Volumetric Flask</button>
          <button type="button" class="apparatus-btn">Wash bottle</button>
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

    // Drag & Drop functionality
    document.addEventListener('DOMContentLoaded', () => {
      const tools = document.querySelectorAll('.apparatus-btn');
      tools.forEach(tool => {
        tool.setAttribute('draggable', 'true');
        
        tool.addEventListener('dragstart', (e) => {
          const name = tool.textContent.trim();
          e.dataTransfer.setData('text/plain', name);
        });

        tool.addEventListener('click', () => {
          addApparatusToDropbox(tool.textContent.trim());
        });
      });

      const dropZone = document.getElementById('apparatus-dropbox');
      dropZone.addEventListener('dragover', (e) => { 
        e.preventDefault(); 
      });
      
      dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
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

        item.addEventListener('click', () => {
          item.remove();
          if (dropZone.children.length === 0 && placeholder) {
            placeholder.style.display = 'inline';
          }
        });

        dropZone.appendChild(item);
      }
    });

    // Form submission
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('exp7-form');
      
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
    document.addEventListener("cheking tab switces", () => {
  if (document.hidden) { console.log("tab_switched");
  }
});



document.addEventListener("fullscreen", () => {
  if (!document.fullscreenElement) {console.log("exit full screen");
  }
});

    // Preview function
function previewExp() {
  const form = document.getElementById('exp7-form');
  const apparatusList = Array.from(document.querySelectorAll("#apparatus-dropbox .tool-item"))
    .map(el => el.textContent.trim());

  const previewHtml = `
<div>
  <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
    <div><b>Experiment No.:</b> 7</div>
    <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
  </div>
  <h2 style="text-align: center;">Determination of Cell Constant and Conductance of Solution</h2>

  <p><b>Aim:</b> ${formatTextWithBreaks(form.aim.value || '')}</p>
  <p><b>Apparatus Used:</b> ${apparatusList.length ? escapeHtml(apparatusList.join(", ")) : '—'}</p>
  <p><b>Chemicals Required:</b> ${formatTextWithBreaks(form.chemicals.value || '')}</p>

  <h3>Preparation of Solutions</h3>
  <p><b>Preparation of 0.1 N KCl solution:</b> ${formatTextWithBreaks(form.preparation_01n.value || '')}</p>
  <p><b>Preparation of 0.01 N KCl solution:</b> ${formatTextWithBreaks(form.preparation_001n.value || '')}</p>

  <h3>Definitions</h3>
  <p><b>Conductivity:</b> ${formatTextWithBreaks(form.conductivity_def.value || '')}</p>
  <p><b>Cell Constant:</b> ${formatTextWithBreaks(form.cell_constant_def.value || '')}</p>
  <p><b>Specific Conductance:</b> ${formatTextWithBreaks(form.specific_conductance_def.value || '')}</p>

  <p><b>Cell Constant = Specific Conductance (ohm⁻¹Cm⁻¹) or Conductivity / Measured conductance (ohm⁻¹)</b></p>
  <p>Thus, the unit of cell constant is Cm⁻¹.</p>

  <h3>Procedure</h3>
  <p>${formatTextWithBreaks(form.procedure.value || '')}</p>

  <h3>Observations</h3>
  <ul>
    <li>Conductance of distilled water: ${escapeHtml(form.conductance_water.value || '')}</li>
    <li>Conductance of 0.1 N KCl: ${escapeHtml(form.conductance_01n.value || '')}</li>
    <li>Conductance of 0.01 N KCl: ${escapeHtml(form.conductance_001n.value || '')}</li>
  </ul>

  <h3>Data Table</h3>
  <table border="1" style="width: 100%; border-collapse: collapse;">
    <tr>
      <th>Concentration of KCl</th>
      <th>Specific Conductance (Ohm⁻¹ cm⁻¹)</th>
      <th>Observed Conductance (Ohm⁻¹)</th>
    </tr>
    <tr>
      <td>0.1 N</td>
      <td>${escapeHtml(form.spec_cond_01n.value || '')}</td>
      <td>${escapeHtml(form.obs_cond_01n.value || '')}</td>
    </tr>
    <tr>
      <td>0.01 N</td>
      <td>${escapeHtml(form.spec_cond_001n.value || '')}</td>
      <td>${escapeHtml(form.obs_cond_001n.value || '')}</td>
    </tr>
  </table>

  <h3>Calculations</h3>
  <div style="border: 1px solid #333; padding: 15px; margin: 10px 0; background: #f9f9f9; border-radius: 6px;">
    <h4 style="text-align: left; margin: 0; color: #222;">Formulas:</h4>      
    
    <div style="margin-top: 20px;">
      <b style="display: block; text-align: left; margin-top: 20px;">Cell constant of 0.1N KCL =</b>
      
      <div style="text-align: center; margin-top: 10px;">
        <span style="display: inline-block; border-bottom: 2px solid #000; padding: 3px 12px;">
          Specific Conductance of 0.1N KCL
        </span>
        <br>
        <span style="display: inline-block; margin-top: 5px;">
          Observed Conductance of 0.1N KCL – Observed Conductance of Distilled Water
        </span>
      </div>
      
      <br>
      <b style="text-align: center; margin-top: 20px;">Cell constant of 0.1N KCL = ${escapeHtml(form.cell_constant_01N?.value || '')}</b>
      <br><br>
      
      <b style="display: block; text-align: left; margin-top: 20px;">Cell constant of 0.01N KCL =</b>
      <div style="text-align: center; margin-top: 10px;">
        <span style="display: inline-block; border-bottom: 2px solid #000; padding: 3px 12px;">
          Specific Conductance of 0.01N KCL
        </span>
        <br>
        <span style="display: inline-block; margin-top: 5px;">
          Observed Conductance of 0.01N KCL – Observed Conductance of Distilled Water
        </span>
      </div>
      
      <br>
      <b style="text-align: center; margin-top: 20px;">Cell constant of 0.01N KCL = ${escapeHtml(form.cell_constant_001N?.value || '')}</b>
    </div>
  </div>

  <h3>Result</h3>
  <p>
    1. Conductance of 0.1N KCL = ${escapeHtml(form.result_1?.value || '')}<br>
    2. Conductance of 0.01N KCL = ${escapeHtml(form.result_2?.value || '')}<br>
    3. Cell constant of 0.1N KCL = ${escapeHtml(form.result_3?.value || '')}<br>
    4. Cell constant of 0.01N KCL = ${escapeHtml(form.result_4?.value || '')}
  </p>
</div>`;

  const win = window.open('', '_blank', 'width=900,height=800');
  win.document.write('<!DOCTYPE html><html><head><title>Preview - Experiment 7</title><meta charset="utf-8"></head><body style="font-family: Arial, sans-serif; padding: 20px;">');
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

    // ---------- Submit Experiment ----------
    async function submitExperiment() { 
        console.log('Submit button clicked - starting submission');
        
        // Show confirmation dialog
        const shouldSubmit = await confirmSubmit();
        if (!shouldSubmit) {
            console.log('Submission cancelled by user');
            return;
        }
        
        const form = document.getElementById('exp7-form');
        if (!form) {
            alert('Error: Form not found!');
            return;
        }
        
        const subject = 'Chemistry';
        const experiment_number = 7;

        // Get retake parameters if this is a retake
        const urlParams = new URLSearchParams(window.location.search);
        const retakeId = urlParams.get('retake_id');
        const isRetake = urlParams.get('is_retake');
        const retakeCount = urlParams.get('retake_count') || 0;
            
        // Validation
        if (!form.expDate.value.trim()) {
            alert("Please enter Date.");
            return;
        }

  
  if (!form.aim.value.trim() || !form.chemicals.value.trim() || 
      !form.procedure.value.trim()) {
    alert("Please fill all required fields.");
    return;
  }

  const apparatusList = Array.from(document.querySelectorAll("#apparatus-dropbox .tool-item"))
    .map(el => el.textContent.trim());

  if (apparatusList.length === 0) {
    alert("Please add at least one apparatus.");
    return;
  }

  // Use the same template as preview
  const submissionHtml = `
<div>
  <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
    <div><b>Experiment No.:7</b></div>
    <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
  </div>
  <h2 style="text-align: center;">Determination of Cell Constant and Conductance of Solution</h2>

  <p><b>Aim:</b> ${formatTextWithBreaks(form.aim.value || '')}</p>
  <p><b>Apparatus Used:</b> ${apparatusList.length ? escapeHtml(apparatusList.join(", ")) : '—'}</p>
  <p><b>Chemicals Required:</b> ${formatTextWithBreaks(form.chemicals.value || '')}</p>

  <h3>Preparation of Solutions</h3>
  <p><b>Preparation of 0.1 N KCl solution:</b> ${formatTextWithBreaks(form.preparation_01n.value || '')}</p>
  <p><b>Preparation of 0.01 N KCl solution:</b> ${formatTextWithBreaks(form.preparation_001n.value || '')}</p>

  <h3>Definitions</h3>
  <p><b>Conductivity:</b> ${formatTextWithBreaks(form.conductivity_def.value || '')}</p>
  <p><b>Cell Constant:</b> ${formatTextWithBreaks(form.cell_constant_def.value || '')}</p>
  <p><b>Specific Conductance:</b> ${formatTextWithBreaks(form.specific_conductance_def.value || '')}</p>

  <p><b>Cell Constant = Specific Conductance (ohm⁻¹Cm⁻¹) or Conductivity / Measured conductance (ohm⁻¹)</b></p>
  <p>Thus, the unit of cell constant is Cm⁻¹.</p>

  <h3>Procedure</h3>
  <p>${formatTextWithBreaks(form.procedure.value || '')}</p>

  <h3>Observations</h3>
  <ul>
    <li>Conductance of distilled water: ${escapeHtml(form.conductance_water.value || '')}</li>
    <li>Conductance of 0.1 N KCl: ${escapeHtml(form.conductance_01n.value || '')}</li>
    <li>Conductance of 0.01 N KCl: ${escapeHtml(form.conductance_001n.value || '')}</li>
  </ul>

  <h3>Data Table</h3>
  <table border="1" style="width: 100%; border-collapse: collapse;">
    <tr>
      <th>Concentration of KCl</th>
      <th>Specific Conductance (Ohm⁻¹ cm⁻¹)</th>
      <th>Observed Conductance (Ohm⁻¹)</th>
    </tr>
    <tr>
      <td>0.1 N</td>
      <td>${escapeHtml(form.spec_cond_01n.value || '')}</td>
      <td>${escapeHtml(form.obs_cond_01n.value || '')}</td>
    </tr>
    <tr>
      <td>0.01 N</td>
      <td>${escapeHtml(form.spec_cond_001n.value || '')}</td>
      <td>${escapeHtml(form.obs_cond_001n.value || '')}</td>
    </tr>
  </table>

  <h3>Calculations</h3>
  <div style="border: 1px solid #333; padding: 15px; margin: 10px 0; background: #f9f9f9; border-radius: 6px;">
    <h4 style="text-align: left; margin: 0; color: #222;">Formulas:</h4>      
    
    <div style="margin-top: 20px;">
      <b style="display: block; text-align: left; margin-top: 20px;">Cell constant of 0.1N KCL =</b>
      
      <div style="text-align: center; margin-top: 10px;">
        <span style="display: inline-block; border-bottom: 2px solid #000; padding: 3px 12px;">
          Specific Conductance of 0.1N KCL
        </span>
        <br>
        <span style="display: inline-block; margin-top: 5px;">
          Observed Conductance of 0.1N KCL – Observed Conductance of Distilled Water
        </span>
      </div>
      
      <br>
      <b style="text-align: center; margin-top: 20px;">Cell constant of 0.1N KCL = ${escapeHtml(form.cell_constant_01N?.value || '')}</b>
      <br><br>
      
      <b style="display: block; text-align: left; margin-top: 20px;">Cell constant of 0.01N KCL =</b>
      <div style="text-align: center; margin-top: 10px;">
        <span style="display: inline-block; border-bottom: 2px solid #000; padding: 3px 12px;">
          Specific Conductance of 0.01N KCL
        </span>
        <br>
        <span style="display: inline-block; margin-top: 5px;">
          Observed Conductance of 0.01N KCL – Observed Conductance of Distilled Water
        </span>
      </div>
      
      <br>
      <b style="text-align: center; margin-top: 20px;">Cell constant of 0.01N KCL = ${escapeHtml(form.cell_constant_001N?.value || '')}</b>
    </div>
  </div>

  <h3>Result</h3>
  <p>
    1. Conductance of 0.1N KCL = ${escapeHtml(form.result_1?.value || '')}<br>
    2. Conductance of 0.01N KCL = ${escapeHtml(form.result_2?.value || '')}<br>
    3. Cell constant of 0.1N KCL = ${escapeHtml(form.result_3?.value || '')}<br>
    4. Cell constant of 0.01N KCL = ${escapeHtml(form.result_4?.value || '')}
  </p>
</div>`;

 const postData = new URLSearchParams();
            postData.append('subject', subject);
            postData.append('experiment_number', experiment_number);
            postData.append('submission_data', submissionHtml);

      // Add retake parameters if this is a retake
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
                    alert(data.message);
                    
                    // Clear the form
                    form.reset();
                    // Clear apparatus dropbox
                    const dropZone = document.getElementById('apparatus-dropbox');
                    const placeholder = document.getElementById('apparatus-placeholder');
                    if (dropZone) {
                        const toolItems = dropZone.querySelectorAll('.tool-item');
                        toolItems.forEach(item => item.remove());
                        if (placeholder) {
                            placeholder.style.display = 'inline';
                        }
                    }
                    
                if (data.is_retake) {
                  // Redirect back to retake page with success message
                  window.location.href = '../../retake_exp.php?retake_success=1';
                } else {
                    // Redirect to experiments list
                    setTimeout(() => {
                        window.location.href = '../../updated_exp.php?subject=Chemistry';
                    }, 1500);
                }
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Fetch error:', err);
                
                // Reset button
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                
                alert('Error submitting experiment. Please try again.');
            });
        }
  </script>
</body>
</html>