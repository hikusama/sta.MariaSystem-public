<?php
// Get selected grade level and section from filter
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('admin', 2);

if ($result['res']) {
    header($result['uri']);
    exit;
}
$result = checkURI('admin', 2);

if ($result['res']) {
    header($result['uri']);
    exit;
}
$selectedGrade = $_POST['gradeLevelCategory'] ?? '';
$selectedSection = $_POST['section'] ?? '';

// Build the SQL query with optional filters
$sql = "SELECT student.*, parents_info.*, stuEnrolmentInfo.*, enrolment.Grade_level, enrolment.section_name
        FROM student
        INNER JOIN parents_info ON student.student_id = parents_info.student_id
        INNER JOIN stuEnrolmentInfo ON student.student_id = stuEnrolmentInfo.student_id
        INNER JOIN enrolment ON student.student_id = enrolment.student_id
        WHERE student.enrolment_status = 'active'";

// Add grade level filter if selected
if (!empty($selectedGrade)) {
    $sql .= " AND enrolment.Grade_level = :grade_level";
}

// Add section filter if selected - FIXED: using section_name instead of section_id
if (!empty($selectedSection)) {
    // Get the section name from sections table
    $stmtSectionName = $pdo->prepare("SELECT section_name FROM sections WHERE section_id = :section_id");
    $stmtSectionName->bindParam(':section_id', $selectedSection);
    $stmtSectionName->execute();
    $sectionData = $stmtSectionName->fetch(PDO::FETCH_ASSOC);

    if ($sectionData) {
        $sectionName = $sectionData['section_name'];
        $sql .= " AND enrolment.section_name = :section_name";
    }
}

$stmtStudents = $pdo->prepare($sql);

// Bind parameters if selected
if (!empty($selectedGrade)) {
    $stmtStudents->bindParam(':grade_level', $selectedGrade);
}
if (!empty($selectedSection) && isset($sectionName)) {
    $stmtStudents->bindParam(':section_name', $sectionName);
}

$stmtStudents->execute();
$studentsEnrolled = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

// Get sections based on selected grade level for the dropdown
$sectionsSql = "SELECT * FROM sections WHERE section_status = 'Available'";
if (!empty($selectedGrade)) {
    $sectionsSql .= " AND section_grade_level = :grade_level";
}
$stmtSections = $pdo->prepare($sectionsSql);
if (!empty($selectedGrade)) {
    $stmtSections->bindParam(':grade_level', $selectedGrade);
}
$stmtSections->execute();
$availableSections = $stmtSections->fetchAll(PDO::FETCH_ASSOC);
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
    <form class="main-container" id="sfFour-form" method="POST">
        <div class="mt-3 text-start">
            <button type="submit" class="btn btn-danger">Save Data</button>
            <button type="button" id="applyFilter" class="btn d-none btn-primary">Apply Filter</button>
            <button type="button" id="clearFilter" class="btn d-none btn-warning">Clear Filter</button>
            <button type="button" class="btn btn-secondary">Generate Report</button>
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
                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM school_year WHERE school_year_status = 'Active' LIMIT 1");
                        $stmt->execute();
                        $sy = $stmt->fetch(PDO::FETCH_ASSOC);
                        ?>
                        <input readonly class="form-control" type="text" name="school_year_name" value="<?= $sy["school_year_name"] ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <!-- Make sure your HTML has a form wrapping the filters -->
                        <form method="GET" action="" id="filterForm">
                            <div class="d-flex align-items-center mb-2">
                                <div class="col-md-12 d-flex align-items-center">
                                    <label class="me-2 col-3">Grade Level</label>
                                    <select id="categoryFilter" name="gradeLevelCategory" class="form-select">
                                        <option value="">All Grade Levels</option>
                                        <option value="Grade 1" <?= $selectedGrade == 'Grade 1' ? 'selected' : '' ?>>Grade 1</option>
                                        <option value="Grade 2" <?= $selectedGrade == 'Grade 2' ? 'selected' : '' ?>>Grade 2</option>
                                        <option value="Grade 3" <?= $selectedGrade == 'Grade 3' ? 'selected' : '' ?>>Grade 3</option>
                                        <option value="Grade 4" <?= $selectedGrade == 'Grade 4' ? 'selected' : '' ?>>Grade 4</option>
                                        <option value="Grade 5" <?= $selectedGrade == 'Grade 5' ? 'selected' : '' ?>>Grade 5</option>
                                        <option value="Grade 6" <?= $selectedGrade == 'Grade 6' ? 'selected' : '' ?>>Grade 6</option>
                                    </select>
                                </div>

                                <div class="col-md-12 d-flex align-items-center">
                                    <label class="me-2 col-2">Section</label>
                                    <select name="section" id="section" class="form-select">
                                        <option value="">All Sections</option>
                                        <?php foreach ($availableSections as $section): ?>
                                            <option value="<?= $section["section_id"] ?>" <?= $selectedSection == $section["section_id"] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($section["section_name"]) ?>
                                            </option>
                                        <?php endforeach ?>
                                    </select>
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
                            <?php if (empty($studentsEnrolled)): ?>
                                <tr>
                                    <td colspan="20" style="text-align: center; height: 50px;">
                                        <?php
                                        if (!empty($selectedGrade) && !empty($selectedSection)) {
                                            $sectionName = '';
                                            foreach ($availableSections as $section) {
                                                if ($section['section_id'] == $selectedSection) {
                                                    $sectionName = $section['section_name'];
                                                    break;
                                                }
                                            }
                                            echo 'No students found in ' . $selectedGrade . ' - ' . $sectionName;
                                        } elseif (!empty($selectedGrade)) {
                                            echo 'No students found in ' . $selectedGrade;
                                        } elseif (!empty($selectedSection)) {
                                            $sectionName = '';
                                            foreach ($availableSections as $section) {
                                                if ($section['section_id'] == $selectedSection) {
                                                    $sectionName = $section['section_name'];
                                                    break;
                                                }
                                            }
                                            echo 'No students found in ' . $sectionName;
                                        } else {
                                            echo 'No students found';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($studentsEnrolled as $student): ?>
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
    </form>
</main>
<!-- filtering Js -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get references to the filter elements
        const gradeFilter = document.getElementById('categoryFilter');
        const sectionFilter = document.getElementById('section');

        // REMOVE ALL PREVIOUS EVENT LISTENERS FIRST (clean slate)
        if (gradeFilter) {
            gradeFilter.replaceWith(gradeFilter.cloneNode(true));
        }
        if (sectionFilter) {
            sectionFilter.replaceWith(sectionFilter.cloneNode(true));
        }

        // Get fresh references after cloning
        const freshGradeFilter = document.getElementById('categoryFilter');
        const freshSectionFilter = document.getElementById('section');

        // Find the form that contains the filters
        let form = null;
        if (freshGradeFilter && freshGradeFilter.closest('form')) {
            form = freshGradeFilter.closest('form');
        }

        // If there's no form wrapping the filters, we need to create/manage one
        if (!form) {
            // Check if the filters are in a form somewhere
            const possibleForms = document.querySelectorAll('form');
            for (let f of possibleForms) {
                if (f.contains(freshGradeFilter) || f.contains(freshSectionFilter)) {
                    form = f;
                    break;
                }
            }
        }

        // SIMPLE SOLUTION: Just submit the form when filters change
        if (freshGradeFilter) {
            freshGradeFilter.addEventListener('change', function() {
                // Reset section to "All" when grade changes
                if (freshSectionFilter) {
                    freshSectionFilter.value = "";
                }

                // Submit the form
                if (form) {
                    form.submit();
                } else {
                    // If no form, redirect with parameters
                    redirectWithFilters();
                }
            });
        }

        if (freshSectionFilter) {
            freshSectionFilter.addEventListener('change', function() {
                // Submit the form
                if (form) {
                    form.submit();
                } else {
                    // If no form, redirect with parameters
                    redirectWithFilters();
                }
            });
        }

        // Helper function to redirect with filter parameters
        function redirectWithFilters() {
            const gradeValue = freshGradeFilter ? freshGradeFilter.value : '';
            const sectionValue = freshSectionFilter ? freshSectionFilter.value : '';

            // Get current URL
            const url = new URL(window.location.href);

            // Update or remove grade parameter
            if (gradeValue) {
                url.searchParams.set('gradeLevelCategory', gradeValue);
            } else {
                url.searchParams.delete('gradeLevelCategory');
            }

            // Update or remove section parameter
            if (sectionValue) {
                url.searchParams.set('section', sectionValue);
            } else {
                url.searchParams.delete('section');
            }

            // Remove pagination if exists
            url.searchParams.delete('page');

            // Redirect to new URL
            window.location.href = url.toString();
        }

        // If you want dynamic section loading based on grade (AJAX)
        // Uncomment this section if you want sections to load dynamically
        /*
        if (freshGradeFilter) {
            freshGradeFilter.addEventListener('change', function() {
                const gradeLevel = this.value;
                
                // Clear and disable section filter while loading
                if (freshSectionFilter) {
                    // Save current value
                    const currentValue = freshSectionFilter.value;
                    
                    // Clear all options except first
                    while (freshSectionFilter.options.length > 1) {
                        freshSectionFilter.remove(1);
                    }
                    
                    // Add loading option
                    const loadingOption = new Option('Loading sections...', '', true, true);
                    loadingOption.disabled = true;
                    freshSectionFilter.add(loadingOption);
                    
                    // Disable dropdown
                    freshSectionFilter.disabled = true;
                    
                    // Fetch sections for selected grade
                    fetch(`get-sections.php?grade=${encodeURIComponent(gradeLevel)}`)
                        .then(response => response.json())
                        .then(sections => {
                            // Remove loading option
                            freshSectionFilter.remove(freshSectionFilter.options.length - 1);
                            
                            // Add new sections
                            sections.forEach(section => {
                                const option = new Option(section.section_name, section.section_id);
                                freshSectionFilter.add(option);
                            });
                            
                            // Re-enable dropdown
                            freshSectionFilter.disabled = false;
                            
                            // Try to restore previous selection if it exists in new options
                            if (currentValue) {
                                const optionExists = Array.from(freshSectionFilter.options)
                                    .some(opt => opt.value === currentValue);
                                if (optionExists) {
                                    freshSectionFilter.value = currentValue;
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error loading sections:', error);
                            freshSectionFilter.remove(freshSectionFilter.options.length - 1);
                            freshSectionFilter.disabled = false;
                        });
                }
            });
        }
        */
    });

    // REMOVE ALL OTHER JAVASCRIPT CODE - Only keep the above
</script>

<!-- Generate report JS -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
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