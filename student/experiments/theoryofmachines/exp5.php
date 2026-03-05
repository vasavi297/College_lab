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
  <title>Experiment 5: Study of Gears</title>
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
    <form id="exp5-form" method="post" class="form-section">
      
      <!-- Experiment header -->
      <div class="exp-header">
        <div style="display:flex;flex-direction:column;">
          <label for="expNo">Experiment No. 5</label>
          
                    <input type="hidden" id="subject" name="subject" value="Theory of Machines">
          <input type="hidden" id="experiment_number" name="experiment_number" value="5"> <!-- Changed to 18 -->
        </div>
                <div style="display:flex;flex-direction:column;position:relative;">
                    <label for="expDate">Date</label>
                    <div style="display:flex;align-items:center;gap:8px;justify-content:space-between;">
                        <input type="date" id="expDate" name="expDate" required/>
             </div>
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

      <h2 style="text-align: center;">STUDY OF GEARS</h2>

      <!-- Gear -->
      <label for="gear">Gear</label>
      <textarea id="gear" name="gear" rows="3" placeholder="Write about Gear" required></textarea>

      <!-- More fields -->
      <label for="function">Function</label>
      <textarea id="function" name="function" rows="3" placeholder="Explain Function" required></textarea>

      <label for="classification">Classification of gears</label>
      <textarea id="classification" name="classification" rows="3" placeholder="Write classification of gears" required></textarea>

      <label for="spur">Spur Gear</label>
      <textarea id="spur" name="spur" rows="3" placeholder="Write about Spur Gear" required></textarea>

      <!-- Nomenclature -->
      <h3>Nomenclature of Gears</h3>

      <label for="pitchCircle">Pitch Circle</label>
      <textarea id="pitchCircle" name="pitchCircle" rows="2"></textarea>

      <label for="pco">PCO</label>
      <textarea id="pco" name="pco" rows="2"></textarea>

      <label for="pitchPoint">Pitch Point</label>
      <textarea id="pitchPoint" name="pitchPoint" rows="2"></textarea>

      <label for="pitchSurface">Pitch Surface</label>
      <textarea id="pitchSurface" name="pitchSurface" rows="2"></textarea>

      <label for="pressureAngle">Pressure Angle</label>
      <textarea id="pressureAngle" name="pressureAngle" rows="2"></textarea>

      <label for="addendum">Addendum</label>
      <textarea id="addendum" name="addendum" rows="2"></textarea>

      <label for="dedendum">Dedendum</label>
      <textarea id="dedendum" name="dedendum" rows="2"></textarea>

      <label for="addCircle">Add Circle</label>
      <textarea id="addCircle" name="addCircle" rows="2"></textarea>

      <label for="circularPitch">Circular Pitch</label>
      <textarea id="circularPitch" name="circularPitch" rows="2"></textarea>

      <label for="diametralPitch">Diametral Pitch</label>
      <textarea id="diametralPitch" name="diametralPitch" rows="2"></textarea>

      <label for="module">Module</label>
      <textarea id="module" name="module" rows="2"></textarea>

      <label for="workingDepth">Working Depth</label>
      <textarea id="workingDepth" name="workingDepth" rows="2"></textarea>

      <label for="toothThickness">Tooth Thickness</label>
      <textarea id="toothThickness" name="toothThickness" rows="2"></textarea>

      <label for="faceOfTooth">Face of Tooth</label>
      <textarea id="faceOfTooth" name="faceOfTooth" rows="2"></textarea>

      <label for="backlash">Backlash</label>
      <textarea id="backlash" name="backlash" rows="2"></textarea>

      <label for="topLand">Top Land</label>
      <textarea id="topLand" name="topLand" rows="2"></textarea>

      <label for="flankOfTooth">Flank of Tooth</label>
      <textarea id="flankOfTooth" name="flankOfTooth" rows="2"></textarea>

      <label for="faceWidth">Face Width</label>
      <textarea id="faceWidth" name="faceWidth" rows="2"></textarea>

      <label for="profiled">Profiled</label>
      <textarea id="profiled" name="profiled" rows="2"></textarea>

      <label for="pathOfContact">Path of Contact</label>
      <textarea id="pathOfContact" name="pathOfContact" rows="2"></textarea>

      <label for="lengthOfPath">Length of the Path of Contact</label>
      <textarea id="lengthOfPath" name="lengthOfPath" rows="2"></textarea>

      <label for="arcOfContact">Arc of Contact</label>
      <textarea id="arcOfContact" name="arcOfContact" rows="2"></textarea>

      <label for="contactRatio">Contact Ratio</label>
      <textarea id="contactRatio" name="contactRatio" rows="2"></textarea>

      <label for="interference">Interference</label>
      <textarea id="interference" name="interference" rows="2"></textarea>

      <label for="lawOfGearing">Law of Gearing</label>
      <textarea id="lawOfGearing" name="lawOfGearing" rows="2"></textarea>

      <!-- Buttons -->
      <div class="btn-group">
        <button type="button" onclick="previewExp()" style="cursor:pointer; background:#007bff; color:#fff; font-weight:600; padding:8px 16px; border-radius:6px; width: fit-content;">Preview</button>
        <button type="button" onclick="submitExperiment()" id="submitBtn" style="cursor:pointer; background:#1a347a; color:#fff; font-weight:600; padding:8px 16px; border-radius:6px; width: fit-content;">Submit</button>
      </div>
    </form>
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

// Prevent form submission on Enter key except for textareas
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('exp5-form');
    
    form.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
        }
    });
});

// ---------- Preview ----------
function previewExp() {
    const form = document.getElementById('exp5-form');
    
    // Get all textareas and their labels
    const textareas = form.querySelectorAll('textarea');
    const labels = form.querySelectorAll('label');
    
    // Create label mapping
    const labelMap = {};
    labels.forEach(label => {
        const forId = label.getAttribute('for');
        if (forId) {
            labelMap[forId] = label.textContent.replace(':', '').trim();
        }
    });

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
    .nomenclature-section {
        margin-top: 20px;
        padding: 15px;
        background: #f9f9f9;
        border-radius: 5px;
        border-left: 4px solid #3460d1;
    }
</style>
<div class="header-row">
    <div><b>Experiment No.:</b> 5</div>
    <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
</div>
<h2 style="text-align:center; margin-top: 0;">STUDY OF GEARS</h2>

<p><b>Gear:</b> ${formatTextWithBreaks(form.gear.value || '')}</p>
<p><b>Function:</b> ${formatTextWithBreaks(form.function.value || '')}</p>
<p><b>Classification of Gears:</b> ${formatTextWithBreaks(form.classification.value || '')}</p>
<p><b>Spur Gear:</b> ${formatTextWithBreaks(form.spur.value || '')}</p>

<div class="nomenclature-section">
    <h3>Nomenclature of Gears:</h3>
    <p><b>Pitch Circle:</b> ${formatTextWithBreaks(form.pitchCircle.value || '')}</p>
    <p><b>PCO:</b> ${formatTextWithBreaks(form.pco.value || '')}</p>
    <p><b>Pitch Point:</b> ${formatTextWithBreaks(form.pitchPoint.value || '')}</p>
    <p><b>Pitch Surface:</b> ${formatTextWithBreaks(form.pitchSurface.value || '')}</p>
    <p><b>Pressure Angle:</b> ${formatTextWithBreaks(form.pressureAngle.value || '')}</p>
    <p><b>Addendum:</b> ${formatTextWithBreaks(form.addendum.value || '')}</p>
    <p><b>Dedendum:</b> ${formatTextWithBreaks(form.dedendum.value || '')}</p>
    <p><b>Add Circle:</b> ${formatTextWithBreaks(form.addCircle.value || '')}</p>
    <p><b>Circular Pitch:</b> ${formatTextWithBreaks(form.circularPitch.value || '')}</p>
    <p><b>Diametral Pitch:</b> ${formatTextWithBreaks(form.diametralPitch.value || '')}</p>
    <p><b>Module:</b> ${formatTextWithBreaks(form.module.value || '')}</p>
    <p><b>Working Depth:</b> ${formatTextWithBreaks(form.workingDepth.value || '')}</p>
    <p><b>Tooth Thickness:</b> ${formatTextWithBreaks(form.toothThickness.value || '')}</p>
    <p><b>Face of Tooth:</b> ${formatTextWithBreaks(form.faceOfTooth.value || '')}</p>
    <p><b>Backlash:</b> ${formatTextWithBreaks(form.backlash.value || '')}</p>
    <p><b>Top Land:</b> ${formatTextWithBreaks(form.topLand.value || '')}</p>
    <p><b>Flank of Tooth:</b> ${formatTextWithBreaks(form.flankOfTooth.value || '')}</p>
    <p><b>Face Width:</b> ${formatTextWithBreaks(form.faceWidth.value || '')}</p>
    <p><b>Profiled:</b> ${formatTextWithBreaks(form.profiled.value || '')}</p>
    <p><b>Path of Contact:</b> ${formatTextWithBreaks(form.pathOfContact.value || '')}</p>
    <p><b>Length of the Path of Contact:</b> ${formatTextWithBreaks(form.lengthOfPath.value || '')}</p>
    <p><b>Arc of Contact:</b> ${formatTextWithBreaks(form.arcOfContact.value || '')}</p>
    <p><b>Contact Ratio:</b> ${formatTextWithBreaks(form.contactRatio.value || '')}</p>
    <p><b>Interference:</b> ${formatTextWithBreaks(form.interference.value || '')}</p>
    <p><b>Law of Gearing:</b> ${formatTextWithBreaks(form.lawOfGearing.value || '')}</p>
</div>`;

    const win = window.open('', '_blank', 'width=900,height=800');
    win.document.write('<!DOCTYPE html><html><head><title>Preview - Study of Gears</title><meta charset="utf-8"></head><body style="font-family:Arial,sans-serif; padding:20px;">');
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
    // Show confirmation dialog
    const shouldSubmit = await confirmSubmit();
    if (!shouldSubmit) {
        return;
    }
    
    const form = document.getElementById('exp5-form');
    const subject = 'Theory of Machines';
    const experiment_number = 5; // From your database (ID 18)
    
    // Get retake parameters if this is a retake
    const urlParams = new URLSearchParams(window.location.search);
    const retakeId = urlParams.get('retake_id');
    const isRetake = urlParams.get('is_retake');
    const retakeCount = urlParams.get('retake_count') || 0;
    
    // Validation
    if (!form.gear.value.trim() || !form.function.value.trim() || 
        !form.classification.value.trim() || !form.spur.value.trim()) {
        alert("Please fill all required fields: Gear, Function, Classification, and Spur Gear.");
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
    .nomenclature-section {
        margin-top: 20px;
        padding: 15px;
        background: #f9f9f9;
        border-radius: 5px;
        border-left: 4px solid #3460d1;
    }
</style>
<div class="header-row">
    <div><b>Experiment No.:</b> 5</div>
    <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
</div>
<h2 style="text-align:center; margin-top: 0;">STUDY OF GEARS</h2>

<p><b>Gear:</b> ${formatTextWithBreaks(form.gear.value || '')}</p>
<p><b>Function:</b> ${formatTextWithBreaks(form.function.value || '')}</p>
<p><b>Classification of Gears:</b> ${formatTextWithBreaks(form.classification.value || '')}</p>
<p><b>Spur Gear:</b> ${formatTextWithBreaks(form.spur.value || '')}</p>

<div class="nomenclature-section">
    <h3>Nomenclature of Gears:</h3>
    <p><b>Pitch Circle:</b> ${formatTextWithBreaks(form.pitchCircle.value || '')}</p>
    <p><b>PCO:</b> ${formatTextWithBreaks(form.pco.value || '')}</p>
    <p><b>Pitch Point:</b> ${formatTextWithBreaks(form.pitchPoint.value || '')}</p>
    <p><b>Pitch Surface:</b> ${formatTextWithBreaks(form.pitchSurface.value || '')}</p>
    <p><b>Pressure Angle:</b> ${formatTextWithBreaks(form.pressureAngle.value || '')}</p>
    <p><b>Addendum:</b> ${formatTextWithBreaks(form.addendum.value || '')}</p>
    <p><b>Dedendum:</b> ${formatTextWithBreaks(form.dedendum.value || '')}</p>
    <p><b>Add Circle:</b> ${formatTextWithBreaks(form.addCircle.value || '')}</p>
    <p><b>Circular Pitch:</b> ${formatTextWithBreaks(form.circularPitch.value || '')}</p>
    <p><b>Diametral Pitch:</b> ${formatTextWithBreaks(form.diametralPitch.value || '')}</p>
    <p><b>Module:</b> ${formatTextWithBreaks(form.module.value || '')}</p>
    <p><b>Working Depth:</b> ${formatTextWithBreaks(form.workingDepth.value || '')}</p>
    <p><b>Tooth Thickness:</b> ${formatTextWithBreaks(form.toothThickness.value || '')}</p>
    <p><b>Face of Tooth:</b> ${formatTextWithBreaks(form.faceOfTooth.value || '')}</p>
    <p><b>Backlash:</b> ${formatTextWithBreaks(form.backlash.value || '')}</p>
    <p><b>Top Land:</b> ${formatTextWithBreaks(form.topLand.value || '')}</p>
    <p><b>Flank of Tooth:</b> ${formatTextWithBreaks(form.flankOfTooth.value || '')}</p>
    <p><b>Face Width:</b> ${formatTextWithBreaks(form.faceWidth.value || '')}</p>
    <p><b>Profiled:</b> ${formatTextWithBreaks(form.profiled.value || '')}</p>
    <p><b>Path of Contact:</b> ${formatTextWithBreaks(form.pathOfContact.value || '')}</p>
    <p><b>Length of the Path of Contact:</b> ${formatTextWithBreaks(form.lengthOfPath.value || '')}</p>
    <p><b>Arc of Contact:</b> ${formatTextWithBreaks(form.arcOfContact.value || '')}</p>
    <p><b>Contact Ratio:</b> ${formatTextWithBreaks(form.contactRatio.value || '')}</p>
    <p><b>Interference:</b> ${formatTextWithBreaks(form.interference.value || '')}</p>
    <p><b>Law of Gearing:</b> ${formatTextWithBreaks(form.lawOfGearing.value || '')}</p>
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
    .fullscreen-btn {
        background: linear-gradient(90deg, #1a347a 0%, #007bff 100%);
        color: #fff;
        font-weight: 600;
        border: none;
        border-radius: 6px;
        padding: 7px 18px;
        font-size: 15px;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: background 0.2s, box-shadow 0.2s;
        white-space: nowrap;
    }
    .fullscreen-btn:hover {
        background: linear-gradient(90deg, #007bff 0%, #1a347a 100%);
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
  </style>