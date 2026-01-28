<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Experiment Edit - Editable Table</title>
  <link href="https://fonts.googleapis.com/css?family=Poppins:400,600&display=swap" rel="stylesheet" />
  <style>
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
      width: 200px;
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

    /* Action Buttons */
    .action-buttons {
      display: flex;
      justify-content: flex-end;
      gap: 18px;
      margin-top: 30px;
    }
    .action-buttons button {
      border: none;
      border-radius: 12px;
      padding: 0 36px;
      height: 38px;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      transition: background 0.18s;
    }
    .action-buttons button:nth-child(1) {
      background: #5396ff;
      color: #fff;
    }
    .action-buttons button:nth-child(1):hover {
      background: #2f6fd2;
    }
    
    

    /* Main Content Area */
    .main-content {
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

    /* Table Styles */
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

    /* Table Controls */
    .table-controls {
      display: flex;
      gap: 10px;
      margin: 10px 0;
    }
    .table-controls button {
      background: #1a347a;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 8px 15px;
      font-size: 0.9rem;
      cursor: pointer;
      transition: background 0.15s;
    }
    .table-controls button:hover {
      background: #234ab7;
    }

    /* Form Groups */
    .form-group {
      margin-bottom: 20px;
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

    /* Dynamic Table Wrapper */
    .dynamic-table-wrapper {
      margin-bottom: 30px;
      border: 1px solid #e4eaf4;
      border-radius: 8px;
      padding: 15px;
      background: #f8fafd;
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
      .main-content, .sidebar {
        margin-right: 0;
        margin-top: 0;
      }
      
      .action-buttons {
        flex-wrap: wrap;
        justify-content: center;
      }
      
      .exp-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
      
      .exp-header input[type="text"],
      .exp-header input[type="date"] {
        width: 100%;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <!-- Main Content Area -->
    <div class="main-content">
      <!-- Experiment Header -->
      <div class="exp-header">
        <div class="exp-field">
          <label for="experiment_id">Experiment No.</label>
          <input type="text" id="experiment_id" name="experiment_id" placeholder="Exp. No" />
        </div>
        <div class="exp-field">
          <label for="expDate">Date</label>
          <input type="date" id="expDate" name="expDate" />
        </div>
      </div>

      <!-- Practice Section -->
      <h2 class="section-title">Practice Section</h2>

      <!-- Aim Section -->
      <div class="form-group">
        <label for="aim">Aim</label>
        <textarea id="aim" name="aim" rows="3" placeholder="Enter experiment aim"></textarea>
      </div>

      <!-- Apparatus Section -->
      <div class="form-group">
        <label>Apparatus Used (Drag and Drop)</label>
        <div id="apparatus-dropbox" class="apparatus-dropbox" aria-label="Apparatus dropbox">
          <small id="apparatus-placeholder" style="color:#777;">Drag apparatus here</small>
        </div>
        <input type="hidden" id="apparatus_list" name="apparatus_list" value="">
      </div>

      <!-- Principle Section -->
      <div class="form-group">
        <h3>Principle:</h3>
        <textarea id="theory" name="theory" rows="4" placeholder="Enter theory here..."></textarea>
      </div>

      <!-- Procedure Section -->
      <div class="form-group">
        <h3>Procedure:</h3>
        <textarea id="procedure" name="procedure" rows="4" placeholder="Enter Procedure"></textarea>
      </div>

      <!-- Main Table Section -->
      <div class="form-group">
        <h4>Tabular Form</h4>
        <div class="table-controls">
          <button onclick="addRowStatic()">Add Row</button>
          <button onclick="removeRowStatic()">Remove Row</button>
          <button onclick="addColumnStatic()">Add Column</button>
          <button onclick="removeColumnStatic()">Remove Column</button>
        </div>
        <table id="data-table" class="data-table">
          <tr>
            <th contenteditable="true">Column 1</th>
            <th contenteditable="true">Column 2</th>
            <th contenteditable="true">Result</th>
          </tr>
          <tr>
            <td contenteditable="true"></td>
            <td contenteditable="true"></td>
            <td contenteditable="true"></td>
          </tr>
        </table>
      </div>

      <!-- Dynamic Tables Section -->
      <div class="form-group">
        <div class="table-controls">
          <button id="addTableButton">Add Table</button>
          <button id="removeTableButton">Remove Table</button>
        </div>
        <div id="tables-container"></div>
      </div>

      <!-- Calculation Section -->
      <div class="form-group">
        <label for="calculation">Calculation</label>
        <textarea id="calculation" placeholder="Show your calculations"></textarea>
      </div>

      <!-- Result Section -->
      <div class="form-group">
        <label for="result">Result</label>
        <textarea id="result" placeholder="Write the result of the experiment"></textarea>
      </div>

      <!-- Action Buttons -->
      <div class="action-buttons">
        <button onclick="previewExp()">Preview</button>
       
       <div class="back-btn" 
      onclick="history.back()" 
      style="cursor:pointer; background:#1a347a; color:#fff; font-weight:600; padding:8px 16px; border-radius:12px; width: fit-content;">
      Back
    </div>
      </div>
    </div>

    <!-- Sidebar with apparatus and calculator -->
    <aside class="sidebar">
      <!-- Apparatus Selection -->
      <div class="apparatus-box">
        <h3 style="font-size:22px;margin-bottom:12px;text-align:center;">Select Apparatus</h3>
        <div class="apparatus-list">
          <button type="button" class="apparatus-btn" draggable="true" ondragstart="drag(event)">Beaker</button>
          <button type="button" class="apparatus-btn" draggable="true" ondragstart="drag(event)">Burette</button>
          <button type="button" class="apparatus-btn" draggable="true" ondragstart="drag(event)">Pipette</button>
          <button type="button" class="apparatus-btn" draggable="true" ondragstart="drag(event)">Conical Flask</button>
          <button type="button" class="apparatus-btn" draggable="true" ondragstart="drag(event)">Measuring Cylinder</button>
          <button type="button" class="apparatus-btn" draggable="true" ondragstart="drag(event)">Test Tube</button>
          <button type="button" class="apparatus-btn" draggable="true" ondragstart="drag(event)">Volumetric Flask</button>
          <button type="button" class="apparatus-btn" draggable="true" ondragstart="drag(event)">Thermometer</button>
          <button type="button" class="apparatus-btn" draggable="true" ondragstart="drag(event)">pH Meter</button>
          <button type="button" class="apparatus-btn" draggable="true" ondragstart="drag(event)">Bunsen Burner</button>
        </div>
      </div>

      <!-- Calculator -->
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
        </div>
      </div>
    </aside>
  </div>

  <script>
    // Navigation functionality
    const params = new URLSearchParams(window.location.search);
    const subject = params.get('subject');

    document.getElementById('backBtn').onclick = () => {
      if(subject) {
        window.location.href = `updated_exp.php?subject=${encodeURIComponent(subject)}`;
      } else {
        window.location.href = 'updated_exp.php';
      }
    };

    let tableCounter = 0;

    // Functions for static table
    function addRowStatic() {
      const table = document.getElementById("data-table");
      const row = table.insertRow();
      const cols = table.rows[0].cells.length;
      for (let i = 0; i < cols; i++) {
        let cell = row.insertCell();
        cell.contentEditable = "true";
      }
    }

    function removeRowStatic() {
      const table = document.getElementById("data-table");
      if (table.rows.length > 1) table.deleteRow(-1);
    }

    function addColumnStatic() {
      const table = document.getElementById("data-table");
      const cols = table.rows[0].cells.length;
      const headerRow = table.rows[0];
      const newHeader = headerRow.insertCell(cols);
      newHeader.innerHTML = "New Column";
      newHeader.contentEditable = "true";
      newHeader.style.fontWeight = "bold";
      newHeader.style.background = "#eef3fa";
      for (let i = 1; i < table.rows.length; i++) {
        let newCell = table.rows[i].insertCell(cols);
        newCell.contentEditable = "true";
        newCell.style.background = "#fff";
      }
    }

    function removeColumnStatic() {
      const table = document.getElementById("data-table");
      const cols = table.rows[0].cells.length;
      if (cols > 1) {
        for (let i = 0; i < table.rows.length; i++) {
          table.rows[i].deleteCell(cols - 1);
        }
      }
    }

    // Dynamic table functions
    document.getElementById('addTableButton').addEventListener('click', addTable);
    document.getElementById('removeTableButton').addEventListener('click', removeLastTable);

    function addTable() {
      const container = document.getElementById('tables-container');

      // Create wrapper for table and controls
      const tableWrapper = document.createElement('div');
      tableWrapper.className = 'dynamic-table-wrapper';
      
      const controlsDiv = document.createElement('div');
      controlsDiv.className = 'table-controls';

      const addRowBtn = document.createElement('button');
      addRowBtn.textContent = 'Add Row';

      const removeRowBtn = document.createElement('button');
      removeRowBtn.textContent = 'Remove Row';

      const addColBtn = document.createElement('button');
      addColBtn.textContent = 'Add Column';

      const removeColBtn = document.createElement('button');
      removeColBtn.textContent = 'Remove Column';

      controlsDiv.append(addRowBtn, removeRowBtn, addColBtn, removeColBtn);

      const newTable = document.createElement('table');
      newTable.className = 'data-table';
      newTable.id = `dynamic-table-${tableCounter++}`;
      newTable.innerHTML = `
        <tr>
          <th contenteditable="true">Column 1</th>
          <th contenteditable="true">Column 2</th>
          <th contenteditable="true">Result</th>
        </tr>
        <tr>
          <td contenteditable="true"></td>
          <td contenteditable="true"></td>
          <td contenteditable="true"></td>
        </tr>
      `;

      // Add controls and table to wrapper
      tableWrapper.appendChild(controlsDiv);
      tableWrapper.appendChild(newTable);
      
      // Add wrapper to container
      container.appendChild(tableWrapper);

      // Event listeners scoped to this specific table
      addRowBtn.addEventListener('click', () => {
        const cols = newTable.rows[0].cells.length;
        const row = newTable.insertRow();
        for(let i=0; i < cols; i++){
          let cell = row.insertCell();
          cell.contentEditable = 'true';
        }
      });

      removeRowBtn.addEventListener('click', () => {
        if(newTable.rows.length > 1) newTable.deleteRow(-1);
      });

      addColBtn.addEventListener('click', () => {
        const cols = newTable.rows[0].cells.length;
        const headerRow = newTable.rows[0];
        const newHeader = headerRow.insertCell(cols);
        newHeader.innerHTML = "New Column";
        newHeader.contentEditable = "true";
        newHeader.style.fontWeight = "bold";
        newHeader.style.background = "#eef3fa";

        for(let i=1; i < newTable.rows.length; i++){
          let newCell = newTable.rows[i].insertCell(cols);
          newCell.contentEditable = "true";
          newCell.style.background = "#fff";
        }
      });

      removeColBtn.addEventListener('click', () => {
        const cols = newTable.rows[0].cells.length;
        if(cols > 1){
          for(let i=0; i < newTable.rows.length; i++){
            newTable.rows[i].deleteCell(cols-1);
          }
        }
      });
    }

    function removeLastTable() {
      const container = document.getElementById('tables-container');
      if (container.lastElementChild) {
        container.removeChild(container.lastElementChild);
      }
    }

    // Calculator functions
    function press(val) {
      document.getElementById("calc-display").value += val;
    }
    
    function calculate() {
      try {
        document.getElementById("calc-display").value = eval(
          document.getElementById("calc-display").value
        );
      } catch {
        document.getElementById("calc-display").value = "Error";
      }
    }
    
    function clearCalc() {
      document.getElementById("calc-display").value = "";
    }

    // Drag & Drop functions
    function allowDrop(ev) {
      ev.preventDefault();
    }
    
    function drag(ev) {
      ev.dataTransfer.setData("text", ev.target.innerText);
    }
    
    function drop(ev) {
      ev.preventDefault();
      let data = ev.dataTransfer.getData("text");
      let item = document.createElement("div");
      item.className = "tool-item";
      item.textContent = data;
      item.addEventListener('click', function() {
        this.remove();
      });
      ev.target.appendChild(item);
      document.getElementById('apparatus-placeholder').style.display = 'none';
    }

    // Initialize dropbox
    document.getElementById('apparatus-dropbox').addEventListener('dragover', allowDrop);
    document.getElementById('apparatus-dropbox').addEventListener('drop', drop);

    // Preview function
    function previewExp() {
      const expNo = document.getElementById("experiment_id").value;
      const expDate = document.getElementById("expDate").value;
      const aim = document.getElementById("aim").value;
      const theory = document.getElementById("theory").value;
      const procedure = document.getElementById("procedure").value;
      const calculation = document.getElementById("calculation").value.replace(/\n/g, "<br>");
      const result = document.getElementById("result").value.replace(/\n/g, "<br>");
      
      // Get static table
      let table = document.getElementById("data-table").outerHTML;
      table = table.replace(/ contenteditable="true"/g, "");
      
      // Get dynamic tables
      const dynamicTables = document.querySelectorAll('#tables-container .dynamic-table-wrapper table');
      let dynamicTablesHTML = '';
      dynamicTables.forEach(table => {
        let tableHTML = table.outerHTML;
        tableHTML = tableHTML.replace(/ contenteditable="true"/g, "");
        dynamicTablesHTML += `<div style="margin-bottom: 20px;">${tableHTML}</div>`;
      });
      
      const apparatusList = Array.from(document.querySelectorAll("#apparatus-dropbox .tool-item")).map(e => e.textContent).join(", ");

      const previewContent = `
        <div class="a4-sheet">
          <div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-weight: 600; color: #003366;">
            <div>Experiment No.: ${expNo}</div>
            <div>Date: ${expDate}</div>
          </div>

          <div style="margin-bottom: 15px; font-size: 16px; color: #222;">
            <b>Aim: </b><span>${aim}</span>
          </div>
          <div style="margin-bottom: 15px; font-size: 16px; color: #222;">
            <b>Apparatus Used: </b><span>${apparatusList}</span>
          </div>
          <div style="margin-bottom: 15px; font-size: 16px; color: #222;">
            <b>Principle: </b><span>${theory}</span>
          </div>
          <div style="margin-bottom: 15px; font-size: 16px; color: #222;">
            <b>Procedure: </b><span>${procedure}</span>
          </div>
          <div style="margin-bottom: 15px;">
            <b>Tabular Form:</b>
            <div>${table}</div>
            ${dynamicTablesHTML}
          </div>
          <div style="margin-bottom: 15px; font-size: 16px; color: #222;">
            <b>Calculations: </b><span>${calculation}</span>
          </div>
          <div style="margin-bottom: 15px; font-size: 16px; color: #222;">
            <b>Result: </b><span>${result}</span>
          </div>
        </div>
      `;

      const previewWindow = window.open('', '_blank');
      previewWindow.document.write(`
        <html>
          <head>
            <title>Experiment Preview</title>
            <link href="https://fonts.googleapis.com/css?family=Poppins:400,600&display=swap" rel="stylesheet" />
            <style>
              body { background: #ddd; font-family: 'Poppins', serif; }
              .a4-sheet {
                width: 720px;
                min-height: 1000px;
                margin: 20px auto;
                background: #fff;
                box-shadow: 0 4px 24px rgba(0,0,0,0.1);
                padding: 35px 55px 50px 55px;
                box-sizing: border-box;
              }
              h2, b {
                color: #003366;
              }
              table {
                width: 100%;
                border-collapse: collapse;
                margin: 15px 0;
                table-layout: fixed;
              }
              th, td {
                border: 1px solid #333;
                padding: 8px;
                text-align: center;
                font-size: 14px;
                word-wrap: break-word;
              }
              th {
                font-weight: 600;
                background: #f3f6fa;
              }
              div[style*="margin-left:20px;"] {
                margin-top: 7px;
              }
            </style>
          </head>
          <body>${previewContent}</body>
        </html>
      `);
      previewWindow.document.close();
    }

  
  </script>

</body>
</html>