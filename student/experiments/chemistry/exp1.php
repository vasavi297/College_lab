<?php session_start();
require_once __DIR__ . '/../../device_guard.php';
ensure_desktop_only(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Experiment 1: Introduction To Chemistry Laboratory</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../../../css/experiments.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 8px 10px;
            text-align: center;
        }
        img {
            max-width: 100%;
            height: auto;
            margin: 12px 0;
        }
        .exp-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .btn-group {
            margin-top: 30px;
            display: flex;
            gap: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Main Form -->
        <form id="exp1-form" method="post" class="form-section">
            <!-- Experiment header -->
            <div class="exp-header">
                <div style="display:flex;flex-direction:column;">
                    <label for="expNo">Experiment No. 1</label>
                   
                    <!-- Hidden fields for database submission -->
                    <input type="hidden" id="subject" name="subject" value="Chemistry">
                    <input type="hidden" id="experiment_number" name="experiment_number" value="1">
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
            
            <h2 style="text-align: center; margin-bottom: 20px;">Introduction To Chemistry Laboratory</h2>
            
            <div id="experiment-content">
                <p>Analytical chemistry is the branch of chemistry which deals with the detection of chemical composition and estimation of the elements and compounds contained in it. It is classified into</p>
                <ol>
                    <li>Qualitative Analysis</li>
                    <li>Quantitative Analysis</li>
                </ol>
                <h3>1. Qualitative Analysis</h3>
                <p>Qualitative analysis deals with the detection and identification of the constituent cations and anions present in the given substance.</p>
                <h3>2. Quantitative Analysis</h3>
                <p>Quantitative Analysis deals with the determination of amount of different constituents present in a sample. The determination can be carried by three methods.</p>
                <ol style="list-style-type: lower-alpha;">
                    <li>Gravimetric Analysis</li>
                    <li>Volumetric Analysis</li>
                    <li>Instrumental Analysis</li>
                </ol>
                <h3>a) Gravimetric Analysis</h3>
                <p>In gravimetric analysis, the estimation of substance is carried out by the process of weighing. The Constituent being determined is isolated as a compound of known and definite composition of insoluble form, which is collected and weighed.</p>
                <h3>b) Volumetric Analysis</h3>
                <p>This method of analysis deals with volumes of solution and the estimation of substance is carried out by the process of titration. This method is rapid and simple.</p>
                <h3>c) Instrumental Analysis</h3>
                <p>It is a field of Analytical chemistry that investigates analytes using scientific instruments.</p>
                <h3>Terms used in Volumetric Analysis:</h3>
                <h3>Titration</h3>
                <p>Titration is a process of addition of known concentration of solution to the unknown solution up to the completion of the reaction.</p>
                <h3>Titrant</h3>
                <p>The reagent of known concentration is called titrant.</p>
                <h3>Titrate</h3>
                <p>The substance being titrated is termed as titrate.</p>
                <h3>End Point</h3>
                <p>The point at which the reaction is just complete is known as the equivalence point or end point.</p>
                <h3>Indicator</h3>
                <p>The substance which is used in the titration to locate or to identify the end point visually on completion of titration by change of colour. Example: Methyl Orange, Phenolphthalein, Methyl Red.</p>
                <h3>Standard Solution</h3>
                <p>A Solution of known concentration is known as standard solution.</p>
                <h3>Primary Standard</h3>
                <p>The substance whose standard solution is prepared by dissolving a directly weighed substance in a definite volume of a solvent is called primary standard. Commonly used primary standards are anhydrous sodium carbonate, oxalic acid, Mohr's salt, potassium dichromate etc. A standard solution is prepared by dissolving an accurately weighed quantity of a highly pure material and diluting to an accurately known volume in a volumetric flask in called primary standard.</p>
                <h3>Secondary standard</h3>
                <p>That substance which cannot be used to prepare a solution by direct weighing. The solution of this type are prepared with approximate strength and then standardised with a standard solution. The common secondary standards are sodium hydroxide, Inorganic acids, Potassium permanganate etc.</p>
                <h3>Normality (N)</h3>
                <p>The number of gram equivalent of the solute per litre of solution is called as the normality of the solution.<br>
                N = No. of Gram Equivalents of Solute / Volume of solution in Liters</p>
                <h3>Molarity (M)</h3>
                <p>The Molarity of a solution is the number of moles of solute per litre of the solution.<br>
                M = No. of Moles of Solute / Volume of solution in Liters</p>
                <h3>Accuracy</h3>
                <p>Accuracy of a measurement system is the degree of closeness of measurements of a quantity to that quantity's actual (true) value. (or) -> It is defined as the concordance between experimental value and true value.</p>
                <h3>Precision</h3>
                <p>Precision of measurement system also called reproducibility or repeatability, is the degree to which repeated measurements under unchanged conditions show the same results (or) it is defined as the degree as the degree of agreement between experimental value and true value.</p>
                <h3>Classification of Titrimetric Reactions:</h3>
                <ol>
                    <li>
                        <h3>Acid - Base Titrations or Neutralization Titrations</h3>
                        <p>-> Titration based upon neutralization reactions are called acid-base titrations.<br>
                        HCl + NaOH ⇌ H<sub>2</sub>O + NaCl<br>
                        H<sup>+</sup> + OH<sup>-</sup> ⇌ H<sub>2</sub>O<br>
                        -> The determination of the concentration of acids by using standard alkali solutions is acidimetry and the reverse process is alkalimetry.</p>
                    </li>
                    <li>
                        <h3>Precipitation Titrations</h3>
                        <p>-> Titration based upon the formation if insoluble precipitates when the reacting solutions are mixed together are called precipitation titrations.<br>
                        -> When a solution of silver nitrate is treated with sodium chloride a white precipitate of silver chloride is obtained.<br>
                        AgNO<sub>3</sub> + NaCl ⇌ NaNO<sub>3</sub> + AgCl</p>
                    </li>
                    <li>
                        <h3>Redox Titrations</h3>
                        <p>Redox titrations are based on a reduction oxidation reaction between an oxidising agent and reducing agent. A Potentiometer or redox indicator is usually used to determine the endpoint of the titration.<br>
                        2KMnO<sub>4</sub> + 5H<sub>2</sub>C<sub>2</sub>O<sub>4</sub> + 3H<sub>2</sub>SO<sub>4</sub> → K<sub>2</sub>SO<sub>4</sub> + 2MnSO<sub>4</sub> + 8H<sub>2</sub>O + 10CO<sub>2</sub> ↑</p>
                    </li>
                    <li>
                        <h3>Complexometric Titrations</h3>
                        <p>Complexometric titrations rely on the Formation of a complex between the analyte and the titrant. In general they require specialised indicators that form weak complexes with the analyte. Common examples are Eriochrome Black T for the titration of calcium and magnesium ions, and the chelating agent EDTA used to titrate metal ions in solution.<br>
                        Ca<sup>2+</sup> + EDTA → Ca-EDTA (Complex)</p>
                    </li>
                </ol>
                <h3>Theory of Indicators</h3>
                <p>An indicator is a substance which is used to determine the end point in a titration. In Acid - Base titrations, Organic substances (Weak acids or weak bases) are generally used as indicators. They change their colour within a certain pH range.<br>
                -> The Colour change and the pH range of some common indicators are tabulated below:</p>
                <table border="1">
                    <thead>
                        <tr>
                            <th>Indicator</th>
                            <th>pH range</th>
                            <th>Colour Change</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Methyl Orange</td>
                            <td>3.2 - 4.5</td>
                            <td>Pink to Yellow</td>
                        </tr>
                        <tr>
                            <td>Methyl Red</td>
                            <td>4.4 - 6.5</td>
                            <td>Red to Yellow</td>
                        </tr>
                        <tr>
                            <td>Litmus</td>
                            <td>5.5 - 7.5</td>
                            <td>Red to Blue</td>
                        </tr>
                        <tr>
                            <td>Phenol Red</td>
                            <td>6.8 - 8.4</td>
                            <td>Yellow to Red</td>
                        </tr>
                        <tr>
                            <td>Phenolphthalein</td>
                            <td>8.3 - 10.5</td>
                            <td>Colourless to Pink</td>
                        </tr>
                    </tbody>
                </table>
                <p>-> Two theories have been proposed to explain the change of colour of acid base indicators with change in pH.</p>
                <ol>
                    <li>
                        <h3>Ostwald's Theory</h3>
                        <p>According to this theory<br>
                        (a) The Colour change is due to ionisation for the acid base indicator. The unionised form that different colour than the ionised form.<br>
                        (b) The ionisation for the indicator is largely affected in acids and bases as it is either a weak acid or a weak base.<br>
                        -> In case the indicator is a weak acid, its ionisation is very much low in acids due to common H<sup>+</sup> ions while it is fairly ionised in alkalis. Similarly if the indicator is a weak base, its ionisation is large in acids and low in alkalis due to common OH ions.<br>
                        -> Considering two important indicators Phenolphthalein (a weak acid) and Methyl orange (a Weak base), Ostwald theory can be illustrated as follows</p>
                        <h5>Phenolphthalein</h5>
                        <p>it can be represented as HPh. It ionizes in solution to a small extent as<br>
                        HPh → H<sup>+</sup> + Ph<sup>-</sup><br>
                        The unionised molecules of Phenolphthalein are colourless while Ph<sup>-</sup> ions are pink in colour.<br>
                        In presence of an acid the ionisation of HPh is practically negligible as the equilibrium shifts to left hand side due to high concentration of H<sup>+</sup> ions, the solution would remain colourless. On addition of alkali, hydrogen ions are removed by OH ions in the form of water molecules and the equilibrium shifts to right hand side. The concentration of Ph ions increases in solution and they impart pink colour to the solution.</p>
                    </li>
                    <li>
                        <h3>Quinonoid theory</h3>
                        <p>According to this theory<br>
                        (a) The acid base indicator exist in two tautomeric forms having different structures, these two forms are in equilibrium. One form is termed benzenoid form and the other is quinonoid form.</p>
                        <div>
                            <img src="/college_lab/images/exp1.jpg" alt="Quinonoid form 1" />
                        </div>
                        <p>(b) The two forms have different colours. The colour change is due to the inter conversion of one tautomeric form into other.<br>
                        (c) One form mainly exists in acidic medium and the other in alkaline medium. Thus during titration the medium changes form acidic to alkaline viceversa. The change in pH converts one tautomeric form into another and thus the colour change occurs. Phenolphthalein has benzenoid form in acidic medium and thus, it is colourless while it has quinonoid form in alkaline medium which has pink colour.</p>
                        <div>
                            <img src="/college_lab/images/exp1_2.jpg" alt="Quinonoid form 2" />
                        </div>
                    </li>
                </ol>
            </div>
            
            <div class="btn-group">
                <button type="button" onclick="previewExp()" style="cursor:pointer; background: #007bff; color:#fff; font-weight:600; padding:8px 16px; border-radius:6px; width: fit-content;">Preview</button>
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
    // Static content HTML - capture it when page loads
    let staticContent = '';
    
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('exp1-form');
        // Capture the content once
        staticContent = document.getElementById('experiment-content').innerHTML;
        
        form.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && e.target.tagName !== 'INPUT') {
                e.preventDefault();
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
        const form = document.getElementById('exp1-form');

        const previewHtml = `<style>
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
                margin: 10px 0;
            }
            table, th, td {
                border: 1px solid #000;
            }
            th, td {
                padding: 8px 10px;
                text-align: center;
            }
            img {
                max-width: 100%;
                height: auto;
                margin: 12px 0;
            }
        </style>
        <div class="header-row">
            <div><b>Experiment No.:</b> 1</div>
            <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
        </div>
        <h2 style="text-align:center; margin-top: 0;">Introduction To Chemistry Laboratory</h2>
        <div>${staticContent}</div>`;

        const win = window.open('', '_blank', 'width=900,height=800');
        win.document.write('<!DOCTYPE html><html><head><title>Preview - Chemistry Experiment 1</title><meta charset="utf-8"></head><body style="font-family:Arial,sans-serif; padding:20px;">');
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
        
        const form = document.getElementById('exp1-form');
        if (!form) {
            alert('Error: Form not found!');
            return;
        }
        
        const subject = 'Chemistry';
        const experiment_number = 1;
        
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

        const submissionHtml = `<style>
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
                margin: 10px 0;
            }
            table, th, td {
                border: 1px solid #000;
            }
            th, td {
                padding: 8px 10px;
                text-align: center;
            }
            img {
                max-width: 100%;
                height: auto;
                margin: 12px 0;
            }
        </style>
        <div class="header-row">
            <div><b>Experiment No.:</b> 1</div>
            <div><b>Date:</b> ${escapeHtml(form.expDate.value || '')}</div>
        </div>
        <h2 style="text-align:center; margin-top: 0;">Introduction To Chemistry Laboratory</h2>
        <div>${staticContent}</div>`;

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
                
                if (data.is_retake) {
                    // Redirect to retake page
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


