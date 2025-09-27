 <style>
        main {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
            max-width: 80vw;
            max-height: 90vh !important;
            overflow: auto !important;
        }
        .main-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin: 0 auto;
             max-width: 80vw;
             /* overflow: auto !important; */
        }
        .scroll-container {
            width: 100%;
            /* overflow-x: auto; */
            border: 1px solid #dee2e6;
            border-radius: 4px;
            margin-top: 20px;
        }
        .form-table {
            min-width: 1400px; /* Increased to ensure scrolling */
            width: 100%;
        }
        .form-table > div {
            display: flex;
            border-bottom: 1px solid #dee2e6;
        }
        .form-table > div > div {
            padding: 8px;
            border-right: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .form-table > div > div:last-child {
            border-right: none;
        }
        .header-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        input {
            border: 1px solid #ced4da;
            padding: 4px 8px;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }
        .nested-columns {
            display: flex;
            flex-direction: column;
        }
        .nested-row {
            display: flex;
            flex: 1;
        }
        .nested-cell {
            flex: 1;
            border-right: 1px solid #dee2e6;
            padding: 4px;
            text-align: center;
            font-size: 0.85rem;
        }
        .nested-cell:last-child {
            border-right: none;
        }
        .scroll-indicator {
            text-align: center;
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .form-title {
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .form-section {
            margin-bottom: 15px;
        }
    </style>
<main>
    <div class="main-container">
        <div class="form-title text-center w-100">
            <h2>School Form 4 (SF4) Monthly Learner's Movement and Attendance</h2>
            <p class="text-muted">(this replaces Form 3 & STS Form 4-Absenteeism and Dropout Profile)</p>
        </div>
        
        <div class="form-section">
            <div class="row mb-2">
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">School ID</label>
                        <input type="text" name="schoolID" class="me-2 flex-grow-1">
                        <input type="text" name="region" class="flex-grow-1" placeholder="Region">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">Division</label>
                        <input type="text" name="division" class="flex-grow-1">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">District</label>
                        <input type="text" name="district" class="flex-grow-1">
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">School Name</label>
                        <input type="text" name="schoolName" class="flex-grow-1">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">School Year</label>
                        <input type="text" name="schoolYear" class="flex-grow-1">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">Report for the month of</label>
                        <input type="text" name="reportMonth" class="flex-grow-1">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="scroll-container">
            <div class="form-table">
                <!-- Header Row -->
                <div class="header-row">
                    <div style="flex: 1;">GRADE/YEAR LEVEL</div>
                    <div style="flex: 1;">SECTION</div>
                    <div style="flex: 1.5;">NAME OF ADVISER</div>
                    <div style="flex: 1.5;">REGISTERED LEARNERS (As of End of the Month)</div>
                    <div style="flex: 2;" class="nested-columns">
                        <div>ATTENDANCE</div>
                        <div class="nested-row">
                            <div class="nested-cell">Daily Average</div>
                            <div class="nested-cell">Percentage for the Month</div>
                        </div>
                    </div>
                    <div style="flex: 3;" class="nested-columns">
                        <div>No longer Participating in Learning Activities</div>
                        <div class="nested-row">
                            <div class="nested-cell">(A) Cumulative as of Previous Month</div>
                            <div class="nested-cell">(B) For the Month</div>
                            <div class="nested-cell">(A + B) cumulative as end of the Month</div>
                        </div>
                    </div>
                    <div style="flex: 3;" class="nested-columns">
                        <div>TRANSFERRED OUT</div>
                        <div class="nested-row">
                            <div class="nested-cell">(A) Cumulative as of Previous Month</div>
                            <div class="nested-cell">(B) For the Month</div>
                            <div class="nested-cell">(A + B) cumulative as end of the Month</div>
                        </div>
                    </div>
                    <div style="flex: 3;" class="nested-columns">
                        <div>TRANSFERRED IN</div>
                        <div class="nested-row">
                            <div class="nested-cell">(A) Cumulative as of Previous Month</div>
                            <div class="nested-cell">(B) For the Month</div>
                            <div class="nested-cell">(A + B) cumulative as end of the Month</div>
                        </div>
                    </div>
                </div>
                
                <!-- Data Rows (Sample) -->
                <div>
                    <div style="flex: 1;"><input type="text" placeholder="Grade 7"></div>
                    <div style="flex: 1;"><input type="text" placeholder="Section A"></div>
                    <div style="flex: 1.5;"><input type="text" placeholder="Adviser Name"></div>
                    <div style="flex: 1.5;"><input type="text" placeholder="40"></div>
                    <div style="flex: 2;" class="nested-columns">
                        <div class="nested-row">
                            <div class="nested-cell"><input type="text" placeholder="38"></div>
                            <div class="nested-cell"><input type="text" placeholder="95%"></div>
                        </div>
                    </div>
                    <div style="flex: 3;" class="nested-columns">
                        <div class="nested-row">
                            <div class="nested-cell"><input type="text" placeholder="0"></div>
                            <div class="nested-cell"><input type="text" placeholder="0"></div>
                            <div class="nested-cell"><input type="text" placeholder="0"></div>
                        </div>
                    </div>
                    <div style="flex: 3;" class="nested-columns">
                        <div class="nested-row">
                            <div class="nested-cell"><input type="text" placeholder="0"></div>
                            <div class="nested-cell"><input type="text" placeholder="0"></div>
                            <div class="nested-cell"><input type="text" placeholder="0"></div>
                        </div>
                    </div>
                    <div style="flex: 3;" class="nested-columns">
                        <div class="nested-row">
                            <div class="nested-cell"><input type="text" placeholder="0"></div>
                            <div class="nested-cell"><input type="text" placeholder="1"></div>
                            <div class="nested-cell"><input type="text" placeholder="1"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional sample rows -->
                <div>
                    <div style="flex: 1;"><input type="text" placeholder="Grade 8"></div>
                    <div style="flex: 1;"><input type="text" placeholder="Section B"></div>
                    <div style="flex: 1.5;"><input type="text" placeholder="Adviser Name"></div>
                    <div style="flex: 1.5;"><input type="text" placeholder="42"></div>
                    <div style="flex: 2;" class="nested-columns">
                        <div class="nested-row">
                            <div class="nested-cell"><input type="text" placeholder="40"></div>
                            <div class="nested-cell"><input type="text" placeholder="95.2%"></div>
                        </div>
                    </div>
                    <div style="flex: 3;" class="nested-columns">
                        <div class="nested-row">
                            <div class="nested-cell"><input type="text" placeholder="1"></div>
                            <div class="nested-cell"><input type="text" placeholder="0"></div>
                            <div class="nested-cell"><input type="text" placeholder="1"></div>
                        </div>
                    </div>
                    <div style="flex: 3;" class="nested-columns">
                        <div class="nested-row">
                            <div class="nested-cell"><input type="text" placeholder="0"></div>
                            <div class="nested-cell"><input type="text" placeholder="1"></div>
                            <div class="nested-cell"><input type="text" placeholder="1"></div>
                        </div>
                    </div>
                    <div style="flex: 3;" class="nested-columns">
                        <div class="nested-row">
                            <div class="nested-cell"><input type="text" placeholder="1"></div>
                            <div class="nested-cell"><input type="text" placeholder="0"></div>
                            <div class="nested-cell"><input type="text" placeholder="1"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-3 text-end">
            <button class="btn btn-primary">Save Data</button>
            <button class="btn btn-secondary">Generate Report</button>
        </div>
    </div>

</main>