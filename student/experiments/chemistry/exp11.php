<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Experiment 11 - MEASURMENT OF I0DQ BY SPECTROPHOTOMETRIC METHOD</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  
  <!-- External CSS -->
    <link rel="stylesheet" href="../../../css/experiments.css" />
  
  </style>
</head>
<body>
  <div class="container">
    <!-- Main Form -->
    <form id="exp11-form" method="post" class="form-section">

      <!-- Experiment Header -->
      <div class="exp-header">
        <div style="display:flex;flex-direction:column;">
          <label for="expNo">Experiment No.11</label>
          <input type="hidden" id="subject" name="subject" value="chemistry">
    <input type="hidden" id="experiment_number" name="experiment_number" value="11">
                </div>
        <div style="display:flex;flex-direction:column;">
          <label for="expDate">Date</label>
          <input type="date" id="expDate" name="expDate" />
        </div>
      </div>

      <h2>MEASURMENT OF I0DQ BY SPECTROPHOTOMETRIC METHOD</h2>

      <label for="aim">Aim</label>
      <textarea id="aim" name="aim" rows="3" placeholder="Enter experiment aim" style="width: 100%; display: block; overflow: auto; resize: none; padding: 10px; border: 1px solid #ccc; border-radius: 5px;" required></textarea>


      <label>Apparatus Used (Drag and Drop)</label>
      <div id="apparatus-dropbox" class="apparatus-dropbox" aria-label="Apparatus dropbox">
        <small id="apparatus-placeholder" style="color:#777;">Drag apparatus here</small>
      </div>
      <input type="hidden" id="apparatus_list" name="apparatus_list" value="">
      
      <label for="chemicals">Chemicals Required</label>
      <textarea id="chemicals" name="chemicals" rows="3" placeholder="List chemicals" style="width: 100%; display: block; overflow: auto; resize: none; padding: 10px; border: 1px solid #ccc; border-radius: 5px;"></textarea>

      <label for="preparation1">Preparation of Tri Ethylene Diamine Chromium(III)</label>
      <textarea id="preparation1" name="preparation1" rows="3" placeholder="Explain Preparation of Tri Ethylene Diamine Chromium(III)" style="width: 100%; display: block; overflow: auto; resize: none; padding: 10px; border: 1px solid #ccc; border-radius: 5px;"></textarea>

      <label for="preparation2">Preparation of Tris (2,4-pentanedionato) Chromium(III)</label>
      <textarea id="preparation2" name="preparation2" rows="3" placeholder="Explain Preparation of Tris (2,4-pentanedionato) Chromium(III)" style="width: 100%; display: block; overflow: auto; resize: none; padding: 10px; border: 1px solid #ccc; border-radius: 5px;"></textarea>

      <label for="discussion">Discussion</label>
      <textarea id="discussion" name="discussion" rows="3" placeholder="Explain discussion" style="width: 100%; display: block; overflow: auto; resize: none; padding: 10px; border: 1px solid #ccc; border-radius: 5px;"></textarea>

      <div style="text-align:center; margin-top:10px;">
        <img src="../../../images/exp11.jpg" alt="Graph Image" style="max-width:100%; height:300px; border:1px solid #ccc; padding:5px;" />
      </div>
      
      <h4><b>Table :</b> Experimental Values of 10 Dq of Cr (III) Complexes</h4>
      <table>
        <tr>
          <th>S.No</th>
          <th>Chromium (III) Complex</th>
          <th>10Dq Cm<sup>-1</sup></th>
        </tr>
        <tr>
          <td>1</td>
          <td>Cr(en)Cl<sub>3</sub>3.5 H<sub>2</sub>0</td>
          <td>21,300</td>
        </tr>
        <tr>
          <td>2</td>
          <td>Cr(C<sub>5</sub>H<sub>7</sub>O<sub>2</sub>)<sub>3</sub></td>
          <td>17,750</td>
        </tr>
        <tr>
          <td>3</td>
          <td>Cr(H<sub>2</sub>O)<sub>6</sub>(NO<sub>3</sub>)<sub>2</sub>.3H<sub>2</sub>O</td>
          <td>21,600</td>
        </tr>
      </table>

      <label for="result">Result</label>
      <textarea id="result" name="result" rows="3" placeholder="Write final conclusion" style="width: 100%; display: block; overflow: auto; resize: none; padding: 10px; border: 1px solid #ccc; border-radius: 5px;"></textarea>

      <div class="btn-group" style="text-align: left; margin-top: 30px;">
        <button type="button" onclick="previewExp()" style="cursor:pointer; background:#007bff; color:#fff; font-weight:600; padding:10px 20px; border-radius:6px; border:none; margin-right: 10px;">Preview</button>
        <button type="button" onclick="submitExperiment()" style="cursor:pointer; background:#1a347a; color:#fff; font-weight:600; padding:10px 20px; border-radius:6px; border:none;">Submit</button>
      </div>
    </form>

    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="apparatus-box">
        <h3 style="font-size:22px;margin-bottom:12px;text-align:center;">Apparatus Select</h3>
        <div class="apparatus-list">
          <button type="button" class="apparatus-btn">Beaker</button>
          <button type="button" class="apparatus-btn">Steam bath</button>
          <button type="button" class="apparatus-btn">UV-Visible spectrophotometer</button>
          
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

    // Prevent form submission on Enter key except for textareas
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('exp11-form');
        
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
        const form = document.getElementById('exp11-form');
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
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            line-height: 1.6;
        }
    </style>
    <div class="header-row">
        <div><b>Experiment No.:11</b></div>
        <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
    </div>
    <h2 style="text-align:center; margin-top: 0;">MEASURMENT OF I0DQ BY SPECTROPHOTOMETRIC METHOD</h2>

    <p><b>Aim:</b> ${formatTextWithBreaks(form.aim.value || '')}</p>
    p><b>Apparatus Used:</b> ${apparatusList.length ? escapeHtml(apparatusList.join(", ")) : '—'}</p>
    <p><b>Chemicals Required:</b> ${formatTextWithBreaks(form.chemicals.value || '')}</p>
    <
    <p><b>Preparation of Tri Ethylene Diamine Chromium(III):</b> ${formatTextWithBreaks(form.preparation1.value || '')}</p>
    <p><b>Preparation of Tris (2,4-pentanedionato) Chromium(III):</b> ${formatTextWithBreaks(form.preparation2.value || '')}</p>
    <p><b>Discussion:</b> ${formatTextWithBreaks(form.discussion.value || '')}</p>

    <div style="text-align:center; margin-top:10px;">
        <img src="../../../images/exp11.jpg" alt="Graph Image" style="max-width:100%; height:300px; border:1px solid #ccc; padding:5px;" />
    </div>

    <h4><b>Table :</b> Experimental Values of 10 Dq of Cr (III) Complexes</h4>
    <table>
        <tr>
            <th>S.No</th>
            <th>Chromium (III) Complex</th>
            <th>10Dq Cm<sup>-1</sup></th>
        </tr>
        <tr>
            <td>1</td>
            <td>Cr(en)Cl<sub>3</sub>3.5 H<sub>2</sub>0</td>
            <td>21,300</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Cr(C<sub>5</sub>H<sub>7</sub>O<sub>2</sub>)<sub>3</sub></td>
            <td>17,750</td>
        </tr>
        <tr>
            <td>3</td>
            <td>Cr(H<sub>2</sub>O)<sub>6</sub>(NO<sub>3</sub>)<sub>2</sub>.3H<sub>2</sub>O</td>
            <td>21,600</td>
        </tr>
    </table>

    <p><b>Result:</b> ${formatTextWithBreaks(form.result.value || '')}</p>`;

        const win = window.open('', '_blank', 'width=900,height=800');
        win.document.write('<!DOCTYPE html><html><head><title>Preview - Experiment 11</title><meta charset="utf-8"></head><body>');
        win.document.write(previewHtml);
        win.document.write('</body></html>');
        win.document.close();
    }

    // ---------- Submit Experiment ----------
    function submitExperiment() { 
        const form = document.getElementById('exp11-form');
         const subject = 'chemistry';
            const experiment_number = 11; // From your database
            const employee_id = '123';

       
        
        if (!form.aim.value.trim()) {
            alert("Please fill the Aim field.");
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
            margin: 15px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #f8f9fa;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            line-height: 1.6;
        }
    </style>
    <div class="header-row">
        <div><b>Experiment No.:11</b> </div>
        <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
    </div>
    <h2 style="text-align:center; margin-top: 0;">MEASURMENT OF I0DQ BY SPECTROPHOTOMETRIC METHOD</h2>

    <p><b>Aim:</b> ${formatTextWithBreaks(form.aim.value || '')}</p>
    <p><b>Apparatus Used:</b> ${apparatusList.length ? escapeHtml(apparatusList.join(", ")) : '—'}</p>
    <p><b>Chemicals Required:</b> ${formatTextWithBreaks(form.chemicals.value || '')}</p>
    
    <p><b>Preparation of Tri Ethylene Diamine Chromium(III):</b> ${formatTextWithBreaks(form.preparation1.value || '')}</p>
    <p><b>Preparation of Tris (2,4-pentanedionato) Chromium(III):</b> ${formatTextWithBreaks(form.preparation2.value || '')}</p>
    <p><b>Discussion:</b> ${formatTextWithBreaks(form.discussion.value || '')}</p>

        <div style="text-align:center; margin-top:10px;">
            <img src="../../../images/exp11.jpg" alt="Graph Image" style="max-width:100%; height:300px; border:1px solid #ccc; padding:5px;" />
            </div>

    <h4><b>Table :</b> Experimental Values of 10 Dq of Cr (III) Complexes</h4>
    <table>
        <tr>
            <th>S.No</th>
            <th>Chromium (III) Complex</th>
            <th>10Dq Cm<sup>-1</sup></th>
        </tr>
        <tr>
            <td>1</td>
            <td>Cr(en)Cl<sub>3</sub>3.5 H<sub>2</sub>0</td>
            <td>21,300</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Cr(C<sub>5</sub>H<sub>7</sub>O<sub>2</sub>)<sub>3</sub></td>
            <td>17,750</td>
        </tr>
        <tr>
            <td>3</td>
            <td>Cr(H<sub>2</sub>O)<sub>6</sub>(NO<sub>3</sub>)<sub>2</sub>.3H<sub>2</sub>O</td>
            <td>21,600</td>
        </tr>
    </table>

    <p><b>Result:</b> ${formatTextWithBreaks(form.result.value || '')}</p>`;
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