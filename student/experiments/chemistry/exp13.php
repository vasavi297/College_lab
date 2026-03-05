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
    <title>Experiment 13: Preparation of Nanomaterials</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../../../css/experiments.css">
</head>
<body>
    <div class="container">
        <!-- Main Form -->
        <form id="exp13-form" method="post" class="form-section">
            <!-- Experiment Header -->
            <div class="exp-header">
                <div style="display:flex;flex-direction:column;">
                    <label for="expNo">Experiment No.13</label>
                   <input type="hidden" id="subject" name="subject" value="Chemistry">
    <input type="hidden" id="experiment_number" name="experiment_number" value="13">
                </div>
                <div style="display:flex;flex-direction:column;">
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
            
            <h2 style="text-align: center;">Preparation of Nanomaterials</h2>
            
            <label for="aim">Aim</label>
            <textarea id="aim" name="aim" rows="3" placeholder="Enter experiment aim" style="width: 100%; display: block; overflow: auto; resize: none; padding: 10px; border: 1px solid #ccc; border-radius: 5px;" required></textarea>
            
            <label>Apparatus Used (Drag and Drop)</label>
            <div id="apparatus-dropbox" class="apparatus-dropbox" aria-label="Apparatus dropbox">
                <small id="apparatus-placeholder" style="color:#777;">Drag apparatus here</small>
            </div>
            <input type="hidden" id="apparatus_list" name="apparatus_list" value="">
            
            <label for="chemicals">Chemicals Required</label>
            <textarea id="chemicals" name="chemicals" rows="3" placeholder="List chemicals" style="width: 100%; display: block; overflow: auto; resize: none; padding: 10px; border: 1px solid #ccc; border-radius: 5px;" required></textarea>
            
            <h3>Procedure</h3>
            <textarea id="procedure" name="procedure" rows="4" placeholder="Enter Procedure" style="width: 100%; display: block; overflow: auto; resize: none; padding: 10px; border: 1px solid #ccc; border-radius: 5px;" required></textarea>
            
            <label for="result">Result</label>
            <textarea id="result" name="result" rows="3" placeholder="Write final conclusion" style="width: 100%; display: block; overflow: auto; resize: none; padding: 10px; border: 1px solid #ccc; border-radius: 5px;" required></textarea>

            <!-- Buttons aligned to left -->
            <div class="btn-group" style="text-align: left; margin-top: 30px;">
                <button type="button" onclick="previewExp()" style="cursor:pointer; background:#007bff; color:#fff; font-weight:600; padding:10px 20px; border-radius:6px; border:none; margin-right: 10px;">Preview</button>
                <button type="button" id="submitBtn" onclick="submitExperiment()" style="cursor:pointer; background:#1a347a; color:#fff; font-weight:600; padding:10px 20px; border-radius:6px; border:none;">Submit</button>
            </div>
        </form>

        <!-- Sidebar with Calculator -->
        <aside class="sidebar">
             <aside class="sidebar">
            <div class="apparatus-box">
                <h3 style="font-size:22px;margin-bottom:12px;text-align:center;">Select Apparatus</h3>
                <div class="apparatus-list">
                    <button type="button" class="apparatus-btn">Beaker</button>
                    <button type="button" class="apparatus-btn">Burette</button>
                    <button type="button" class="apparatus-btn">Glass Rod</button>
                    <button type="button" class="apparatus-btn">Centrifuge</button>
                    <button type="button" class="apparatus-btn">pH Meter</button>
                    <button type="button" class="apparatus-btn">Thermometer</button>
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
            const form = document.getElementById('exp13-form');
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
            const form = document.getElementById('exp13-form');
            const apparatusList = Array.from(document.querySelectorAll("#apparatus-dropbox .tool-item"))
                .map(el => el.textContent.trim());

            const previewHtml = `<style>
                .header-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-weight: 400; font-size: 1rem; color: #000000; }
                body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; }
            </style>
            <div class="header-row">
                <div><b>Experiment No.:13</b> </div>
                <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
            </div>
            <h2 style="text-align:center; margin-top: 0;">Preparation of Nanomaterials</h2>
            <p><b>Aim:</b> ${formatTextWithBreaks(form.aim.value || '')}</p>
            <p><b>Apparatus Used:</b> ${apparatusList.length ? escapeHtml(apparatusList.join(", ")) : '—'}</p>
            <p><b>Chemicals Required:</b> ${formatTextWithBreaks(form.chemicals.value || '')}</p>
            <h3>Procedure :</h3>
            <p>${formatTextWithBreaks(form.procedure.value || '')}</p>
            <h3>Result :</h3>
            <p>${formatTextWithBreaks(form.result.value || '')}</p>`;

            const win = window.open('', '_blank', 'width=900,height=800');
            win.document.write('<!DOCTYPE html><html><head><title>Preview - Preparation of Nanomaterials</title><meta charset="utf-8"></head><body>');
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
            
            const form = document.getElementById('exp13-form');
            if (!form) {
                alert('Error: Form not found!');
                return;
            }
            
            const subject = 'Chemistry';
            const experiment_number = 13;
            
            // Get retake parameters if this is a retake
            const urlParams = new URLSearchParams(window.location.search);
            const retakeId = urlParams.get('retake_id');
            const isRetake = urlParams.get('is_retake');
            const retakeCount = urlParams.get('retake_count') || 0;
            
            // Validation
            if (!form.aim.value.trim() || !form.chemicals.value.trim() || !form.procedure.value.trim() || !form.result.value.trim()) {
                alert("Please fill all required fields.");
                return;
            }
            const apparatusList = Array.from(document.querySelectorAll("#apparatus-dropbox .tool-item"))
                .map(el => el.textContent.trim());
            if (apparatusList.length === 0) {
                alert("Please add at least one apparatus.");
                return;
            }

            const submissionHtml = `<style>
                .header-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-weight: 400; font-size: 1rem; color: #000000; }
                body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; }
            </style>
            <div class="header-row">
                <div><b>Experiment No.:13</b> </div>
                <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
            </div>
            <h2 style="text-align:center; margin-top: 0;">Preparation of Nanomaterials</h2>
            <p><b>Aim:</b> ${formatTextWithBreaks(form.aim.value || '')}</p>
            <p><b>Apparatus Used:</b> ${apparatusList.length ? escapeHtml(apparatusList.join(", ")) : '—'}</p>
            <p><b>Chemicals Required:</b> ${formatTextWithBreaks(form.chemicals.value || '')}</p>
            <h3>Procedure :</h3>
            <p>${formatTextWithBreaks(form.procedure.value || '')}</p>
            <h3>Result :</h3>
            <p>${formatTextWithBreaks(form.result.value || '')}</p>`;

            // Prepare POST data
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
                        // Show success message and redirect to experiments list
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