<?php
// Get selected grade level from filter
$selectedGrade = $_POST['gradeLevelCategory'] ?? '';

// Build the SQL query with optional grade level filter
$sql = "SELECT student.*, parents_info.*, stuEnrolmentInfo.*, enrolment.Grade_level 
        FROM student
        INNER JOIN parents_info ON student.student_id = parents_info.student_id
        INNER JOIN stuEnrolmentInfo ON student.student_id = stuEnrolmentInfo.student_id
        INNER JOIN enrolment ON student.student_id = enrolment.student_id
        WHERE student.enrolment_status = 'active'";

// Add grade level filter if selected
if (!empty($selectedGrade)) {
    $sql .= " AND enrolment.Grade_level = :grade_level";
}

$stmtStudents = $pdo->prepare($sql);

// Bind parameter if grade level is selected
if (!empty($selectedGrade)) {
    $stmtStudents->bindParam(':grade_level', $selectedGrade);
}

$stmtStudents->execute();
$studentsEnrolled = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);
?>
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
}

.scroll-container {
    width: 100%;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    margin-top: 20px;
    overflow-x: auto;
}

.form-table {
    min-width: 2500px;
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

.table-bordered {
    border: 1px solid #000;
    width: 100%;
}

.table-bordered th,
.table-bordered td {
    border: 1px solid #000;
    padding: 4px;
    text-align: center;
    vertical-align: middle;
    font-weight: bold;
}

.table-bordered th {
    background-color: #f8f9fa;
    font-weight: bold;
}

.table-bordered td {
    font-weight: normal;
    height: 35px;
}

.responsive-table {
    overflow-x: auto;
    width: 100%;
}

.loading {
    opacity: 0.6;
    pointer-events: none;
}
</style>
<main>
    <?php
        $stmt = $pdo->prepare("SELECT * FROM sf_add_data");
        $stmt->execute();
        $data_sf_four = $stmt->fetch(PDO::FETCH_ASSOC);
    ?>
    <form class="main-container" id="sfFour-form">
        <input type="hidden" name="id" value="<?= htmlspecialchars($data_sf_four["sf_add_data_id"] ?? '') ?>">
        <div class="col-md-12 d-flex justify-content-between">
            <div class="col-md-3 d-flex align-items-center justify-content-start">
                <img src="../../assets/image/logo.png" alt="No Image" style="width: auto; height: 150px;">
            </div>
            <div class="col-md-6">
                <div class="form-title text-center w-100">
                    <h2>School Form 1 (SF1) School Register</h2>
                    <p class="text-muted">(this replaces Form 1, Master List & STS Form 2-Family Background and Profile)</p>
                </div>
            </div>
            <div class="col-md-3 d-flex align-items-center justify-content-end ">
                <img src="../../assets/image/deped.png" alt="No Image" style="width: 200px; height: auto; transform: translateX(-30px);">
            </div>
        </div>

        <div class="form-section">
            <div class="row mb-2">
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">School ID</label>
                        <input type="text" name="school_id" value="<?= htmlspecialchars($data_sf_four["school_id"] ?? '') ?>" class="me-2 flex-grow-1">
                        <input type="text" name="region" value="<?= htmlspecialchars($data_sf_four["region"] ?? '') ?>" class="flex-grow-1" placeholder="Region">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">Division</label>
                        <input type="text" name="Division" value="<?= htmlspecialchars($data_sf_four["Division"] ?? '') ?>" class="flex-grow-1">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">District</label>
                        <input type="text" name="district" value="<?= htmlspecialchars($data_sf_four["district"] ?? '') ?>" class="flex-grow-1">
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">School Name</label>
                        <input type="text" name="school_name" value="<?= htmlspecialchars($data_sf_four["school_name"] ?? '') ?>" class="flex-grow-1">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">School Year</label>
                        <?php
                            $stmt = $pdo->prepare("SELECT * FROM school_year WHERE school_year_status = 'Active'");
                            $stmt->execute();
                            $sy = $stmt->fetch(PDO::FETCH_ASSOC);
                        ?>
                        <input readonly class="form-control" type="text" name="school_year_name" value="<?= $sy["school_year_name"] ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-2">Grade Level</label>
                        <select id="categoryFilter" name="gradeLevelCategory" class="form-select">
                            <option value="">All Grade Levels</option>
                            <option value="Grade 1" <?= $selectedGrade == 'Grade 1' ? 'selected' : '' ?>>Grade 1</option>
                            <option value="Grade 2" <?= $selectedGrade == 'Grade 2' ? 'selected' : '' ?>>Grade 2</option>
                            <option value="Grade 3" <?= $selectedGrade == 'Grade 3' ? 'selected' : '' ?>>Grade 3</option>
                            <option value="Grade 4" <?= $selectedGrade == 'Grade 4' ? 'selected' : '' ?>>Grade 4</option>
                            <option value="Grade 5" <?= $selectedGrade == 'Grade 5' ? 'selected' : '' ?>>Grade 5</option>
                            <option value="Grade 6" <?= $selectedGrade == 'Grade 6' ? 'selected' : '' ?>>Grade 6</option>
                        </select>
                        <label class="me-2 col-1">Section</label>
                        <input type="text" name="section" value="<?= htmlspecialchars($data_sf_four["section"] ?? '') ?>" class="flex-grow-1" placeholder="Section">
                    </div>
                </div>
            </div>
            
            <div class="scroll-container">
                <div class="responsive-table">
                    <table class="table-bordered" style="width: 100%; border-collapse: collapse; font-size: 11px;">
                        <thead>
                            <tr>
                                <th rowspan="3">LRN</th>
                                <th colspan="2">NAME</th>
                                <th rowspan="3">BIRTH DATE<br>(mm/dd/yy)</th>
                                <th rowspan="3" style="min-width: 80px;">
                                    AGE as of 1st friday of June<br>
                                    <span style="font-size: 9px; font-weight: normal;">(nos. of years as per last birthday)</span>
                                </th>
                                <th colspan="2">BIRTH PLACE</th>
                                <th rowspan="3">MOTHER TONGUE</th>
                                <th rowspan="3">IP<br><span style="font-size: 9px; font-weight: normal;">(Specify ethnic group)</span></th>
                                <th rowspan="3">Religion</th>
                                <th colspan="4">ADDRESS</th>
                                <th colspan="2">NAME OF PARENTS</th>
                                <th colspan="2">GUARDIAN</th>
                                <th rowspan="3">CONTACT NUMBER<br><span style="font-size: 9px; font-weight: normal;">Parent/Guardian</span></th>
                                <th rowspan="3">REMARKS<br><span style="font-size: 9px; font-weight: normal;">(Please refer to the legend on last page)</span></th>
                            </tr>
                            <tr>
                                <!-- NAME sub-headers -->
                                <th rowspan="2">Last Name</th>
                                <th rowspan="2">First Name<br>Middle Name</th>
                                
                                <!-- BIRTH PLACE sub-headers -->
                                <th rowspan="2">Municipality/<br>City</th>
                                <th rowspan="2">Province</th>
                                
                                <!-- ADDRESS sub-headers -->
                                <th>House # / Street<br>Sitio/Purok</th>
                                <th>Barangay</th>
                                <th>Municipality/<br>City</th>
                                <th>Province</th>
                                
                                <!-- PARENTS sub-headers -->
                                <th rowspan="2">Father<br><span style="font-size: 9px; font-weight: normal;">(1st name only if family name identical to learner)</span></th>
                                <th rowspan="2">Mother<br><span style="font-size: 9px; font-weight: normal;">(Maiden: 1st Name, Middle & Last name)</span></th>
                                
                                <!-- GUARDIAN sub-headers -->
                                <th rowspan="2">Name</th>
                                <th rowspan="2">Relationship</th>
                            </tr>
                            <tr>
                                <!-- ADDRESS second level sub-headers -->
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="studentsTableBody">
                            <?php if(empty($studentsEnrolled)): ?>
                                <tr>
                                    <td colspan="20" style="text-align: center; height: 50px;">
                                        <?= empty($selectedGrade) ? 'No students found' : 'No students found in ' . $selectedGrade ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($studentsEnrolled as $student): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($student['lrn'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($student['lname'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($student['fname'] ?? '') . ' ' . htmlspecialchars(substr($student["mname"] ?? '', 0, 1)) . '.' ?></td>
                                        <td><?= $student['birthdate'] ? date('m/d/y', strtotime($student['birthdate'])) : '' ?></td>
                                        <td><?= htmlspecialchars($student['age'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($student['birthplace_city'] ?? $student['birthplace'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($student['birthplace_province'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($student['mother_tongue'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($student['indigenous_people'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($student['religion'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($student['street'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($student['barangay'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($student['city'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($student['province'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($student['f_firstname'] ?? '') . ' ' . htmlspecialchars(substr($student["f_middlename"] ?? '', 0, 1)) . '. ' . htmlspecialchars($student["f_lastname"] ?? '') ?></td>
                                        <td><?= htmlspecialchars($student['m_firstname'] ?? '') . ' ' . htmlspecialchars(substr($student["m_middlename"] ?? '', 0, 1)) . '. ' . htmlspecialchars($student["m_lastname"] ?? '') ?></td>
                                        <td><?= htmlspecialchars($student['g_firstname'] ?? '') . ' ' . htmlspecialchars(substr($student["g_middlename"] ?? '', 0, 1)) . '. ' . htmlspecialchars($student["g_lastname"] ?? '') ?></td>
                                        <td><?= htmlspecialchars($student['g_relationship'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($student['p_contact'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($student['remarks'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="mt-3 text-start">
            <button type="button" id="applyFilter" class="btn btn-primary">Apply Filter</button>
            <button type="button" id="clearFilter" class="btn btn-warning">Clear Filter</button>
            <button type="button" class="btn btn-secondary">Generate Report</button>
        </div>
    </form>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryFilter = document.getElementById('categoryFilter');
    const applyFilterBtn = document.getElementById('applyFilter');
    const clearFilterBtn = document.getElementById('clearFilter');
    const studentsTableBody = document.getElementById('studentsTableBody');
    const form = document.getElementById('sfFour-form');

    // Apply filter button click
    applyFilterBtn.addEventListener('click', function() {
        filterStudents();
    });

    // Clear filter button click
    clearFilterBtn.addEventListener('click', function() {
        categoryFilter.value = '';
        filterStudents();
    });

    // Filter students function
    function filterStudents() {
        const gradeLevel = categoryFilter.value;
        
        // Show loading state
        form.classList.add('loading');
        studentsTableBody.innerHTML = '<tr><td colspan="20" style="text-align: center;">Loading...</td></tr>';

        // Create form data
        const formData = new FormData();
        formData.append('gradeLevelCategory', gradeLevel);

        // AJAX request to filter students
        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            // Create a temporary element to parse the response
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            
            // Extract the table body content from the response
            const newTableBody = tempDiv.querySelector('#studentsTableBody');
            if (newTableBody) {
                studentsTableBody.innerHTML = newTableBody.innerHTML;
            }
            
            form.classList.remove('loading');
        })
        .catch(error => {
            console.error('Error:', error);
            studentsTableBody.innerHTML = '<tr><td colspan="20" style="text-align: center;">Error loading data</td></tr>';
            form.classList.remove('loading');
        });
    }

    // Get the Generate Report button
    const generateReportBtn = document.querySelector('.btn-secondary');
    
    // Add click event listener
    generateReportBtn.addEventListener('click', function(e) {
        e.preventDefault();
        generatePrintableReport();
    });
    
    function generatePrintableReport() {
        // Store original content
        const originalContent = document.querySelector('.main-container').innerHTML;
        
        // Create a print-friendly version
        const printContent = createPrintFriendlyContent();
        
        // Open print window
        const printWindow = window.open('', '_blank', 'width=1200,height=800');
        
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>School Form 1 (SF1) School Register</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 10px;
                        color: #000;
                        font-size: 12px;
                    }
                    .print-container {
                        width: 100%;
                    }
                    .header-section {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 15px;
                    }
                    .logo {
                        height: 80px;
                    }
                    .form-title {
                        text-align: center;
                        border-bottom: 2px solid #0d6efd;
                        padding-bottom: 5px;
                        margin-bottom: 10px;
                    }
                    .form-title h2 {
                        margin: 0;
                        font-size: 16px;
                    }
                    .form-title p {
                        margin: 2px 0 0 0;
                        font-size: 10px;
                        color: #666;
                    }
                    .school-info {
                        margin-bottom: 15px;
                        font-size: 11px;
                    }
                    .school-info table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    .school-info td {
                        padding: 1px 3px;
                        vertical-align: top;
                    }
                    .student-table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 9px;
                        margin-top: 5px;
                    }
                    .student-table th,
                    .student-table td {
                        border: 1px solid #000;
                        padding: 2px;
                        text-align: center;
                        vertical-align: middle;
                    }
                    .student-table th {
                        background-color: #f0f0f0 !important;
                        font-weight: bold;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    .name-column {
                        width: 180px;
                        text-align: left !important;
                    }
                    .birthdate-column {
                        width: 60px;
                    }
                    .age-column {
                        width: 30px;
                    }
                    .sex-column {
                        width: 25px;
                    }
                    .parent-column {
                        width: 100px;
                        text-align: left !important;
                    }
                    .contact-column {
                        width: 80px;
                    }
                    .small-column {
                        width: 40px;
                    }
                    @media print {
                        body { margin: 5mm; }
                        .print-container { width: 100%; }
                        .student-table { font-size: 8px; }
                    }
                    @page {
                        size: landscape;
                        margin: 5mm;
                    }
                </style>
            </head>
            <body>
                ${printContent}
                <script>
                    window.onload = function() {
                        window.print();
                        setTimeout(function() {
                            window.close();
                        }, 500);
                    };
                <\/script>
            </body>
            </html>
        `);
        
        printWindow.document.close();
    }
    
    function createPrintFriendlyContent() {
        // Get school information for the header
        const schoolInfo = `
            <div class="school-info">
                <table>
                    <tr>
                        <td style="width: 25%;"><strong>School ID:</strong> ${document.querySelector('input[name="school_id"]')?.value || ''}</td>
                        <td style="width: 25%;"><strong>Region:</strong> ${document.querySelector('input[name="region"]')?.value || ''}</td>
                        <td style="width: 25%;"><strong>Division:</strong> ${document.querySelector('input[name="Division"]')?.value || ''}</td>
                        <td style="width: 25%;"><strong>District:</strong> ${document.querySelector('input[name="district"]')?.value || ''}</td>
                    </tr>
                    <tr>
                        <td><strong>School Name:</strong> ${document.querySelector('input[name="school_name"]')?.value || ''}</td>
                        <td><strong>School Year:</strong> ${document.querySelector('input[name="school_year_name"]')?.value || ''}</td>
                        <td><strong>Grade Level:</strong> ${document.querySelector('select[name="gradeLevelCategory"]')?.value || 'All'}</td>
                        <td><strong>Section:</strong> ${document.querySelector('input[name="section"]')?.value || ''}</td>
                    </tr>
                </table>
            </div>
        `;
        
        return `
            <div class="print-container">
                <div class="header-section">
                    <img src="../../assets/image/logo.png" alt="School Logo" class="logo">
                    <div class="form-title">
                        <h2>School Form 1 (SF1) School Register</h2>
                        <p>(this replaces Form 1, Master List & STS Form 2-Family Background and Profile)</p>
                    </div>
                    <img src="../../assets/image/deped.png" alt="DepEd Logo" class="logo">
                </div>
                ${schoolInfo}
                ${document.querySelector('.student-table').outerHTML}
                <div style="margin-top: 10px; font-size: 10px; text-align: center;">
                    <p>Generated on: ${new Date().toLocaleDateString()} ${new Date().toLocaleTimeString()}</p>
                </div>
            </div>
        `;
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryFilter = document.getElementById('categoryFilter');
    const applyFilterBtn = document.getElementById('applyFilter');
    const clearFilterBtn = document.getElementById('clearFilter');
    const studentsTableBody = document.getElementById('studentsTableBody');
    const form = document.getElementById('sfFour-form');
    const generateReportBtn = document.querySelector('.btn-secondary');

    // Apply filter button click
    applyFilterBtn.addEventListener('click', function() {
        filterStudents();
    });

    // Clear filter button click
    clearFilterBtn.addEventListener('click', function() {
        categoryFilter.value = '';
        filterStudents();
    });

    // Filter students function
    function filterStudents() {
        const gradeLevel = categoryFilter.value;
        
        // Show loading state
        form.classList.add('loading');
        studentsTableBody.innerHTML = '<tr><td colspan="20" style="text-align: center;">Loading...</td></tr>';

        // Create form data
        const formData = new FormData();
        formData.append('gradeLevelCategory', gradeLevel);

        // AJAX request to filter students
        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            // Create a temporary element to parse the response
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            
            // Extract the table body content from the response
            const newTableBody = tempDiv.querySelector('#studentsTableBody');
            if (newTableBody) {
                studentsTableBody.innerHTML = newTableBody.innerHTML;
            }
            
            form.classList.remove('loading');
        })
        .catch(error => {
            console.error('Error:', error);
            studentsTableBody.innerHTML = '<tr><td colspan="20" style="text-align: center;">Error loading data</td></tr>';
            form.classList.remove('loading');
        });
    }

    // Generate Report button click
    generateReportBtn.addEventListener('click', function(e) {
        e.preventDefault();
        generatePrintableReport();
    });

    function generatePrintableReport() {
        // Get current form values
        const schoolId = document.querySelector('input[name="school_id"]')?.value || '';
        const region = document.querySelector('input[name="region"]')?.value || '';
        const division = document.querySelector('input[name="Division"]')?.value || '';
        const district = document.querySelector('input[name="district"]')?.value || '';
        const schoolName = document.querySelector('input[name="school_name"]')?.value || '';
        const schoolYear = document.querySelector('input[name="school_year_name"]')?.value || '';
        const gradeLevel = document.querySelector('select[name="gradeLevelCategory"]')?.value || 'All';
        const section = document.querySelector('input[name="section"]')?.value || '';

        // Get current date
        const currentDate = new Date().toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        const currentTime = new Date().toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });

        // Create print window
        const printWindow = window.open('', '_blank', 'width=1200,height=800,scrollbars=yes');
        
        // Get the table HTML
        const tableHTML = document.querySelector('.table-bordered').outerHTML;

        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>School Form 1 (SF1) - School Register</title>
                <style>
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                        color: #000;
                        background: white;
                        font-size: 12px;
                        line-height: 1.4;
                    }
                    .print-container {
                        width: 100%;
                        max-width: 100%;
                    }
                    .header-section {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 20px;
                        padding-bottom: 10px;
                        border-bottom: 2px solid #0d6efd;
                    }
                    .logo {
                        height: 80px;
                        width: auto;
                    }
                    .form-title {
                        text-align: center;
                        flex-grow: 1;
                    }
                    .form-title h1 {
                        margin: 0;
                        font-size: 18px;
                        color: #000;
                    }
                    .form-title p {
                        margin: 5px 0 0 0;
                        font-size: 11px;
                        color: #666;
                        font-style: italic;
                    }
                    .school-info {
                        margin-bottom: 15px;
                        padding: 10px;
                        background: #f8f9fa;
                        border-radius: 5px;
                        border: 1px solid #dee2e6;
                    }
                    .school-info table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    .school-info td {
                        padding: 4px 8px;
                        vertical-align: top;
                        border: none;
                    }
                    .school-info strong {
                        color: #000;
                    }
                    .report-table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 15px;
                        font-size: 9px;
                    }
                    .report-table th,
                    .report-table td {
                        border: 1px solid #000;
                        padding: 3px;
                        text-align: center;
                        vertical-align: middle;
                        page-break-inside: avoid;
                    }
                    .report-table th {
                        background-color: #f0f0f0 !important;
                        font-weight: bold;
                        color: #000;
                    }
                    .report-table tr {
                        page-break-inside: avoid;
                        page-break-after: auto;
                    }
                    .footer {
                        margin-top: 20px;
                        padding-top: 10px;
                        border-top: 1px solid #ccc;
                        text-align: center;
                        font-size: 10px;
                        color: #666;
                    }
                    .page-break {
                        page-break-before: always;
                    }
                    @media print {
                        body {
                            margin: 10mm;
                            font-size: 10px;
                        }
                        .print-container {
                            width: 100%;
                        }
                        .report-table {
                            font-size: 8px;
                        }
                        .header-section {
                            margin-bottom: 15px;
                        }
                        .logo {
                            height: 70px;
                        }
                        .no-print {
                            display: none !important;
                        }
                    }
                    @page {
                        size: landscape;
                        margin: 10mm;
                    }
                </style>
            </head>
            <body>
                <div class="print-container">
                    <!-- Header Section -->
                    <div class="header-section">
                        <img src="${window.location.origin + '/../../assets/image/logo.png'}" alt="School Logo" class="logo" onerror="this.style.display='none'">
                        <div class="form-title">
                            <h1>School Form 1 (SF1) School Register</h1>
                            <p>(this replaces Form 1, Master List & STS Form 2-Family Background and Profile)</p>
                        </div>
                        <img src="${window.location.origin + '/../../assets/image/deped.png'}" alt="DepEd Logo" class="logo" onerror="this.style.display='none'">
                    </div>

                    <!-- School Information -->
                    <div class="school-info">
                        <table>
                            <tr>
                                <td style="width: 25%;"><strong>School ID:</strong> ${schoolId}</td>
                                <td style="width: 25%;"><strong>Region:</strong> ${region}</td>
                                <td style="width: 25%;"><strong>Division:</strong> ${division}</td>
                                <td style="width: 25%;"><strong>District:</strong> ${district}</td>
                            </tr>
                            <tr>
                                <td><strong>School Name:</strong> ${schoolName}</td>
                                <td><strong>School Year:</strong> ${schoolYear}</td>
                                <td><strong>Grade Level:</strong> ${gradeLevel}</td>
                                <td><strong>Section:</strong> ${section}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Student Data Table -->
                    <div class="table-container">
                        ${tableHTML}
                    </div>

                    <!-- Footer -->
                    <div class="footer">
                        <p><strong>Generated on:</strong> ${currentDate} at ${currentTime}</p>
                        <p>This is a computer-generated report. No signature required.</p>
                    </div>
                </div>

                <script>
                    // Auto-print and close
                    window.onload = function() {
                        // Add slight delay to ensure all images are loaded
                        setTimeout(function() {
                            window.print();
                            
                            // Close window after printing
                            setTimeout(function() {
                                window.close();
                            }, 1000);
                        }, 500);
                    };

                    // Handle print dialog cancel
                    window.addEventListener('afterprint', function() {
                        setTimeout(function() {
                            window.close();
                        }, 500);
                    });

                    // Fallback close button for mobile/tablet
                    document.addEventListener('DOMContentLoaded', function() {
                        const closeBtn = document.createElement('button');
                        closeBtn.innerHTML = 'Close Window';
                        closeBtn.style.cssText = 'position: fixed; top: 10px; right: 10px; padding: 10px; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer; z-index: 10000;';
                        closeBtn.className = 'no-print';
                        closeBtn.onclick = function() { window.close(); };
                        document.body.appendChild(closeBtn);
                    });
                <\/script>
            </body>
            </html>
        `);

        printWindow.document.close();

        // Focus on the print window
        printWindow.focus();
    }

    // Additional utility functions
    function exportToPDF() {
        // This would require a PDF library like jsPDF or html2pdf.js
        console.log('PDF export functionality would be implemented here');
        alert('PDF export feature would require additional libraries like jsPDF or html2pdf.js');
    }

    function exportToExcel() {
        // Simple CSV export
        const table = document.querySelector('.table-bordered');
        const rows = table.querySelectorAll('tr');
        let csv = [];
        
        rows.forEach(row => {
            const rowData = [];
            const cells = row.querySelectorAll('th, td');
            cells.forEach(cell => {
                rowData.push(cell.textContent.trim());
            });
            csv.push(rowData.join(','));
        });
        
        const csvContent = csv.join('\\n');
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'SF1_School_Register.csv';
        a.click();
        URL.revokeObjectURL(url);
    }

    // Optional: Add right-click context menu for additional export options
    document.addEventListener('contextmenu', function(e) {
        if (e.target.closest('.table-bordered')) {
            e.preventDefault();
            // Could show custom context menu with export options
        }
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+P for print
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            generatePrintableReport();
        }
        // Ctrl+E for Excel export
        if (e.ctrlKey && e.key === 'e') {
            e.preventDefault();
            exportToExcel();
        }
    });

    // Show keyboard shortcuts help
    console.log('Keyboard shortcuts:');
    console.log('Ctrl+P - Generate Printable Report');
    console.log('Ctrl+E - Export to Excel (CSV)');
});
</script>