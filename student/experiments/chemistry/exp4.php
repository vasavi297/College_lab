<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Experiment 4: DETERMINATION OF STRENGTH OF AN ACID IN Pb-ACID BATTERY</title>
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
</head>
<body>
  <div class="container">
    <!-- Main Form -->
    <form id="exp4-form" method="post" class="form-section">

      <!-- Experiment Header -->
      <div class="exp-header">
        <div style="display:flex;flex-direction:column;">
          <label for="expNo">Experiment No.4</label>
          <input type="hidden" id="subject" name="subject" value="chemistry">
    <input type="hidden" id="experiment_number" name="experiment_number" value="4">
        </div>
        <div style="display:flex;flex-direction:column;">
          <label for="expDate">Date</label>
          <input type="date" id="expDate" name="expDate" required/>
        </div>
      </div>

      <h2 style="font-size: 30px;">DETERMINATION OF STRENGTH OF AN ACID IN Pb-ACID BATTERY</h2>

      <label for="aim">Aim:</label>
      <textarea id="aim" name="aim" rows="3" placeholder="Enter experiment aim" required></textarea>

      <label>Apparatus Used (Drag and Drop)</label>
      <div id="apparatus-dropbox" class="apparatus-dropbox" aria-label="Apparatus dropbox">
        <small id="apparatus-placeholder" style="color:#777;">Drag apparatus here</small>
      </div>
      <input type="hidden" id="apparatus_list" name="apparatus_list" value="">

      <label for="chemicals">Chemicals Required:</label>
      <textarea id="chemicals" name="chemicals" rows="3" placeholder="List chemicals" required></textarea>

      <label for="principle">Principle:</label>
      <textarea id="principle" name="principle" rows="3" placeholder="Explain principle" required></textarea>

      <label for="discharge_reactions">Discharge Reactions:</label>
      <textarea id="discharge_reactions" name="discharge_reactions" rows="3" placeholder="Explain Discharge Reactions" required></textarea>

      <label for="recharge_reactions">Recharge Reactions:</label>
      <textarea id="recharge_reactions" name="recharge_reactions" rows="3" placeholder="Explain Recharge Reactions" required></textarea>

      <h3>Procedure - Step I: Standardization of NaOH Solution</h3>
      <textarea id="procedure_a" name="procedure_a" rows="4" placeholder="Enter Procedure Part A" required></textarea>

      <h4>Standardization of NaOH Solution</h4>
      <table>
        <tr>
          <th rowspan="2">S.No</th>
          <th rowspan="2">Volume of Oxalic acid Solution(ml)</th>
          <th colspan="2">Burette Reading (ml)</th>
          <th rowspan="2">Volume of NaOH solution (ml)</th>
        </tr>
        <tr><th>Initial</th><th>Final</th></tr>
        <tr><td>1</td><td><input type="text" name="std_v1" /></td><td><input type="text" name="std_br_initial1" /></td><td><input type="text" name="std_br_final1" /></td><td><input type="text" name="std_vol_k2_1" /></td></tr>
        <tr><td>2</td><td><input type="text" name="std_v2" /></td><td><input type="text" name="std_br_initial2" /></td><td><input type="text" name="std_br_final2" /></td><td><input type="text" name="std_vol_k2_2" /></td></tr>
        <tr><td>3</td><td><input type="text" name="std_v3" /></td><td><input type="text" name="std_br_initial3" /></td><td><input type="text" name="std_br_final3" /></td><td><input type="text" name="std_vol_k2_3" /></td></tr>
      </table>

      <h3>Calculations:</h3>
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

      <h3>Step II: Determination of Strength of an Acid</h3>
      <textarea id="procedure_b" name="procedure_b" rows="4" placeholder="Enter Procedure Part B" required></textarea>

      <h4>Determination of Strength of an Acid</h4>
      <table>
        <tr>
          <th rowspan="2">S.No</th>
          <th rowspan="2">Volume of H<sub>2</sub>SO<sub>4</sub> Solution(ml)</th>
          <th colspan="2">Burette Readings(ml)</th>
          <th rowspan="2">Volume of NaOH Solution(ml)</th>
        </tr>
        <tr><th>Initial</th><th>Final</th></tr>
        <tr><td>1</td><td><input type="text" name="est_v1" /></td><td><input type="text" name="est_br_initial1" /></td><td><input type="text" name="est_br_final1" /></td><td><input type="text" name="est_vol_k2_1" /></td></tr>
        <tr><td>2</td><td><input type="text" name="est_v2" /></td><td><input type="text" name="est_br_initial2" /></td><td><input type="text" name="est_br_final2" /></td><td><input type="text" name="est_vol_k2_2" /></td></tr>
        <tr><td>3</td><td><input type="text" name="est_v3" /></td><td><input type="text" name="est_br_initial3" /></td><td><input type="text" name="est_br_final3" /></td><td><input type="text" name="est_vol_k2_3" /></td></tr>
      </table>
      
      <h3>Calculations:</h3>
      <div class="calculation-box">
        <div class="calc-row">
          <label for="calc_est_n1">N₂ = Normality of NaOH solution =</label>
          <input type="text" id="calc_est_n1" name="calc_est_n1" /><p><b>N</b></p>
        </div>
        <div class="calc-row">
          <label for="calc_est_n2">N₃ = Normality of H<sub>2</sub>SO<sub>4</sub> Solution =</label>
          <input type="text" id="calc_est_n2" name="calc_est_n2" />
        </div>
        <div class="calc-row">
          <label for="calc_est_v1">V₂ = Volume of NaOH solution =</label>
          <input type="text" id="calc_est_v1" name="calc_est_v1" /><p><b>ml</b></p>
        </div>
        <div class="calc-row">
          <label for="calc_est_v2">V₃ = Volume of H<sub>2</sub>SO<sub>4</sub> solution =</label>
          <input type="text" id="calc_est_v2" name="calc_est_v2" /><p><b>ml</b></p>
        </div>

        <div class="formula-text">Formula: N₂V₂ = N₃V₃</div>

        <div class="calc-row">
          <label for="calc_est_n3_calc">N₃ = (N₂V₂) / V₃ = </label>
          <input type="text" id="calc_est_n3_calc" name="calc_est_n3_calc" />
        </div>
        <div class="result-text">Normality of H<sub>2</sub>SO<sub>4</sub> solution (N₃) is <input type="text" id="calc_est_n3_final" name="calc_est_n3_final" style="width: 110px;" /> N.</div>
      </div>

      <label for="result">Result:</label>
      <textarea id="result" name="result" rows="3" placeholder="Write final conclusion" required></textarea>

      <div class="btn-group">
        
        <button type="button" onclick="previewExp()" style="cursor:pointer; background:#007bff; color:#fff; font-weight:600; padding:8px 16px; border-radius:6px; width: fit-content;">Preview</button>
       <button type="button" onclick="submitExperiment()" style="cursor:pointer; background:#1a347a; color:#fff; font-weight:600; padding:8px 16px; border-radius:6px; width: fit-content;">Submit</button>
            </div>
    </form>

    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="apparatus-box">
        <h3 style="font-size:22px;margin-bottom:12px;text-align:center;">Select Apparatus</h3>
        <div class="apparatus-list">
          <button type="button" class="apparatus-btn">Burette</button>
          <button type="button" class="apparatus-btn">Conical Flask</button>
          <button type="button" class="apparatus-btn">Beaker</button>
          <button type="button" class="apparatus-btn">Burette Stand</button>
          <button type="button" class="apparatus-btn">Pipette</button>
          <button type="button" class="apparatus-btn">Wash Bottle</button>
          <button type="button" class="apparatus-btn">Micro Burette</button>
          <button type="button" class="apparatus-btn">Magnetic Stirrer</button>
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
      const form = document.getElementById('exp4-form');
      
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


    // ---------- Preview ----------
    function previewExp() {
      const form = document.getElementById('exp4-form');
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
  <div><b>Experiment No.:</b> 4</div>
  <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
</div>
<h2 style="text-align:center; margin-top: 0;">DETERMINATION OF STRENGTH OF AN ACID IN Pb-ACID BATTERY</h2>

<p><b>Aim:</b> ${formatTextWithBreaks(form.aim.value || '')}</p>
<p><b>Apparatus Used:</b> ${apparatusList.length ? escapeHtml(apparatusList.join(", ")) : '—'}</p>
<p><b>Chemicals Required:</b> ${formatTextWithBreaks(form.chemicals.value || '')}</p>
<p><b>Principle:</b> ${formatTextWithBreaks(form.principle.value || '')}</p>
<p><b>Discharge Reactions:</b> ${formatTextWithBreaks(form.discharge_reactions.value || '')}</p>
<p><b>Recharge Reactions:</b> ${formatTextWithBreaks(form.recharge_reactions.value || '')}</p>

<h3>Procedure : </h3>
<p><b>Step I: Standardization of NaOH Solution</b></p>
<p>${formatTextWithBreaks(form.procedure_a.value || '')}</p>

<table border="1" cellspacing="0" cellpadding="5" style="width:100%; border-collapse: collapse;">
  <tr><th rowspan="2">S.No</th><th rowspan="2">Volume of Oxalic acid Solution(ml)</th><th colspan="2">Burette Reading (ml)</th><th rowspan="2">Volume of NaOH solution (ml)</th></tr>
  <tr><th>Initial</th><th>Final</th></tr>
  <tr><td>1</td><td>${escapeHtml(form.std_v1.value || '')}</td><td>${escapeHtml(form.std_br_initial1.value || '')}</td><td>${escapeHtml(form.std_br_final1.value || '')}</td><td>${escapeHtml(form.std_vol_k2_1.value || '')}</td></tr>
  <tr><td>2</td><td>${escapeHtml(form.std_v2.value || '')}</td><td>${escapeHtml(form.std_br_initial2.value || '')}</td><td>${escapeHtml(form.std_br_final2.value || '')}</td><td>${escapeHtml(form.std_vol_k2_2.value || '')}</td></tr>
  <tr><td>3</td><td>${escapeHtml(form.std_v3.value || '')}</td><td>${escapeHtml(form.std_br_initial3.value || '')}</td><td>${escapeHtml(form.std_br_final3.value || '')}</td><td>${escapeHtml(form.std_vol_k2_3.value || '')}</td></tr>
</table>

<h3>Calculations :</h3>
<div>
  N₁: Normality of Oxalic Acid solution = ${escapeHtml(form.calc_std_n1.value || '')} N<br><br>
  N₂: Normality of NaOH solution = ${escapeHtml(form.calc_std_n2.value || '')}<br><br>
  V₁: Volume of Oxalic Acid solution = ${escapeHtml(form.calc_std_v1.value || '')}<br><br>
  V₂: Volume of NaOH solution = ${escapeHtml(form.calc_std_v2.value || '')}<br><br>
  Formula: N₁V₁ = N₂V₂<br><br>
  N₂ = (N₁V₁) / V₂ = ${escapeHtml(form.calc_std_n2_calc.value || '')}<br><br>
  Normality of NaOH solution (N₂) is ${escapeHtml(form.calc_std_n2_final.value || '')} N.
</div>


<p><b>Step II: Determination of Strength of an Acid</b></p>
<p>${formatTextWithBreaks(form.procedure_b.value || '')}</p>

<table border="1" cellspacing="0" cellpadding="5" style="width:100%; border-collapse: collapse;">
  <tr><th rowspan="2">S.No</th><th rowspan="2">Volume of H<sub>2</sub>SO<sub>4</sub> Solution(ml)</th><th colspan="2">Burette Reading (ml)</th><th rowspan="2">Volume of NaOH Solution(ml)</th></tr>
  <tr><th>Initial</th><th>Final</th></tr>
  <tr><td>1</td><td>${escapeHtml(form.est_v1.value || '')}</td><td>${escapeHtml(form.est_br_initial1.value || '')}</td><td>${escapeHtml(form.est_br_final1.value || '')}</td><td>${escapeHtml(form.est_vol_k2_1.value || '')}</td></tr>
  <tr><td>2</td><td>${escapeHtml(form.est_v2.value || '')}</td><td>${escapeHtml(form.est_br_initial2.value || '')}</td><td>${escapeHtml(form.est_br_final2.value || '')}</td><td>${escapeHtml(form.est_vol_k2_2.value || '')}</td></tr>
  <tr><td>3</td><td>${escapeHtml(form.est_v3.value || '')}</td><td>${escapeHtml(form.est_br_initial3.value || '')}</td><td>${escapeHtml(form.est_br_final3.value || '')}</td><td>${escapeHtml(form.est_vol_k2_3.value || '')}</td></tr>
</table>

<h3>Calculations :</h3>
<div>
  N₂: Normality of NaOH solution = ${escapeHtml(form.calc_est_n1.value || '')} N<br><br>
  N₃: Normality of H<sub>2</sub>SO<sub>4</sub> Solution = ${escapeHtml(form.calc_est_n2.value || '')}<br><br>
  V₂: Volume of NaOH solution = ${escapeHtml(form.calc_est_v1.value || '')} ml<br><br>
  V₃: Volume of H<sub>2</sub>SO<sub>4</sub> solution = ${escapeHtml(form.calc_est_v2.value || '')} ml<br><br>
  Formula: N₂V₂ = N₃V₃<br><br>
  N₃ = (N₂V₂) / V₃ = ${escapeHtml(form.calc_est_n3_calc.value || '')}<br><br>
  Normality of H<sub>2</sub>SO<sub>4</sub> solution (N₃) is ${escapeHtml(form.calc_est_n3_final.value || '')} N.
</div>

<h3>Result :</h3><p>${formatTextWithBreaks(form.result.value || '')}</p>`;

      const win = window.open('', '_blank', 'width=900,height=800');
      win.document.write('<!DOCTYPE html><html><head><title>Preview</title><meta charset="utf-8"></head><body style="font-family:Arial,sans-serif; padding:20px;">');
      win.document.write(previewHtml);
      win.document.write('</body></html>');
      win.document.close();
    }

    // ---------- Submit Experiment ----------
    function submitExperiment() {
      const form = document.getElementById('exp4-form');
         const subject = 'chemistry';
            const experiment_number = 4; // From your database
            const employee_id = '123';
      
      if (!form.aim.value.trim() || !form.chemicals.value.trim() || 
          !form.principle.value.trim() || !form.result.value.trim() ||
          !form.discharge_reactions.value.trim() || !form.recharge_reactions.value.trim()) {
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
   <div><b>Experiment No.:</b> 4</div>
  <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
</div>
<h2 style="text-align:center; margin-top: 0;">DETERMINATION OF STRENGTH OF AN ACID IN Pb-ACID BATTERY</h2>

<p><b>Aim:</b> ${formatTextWithBreaks(form.aim.value || '')}</p>
<p><b>Apparatus Used:</b> ${apparatusList.length ? escapeHtml(apparatusList.join(", ")) : '—'}</p>
<p><b>Chemicals Required:</b> ${formatTextWithBreaks(form.chemicals.value || '')}</p>
<p><b>Principle:</b> ${formatTextWithBreaks(form.principle.value || '')}</p>
<p><b>Discharge Reactions:</b> ${formatTextWithBreaks(form.discharge_reactions.value || '')}</p>
<p><b>Recharge Reactions:</b> ${formatTextWithBreaks(form.recharge_reactions.value || '')}</p>

<h3>Procedure : </h3>
<p><b>Step I: Standardization of NaOH Solution</b></p>
<p>${formatTextWithBreaks(form.procedure_a.value || '')}</p>

<table border="1" cellspacing="0" cellpadding="5" style="width:100%; border-collapse: collapse;">
  <tr><th rowspan="2">S.No</th><th rowspan="2">Volume of Oxalic acid Solution(ml)</th><th colspan="2">Burette Reading (ml)</th><th rowspan="2">Volume of NaOH solution (ml)</th></tr>
  <tr><th>Initial</th><th>Final</th></tr>
  <tr><td>1</td><td>${escapeHtml(form.std_v1.value || '')}</td><td>${escapeHtml(form.std_br_initial1.value || '')}</td><td>${escapeHtml(form.std_br_final1.value || '')}</td><td>${escapeHtml(form.std_vol_k2_1.value || '')}</td></tr>
  <tr><td>2</td><td>${escapeHtml(form.std_v2.value || '')}</td><td>${escapeHtml(form.std_br_initial2.value || '')}</td><td>${escapeHtml(form.std_br_final2.value || '')}</td><td>${escapeHtml(form.std_vol_k2_2.value || '')}</td></tr>
  <tr><td>3</td><td>${escapeHtml(form.std_v3.value || '')}</td><td>${escapeHtml(form.std_br_initial3.value || '')}</td><td>${escapeHtml(form.std_br_final3.value || '')}</td><td>${escapeHtml(form.std_vol_k2_3.value || '')}</td></tr>
</table>

<h3>Calculations :</h3>
<div>
  N₁: Normality of Oxalic Acid solution = ${escapeHtml(form.calc_std_n1.value || '')} N<br><br>
  N₂: Normality of NaOH solution = ${escapeHtml(form.calc_std_n2.value || '')}<br><br>
  V₁: Volume of Oxalic Acid solution = ${escapeHtml(form.calc_std_v1.value || '')}<br><br>
  V₂: Volume of NaOH solution = ${escapeHtml(form.calc_std_v2.value || '')}<br><br>
  Formula: N₁V₁ = N₂V₂<br><br>
  N₂ = (N₁V₁) / V₂ = ${escapeHtml(form.calc_std_n2_calc.value || '')}<br><br>
  Normality of NaOH solution (N₂) is ${escapeHtml(form.calc_std_n2_final.value || '')} N.
</div>


<p><b>Step II: Determination of Strength of an Acid</b></p>
<p>${formatTextWithBreaks(form.procedure_b.value || '')}</p>

<table border="1" cellspacing="0" cellpadding="5" style="width:100%; border-collapse: collapse;">
  <tr><th rowspan="2">S.No</th><th rowspan="2">Volume of H<sub>2</sub>SO<sub>4</sub> Solution(ml)</th><th colspan="2">Burette Reading (ml)</th><th rowspan="2">Volume of NaOH Solution(ml)</th></tr>
  <tr><th>Initial</th><th>Final</th></tr>
  <tr><td>1</td><td>${escapeHtml(form.est_v1.value || '')}</td><td>${escapeHtml(form.est_br_initial1.value || '')}</td><td>${escapeHtml(form.est_br_final1.value || '')}</td><td>${escapeHtml(form.est_vol_k2_1.value || '')}</td></tr>
  <tr><td>2</td><td>${escapeHtml(form.est_v2.value || '')}</td><td>${escapeHtml(form.est_br_initial2.value || '')}</td><td>${escapeHtml(form.est_br_final2.value || '')}</td><td>${escapeHtml(form.est_vol_k2_2.value || '')}</td></tr>
  <tr><td>3</td><td>${escapeHtml(form.est_v3.value || '')}</td><td>${escapeHtml(form.est_br_initial3.value || '')}</td><td>${escapeHtml(form.est_br_final3.value || '')}</td><td>${escapeHtml(form.est_vol_k2_3.value || '')}</td></tr>
</table>

<h3>Calculations :</h3>
<div>
  N₂: Normality of NaOH solution = ${escapeHtml(form.calc_est_n1.value || '')} N<br><br>
  N₃: Normality of H<sub>2</sub>SO<sub>4</sub> Solution = ${escapeHtml(form.calc_est_n2.value || '')}<br><br>
  V₂: Volume of NaOH solution = ${escapeHtml(form.calc_est_v1.value || '')} ml<br><br>
  V₃: Volume of H<sub>2</sub>SO<sub>4</sub> solution = ${escapeHtml(form.calc_est_v2.value || '')} ml<br><br>
  Formula: N₂V₂ = N₃V₃<br><br>
  N₃ = (N₂V₂) / V₃ = ${escapeHtml(form.calc_est_n3_calc.value || '')}<br><br>
  Normality of H<sub>2</sub>SO<sub>4</sub> solution (N₃) is ${escapeHtml(form.calc_est_n3_final.value || '')} N.
</div>

<h3>Result :</h3><p>${formatTextWithBreaks(form.result.value || '')}</p>`;

     const postData = new URLSearchParams();
            postData.append('subject', subject);
            postData.append('experiment_number', experiment_number);
            postData.append('employee_id', employee_id);
            postData.append('submission_data', submissionHtml);

            fetch('../../submit_experiment.php', {
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