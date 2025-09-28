 <style>
main {
    font-family: Arial, sans-serif;
    background-color: #f8f9fa;
    padding: 20px;
    max-width: 80vw;
    max-height: 88vh !important;
    overflow: auto !important;
}

.main-container {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
    margin: 0 auto;
    min-width: 2500px !important;
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
    min-width: 2500px;
    /* Increased to ensure scrolling */
    width: 100%;
}

.form-table>div {
    display: flex;
    border-bottom: 1px solid #dee2e6;
}

.form-table>div>div {
    padding: 8px;
    border-right: 1px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.form-table>div>div:last-child {
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
             <style>
                table {
                    width: 100%;
                    border-collapse: collapse;
                    text-align: center;
                }

                th,
                td {
                    border: 1px solid #000;
                    padding: 5px;
                    vertical-align: middle;
                }

                th {
                    background: #f8f8f8;
                }
             </style>

             <div class="">
                 <table>
                     <thead>
                         <tr>
                             <th rowspan="2">GRADE / YEAR LEVEL</th>
                             <th rowspan="2">SECTION</th>
                             <th rowspan="2">NAME OF ADVISER</th>
                             <th colspan="3">REGISTERED LEARNERS (As of End of the Month)</th>
                             <th colspan="6">ATTENDANCE</th>
                             <th colspan="9">NO LONGER PARTICIPATING IN LEARNING ACTIVITIES</th>
                             <th colspan="9">TRANSFERRED OUT</th>
                             <th colspan="9">TRANSFERRED IN</th>
                         </tr>
                         <tr>
                             <!-- Registered Learners -->
                             <th colspan="3"></th>

                             <!-- Attendance -->
                             <th colspan="3">Daily Average</th>
                             <th colspan="3">Percentage for the Month</th>

                             <!-- No Longer Participating -->
                             <th colspan="3">(A) Cumulative as of Previous Month</th>
                             <th colspan="3">(B) For the Month</th>
                             <th colspan="3">(A + B) Cumulative as End of Month</th>

                             <!-- Transferred Out -->
                             <th colspan="3">(A) Cumulative as of Previous Month</th>
                             <th colspan="3">(B) For the Month</th>
                             <th colspan="3">(A + B) Cumulative as End of Month</th>

                             <!-- Transferred In -->
                             <th colspan="3">(A) Cumulative as of Previous Month</th>
                             <th colspan="3">(B) For the Month</th>
                             <th colspan="3">(A + B) Cumulative as End of Month</th>
                         </tr>
                         <tr>
                             <!-- Registered Learners breakdown -->
                             <th colspan="3"></th> <!-- spacer for GRADE/SECTION/ADVISER -->
                             <th>M</th>
                             <th>F</th>
                             <th>T</th>

                             <!-- Attendance Daily Average -->
                             <th>M</th>
                             <th>F</th>
                             <th>T</th>
                             <!-- Attendance Percentage -->
                             <th>M</th>
                             <th>F</th>
                             <th>T</th>

                             <!-- NLP in Learning Activities -->
                             <th>M</th>
                             <th>F</th>
                             <th>T</th>
                             <th>M</th>
                             <th>F</th>
                             <th>T</th>
                             <th>M</th>
                             <th>F</th>
                             <th>T</th>

                             <!-- Transferred Out -->
                             <th>M</th>
                             <th>F</th>
                             <th>T</th>
                             <th>M</th>
                             <th>F</th>
                             <th>T</th>
                             <th>M</th>
                             <th>F</th>
                             <th>T</th>

                             <!-- Transferred In -->
                             <th>M</th>
                             <th>F</th>
                             <th>T</th>
                             <th>M</th>
                             <th>F</th>
                             <th>T</th>
                             <th>M</th>
                             <th>F</th>
                             <th>T</th>
                         </tr>
                     </thead>
                     <tbody>
                    <?php
                        $stmt = $pdo->prepare("SELECT * FROM classes");
                        $stmt->execute();
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                     ?>
                         <?php foreach($rows as $row): ?>
                         <tr>
                             <td><?= htmlspecialchars($row['section_name']) ?></td>
                             <td><?= htmlspecialchars($row['section_name']) ?></td>
                             <td><?= htmlspecialchars($row['section_name']) ?></td>

                             <!-- Registered -->
                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>

                             <!-- Attendance Daily Avg -->
                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>

                             <!-- Attendance Percentage -->
                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>

                             <!-- NLP -->
                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>

                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>

                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>

                             <!-- Transferred Out -->
                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>

                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>

                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>

                             <!-- Transferred In -->
                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>

                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>

                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>
                             <td><?= $row['section_name'] ?></td>
                         </tr>
                         <?php endforeach; ?>
                     </tbody>
                 </table>
             </div>

         </div>

         <div class="mt-3 text-end">
             <button class="btn btn-primary">Save Data</button>
             <button class="btn btn-secondary">Generate Report</button>
         </div>
     </div>

 </main>