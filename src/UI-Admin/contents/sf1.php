<?php
require_once __DIR__ . '/../../../tupperware.php';

// Security check
$result = checkURI('admin', 2);
if ($result['res']) {
    header($result['uri']);
    exit;
}

// AJAX request for filtering students
if (isset($_POST['ajax']) && $_POST['ajax'] == 'true') {
    $school_year = $_POST['school_year'] ?? '';
    $selectedGrade = $_POST['gradeLevelCategory'] ?? '';
    $selectedSection = $_POST['section'] ?? '';

    // Default to active school year if not provided
    if (!$school_year) {
        $currentSyStmt = $pdo->prepare("SELECT school_year_id, school_year_name FROM school_year WHERE school_year_status = 'Active' LIMIT 1");
        $currentSyStmt->execute();
        $currentSy = $currentSyStmt->fetch(PDO::FETCH_ASSOC);
        $school_year = $currentSy['school_year_id'] ?? null;
    }

    // Build SQL with optional filters
    $sql = "SELECT student.*, parents_info.*, stuenrolmentinfo.*, enrolment.Grade_level, enrolment.section_name
            FROM student
            INNER JOIN parents_info ON student.student_id = parents_info.student_id
            INNER JOIN stuenrolmentinfo ON student.student_id = stuenrolmentinfo.student_id
            INNER JOIN enrolment ON student.student_id = enrolment.student_id
            LEFT JOIN school_year ON school_year.school_year_id = enrolment.school_year_id
            WHERE student.enrolment_status = 'active'";

    if (!empty($school_year)) $sql .= " AND school_year.school_year_id = :syFilter";
    if (!empty($selectedGrade)) $sql .= " AND enrolment.Grade_level = :grade_level";
    if (!empty($selectedSection)) {
        $stmtSection = $pdo->prepare("SELECT section_name FROM sections WHERE section_id = :section_id");
        $stmtSection->execute([':section_id' => $selectedSection]);
        $sectionName = $stmtSection->fetchColumn();
        if ($sectionName) $sql .= " AND enrolment.section_name = :section_name";
    }

    $stmt = $pdo->prepare($sql);
    if (!empty($school_year)) $stmt->bindParam(':syFilter', $school_year);
    if (!empty($selectedGrade)) $stmt->bindParam(':grade_level', $selectedGrade);
    if (!empty($selectedSection) && isset($sectionName)) $stmt->bindParam(':section_name', $sectionName);
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get sections for grade dropdown
    $sectionsSql = "SELECT * FROM sections WHERE section_status='Available'";
    if (!empty($selectedGrade)) $sectionsSql .= " AND section_grade_level = :grade_level";
    $stmtSections = $pdo->prepare($sectionsSql);
    if (!empty($selectedGrade)) $stmtSections->bindParam(':grade_level', $selectedGrade);
    $stmtSections->execute();
    $availableSections = $stmtSections->fetchAll(PDO::FETCH_ASSOC);

    ob_start();
    if (empty($students)) {
        echo "<tr><td colspan='20' style='text-align:center;'>No students found</td></tr>";
    } else {
        foreach ($students as $s) {
            echo "<tr>
                <td>" . htmlspecialchars($s['lrn'] ?? '') . "</td>
                <td>" . htmlspecialchars($s['lname'] ?? '') . "</td>
                <td>" . htmlspecialchars($s['fname'] ?? '') . " " . htmlspecialchars(substr($s['mname'] ?? '', 0, 1)) . ".</td>
                <td>" . ($s['birthdate'] ? date('m/d/y', strtotime($s['birthdate'])) : '') . "</td>
                <td>" . htmlspecialchars($s['age'] ?? '') . "</td>
                <td>" . htmlspecialchars($s['birthplace_city'] ?? $s['birthplace'] ?? '') . "</td>
                <td>" . htmlspecialchars($s['birthplace_province'] ?? '') . "</td>
                <td>" . htmlspecialchars($s['mother_tongue'] ?? '') . "</td>
                <td>" . htmlspecialchars($s['indigenous_people'] ?? '') . "</td>
                <td>" . htmlspecialchars($s['religion'] ?? '') . "</td>
                <td>" . htmlspecialchars($s['street'] ?? '') . "</td>
                <td>" . htmlspecialchars($s['barangay'] ?? '') . "</td>
                <td>" . htmlspecialchars($s['city'] ?? '') . "</td>
                <td>" . htmlspecialchars($s['province'] ?? '') . "</td>
                <td>" . htmlspecialchars($s['f_firstname'] ?? '') . " " . htmlspecialchars(substr($s['f_middlename'] ?? '', 0, 1)) . ". " . htmlspecialchars($s['f_lastname'] ?? '') . "</td>
                <td>" . htmlspecialchars($s['m_firstname'] ?? '') . " " . htmlspecialchars(substr($s['m_middlename'] ?? '', 0, 1)) . ". " . htmlspecialchars($s['m_lastname'] ?? '') . "</td>
                <td>" . htmlspecialchars($s['g_firstname'] ?? '') . " " . htmlspecialchars(substr($s['g_middlename'] ?? '', 0, 1)) . ". " . htmlspecialchars($s['g_lastname'] ?? '') . "</td>
                <td>" . htmlspecialchars($s['g_relationship'] ?? '') . "</td>
                <td>" . htmlspecialchars($s['p_contact'] ?? '') . "</td>
                <td>" . htmlspecialchars($s['remarks'] ?? '') . "</td>
            </tr>";
        }
    }

    $html = ob_get_clean();
    echo json_encode([
        'html' => $html,
        'sections' => $availableSections
    ]);
    exit;
}

// Get active school year name
$currentSyStmt = $pdo->prepare("SELECT school_year_id, school_year_name FROM school_year WHERE school_year_status='Active' LIMIT 1");
$currentSyStmt->execute();
$currentSy = $currentSyStmt->fetch(PDO::FETCH_ASSOC);
$school_year_name = $currentSy['school_year_name'] ?? '';
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
    $stmt = $pdo->prepare("SELECT * FROM sf_add_data WHERE sf_add_data_id = 1");
    $stmt->execute();
    $data_sf_four = $stmt->fetch(PDO::FETCH_ASSOC);
    ?>
    <form class="main-container" id="sfFour-form" method="POST">
        <div class="mt-3 text-start" style="display: flex;gap: 1rem;">
            <button type="submit" class="btn btn-danger">Save Data</button>
            <button type="button" id="applyFilter" class="btn d-none btn-primary">Apply Filter</button>
            <button type="button" id="clearFilter" class="btn d-none btn-warning">Clear Filter</button>
            <button type="button" class="btn btn-secondary">Generate Report</button>
            <div class="col-md-4 text-start">
                <select id="syFilter" name="school_year" class="form-select" style="max-width: 200px;">
                    <?php
                    $catStmt = $pdo->query("
                            SELECT school_year_id, school_year_name, school_year_status
                            FROM school_year
                            ORDER BY 
                                CASE WHEN school_year_status = 'Active' THEN 0 ELSE 1 END,
                                school_year_name ASC
                        ");

                    $activeSyId = null;
                    $yr['school_year_id'] = null;
                    $yr['school_year_name'] = null;
                    $schoolYears = [];
                    while ($cat = $catStmt->fetch(PDO::FETCH_ASSOC)) {
                        if ($cat['school_year_status'] === 'Active' && $activeSyId === null) {
                            $activeSyId = $cat['school_year_id'];
                            $yr['school_year_id'] = $cat['school_year_id'];
                            $yr['school_year_name'] = $cat['school_year_name'];
                        }
                        $schoolYears[] = $cat;
                    }
                    ?>
                    <?php foreach ($schoolYears as $sy): ?>
                        <option value="<?= htmlspecialchars($sy['school_year_id']) ?>"
                            <?= ($sy['school_year_id'] == $activeSyId) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sy['school_year_name']) ?>
                            <?= $sy['school_year_status'] === 'Active' ? ' (Active)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
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
                        <label class="me-2 col-1">District</label>
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
                        <input readonly class="form-control" type="text" name="school_year_name" value="<?= $school_year_name ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <!-- Make sure your HTML has a form wrapping the filters -->
                        <form method="GET" action="" id="filterForm">
                            <div class="d-flex align-items-center mb-2">
                                <div class="col-md-12">
                                    <div class="d-flex align-items-center mb-2">
                                        <select id="categoryFilter" name="gradeLevelCategory" class="form-select">
                                            <option value="">All Grade Levels</option>
                                            <option value="Grade 1">Grade 1</option>
                                            <option value="Grade 2">Grade 2</option>
                                            <option value="Grade 3">Grade 3</option>
                                            <option value="Grade 4">Grade 4</option>
                                            <option value="Grade 5">Grade 5</option>
                                            <option value="Grade 6">Grade 6</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="d-flex align-items-center mb-2">
                                        <select name="section" id="section" class="form-select">
                                            <option value="">All Sections</option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                            <!-- Hidden submit button -->
                            <button type="submit" style="display: none;">Submit</button>
                        </form>
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

                        </thead>
                        <tbody id="studentsTableBody">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
</main>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const gradeFilter = document.getElementById('categoryFilter');
        const sectionFilter = document.getElementById('section');
        const syFilter = document.getElementById('syFilter');
        const studentsTableBody = document.getElementById('studentsTableBody');

        async function fetchStudents() {
            const formData = new FormData();
            formData.append('ajax', 'true');
            formData.append('school_year', syFilter.value);
            formData.append('gradeLevelCategory', gradeFilter.value);
            formData.append('section', sectionFilter.value);

            studentsTableBody.innerHTML = '<tr><td colspan="20" style="text-align:center;">Loading...</td></tr>';

            try {
                const res = await fetch('contents/sf1.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                // Update table
                studentsTableBody.innerHTML = data.html;

                // Update section dropdown
                const prevValue = sectionFilter.value;
                sectionFilter.innerHTML = '<option value="">All Sections</option>';
                data.sections.forEach(sec => {
                    const opt = document.createElement('option');
                    opt.value = sec.section_id;
                    opt.textContent = sec.section_name;
                    if (sec.section_id == prevValue) opt.selected = true;
                    sectionFilter.appendChild(opt);
                });
            } catch (err) {
                console.error(err);
                studentsTableBody.innerHTML = '<tr><td colspan="20" style="text-align:center;">Error loading data</td></tr>';
            }
        }

        // Event listeners
        gradeFilter.addEventListener('change', () => {
            sectionFilter.value = '';
            fetchStudents();
        });
        sectionFilter.addEventListener('change', fetchStudents);
        syFilter.addEventListener('change', fetchStudents);

        // Initial load
        fetchStudents();
    });
</script>



<!-- Generate report JS -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const syFilter = document.getElementById('syFilter');
        const categoryFilter = document.getElementById('categoryFilter');
        const sectionFilter = document.getElementById('section');
        const applyFilterBtn = document.getElementById('applyFilter');
        const clearFilterBtn = document.getElementById('clearFilter');
        const studentsTableBody = document.getElementById('studentsTableBody');
        const generateReportBtn = document.querySelector('.btn-secondary');

        // Apply filter
        applyFilterBtn.addEventListener('click', filterStudents);

        // Clear filter
        clearFilterBtn.addEventListener('click', function() {
            categoryFilter.value = '';
            sectionFilter.value = '';
            filterStudents();
        });

        // Filter students function
        function filterStudents() {
            const gradeLevel = categoryFilter.value;
            const section = sectionFilter.value;
            const formData = new FormData();

            formData.append('gradeLevelCategory', gradeLevel);
            formData.append('section', section);

            studentsTableBody.innerHTML = '<tr><td colspan="20" style="text-align: center;">Loading...</td></tr>';

            fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(html => {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    const newTableBody = tempDiv.querySelector('#studentsTableBody');
                    if (newTableBody) {
                        studentsTableBody.innerHTML = newTableBody.innerHTML;
                    }

                    // Update sections dropdown from the response
                    const newSectionFilter = tempDiv.querySelector('#section');
                    if (newSectionFilter) {
                        sectionFilter.innerHTML = newSectionFilter.innerHTML;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    studentsTableBody.innerHTML = '<tr><td colspan="20" style="text-align: center;">Error loading data</td></tr>';
                });
        }

        // Generate Report
        generateReportBtn.addEventListener('click', generatePrintableReport);
        syFilter.addEventListener('change', );

        function generatePrintableReport() {
            // Get form data
            const schoolData = {
                schoolId: document.querySelector('input[name="school_id"]')?.value || '',
                region: document.querySelector('input[name="region"]')?.value || '',
                division: document.querySelector('input[name="Division"]')?.value || '',
                district: document.querySelector('input[name="district"]')?.value || '',
                schoolName: document.querySelector('input[name="school_name"]')?.value || '',
                schoolYear: document.querySelector('input[name="school_year_name"]')?.value || '',
                gradeLevel: document.querySelector('select[name="gradeLevelCategory"]')?.value || 'All',
                section: document.querySelector('select[name="section"] option:checked')?.textContent || 'All Sections'
            };

            const currentDate = new Date().toLocaleDateString();
            const currentTime = new Date().toLocaleTimeString();

            // Create print window
            const printWindow = window.open('', '_blank', 'width=1300,height=800');

            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                <?=
                '<link rel="icon" href="' . base_url() . '/assets/image/logo2.png" type="image/x-icon">'
                ?>
                    <title>School Form 1 (SF1) - School Register</title>
                    <style>
                        body { 
                            font-family: Arial; 
                            margin: 15px; 
                            font-size: 11px; 
                            background: white;
                        }
                        .header { 
                            display: flex; 
                            justify-content: space-between; 
                            align-items: center; 
                            margin-bottom: 15px;
                            border-bottom: 2px solid #0066cc;
                            padding-bottom: 10px;
                        }
                        .logo { 
                            height: 70px; 
                        }
                        .title { 
                            text-align: center; 
                        }
                        .title h1 { 
                            margin: 0; 
                            font-size: 16px; 
                            color: #0066cc;
                        }
                        .title p { 
                            margin: 5px 0 0 0; 
                            font-size: 10px; 
                            color: #666; 
                        }
                        .school-info {
                            background: #f8f8f8;
                            padding: 10px;
                            border-radius: 5px;
                            margin-bottom: 15px;
                            border: 1px solid #ddd;
                        }
                        .info-row {
                            display: flex;
                            margin-bottom: 5px;
                        }
                        .info-item {
                            flex: 1;
                            padding: 0 10px;
                        }
                        .info-item strong {
                            color: #333;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            font-size: 9px;
                            margin-top: 10px;
                        }
                        th, td {
                            border: 1px solid black !important;
                            padding: 4px 3px;
                            text-align: center;
                            vertical-align: middle;
                        }
                        th {
                            background-color: #e6e6e6 !important;
                            font-weight: bold;
                            border-bottom: 2px solid black !important;
                        }
                        .footer {
                            margin-top: 20px;
                            text-align: center;
                            font-size: 9px;
                            color: #666;
                            border-top: 1px solid #ccc;
                            padding-top: 10px;
                        }
                        @media print {
                            body { margin: 10mm; }
                            table { font-size: 8px; }
                            th, td { padding: 3px 2px; }
                        }
                        @page {
                            size: landscape;
                            margin: 10mm;
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <img src="../../assets/image/logo.png" alt="no image" class="logo">
                        <div class="title">
                            <h1>School Form 1 (SF1) School Register</h1>
                            <p>(this replaces Form 1, Master List & STS Form 2-Family Background and Profile)</p>
                        </div>
                        <img src="../../assets/image/deped.png" class="logo">
                    </div>

                    <div class="school-info">
                        <div class="info-row">
                            <div class="info-item"><strong>School ID:</strong> ${schoolData.schoolId}</div>
                            <div class="info-item"><strong>Region:</strong> ${schoolData.region}</div>
                            <div class="info-item"><strong>Division:</strong> ${schoolData.division}</div>
                            <div class="info-item"><strong>District:</strong> ${schoolData.district}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-item"><strong>School Name:</strong> ${schoolData.schoolName}</div>
                            <div class="info-item"><strong>School Year:</strong> ${schoolData.schoolYear}</div>
                            <div class="info-item"><strong>Grade Level:</strong> ${schoolData.gradeLevel}</div>
                            <div class="info-item"><strong>Section:</strong> ${schoolData.section}</div>
                        </div>
                    </div>

                    ${document.querySelector('.table-bordered').outerHTML}

                    <div class="footer">
                        <p><strong>Generated on:</strong> ${currentDate} at ${currentTime}</p>
                        <p>Computer-generated report</p>
                    </div>

                    <script>
                        window.onload = function() {
                            setTimeout(() => {
                                window.print();
                                setTimeout(() => window.close(), 1000);
                            }, 500);
                        };
                    <\/script>
                </body>
                </html>
            `);

            printWindow.document.close();
        }
    });
</script>