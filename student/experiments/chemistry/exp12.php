<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Experiment 12: Identification of Simple Organic Compounds by IR</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  
  <!-- External CSS -->
  <link rel="stylesheet" href="../../../css/experiments.css" />
  <style>
    .image-container {
        border: 1px dashed #ccc;
        padding: 15px;
        margin: 10px auto;
        text-align: center;
        background: #f9f9f9;
        width: 80%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 250px;
    }
    
    .image-container img {
        max-width: 100%;
        max-height: 200px;
        width: auto;
        height: auto;
        object-fit: contain;
        display: block;
    }
    
    .image-caption {
        margin-top: 8px;
        color: #666;
        font-size: 14px;
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Main Form -->
    <form id="exp12-form" method="post" class="form-section">
      <!-- Experiment Header -->
      <div class="exp-header">
        <div style="display:flex;flex-direction:column;">
          <label for="expNo">Experiment No.12</label>
          <input type="hidden" id="subject" name="subject" value="chemistry">
    <input type="hidden" id="experiment_number" name="experiment_number" value="12">
                </div>
        <div style="display:flex;flex-direction:column;">
          <label for="expDate">Date</label>
          <input type="date" id="expDate" name="expDate" required/>
        </div>
      </div>

      <h2 style="text-align: center; font-size: 25px;">IDENTIFICATION OF SIMPLE ORGANIC COMPOUNDS BY I.R</h2>

      <label for="aim">Aim</label>
      <textarea id="aim" name="aim" rows="3" placeholder="Enter experiment aim" style="width: 100%; display: block; overflow: auto; resize: none; padding: 10px; border: 1px solid #ccc; border-radius: 5px;"></textarea>

      <!-- Content Box 1 -->
      <div class="content-box" style="border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 8px; text-align: center;">
        <h3 style="text-align: center; margin-bottom: 15px;">SPECTROSCOPY</h3>
        <textarea id="spectroscopy" name="spectroscopy" rows="4" placeholder="Enter spectroscopy information" style="width: 80%; margin: 0 auto 15px; display: block; overflow: auto; resize: none; padding: 10px; border: 1px solid #ccc; border-radius: 5px;"></textarea>
        <div class="image-container">
          <img src="../../../images/exp12_1.png" alt="Spectroscopy Diagram" onerror="this.style.display='none'"style="max-width: 100%; height: auto;">
          <p class="image-caption">Fig: Spectroscopy Process Diagram</p>
        </div>
      </div>

      <!-- Content Box 2 -->
      <div class="content-box" style="border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 8px; text-align: center;">
        <h3 style="text-align: center; margin-bottom: 15px;">IR SPECTRUM IN ABSORPTION MODE</h3>
        <textarea id="absorption_mode" name="absorption_mode" rows="4" placeholder="Enter information about absorption mode" style="width: 80%; margin: 0 auto 15px; display: block; overflow: auto; resize: none; padding: 10px; border: 1px solid #ccc; border-radius: 5px;"></textarea>
        <div class="image-container">
          <img src="../../../images/exp12_2.png" alt="Absorption Spectrum" onerror="this.style.display='none'">
          <p class="image-caption">Fig: IR Spectrum in Absorption Mode</p>
        </div>
      </div>

      <!-- Content Box 3 -->
      <div class="content-box" style="border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 8px; text-align: center;">
        <h3 style="text-align: center; margin-bottom: 15px;">IR SPECTRUM IN TRANSMISSION MODE</h3>
        <textarea id="transmission_mode" name="transmission_mode" rows="4" placeholder="Enter information about transmission mode" style="width: 80%; margin: 0 auto 15px; display: block; overflow: auto; resize: none; padding: 10px; border: 1px solid #ccc; border-radius: 5px;"></textarea>
        <div class="image-container">
          <img src="../../../images/exp12_3.png" alt="Transmission Spectrum" onerror="this.style.display='none'">
          <p class="image-caption">Fig: IR Spectrum in Transmission Mode</p>
        </div>
      </div>

      <!-- Content Box 4 -->
      <div class="content-box" style="border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 8px; text-align: center;">
        <h3 style="text-align: center; margin-bottom: 15px;">TRANSMISSION vs ABSORPTION</h3>
        <textarea id="transmission_absorption" name="transmission_absorption" rows="4" placeholder="Enter information about transmission vs absorption" style="width: 80%; margin: 0 auto 15px; display: block; overflow: auto; resize: none; padding: 10px; border: 1px solid #ccc; border-radius: 5px;"></textarea>
        <div class="image-container">
          <img src="../../../images/exp12_4.png" alt="Transmission vs Absorption" onerror="this.style.display='none'">
          <p class="image-caption">Fig: Transmission vs Absorption Comparison</p>
        </div>
      </div>

      <!-- Content Box 5 -->
      <div class="content-box" style="border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 8px; text-align: center;">
        <h3 style="text-align: center; margin-bottom: 15px;">CLASSIFICATION OF IR BANDS</h3>
        <textarea id="band_classification" name="band_classification" rows="4" placeholder="Enter information about IR band classification" style="width: 80%; margin: 0 auto 15px; display: block; overflow: auto; resize: none; padding: 10px; border: 1px solid #ccc; border-radius: 5px;"></textarea>
        <div class="image-container">
          <img src="../../../images/exp12_5.png" alt="IR Band Classification" onerror="this.style.display='none'">
          <p class="image-caption">Fig: Classification of IR Bands (Strong, Medium, Weak)</p>
        </div>
      </div>

      <!-- Result Section -->
      <h3 style="text-align: center; margin-bottom: 15px;">RESULT</h3>
      
      <!-- Content Box 6 - I.R SPECTRUM OF AN ALCOHOL -->
      <div class="content-box" style="border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 8px; text-align: center;">
        <h3 style="text-align: center; margin-bottom: 15px;">I.R SPECTRUM OF AN ALCOHOL</h3>
        <textarea id="alcohol_spectrum" name="alcohol_spectrum" rows="4" placeholder="Enter information about alcohol IR spectrum" style="width: 80%; margin: 0 auto 15px; display: block; overflow: auto; resize: none; padding: 10px; border: 1px solid #ccc; border-radius: 5px;"></textarea>
        <div class="image-container">
          <img src="../../../images/exp12_6.png" alt="Alcohol IR Spectrum" onerror="this.style.display='none'">
          <p class="image-caption">Fig: IR Spectrum of 1-Butanol showing O-H stretch</p>
        </div>
      </div>

      <!-- Content Box 7 - I.R SPECTRUM OF DIPROPYLAMINE -->
      <div class="content-box" style="border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 8px; text-align: center;">
        <h3 style="text-align: center; margin-bottom: 15px;">I.R SPECTRUM OF DIPROPYLAMINE</h3>
        <textarea id="dipropylamine_spectrum" name="dipropylamine_spectrum" rows="4" placeholder="Enter information about dipropylamine IR spectrum" style="width: 80%; margin: 0 auto 15px; display: block; overflow: auto; resize: none; padding: 10px; border: 1px solid #ccc; border-radius: 5px;"></textarea>
        <div class="image-container">
          <img src="../../../images/exp12_7.png" alt="Dipropylamine IR Spectrum" onerror="this.style.display='none'">
          <p class="image-caption">Fig: IR Spectrum of Dipropylamine showing N-H stretch</p>
        </div>
      </div>

      <!-- Buttons aligned to left -->
      <div class="btn-group" style="text-align: left; margin-top: 30px;">
        <button type="button" onclick="previewExp()" style="cursor:pointer; background:#007bff; color:#fff; font-weight:600; padding:10px 20px; border-radius:6px; border:none; margin-right: 10px;">Preview</button>
        <button type="button" onclick="submitExperiment()" style="cursor:pointer; background:#1a347a; color:#fff; font-weight:600; padding:10px 20px; border-radius:6px; border:none;">Submit</button>
      </div>
    </form>
  </div>

  <script>
    // ---------- Preview Functions ----------
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

    function previewExp() {
        const form = document.getElementById('exp12-form');
        
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
        .content-box {
            margin-bottom: 25px;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .image-container {
            border: 1px dashed #ccc;
            padding: 15px;
            margin: 15px auto;
            text-align: center;
            background: #f9f9f9;
            width: 80%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 250px;
        }
        .image-container img {
            max-width: 100%;
            max-height: 200px;
            width: auto;
            height: auto;
            object-fit: contain;
        }
        .image-caption {
            margin-top: 8px;
            color: #666;
            font-size: 14px;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            line-height: 1.6;
        }
        textarea {
            width: 80%;
            margin: 0 auto 15px;
            display: block;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
    <div class="header-row">
        <div><b>Experiment No.:12</b></div>
        <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
    </div>
    <h2 style="text-align:center; margin-top: 0;">IDENTIFICATION OF SIMPLE ORGANIC COMPOUNDS BY I.R</h2>

    <p><b>Aim:</b> ${formatTextWithBreaks(form.aim.value || '')}</p>

    <div class="content-box">
        <h3 style="text-align: center;">SPECTROSCOPY</h3>
        <p>${formatTextWithBreaks(form.spectroscopy.value || '')}</p>
        <div class="image-container">
            <img src="../../../images/exp12_1.png" alt="Spectroscopy Diagram">
            <p class="image-caption">Fig: Spectroscopy Process Diagram</p>
        </div>
    </div>

    <div class="content-box">
        <h3 style="text-align: center;">IR SPECTRUM IN ABSORPTION MODE</h3>
        <p>${formatTextWithBreaks(form.absorption_mode.value || '')}</p>
        <div class="image-container">
            <img src="../../../images/exp12_2.png" alt="Absorption Spectrum">
            <p class="image-caption">Fig: IR Spectrum in Absorption Mode</p>
        </div>
    </div>

    <div class="content-box">
        <h3 style="text-align: center;">IR SPECTRUM IN TRANSMISSION MODE</h3>
        <p>${formatTextWithBreaks(form.transmission_mode.value || '')}</p>
        <div class="image-container">
            <img src="../../../images/exp12_3.png" alt="Transmission Spectrum">
            <p class="image-caption">Fig: IR Spectrum in Transmission Mode</p>
        </div>
    </div>

    <div class="content-box">
        <h3 style="text-align: center;">TRANSMISSION vs ABSORPTION</h3>
        <p>${formatTextWithBreaks(form.transmission_absorption.value || '')}</p>
        <div class="image-container">
            <img src="../../../images/exp12_4.png" alt="Transmission vs Absorption">
            <p class="image-caption">Fig: Transmission vs Absorption Comparison</p>
        </div>
    </div>

    <div class="content-box">
        <h3 style="text-align: center;">CLASSIFICATION OF IR BANDS</h3>
        <p>${formatTextWithBreaks(form.band_classification.value || '')}</p>
        <div class="image-container">
            <img src="../../../images/exp12_5.png" alt="IR Band Classification">
            <p class="image-caption">Fig: Classification of IR Bands (Strong, Medium, Weak)</p>
        </div>
    </div>

    <h3 style="text-align: center;">RESULT</h3>
    <div class="content-box">
        <h3 style="text-align: center;">I.R SPECTRUM OF AN ALCOHOL</h3>
        <p>${formatTextWithBreaks(form.alcohol_spectrum.value || '')}</p>
        <div class="image-container">
            <img src="../../../images/exp12_6.png" alt="Alcohol IR Spectrum">
            <p class="image-caption">Fig: IR Spectrum of 1-Butanol showing O-H stretch</p>
        </div>
    </div>

    <div class="content-box">
        <h3 style="text-align: center;">I.R SPECTRUM OF DIPROPYLAMINE</h3>
        <p>${formatTextWithBreaks(form.dipropylamine_spectrum.value || '')}</p>
        <div class="image-container">
            <img src="../../../images/exp12_7.png" alt="Dipropylamine IR Spectrum">
            <p class="image-caption">Fig: IR Spectrum of Dipropylamine showing N-H stretch</p>
        </div>
    </div>`;

        const win = window.open('', '_blank', 'width=900,height=800');
        win.document.write('<!DOCTYPE html><html><head><title>Preview - IR Spectroscopy</title><meta charset="utf-8"></head><body>');
        win.document.write(previewHtml);
        win.document.write('</body></html>');
        win.document.close();
    }

    // ---------- Submit Experiment ----------
    function submitExperiment() { 
        const form = document.getElementById('exp12-form');
         const subject = 'chemistry';
            const experiment_number = 12; // From your database
            const employee_id = '123';
        
        if (!form.aim.value.trim()) {
            alert("Please fill the Aim field.");
            return;
        }

        // Prepare submission data (same as preview but with actual images)
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
        .content-box {
            margin-bottom: 25px;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .image-container {
            border: 1px dashed #ccc;
            padding: 15px;
            margin: 15px auto;
            text-align: center;
            background: #f9f9f9;
            width: 80%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 250px;
        }
        .image-container img {
            max-width: 100%;
            max-height: 200px;
            width: auto;
            height: auto;
            object-fit: contain;
        }
        .image-caption {
            margin-top: 8px;
            color: #666;
            font-size: 14px;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            line-height: 1.6;
        }
    </style>
    <div class="header-row">
        <div><b>Experiment No.:12</b></div>
        <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
    </div>
    <h2 style="text-align:center; margin-top: 0;">IDENTIFICATION OF SIMPLE ORGANIC COMPOUNDS BY I.R</h2>

    <p><b>Aim:</b> ${formatTextWithBreaks(form.aim.value || '')}</p>

    <div class="content-box">
        <h3 style="text-align: center;">SPECTROSCOPY</h3>
        <p>${formatTextWithBreaks(form.spectroscopy.value || '')}</p>
        <div class="image-container">
            <img src="../../../images/exp12_1.png" alt="Spectroscopy Diagram">
            <p class="image-caption">Fig: Spectroscopy Process Diagram</p>
        </div>
    </div>

    <div class="content-box">
        <h3 style="text-align: center;">IR SPECTRUM IN ABSORPTION MODE</h3>
        <p>${formatTextWithBreaks(form.absorption_mode.value || '')}</p>
        <div class="image-container">
            <img src="../../../images/exp12_2.png" alt="Absorption Spectrum">
            <p class="image-caption">Fig: IR Spectrum in Absorption Mode</p>
        </div>
    </div>

    <div class="content-box">
        <h3 style="text-align: center;">IR SPECTRUM IN TRANSMISSION MODE</h3>
        <p>${formatTextWithBreaks(form.transmission_mode.value || '')}</p>
        <div class="image-container">
            <img src="../../../images/exp12_3.png" alt="Transmission Spectrum">
            <p class="image-caption">Fig: IR Spectrum in Transmission Mode</p>
        </div>
    </div>

    <div class="content-box">
        <h3 style="text-align: center;">TRANSMISSION vs ABSORPTION</h3>
        <p>${formatTextWithBreaks(form.transmission_absorption.value || '')}</p>
        <div class="image-container">
            <img src="../../../images/exp12_4.png" alt="Transmission vs Absorption">
            <p class="image-caption">Fig: Transmission vs Absorption Comparison</p>
        </div>
    </div>

    <div class="content-box">
        <h3 style="text-align: center;">CLASSIFICATION OF IR BANDS</h3>
        <p>${formatTextWithBreaks(form.band_classification.value || '')}</p>
        <div class="image-container">
            <img src="../../../images/exp12_5.png" alt="IR Band Classification">
            <p class="image-caption">Fig: Classification of IR Bands (Strong, Medium, Weak)</p>
        </div>
    </div>

    <h3 style="text-align: center;">RESULT</h3>
    <div class="content-box">
        <h3 style="text-align: center;">I.R SPECTRUM OF AN ALCOHOL</h3>
        <p>${formatTextWithBreaks(form.alcohol_spectrum.value || '')}</p>
        <div class="image-container">
            <img src="../../../images/exp12_6.png" alt="Alcohol IR Spectrum">
            <p class="image-caption">Fig: IR Spectrum of 1-Butanol showing O-H stretch</p>
        </div>
    </div>

    <div class="content-box">
        <h3 style="text-align: center;">I.R SPECTRUM OF DIPROPYLAMINE</h3>
        <p>${formatTextWithBreaks(form.dipropylamine_spectrum.value || '')}</p>
        <div class="image-container">
            <img src="../../../images/exp12_7.png" alt="Dipropylamine IR Spectrum">
            <p class="image-caption">Fig: IR Spectrum of Dipropylamine showing N-H stretch</p>
        </div>
    </div>`;

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