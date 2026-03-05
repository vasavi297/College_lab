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
  <title>Experiment: Natural Frequency of Single Degree Undamped Free Vibrations</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  
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
    body {
  font-family: Arial, sans-serif;
  background-color: #f0f2f5;
  margin: 0;
  padding: 20px;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  min-height: 100vh;
}

.container {
  display: flex;
  width: 100%;
  max-width: 1440px;
  background-color: #ffffff;
  border: 1px solid #dcdcdc;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  overflow: hidden;
  padding: 32px;
  box-sizing: border-box;
  gap: 24px;
}


    /* Head Section */
    h2 {
      color: #112644;
      margin-bottom: 13px;
      font-weight: 600;
      font-size: 2.1rem;
      text-align: center;
      letter-spacing: 0.03em;
    }
    h3, h4 {
      margin-top: 18px;
      font-size: 1.2rem;
      font-weight: 600;
    }
    .exp-header {
  display: flex;
  gap: 34px;
  align-items: flex-end;
  margin-bottom: 24px;
}

.exp-header label {
  font-weight: 700;
  margin-bottom: 7px;
  font-size: 1.11rem;
  color: #182642;
}
.exp-header input[type="text"],
.exp-header input[type="date"] {
  font-family: inherit;
  font-size: 1rem;
  border-radius: 8px;
  border: 1.5px solid #ccd6ec;
  padding: 7px 12px;
  background: #f8fafd;
  margin-bottom: 0;
  margin-top: 3px;
  width: 200px; /* or 210px to match your text inputs */
  height: 36px;
  box-sizing: border-box;
  transition: border-color 0.2s;
}
.exp-header input[type="date"] {
  min-width: 170px;
}
.exp-header input[type="text"]:focus,
.exp-header input[type="date"]:focus {
  outline: none;
  border-color: #3460d1;
  background: #fff;
}

    label {
      font-size: 1rem;
      font-weight: 600;
      margin-bottom: 7px;
      display: block;
    }

    input[type="text"],
    textarea {
      font-family: inherit;
      font-size: 1rem;
      border-radius: 8px;
      border: 1.5px solid #ccd6ec;
      padding: 7px 12px;
      background: #f8fafd;
      margin-bottom: 16px;
      width: 100%;
      box-sizing: border-box;
    }

    input[type="text"]:focus,
    textarea:focus {
      outline: none;
      border-color: #3460d1;
      background: #fff;
    }

    /* Apparatus Buttons */
    .apparatus-list {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 9px;
      margin-top: 5px;
    }
    .apparatus-btn {
      background: #1a347a;
      color: #fff;
      border: none;
      border-radius: 16px;
      height: 36px;
      font-size: 0.88rem;
      font-weight: 600;
      width: 98%;
      cursor: pointer;
      box-shadow: 0 2px 7px #c7d5f9;
      transition: background 0.15s;
      padding: 0 10px;
    }
    .apparatus-btn:hover {
      background: #234ab7;
    }

    /* Calculator Section */
    .calculator-box {
      background: #f6f8fc;
      border-radius: 16px;
      padding: 16px;
      margin-top: 15px;
      box-shadow: 0 2px 10px #d6e5fc;
    }

    #calc-display {
      width: 100%;
      height: 38px;
      font-size: 1.3rem;
      margin-bottom: 12px;
      border-radius: 8px;
      border: 1.5px solid #ccd6ec;
      font-weight: 600;
      text-align: right;
      background: #fff;
      padding: 0 11px;
      box-sizing: border-box;
    }

    .calc-buttons {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 8px;
    }
    .calc-btn {
      background: #f0f4fa;
      color: #1a1a1a;
      border: none;
      font-size: 1.1rem;
      border-radius: 10px;
      height: 38px;
      width: 100%;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.14s;
      box-shadow: 0 1px 4px #e4eaf4;
      padding: 0;
    }
    .calc-btn:hover {
      background: #e0e9ff;
    }
    .calc-red {
      background: #ef4444;
      color: #fff;
    }
    .calc-red:hover {
      background: #b91c1c;
    }
    .calc-equal {
      background: #1a347a;
      color: #fff;
    }
    .calc-equal:hover {
      background: #234ab7;
    }

    /* Verify & Submit Button group */
    .btn-group {
      display: flex;
      justify-content: flex-end;
      gap: 18px;
      margin-top: 11px;
    }
    .btn-group button {
      border: none;
      border-radius: 12px;
      padding: 0 36px;
      height: 38px;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      transition: background 0.18s;
    }
    .btn-group .verify-btn {
      background: #5396ff;
      color: #fff;
    }
    .btn-group .verify-btn:hover {
      background: #2f6fd2;
    }
    .btn-group .submit-btn {
      background: #1a347a;
      color: #fff;
    }
    .btn-group .submit-btn:hover {
      background: #234ab7;
    }

    /* Bottom Margin and Clean Layout */
    .form-section {
      flex: 2;
      margin-right: 8px;
      width: 100%;
    }
    .sidebar {
      flex: 0 0 240px;
      min-width: 240px;
      background: #fcfcff;
      border-radius: 16px;
      padding: 16px 12px;
      box-shadow: 0 2px 10px #eaf3ff;
      margin-top: 0;
    }

    /* For Extra Cleanliness */
    table {
      font-size: 0.99rem;
      border-collapse: collapse;
      width: 100%;
      margin: 14px 0 18px 0;
    }
    th, td {
      border: 1px solid #e4eaf4;
      padding: 7px 9px;
      text-align: center;
    }
    th {
      background: #f8fafd;
      font-weight: 700;
    }

    /* Calculation Box Styles - FIXED */
    .calculation-box {
      background: #f6f8fc;
      border-radius: 16px;
      padding: 20px;
      margin: 20px 0;
      box-shadow: 0 2px 10px #d6e5fc;
      text-align: center;
      width: 100%;
      box-sizing: border-box;
    }

    .calc-row {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-bottom: 12px;
      flex-wrap: wrap;
    }

    .calc-row label {
      margin-right: 10px;
      margin-bottom: 0;
      min-width: 250px;
      text-align: right;
    }

    .calc-row input[type="text"] {
      width: 150px;
      margin-bottom: 0;
    }

    .formula-text {
      font-weight: 600;
      margin: 15px 0;
      font-size: 1.1rem;
      color: #1a347a;
    }

    .result-text {
      margin-top: 15px;
      font-weight: 600;
      background: #e8edff;
      padding: 10px;
      border-radius: 8px;
      display: inline-block;
    }

    /* Apparatus Dropbox */
    .apparatus-dropbox {
      min-height: 60px;
      border: 2px dashed #ccd6ec;
      border-radius: 8px;
      padding: 10px;
      margin-bottom: 16px;
      background: #f8fafd;
    }

    .tool-item {
      display: inline-block;
      background: #1a347a;
      color: white;
      padding: 5px 12px;
      margin: 5px;
      border-radius: 16px;
      font-size: 0.9rem;
      cursor: pointer;
    }

    .tool-item:hover {
      background: #234ab7;
    }

    @media (max-width: 950px) {
      body {
        padding: 10px;
      }
      
      .container {
        flex-direction: column;
        padding: 14px;
        gap: 0;
      }
      .form-section, .sidebar {
        margin-right: 0;
        margin-top: 0;
      }
      
      .calc-row {
        flex-direction: column;
        align-items: center;
      }
      
      .calc-row label {
        text-align: center;
        margin-right: 0;
        margin-bottom: 5px;
      }
    }
</style>
</head>
<body>
  <div class="container">
    <!-- Main Form -->
   <form id="exp3-form" method="post" class="form-section">
      <!-- Experiment Header -->
      <div class="exp-header">
        <div style="display:flex;flex-direction:column;">
          <label for="expNo">Experiment No. 3</label>
          
          <input type="hidden" id="subject" name="subject" value="Theory of Machines">
          <input type="hidden" id="experiment_number" name="experiment_number" value="3"> 
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

      <h2 style="font-size: 24px;">NATURAL FREQUENCY OF SINGLE DEGREE UNDAMPED FREE VIBRATIONS</h2>

      <label for="aim">Aim:</label>
      <textarea id="aim" name="aim" rows="3" placeholder="Enter experiment aim"></textarea>

      <label>Apparatus: (Drag and Drop)</label>
      <div id="apparatus-dropbox" class="apparatus-dropbox" aria-label="Apparatus dropbox">
        <small id="apparatus-placeholder" style="color:#777;">Drag apparatus here</small>
      </div>
      <input type="hidden" id="apparatus_list" name="apparatus_list" value="">

      <label for="theory">Theory:</label>
      <textarea id="theory" name="theory" rows="4" placeholder="Enter theory explanation"></textarea>

      <label for="definitions">Definitions:</label>
      <textarea id="definitions" name="definitions" rows="4" placeholder="Free (natural) vibrations, Damped vibrations..."></textarea>

      <label for="Procedure">Procedure:</label>
      <textarea id="Procedure" name="Procedure" rows="4" placeholder="Enter Procedure"></textarea>

      <h3>Observation Table:</h3>
      <table>
        <tr>
          <th>S.No</th>
          <th>Mass (kg)</th>
          <th>Deflection (m)</th>
          <th>Stiffness (k)</th>
          <th>Time for 10 Oscillations (sec)</th>
          <th>Theoretical Frequency (Hz)</th>
          <th>Actual Frequency (Hz)</th>
        </tr>
        <tr>
          <td>1</td>
          <td><input type="text" name="m1" id="m1" /></td>
          <td><input type="text" name="d1" id="d1" /></td>
          <td><input type="text" name="k1" id="k1" /></td>
          <td><input type="text" name="t1" id="t1" /></td>
          <td><input type="text" name="f_theo1" id="f_theo1" /></td>
          <td><input type="text" name="f_act1" id="f_act1" /></td>
        </tr>
        <tr>
          <td>2</td>
          <td><input type="text" name="m2" id="m2" /></td>
          <td><input type="text" name="d2" id="d2" /></td>
          <td><input type="text" name="k2" id="k2" /></td>
          <td><input type="text" name="t2" id="t2" /></td>
          <td><input type="text" name="f_theo2" id="f_theo2" /></td>
          <td><input type="text" name="f_act2" id="f_act2" /></td>
        </tr>
        <tr>
          <td>3</td>
          <td><input type="text" name="m3" id="m3" /></td>
          <td><input type="text" name="d3" id="d3" /></td>
          <td><input type="text" name="k3" id="k3" /></td>
          <td><input type="text" name="t3" id="t3" /></td>
          <td><input type="text" name="f_theo3" id="f_theo3" /></td>
          <td><input type="text" name="f_act3" id="f_act3" /></td>
        </tr>
      </table><br>

      <div style="font-family:Arial, sans-serif; border:1px solid #333; padding:15px; width:90%; margin:10px auto; background:#f9f9f9; border-radius:6px;">
        <h3 style="text-align:left; margin:0; color:#222;">Formulas :</h3>  
        <h4 style="text-align: left;"> Stiffness  K = (m*g)/δ </h4>
        <h4 style="text-align: left;"> Theoretical Frequency  f = (1/2π)√(k/m) </h4>
        <h4 style="text-align: left;"> Actual Frequency  f = 10/t </h4> 
        <h3>Calculations</h3>   
        <textarea id="calculations" name="calculations" rows="4" placeholder="Enter calculations here..."></textarea>
      </div>

      <h3>Result:</h3>
      <textarea id="result" name="result" rows="3" placeholder="Final result of natural frequency"></textarea>
 
       <div class="btn-group">
        <button type="button" onclick="previewExp()" style="cursor:pointer; background:#007bff; color:#fff; font-weight:600; padding:8px 16px; border-radius:6px; width: fit-content;">Preview</button>
        <button type="button" onclick="submitExperiment()" id="submitBtn" style="cursor:pointer; background:#1a347a; color:#fff; font-weight:600; padding:8px 16px; border-radius:6px; width: fit-content;">Submit</button>
      </div>
    </form>

    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="apparatus-box">
        <h3 style="font-size:22px;margin-bottom:12px;text-align:center;">Select Apparatus</h3>
        <div class="apparatus-list">
          <button type="button" class="apparatus-btn">Spring with pan</button>
          <button type="button" class="apparatus-btn">Weights</button>
          <button type="button" class="apparatus-btn">Steel rule</button>
          <button type="button" class="apparatus-btn">Stopwatch</button>
          <button type="button" class="apparatus-btn">Retort Stand</button>
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
// ---------- Calculator Variables ----------
let hasDecimal = false;

// ---------- Calculator Functions ----------
function press(value) {
    const display = document.getElementById('calc-display');
    
    if (['+', '-', '*', '/'].includes(value)) {
        hasDecimal = false;
    }
    
    display.value += value;
}

function addDecimal() {
    const display = document.getElementById('calc-display');
    
    if (!hasDecimal) {
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

// Prevent form submission on Enter key except for textareas
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('exp3-form');
    
    form.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
        }
    });
});

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
document.addEventListener("cheking tab switces", () => {
  if (document.hidden) { console.log("tab_switched");
  }
});



document.addEventListener("fullscreen", () => {
  if (!document.fullscreenElement) {console.log("exit full screen");
  }
});

// ---------- Preview ----------
function previewExp() {
    const form = document.getElementById('exp3-form');
    const apparatusList = Array.from(document.querySelectorAll("#apparatus-dropbox .tool-item"))
        .map(el => el.textContent.trim());

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
    <div><b>Experiment No.:</b> 3</div>
    <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
</div>
<h2 style="text-align:center; margin-top: 0;">NATURAL FREQUENCY OF SINGLE DEGREE UNDAMPED FREE VIBRATIONS</h2>

<p><b>Aim:</b> ${formatTextWithBreaks(form.aim.value || '')}</p>
<p><b>Apparatus Used:</b> ${apparatusList.length ? escapeHtml(apparatusList.join(", ")) : '—'}</p>

<h3>Theory:</h3>
<p>${formatTextWithBreaks(form.theory.value || '')}</p>

<h3>Definitions:</h3>
<p>${formatTextWithBreaks(form.definitions.value || '')}</p>

<h3>Procedure:</h3>
<p>${formatTextWithBreaks(form.Procedure.value || '')}</p>

<h3>Observation Table:</h3>
<table border="1" cellspacing="0" cellpadding="5" style="width:100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th>S.No</th>
            <th>Mass (kg)</th>
            <th>Deflection (m)</th>
            <th>Stiffness (k)</th>
            <th>Time for 10 Oscillations (sec)</th>
            <th>Theoretical Frequency (Hz)</th>
            <th>Actual Frequency (Hz)</th>
        </tr>
    </thead>
    <tbody>
        ${Array.from({length: 3}, (_, i) => {
            const num = i + 1;
            return `
        <tr>
            <td>${num}</td>
            <td>${escapeHtml(document.getElementById('m' + num).value || '')}</td>
            <td>${escapeHtml(document.getElementById('d' + num).value || '')}</td>
            <td>${escapeHtml(document.getElementById('k' + num).value || '')}</td>
            <td>${escapeHtml(document.getElementById('t' + num).value || '')}</td>
            <td>${escapeHtml(document.getElementById('f_theo' + num).value || '')}</td>
            <td>${escapeHtml(document.getElementById('f_act' + num).value || '')}</td>
        </tr>
        `}).join('')}
    </tbody>
</table><br>

<div style="font-family:Arial, sans-serif; text-align:left;">
    <h3 style="margin:0; color:#222;">Formulas:</h3>   
    <p>Stiffness  K = (m*g)/δ</p>
    <p>Theoretical Frequency  f = (1/2π)√(k/m)</p>
    <p>Actual Frequency  f = 10/t</p> 

    <h3>Calculations:</h3>
    <p>${formatTextWithBreaks(form.calculations.value || '')}</p>
</div>

<h3>Result:</h3>
<p>${formatTextWithBreaks(form.result.value || '')}</p>`;

    const win = window.open('', '_blank', 'width=900,height=800');
    win.document.write('<!DOCTYPE html><html><head><title>Preview - Natural Frequency Experiment</title><meta charset="utf-8"></head><body style="font-family:Arial,sans-serif; padding:20px;">');
    win.document.write(previewHtml);
    win.document.write('</body></html>');
    win.document.close();
}

// ---------- Submit Experiment ----------
// ---------- Confirmation Dialog ----------
async function confirmSubmit() {
    return new Promise((resolve) => {
        const confirmed = confirm("Do you really want to submit this experiment?\nPlease review all your answers before submitting.\nClick OK to submit ");
        resolve(confirmed);
    });
}

// ---------- Submit Experiment ----------
async function submitExperiment() {
    // Show confirmation dialog
    const shouldSubmit = await confirmSubmit();
    if (!shouldSubmit) {
        return;
    }
    
    const form = document.getElementById('exp3-form');
  const subject = 'Theory of Machines';
    const experiment_number = 3; 
    
  // Get retake parameters if this is a retake
  const urlParams = new URLSearchParams(window.location.search);
  const retakeId = urlParams.get('retake_id');
  const isRetake = urlParams.get('is_retake');
  const retakeCount = urlParams.get('retake_count') || 0;
    
    // Validation
    if (!form.aim.value.trim() || !form.Procedure.value.trim() || 
        !form.result.value.trim()) {
        alert("Please fill all required fields: Aim, Procedure, and Result.");
        return;
    }

    const apparatusList = Array.from(document.querySelectorAll("#apparatus-dropbox .tool-item"))
        .map(el => el.textContent.trim());

    if (apparatusList.length === 0) {
        alert("Please add at least one apparatus.");
        return;
    }

    // Prepare submission data
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
    <div><b>Experiment No.:</b> 3</div>
    <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
</div>
<h2 style="text-align:center; margin-top: 0;">NATURAL FREQUENCY OF SINGLE DEGREE UNDAMPED FREE VIBRATIONS</h2>

<p><b>Aim:</b> ${formatTextWithBreaks(form.aim.value || '')}</p>
<p><b>Apparatus Used:</b> ${apparatusList.length ? escapeHtml(apparatusList.join(", ")) : '—'}</p>

<h3>Theory:</h3>
<p>${formatTextWithBreaks(form.theory.value || '')}</p>

<h3>Definitions:</h3>
<p>${formatTextWithBreaks(form.definitions.value || '')}</p>

<h3>Procedure:</h3>
<p>${formatTextWithBreaks(form.Procedure.value || '')}</p>

<h3>Observation Table:</h3>
<table border="1" cellspacing="0" cellpadding="5" style="width:100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th>S.No</th>
            <th>Mass (kg)</th>
            <th>Deflection (m)</th>
            <th>Stiffness (k)</th>
            <th>Time for 10 Oscillations (sec)</th>
            <th>Theoretical Frequency (Hz)</th>
            <th>Actual Frequency (Hz)</th>
        </tr>
    </thead>
    <tbody>
        ${Array.from({length: 3}, (_, i) => {
            const num = i + 1;
            return `
        <tr>
            <td>${num}</td>
            <td>${escapeHtml(document.getElementById('m' + num).value || '')}</td>
            <td>${escapeHtml(document.getElementById('d' + num).value || '')}</td>
            <td>${escapeHtml(document.getElementById('k' + num).value || '')}</td>
            <td>${escapeHtml(document.getElementById('t' + num).value || '')}</td>
            <td>${escapeHtml(document.getElementById('f_theo' + num).value || '')}</td>
            <td>${escapeHtml(document.getElementById('f_act' + num).value || '')}</td>
        </tr>
        `}).join('')}
    </tbody>
</table><br>

<div style="font-family:Arial, sans-serif; text-align:left;">
    <h3 style="margin:0; color:#222;">Formulas:</h3>   
    <p>Stiffness  K = (m*g)/δ</p>
    <p>Theoretical Frequency  f = (1/2π)√(k/m)</p>
    <p>Actual Frequency  f = 10/t</p> 

    <h3>Calculations:</h3>
    <p>${formatTextWithBreaks(form.calculations.value || '')}</p>
</div>

<h3>Result:</h3>
<p>${formatTextWithBreaks(form.result.value || '')}</p>`;
    
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
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn ? submitBtn.textContent : 'Submit';
    if (submitBtn) {
        submitBtn.textContent = 'Submitting...';
        submitBtn.disabled = true;
    }

    fetch('../../submit_experiment.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: postData.toString()
    })
    .then(res => {
        if (!res.ok) {
        throw new Error('Network response was not ok: ' + res.status);
        }
        return res.json();
    })
    .then(data => {
        // Reset button
        if (submitBtn) {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
        
        if (data.success) {
            alert(data.message);
        if (data.is_retake) {
          window.location.href = '../../retake_exp.php?retake_success=1';
        } else {
            setTimeout(() => {
                window.location.href = '../../updated_exp.php?subject=Theory%20of%20Machines';
            }, 1500);
        }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        if (submitBtn) {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
      alert('Error submitting experiment. Please try again.');
    });
}
  </script>
</body>
</html>