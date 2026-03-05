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
  <title>Experiment 2: WHIRLING OF SHAFT</title>
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
    <!-- Main Form -->
   <form id="exp2-form" method="post" class="form-section">
      <!-- Experiment Header -->
      <div class="exp-header">
        <div style="display:flex;flex-direction:column;">
          <label for="expNo">Experiment No. 2</label>
          
                    <input type="hidden" id="subject" name="subject" value="Theory of Machines">
          <input type="hidden" id="experiment_number" name="experiment_number" value="2">
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

      <h2 style="font-size: 25px;">WHIRLING OF SHAFT</h2>

      <label for="aim">Aim</label>
      <textarea id="aim" name="aim" rows="3" placeholder="Enter experiment aim"></textarea>

      <label>Apparatus Used (Drag and Drop)</label>
      <div id="apparatus-dropbox" class="apparatus-dropbox" aria-label="Apparatus dropbox">
        <small id="apparatus-placeholder" style="color:#777;">Drag apparatus here</small>
      </div>
      <input type="hidden" id="apparatus_list" name="apparatus_list" value="">
      
      <h3>Theory:</h3>
      <textarea id="theory" name="theory" rows="4" placeholder="Enter theory here..."></textarea>

      <h3>Procedure :- </h3>
      <textarea id="procedure" name="procedure" rows="4" placeholder="Enter Procedure "></textarea>

      <h4>Tabular Form</h4>
      <table>
        <thead>
          <tr>
            <th rowspan="2">S.No</th>
            <th>Diameter of the <br> draft(d) in mm</th>
            <th>End Condition</th>
            <th>Whirling Speed<br> In (rpm)</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td><input type="text" name="diameter1" id="diameter1"></td>
            <td><input type="text" name="end_condition1" id="end_condition1"></td>
            <td><input type="text" name="whirling_speed1" id="whirling_speed1"></td>
          </tr>
        </tbody>
      </table>
      
      <br>
      <h4>Formula :</h4>
      <div class="calc-row">
        <label for="calc_std_n" style="text-align: left;">Fundamental frequency of transverse vibrations(f) =  f = √(E . I . g / W . L<sup>4</sup>) =</label><br><br><br>
        <textarea name="Fundamental_frequency" id="Fundamental_frequency" placeholder="Calculations Here "></textarea>
        <label for="calc_std_n">Fundamental frequency of transverse vibrations(f) =</label>
        <input type="text" id="calc_std_n" name="calc_std_n" /><p><b> Hz</b></p><br>
        <label for="calc_std_n1">Length of the Shaft (L) =</label>
        <input type="text" id="calc_std_n1" name="calc_std_n1" /><p><b>m</b></p>
      </div>
      
      <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
        <p style="text-align: center; margin: 0;"><b>Theoretical Critical speed = f * 60 =</b></p>
        <input type="text" id="theoretical_critical_speed" name="theoretical_critical_speed" style="width: 110px;" />
        <p><b>rpm</b></p>
      </div>
      
      <h3>Precautions:</h3>
      <textarea id="precautions" name="precautions" rows="3" placeholder="Enter precautions here..."></textarea>
      
      <h3>Result:</h3>
      <textarea id="result" name="result" rows="3" placeholder="Write final conclusion"></textarea>
      
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
          <button type="button" class="apparatus-btn">Shaft</button>
          <button type="button" class="apparatus-btn">Variable speed motor</button>
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
    const form = document.getElementById('exp2-form');
    
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
    const form = document.getElementById('exp2-form');
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
    .calc-row {
        margin: 10px 0;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
</style>
<div class="header-row">
    <div><b>Experiment No.:</b> 2</div>
    <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
</div>
<h2 style="text-align:center; margin-top: 0;">WHIRLING OF SHAFT</h2>

<p><b>Aim:</b> ${formatTextWithBreaks(form.aim.value || '')}</p>
<p><b>Apparatus Used:</b> ${apparatusList.length ? escapeHtml(apparatusList.join(", ")) : '—'}</p>

<h3>Theory:</h3>
<p>${formatTextWithBreaks(form.theory.value || '')}</p>

<h3>Procedure:</h3>
<p>${formatTextWithBreaks(form.procedure.value || '')}</p>

<h4>Tabular Form</h4>
<table border="1" cellspacing="0" cellpadding="5" style="width:100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th rowspan="2">S.No</th>
            <th>Diameter of the<br>draft(d) in mm</th>
            <th>End Condition</th>
            <th>Whirling Speed<br>In (rpm)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>${escapeHtml(document.getElementById('diameter1').value || '')}</td>
            <td>${escapeHtml(document.getElementById('end_condition1').value || '')}</td>
            <td>${escapeHtml(document.getElementById('whirling_speed1').value || '')}</td>
        </tr>
    </tbody>
</table>

<br>
<h4>Formula:</h4>
<div class="calc-row">
    <p><b>Fundamental frequency of transverse vibrations(f) = f = √(E . I . g / W . L<sup>4</sup>) =</b></p>
    <p>${formatTextWithBreaks(form.Fundamental_frequency.value || '')}</p>
    <p><b>Fundamental frequency of transverse vibrations(f) = ${escapeHtml(form.calc_std_n.value || '')} Hz</b></p>
    <p><b>Length of the Shaft (L) = ${escapeHtml(form.calc_std_n1.value || '')} m</b></p>
</div>

<div style="text-align: center; margin: 15px 0;">
    <p><b>Theoretical Critical speed = f * 60 = ${escapeHtml(form.theoretical_critical_speed.value || '')} rpm</b></p>
</div>

<h3>Precautions:</h3>
<p>${formatTextWithBreaks(form.precautions.value || '')}</p>

<h3>Result:</h3>
<p>${formatTextWithBreaks(form.result.value || '')}</p>`;

    const win = window.open('', '_blank', 'width=900,height=800');
    win.document.write('<!DOCTYPE html><html><head><title>Preview - WHIRLING OF SHAFT</title><meta charset="utf-8"></head><body style="font-family:Arial,sans-serif; padding:20px;">');
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
    
    const form = document.getElementById('exp2-form');
    const subject = 'Theory of Machines';
    const experiment_number = 2;
    
    // Get retake parameters if this is a retake
    const urlParams = new URLSearchParams(window.location.search);
    const retakeId = urlParams.get('retake_id');
    const isRetake = urlParams.get('is_retake');
    const retakeCount = urlParams.get('retake_count') || 0;
    
    // Validation
    if (!form.aim.value.trim() || !form.procedure.value.trim() || 
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
    .calc-row {
        margin: 10px 0;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
</style>
<div class="header-row">
    <div><b>Experiment No.:</b> 2</div>
    <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
</div>
<h2 style="text-align:center; margin-top: 0;">WHIRLING OF SHAFT</h2>

<p><b>Aim:</b> ${formatTextWithBreaks(form.aim.value || '')}</p>
<p><b>Apparatus Used:</b> ${apparatusList.length ? escapeHtml(apparatusList.join(", ")) : '—'}</p>

<h3>Theory:</h3>
<p>${formatTextWithBreaks(form.theory.value || '')}</p>

<h3>Procedure:</h3>
<p>${formatTextWithBreaks(form.procedure.value || '')}</p>

<h4>Tabular Form</h4>
<table border="1" cellspacing="0" cellpadding="5" style="width:100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th rowspan="2">S.No</th>
            <th>Diameter of the<br>draft(d) in mm</th>
            <th>End Condition</th>
            <th>Whirling Speed<br>In (rpm)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>${escapeHtml(document.getElementById('diameter1').value || '')}</td>
            <td>${escapeHtml(document.getElementById('end_condition1').value || '')}</td>
            <td>${escapeHtml(document.getElementById('whirling_speed1').value || '')}</td>
        </tr>
    </tbody>
</table>

<br>
<h4>Formula:</h4>
<div class="calc-row">
    <p><b>Fundamental frequency of transverse vibrations(f) = f = √(E . I . g / W . L<sup>4</sup>) =</b></p>
    <p>${formatTextWithBreaks(form.Fundamental_frequency.value || '')}</p>
    <p><b>Fundamental frequency of transverse vibrations(f) = ${escapeHtml(form.calc_std_n.value || '')} Hz</b></p>
    <p><b>Length of the Shaft (L) = ${escapeHtml(form.calc_std_n1.value || '')} m</b></p>
</div>

<div style="text-align: center; margin: 15px 0;">
    <p><b>Theoretical Critical speed = f * 60 = ${escapeHtml(form.theoretical_critical_speed.value || '')} rpm</b></p>
</div>

<h3>Precautions:</h3>
<p>${formatTextWithBreaks(form.precautions.value || '')}</p>

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