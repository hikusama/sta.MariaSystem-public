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
        min-width: 1500px !important;
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
        $stmt = $pdo->prepare("SELECT * FROM sf_add_data WHERE sf_type = 'sf_8'");
        $stmt->execute();
        $data_sf_eight = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmtAdviser_data = $pdo->prepare("SELECT * FROM users
        INNER JOIN classes ON users.user_id = classes.adviser_id
        WHERE classes.adviser_id = '$user_id'");
        $stmtAdviser_data->execute();
        $adviser_data = $stmtAdviser_data->fetch(PDO::FETCH_ASSOC);
    ?>
    <form class="main-container" id="sfEight-form">
        <div class="mt-3 text-start">
            <button type="submit" class="btn btn-danger">Save Data</button>
            <button type="button" class="btn btn-secondary" onclick="generateReport()">Generate Report</button>
        </div>
        <input type="hidden" name="id" value="<?= htmlspecialchars($data_sf_eight["sf_add_data_id"] ?? '') ?>">
        <div class="col-md-12 d-flex justify-content-between">
            <div class="col-md-3 d-flex align-items-center justify-content-start">
                <img src="../../assets/image/logo.png" alt="No Image" style="width: auto; height: 150px;">
            </div>
            <div class="col-md-6">
                <div class="form-title text-center w-100">
                    <h2>Department of Education <br> School Form 8 LEarner's Basic Health and Nutrition Report (SF8)
                    </h2>
                    <p class="text-muted">(this replaces Form 1, Master List & STS Form 2-Family Background and Profile)
                    </p>
                </div>
            </div>
            <div class="col-md-3 d-flex align-items-center justify-content-end ">
                <img src="../../assets/image/deped.png" alt="No Image"
                    style="width: 200px; height: auto; transform: translateX(-30px);">
            </div>
        </div>

        <div class="form-section">
            <div class="row mb-2">
                <div class="col-md-3">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">School Name</label>
                        <input type="text" name="school_name"
                            value="<?= htmlspecialchars($data_sf_eight["school_name"] ?? '') ?>" class="flex-grow-1">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">District</label>
                        <input type="text" name="district"
                            value="<?= htmlspecialchars($data_sf_eight["district"] ?? '') ?>" class="flex-grow-1">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">Division</label>
                        <input type="text" name="Division"
                            value="<?= htmlspecialchars($data_sf_eight["Division"] ?? '') ?>" class="flex-grow-1">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">Region</label>
                        <input type="text" name="region" value="<?= htmlspecialchars($data_sf_eight["region"] ?? '') ?>"
                            class="flex-grow-1">
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-2">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">School ID</label>
                        <input type="text" name="school_id"
                            value="<?= htmlspecialchars($data_sf_eight["school_id"] ?? '') ?>" class="me-2 flex-grow-1">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">Grade</label>
                        <input readonly class="form-control" type="text" name="Grade_level"
                            value="<?= $adviser_data["grade_level"] ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">Section</label>
                        <input readonly class="form-control" type="text" name="section_name"
                            value="<?= $adviser_data["section_name"] ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">Track/Strand (SHS)</label>
                        <input readonly class="form-control" type="text" name="section_name" value="">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">School Year</label>
                        <?php
                            $stmt = $pdo->prepare("SELECT * FROM school_year WHERE school_year_status = 'Active'");
                            $stmt->execute();
                            $sy = $stmt->fetch(PDO::FETCH_ASSOC);
                        ?>
                        <input readonly class="form-control" type="text" name="school_year_name"
                            value="<?= $sy["school_year_name"] ?>">
                    </div>
                </div>
            </div>
            <div class="responsive-table">
                <?php
                    $stmtMedicalRecordsMale = $pdo->prepare("SELECT * FROM student
                    INNER JOIN enrolment ON student.student_id = enrolment.student_id
                    INNER JOIN users ON enrolment.adviser_id = users.user_id
                    WHERE users.user_id = '$user_id' AND student.sex = 'MALE'");
                    $stmtMedicalRecordsMale->execute();
                    $studentsMale = $stmtMedicalRecordsMale->fetchAll(PDO::FETCH_ASSOC);

                    $stmtMedicalRecordsFeMale = $pdo->prepare("SELECT * FROM student
                    INNER JOIN enrolment ON student.student_id = enrolment.student_id
                    INNER JOIN users ON enrolment.adviser_id = users.user_id
                    WHERE users.user_id = '$user_id' AND student.sex = 'FEMALE'");
                    $stmtMedicalRecordsFeMale->execute();
                    $studentsFemale = $stmtMedicalRecordsFeMale->fetchAll(PDO::FETCH_ASSOC);
                    $student_count = 1;
                ?>
                <table class="table-bordered table-sm">
                    <thead>
                        <tr>
                            <th rowspan="3">No.</th>
                            <th rowspan="3">LRN</th>
                            <th rowspan="3">Learner's Name <br> (Last Name, First Name, Name <br> Extension, Middle
                                Name)</th>
                            <th rowspan="3">Birthdate (MM/DD/YY)</th>
                            <th rowspan="3">Age</th>
                            <th rowspan="3">Weight <br> (kg)</th>
                            <th rowspan="3">Height <br> (m)</th>
                            <th rowspan="3">Height2 <br> (m2)</th>
                            <th rowspan="1" colspan="2">Nutrition Status</th>
                            <th rowspan="3">Height for <br> Age (HFA)</th>
                            <th rowspan="3">Remarks</th>
                        </tr>
                        <tr>
                            <th colspan="1">BMI <br> (kg/m2)</th>
                            <th colspan="1">BMI <br> Category</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="w-100 bg-warning">
                            <th class="text-start">MALE</th>
                        </tr>
                        <?php foreach($studentsMale as $male) : ?>
                        <tr>
                            <th><?= $student_count++ ?></th>
                            <th><?= htmlspecialchars($male["lrn"]) ?></th>
                            <th><?= htmlspecialchars($male["lname"]) . ', ' . htmlspecialchars($male["fname"]) .
                ' ' . htmlspecialchars($male["suffix"] ?? '') . ' ' . htmlspecialchars($male["mname"])  ?></th>
                            <th><?= htmlspecialchars($male["birthdate"]) ?></th>
                            <th class="calculated-age"></th>
                            <th><?= htmlspecialchars($male["weight"] ?? '') ?></th>
                            <th><?= htmlspecialchars($male["height"] ?? '') ?></th>
                            <th class="calculated-height2"></th>
                            <th class="calculated-bmi"></th>
                            <th class="calculated-bmi-category"></th>
                            <th class="calculated-hfa"></th>
                            <th class="calculated-remarks"></th>
                        </tr>
                        <?php endforeach ?>
                        <tr class="w-100 bg-warning">
                            <th class="text-start">FEMALE</th>
                        </tr>
                        <?php foreach($studentsFemale as $Female) : ?>
                        <tr>
                            <th><?= $student_count++ ?></th>
                            <th><?= htmlspecialchars($Female["lrn"]) ?></th>
                            <th><?= htmlspecialchars($Female["lname"]) . ', ' . htmlspecialchars($Female["fname"]) .
                ' ' . htmlspecialchars($Female["suffix"] ?? '') . ' ' . htmlspecialchars($Female["mname"])  ?></th>
                            <th><?= htmlspecialchars($Female["birthdate"]) ?></th>
                            <th class="calculated-age"></th>
                            <th><?= htmlspecialchars($Female["weight"] ?? '') ?></th>
                            <th><?= htmlspecialchars($Female["height"] ?? '') ?></th>
                            <th class="calculated-height2"></th>
                            <th class="calculated-bmi"></th>
                            <th class="calculated-bmi-category"></th>
                            <th class="calculated-hfa"></th>
                            <th class="calculated-remarks"></th>
                        </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            <h5 class="w-100 text-center mt-5 mb-3">SUMMARY TABLE</h5>
            <div class="responsive-table">
                <table class="table-sm table-bordered">
                    <thead>
                        <tr>
                            <th rowspan="3">SEX</th>
                            <th colspan="6">Nutritional Status</th>
                            <th colspan="5">Height for Age (HFA)</th>
                        </tr>
                        <tr>
                            <th>Severly Wasted</th>
                            <th>Wasted</th>
                            <th>Normal</th>
                            <th>Overweight</th>
                            <th>Obese</th>
                            <th>TOTAL</th>
                            <th>Severly Stunted</th>
                            <th>Stunted</th>
                            <th>Normal</th>
                            <th>Tall</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>MALE</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                        <tr>
                            <th>FEMALE</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                        <tr>
                            <th>TOTAL</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</main>
<script>
function calculateNutritionalData() {
    const tbody = document.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');

    rows.forEach(row => {
        // Skip the header rows (MALE/FEMALE sections)
        if (row.classList.contains('bg-warning')) {
            return;
        }

        const cells = row.querySelectorAll('th');

        // Get the input values - adjust indices since we're using <th> for all cells
        const birthdate = cells[3].textContent.trim();
        const weight = parseFloat(cells[5].textContent) || 0;
        const height = parseFloat(cells[6].textContent) || 0;

        // Skip calculation if essential data is missing
        if (!birthdate || weight <= 0 || height <= 0) {
            // Clear calculated fields if data is missing
            cells[4].textContent = '';
            cells[7].textContent = '';
            cells[8].textContent = '';
            cells[9].textContent = '';
            cells[10].textContent = '';
            cells[11].textContent = 'Missing data';
            return;
        }

        // Calculate Age
        const age = calculateAge(birthdate);
        cells[4].textContent = age;

        // Calculate Height² (m²)
        const height2 = Math.pow(height, 2);
        cells[7].textContent = height2.toFixed(4);

        // Calculate BMI
        const bmi = weight / height2;
        cells[8].textContent = bmi.toFixed(2);

        // Calculate BMI Category
        const bmiCategory = getBMICategory(bmi, age);
        cells[9].textContent = bmiCategory;

        // Calculate Height for Age (HFA)
        const hfa = calculateHFA(height, age);
        cells[10].textContent = hfa;

        // Generate Remarks
        const remarks = generateRemarks(bmiCategory, hfa);
        cells[11].textContent = remarks;

        // Add visual styling based on BMI category
        applyRowStyling(row, bmiCategory, hfa);
    });
}

function calculateAge(birthdate) {
    try {
        // Handle different date formats
        let birthDate;
        if (birthdate.includes('/')) {
            // MM/DD/YY format
            const [month, day, year] = birthdate.split('/');
            const fullYear = year.length === 2 ? `20${year}` : year;
            birthDate = new Date(`${fullYear}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`);
        } else {
            // Assume YYYY-MM-DD format
            birthDate = new Date(birthdate);
        }

        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        return age > 0 ? age : 0;
    } catch (error) {
        console.error('Error calculating age for:', birthdate, error);
        return '';
    }
}

function getBMICategory(bmi, age) {
    if (!bmi || !age) return 'N/A';

    // More accurate BMI categories based on WHO standards for children and adolescents
    if (age >= 2 && age < 5) {
        // For toddlers (2-5 years)
        if (bmi < 14) return 'Severely Underweight';
        if (bmi < 14.5) return 'Underweight';
        if (bmi <= 16.9) return 'Normal';
        if (bmi <= 17.9) return 'Overweight';
        return 'Obese';
    } else if (age >= 5 && age < 19) {
        // For children and adolescents (5-18 years) - simplified ranges
        if (bmi < 14.5) return 'Severely Underweight';
        if (bmi < 16.5) return 'Underweight';
        if (bmi <= 22.9) return 'Normal';
        if (bmi <= 27.9) return 'Overweight';
        return 'Obese';
    } else {
        // For adults (19+ years) - standard WHO categories
        if (bmi < 16) return 'Severely Underweight';
        if (bmi < 18.5) return 'Underweight';
        if (bmi < 25) return 'Normal';
        if (bmi < 30) return 'Overweight';
        return 'Obese';
    }
}

function calculateHFA(height, age) {
    if (!height || !age) return 'N/A';

    // More comprehensive height-for-age ranges based on WHO growth standards
    const expectedHeightRanges = {
        5: {
            min: 1.05,
            max: 1.15
        },
        6: {
            min: 1.10,
            max: 1.22
        },
        7: {
            min: 1.15,
            max: 1.28
        },
        8: {
            min: 1.20,
            max: 1.34
        },
        9: {
            min: 1.25,
            max: 1.39
        },
        10: {
            min: 1.30,
            max: 1.44
        },
        11: {
            min: 1.35,
            max: 1.49
        },
        12: {
            min: 1.40,
            max: 1.55
        },
        13: {
            min: 1.45,
            max: 1.60
        },
        14: {
            min: 1.50,
            max: 1.65
        },
        15: {
            min: 1.53,
            max: 1.68
        },
        16: {
            min: 1.55,
            max: 1.70
        },
        17: {
            min: 1.57,
            max: 1.72
        },
        18: {
            min: 1.58,
            max: 1.73
        },
        19: {
            min: 1.59,
            max: 1.74
        }
    };

    if (expectedHeightRanges[age]) {
        const {
            min,
            max
        } = expectedHeightRanges[age];
        if (height < min - 0.05) return 'Severely Stunted';
        if (height < min) return 'Stunted';
        if (height > max + 0.05) return 'Tall';
        if (height > max) return 'Above Average';
        return 'Normal';
    }

    return 'N/A';
}

function generateRemarks(bmiCategory, hfa) {
    const remarks = [];

    // BMI-based remarks
    if (bmiCategory.includes('Severely Underweight')) {
        remarks.push('Urgent nutritional intervention needed');
    } else if (bmiCategory === 'Underweight') {
        remarks.push('Needs nutritional support');
    } else if (bmiCategory === 'Overweight') {
        remarks.push('Monitor diet and increase physical activity');
    } else if (bmiCategory === 'Obese') {
        remarks.push('Comprehensive weight management program needed');
    }

    // Height-based remarks
    if (hfa.includes('Severely Stunted')) {
        remarks.push('Urgent growth monitoring and intervention');
    } else if (hfa === 'Stunted') {
        remarks.push('Growth monitoring needed');
    } else if (hfa === 'Tall' || hfa === 'Above Average') {
        remarks.push('Monitor growth pattern');
    }

    // Positive remarks
    if (bmiCategory === 'Normal' && hfa === 'Normal') {
        remarks.push('Healthy - maintain current lifestyle');
    }

    return remarks.length > 0 ? remarks.join(', ') : 'No significant concerns';
}

function applyRowStyling(row, bmiCategory, hfa) {
    // Remove existing styling
    row.classList.remove(
        'row-severely-underweight',
        'row-underweight',
        'row-normal',
        'row-overweight',
        'row-obese',
        'row-stunted'
    );

    // Apply BMI-based styling
    if (bmiCategory.includes('Severely Underweight')) {
        row.classList.add('row-severely-underweight');
    } else if (bmiCategory === 'Underweight') {
        row.classList.add('row-underweight');
    } else if (bmiCategory === 'Normal') {
        row.classList.add('row-normal');
    } else if (bmiCategory === 'Overweight') {
        row.classList.add('row-overweight');
    } else if (bmiCategory === 'Obese') {
        row.classList.add('row-obese');
    }

    // Apply height-based styling
    if (hfa.includes('Stunted')) {
        row.classList.add('row-stunted');
    }
}

function displaySummaryInUI(summary) {
    // Create or update summary display in your HTML
    let summaryElement = document.getElementById('summary-statistics');

    if (!summaryElement) {
        summaryElement = document.createElement('div');
        summaryElement.id = 'summary-statistics';
        summaryElement.className = 'alert alert-info mt-3';
        document.querySelector('.form-section').appendChild(summaryElement);
    }

    const normalPercentage = summary.totalStudents > 0 ?
        ((summary.bmiCategories.Normal / summary.totalStudents) * 100).toFixed(1) : 0;

    summaryElement.innerHTML = `
        <h5>Summary Statistics</h5>
        <div class="row">
            <div class="col-md-3">
                <strong>Total Students:</strong> ${summary.totalStudents}<br>
                <strong>Male:</strong> ${summary.maleCount}<br>
                <strong>Female:</strong> ${summary.femaleCount}
            </div>
            <div class="col-md-5">
                <strong>BMI Categories:</strong><br>
                Normal: ${summary.bmiCategories.Normal} (${normalPercentage}%)<br>
                Underweight: ${summary.bmiCategories.Underweight + summary.bmiCategories['Severely Underweight']}<br>
                Overweight/Obese: ${summary.bmiCategories.Overweight + summary.bmiCategories.Obese}
            </div>
            <div class="col-md-4">
                <strong>Height Status:</strong><br>
                Normal: ${summary.hfaCategories.Normal}<br>
                Stunted: ${summary.hfaCategories.Stunted + summary.hfaCategories['Severely Stunted']}<br>
                Above Average: ${summary.hfaCategories['Above Average'] + summary.hfaCategories.Tall}
            </div>
        </div>
    `;
}

// Enhanced initialization with error handling
function initializeCalculation() {
    try {
        // Add CSS for row styling
        addCalculationStyles();

        // Calculate immediately
        calculateNutritionalData();

        // Recalculate every 5 seconds in case data changes (optional)
        setInterval(calculateNutritionalData, 5000);

        console.log('Nutritional data calculation initialized');
    } catch (error) {
        console.error('Error initializing calculation:', error);
    }
}

function addCalculationStyles() {
    const styles = `
        .row-severely-underweight { background-color: #ffcccc !important; }
        .row-underweight { background-color: #ffe6cc !important; }
        .row-normal { background-color: #e6ffe6 !important; }
        .row-overweight { background-color: #fff0cc !important; }
        .row-obese { background-color: #ffcccc !important; }
        .row-stunted { border-left: 4px solid #ff6666 !important; }
        #summary-statistics { font-size: 0.9rem; }
    `;

    const styleSheet = document.createElement('style');
    styleSheet.textContent = styles;
    document.head.appendChild(styleSheet);
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCalculation);
} else {
    initializeCalculation();
}
</script>
<script>
    // Function to calculate and populate summary table
function calculateSummaryTable() {
    const tbody = document.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr')).filter(row => !row.classList.contains('bg-warning'));
    
    // Initialize counters
    const summaryData = {
        male: {
            nutritional: {
                severelyWasted: 0,
                wasted: 0,
                normal: 0,
                overweight: 0,
                obese: 0,
                total: 0
            },
            hfa: {
                severelyStunted: 0,
                stunted: 0,
                normal: 0,
                tall: 0,
                total: 0
            }
        },
        female: {
            nutritional: {
                severelyWasted: 0,
                wasted: 0,
                normal: 0,
                overweight: 0,
                obese: 0,
                total: 0
            },
            hfa: {
                severelyStunted: 0,
                stunted: 0,
                normal: 0,
                tall: 0,
                total: 0
            }
        }
    };
    
    let currentGender = 'male';
    
    // Count each student's data
    rows.forEach(row => {
        // Check if we've moved to female section
        if (row.previousElementSibling && row.previousElementSibling.textContent.includes('FEMALE')) {
            currentGender = 'female';
        }
        
        const cells = row.querySelectorAll('th');
        const bmiCategory = cells[9]?.textContent.trim();
        const hfa = cells[10]?.textContent.trim();
        
        // Skip if data is not calculated
        if (!bmiCategory || bmiCategory === 'N/A' || !hfa || hfa === 'N/A') {
            return;
        }
        
        // Count nutritional status
        switch(bmiCategory) {
            case 'Severely Underweight':
                summaryData[currentGender].nutritional.severelyWasted++;
                break;
            case 'Underweight':
                summaryData[currentGender].nutritional.wasted++;
                break;
            case 'Normal':
                summaryData[currentGender].nutritional.normal++;
                break;
            case 'Overweight':
                summaryData[currentGender].nutritional.overweight++;
                break;
            case 'Obese':
                summaryData[currentGender].nutritional.obese++;
                break;
        }
        
        // Count height for age
        switch(hfa) {
            case 'Severely Stunted':
                summaryData[currentGender].hfa.severelyStunted++;
                break;
            case 'Stunted':
                summaryData[currentGender].hfa.stunted++;
                break;
            case 'Normal':
                summaryData[currentGender].hfa.normal++;
                break;
            case 'Tall':
            case 'Above Average':
                summaryData[currentGender].hfa.tall++;
                break;
        }
        
        // Increment totals
        summaryData[currentGender].nutritional.total++;
        summaryData[currentGender].hfa.total++;
    });
    
    // Calculate totals
    const total = {
        nutritional: {
            severelyWasted: summaryData.male.nutritional.severelyWasted + summaryData.female.nutritional.severelyWasted,
            wasted: summaryData.male.nutritional.wasted + summaryData.female.nutritional.wasted,
            normal: summaryData.male.nutritional.normal + summaryData.female.nutritional.normal,
            overweight: summaryData.male.nutritional.overweight + summaryData.female.nutritional.overweight,
            obese: summaryData.male.nutritional.obese + summaryData.female.nutritional.obese,
            total: summaryData.male.nutritional.total + summaryData.female.nutritional.total
        },
        hfa: {
            severelyStunted: summaryData.male.hfa.severelyStunted + summaryData.female.hfa.severelyStunted,
            stunted: summaryData.male.hfa.stunted + summaryData.female.hfa.stunted,
            normal: summaryData.male.hfa.normal + summaryData.female.hfa.normal,
            tall: summaryData.male.hfa.tall + summaryData.female.hfa.tall,
            total: summaryData.male.hfa.total + summaryData.female.hfa.total
        }
    };
    
    // Populate summary table
    populateSummaryTable(summaryData, total);
    
    return summaryData;
}

// Function to populate the summary table with calculated data
function populateSummaryTable(summaryData, total) {
    const summaryTbody = document.querySelector('.responsive-table:last-child tbody');
    const rows = summaryTbody.querySelectorAll('tr');
    
    // Male row
    const maleCells = rows[0].querySelectorAll('th');
    maleCells[1].textContent = summaryData.male.nutritional.severelyWasted;
    maleCells[2].textContent = summaryData.male.nutritional.wasted;
    maleCells[3].textContent = summaryData.male.nutritional.normal;
    maleCells[4].textContent = summaryData.male.nutritional.overweight;
    maleCells[5].textContent = summaryData.male.nutritional.obese;
    maleCells[6].textContent = summaryData.male.nutritional.total;
    maleCells[7].textContent = summaryData.male.hfa.severelyStunted;
    maleCells[8].textContent = summaryData.male.hfa.stunted;
    maleCells[9].textContent = summaryData.male.hfa.normal;
    maleCells[10].textContent = summaryData.male.hfa.tall;
    maleCells[11].textContent = summaryData.male.hfa.total;
    
    // Female row
    const femaleCells = rows[1].querySelectorAll('th');
    femaleCells[1].textContent = summaryData.female.nutritional.severelyWasted;
    femaleCells[2].textContent = summaryData.female.nutritional.wasted;
    femaleCells[3].textContent = summaryData.female.nutritional.normal;
    femaleCells[4].textContent = summaryData.female.nutritional.overweight;
    femaleCells[5].textContent = summaryData.female.nutritional.obese;
    femaleCells[6].textContent = summaryData.female.nutritional.total;
    femaleCells[7].textContent = summaryData.female.hfa.severelyStunted;
    femaleCells[8].textContent = summaryData.female.hfa.stunted;
    femaleCells[9].textContent = summaryData.female.hfa.normal;
    femaleCells[10].textContent = summaryData.female.hfa.tall;
    femaleCells[11].textContent = summaryData.female.hfa.total;
    
    // Total row
    const totalCells = rows[2].querySelectorAll('th');
    totalCells[1].textContent = total.nutritional.severelyWasted;
    totalCells[2].textContent = total.nutritional.wasted;
    totalCells[3].textContent = total.nutritional.normal;
    totalCells[4].textContent = total.nutritional.overweight;
    totalCells[5].textContent = total.nutritional.obese;
    totalCells[6].textContent = total.nutritional.total;
    totalCells[7].textContent = total.hfa.severelyStunted;
    totalCells[8].textContent = total.hfa.stunted;
    totalCells[9].textContent = total.hfa.normal;
    totalCells[10].textContent = total.hfa.tall;
    totalCells[11].textContent = total.hfa.total;
    
    // Add visual indicators for high numbers
    highlightSummaryTable();
}

// Function to add visual highlighting to summary table
function highlightSummaryTable() {
    const summaryTbody = document.querySelector('.responsive-table:last-child tbody');
    const rows = summaryTbody.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('th');
        
        // Skip the first cell (SEX label)
        for (let i = 1; i < cells.length; i++) {
            const value = parseInt(cells[i].textContent) || 0;
            
            // Remove previous highlighting
            cells[i].classList.remove('highlight-high', 'highlight-medium', 'highlight-low');
            
            // Add highlighting based on value (adjust thresholds as needed)
            if (value >= 5) {
                cells[i].classList.add('highlight-high');
            } else if (value >= 3) {
                cells[i].classList.add('highlight-medium');
            } else if (value > 0) {
                cells[i].classList.add('highlight-low');
            }
        }
    });
}

// Function to calculate percentages (optional - for detailed analysis)
function calculatePercentages(summaryData) {
    const percentages = {
        male: {
            nutritional: {},
            hfa: {}
        },
        female: {
            nutritional: {},
            hfa: {}
        },
        total: {
            nutritional: {},
            hfa: {}
        }
    };
    
    // Calculate male percentages
    Object.keys(summaryData.male.nutritional).forEach(key => {
        percentages.male.nutritional[key] = summaryData.male.nutritional.total > 0 ? 
            ((summaryData.male.nutritional[key] / summaryData.male.nutritional.total) * 100).toFixed(1) + '%' : '0%';
    });
    
    Object.keys(summaryData.male.hfa).forEach(key => {
        percentages.male.hfa[key] = summaryData.male.hfa.total > 0 ? 
            ((summaryData.male.hfa[key] / summaryData.male.hfa.total) * 100).toFixed(1) + '%' : '0%';
    });
    
    // Calculate female percentages
    Object.keys(summaryData.female.nutritional).forEach(key => {
        percentages.female.nutritional[key] = summaryData.female.nutritional.total > 0 ? 
            ((summaryData.female.nutritional[key] / summaryData.female.nutritional.total) * 100).toFixed(1) + '%' : '0%';
    });
    
    Object.keys(summaryData.female.hfa).forEach(key => {
        percentages.female.hfa[key] = summaryData.female.hfa.total > 0 ? 
            ((summaryData.female.hfa[key] / summaryData.female.hfa.total) * 100).toFixed(1) + '%' : '0%';
    });
    
    return percentages;
}

// Function to export summary data (optional)
function exportSummaryData() {
    const summaryData = calculateSummaryTable();
    const percentages = calculatePercentages(summaryData);
    
    console.log('Summary Data:', summaryData);
    console.log('Percentages:', percentages);
    
    // You can add code here to export to CSV, Excel, or display in a modal
    displaySummaryAnalysis(summaryData, percentages);
}

// Function to display detailed analysis (optional)
function displaySummaryAnalysis(summaryData, percentages) {
    let analysisElement = document.getElementById('summary-analysis');
    
    if (!analysisElement) {
        analysisElement = document.createElement('div');
        analysisElement.id = 'summary-analysis';
        analysisElement.className = 'alert alert-secondary mt-3';
        document.querySelector('.form-section').appendChild(analysisElement);
    }
    
    analysisElement.innerHTML = `
        <h5>Detailed Analysis</h5>
        <div class="row">
            <div class="col-md-6">
                <strong>Male Students (${summaryData.male.nutritional.total}):</strong><br>
                - Normal BMI: ${summaryData.male.nutritional.normal} (${percentages.male.nutritional.normal})<br>
                - Underweight: ${summaryData.male.nutritional.wasted + summaryData.male.nutritional.severelyWasted} (${percentages.male.nutritional.wasted})<br>
                - Overweight/Obese: ${summaryData.male.nutritional.overweight + summaryData.male.nutritional.obese} (${percentages.male.nutritional.overweight})
            </div>
            <div class="col-md-6">
                <strong>Female Students (${summaryData.female.nutritional.total}):</strong><br>
                - Normal BMI: ${summaryData.female.nutritional.normal} (${percentages.female.nutritional.normal})<br>
                - Underweight: ${summaryData.female.nutritional.wasted + summaryData.female.nutritional.severelyWasted} (${percentages.female.nutritional.wasted})<br>
                - Overweight/Obese: ${summaryData.female.nutritional.overweight + summaryData.female.nutritional.obese} (${percentages.female.nutritional.overweight})
            </div>
        </div>
    `;
}

// Update your existing calculateNutritionalData function to include summary calculation
function calculateNutritionalData() {
    const tbody = document.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');

    rows.forEach(row => {
        // Skip the header rows (MALE/FEMALE sections)
        if (row.classList.contains('bg-warning')) {
            return;
        }

        const cells = row.querySelectorAll('th');

        // Get the input values
        const birthdate = cells[3].textContent.trim();
        const weight = parseFloat(cells[5].textContent) || 0;
        const height = parseFloat(cells[6].textContent) || 0;

        // Skip calculation if essential data is missing
        if (!birthdate || weight <= 0 || height <= 0) {
            cells[4].textContent = '';
            cells[7].textContent = '';
            cells[8].textContent = '';
            cells[9].textContent = '';
            cells[10].textContent = '';
            cells[11].textContent = 'Missing data';
            return;
        }

        // Calculate Age
        const age = calculateAge(birthdate);
        cells[4].textContent = age;

        // Calculate Height² (m²)
        const height2 = Math.pow(height, 2);
        cells[7].textContent = height2.toFixed(4);

        // Calculate BMI
        const bmi = weight / height2;
        cells[8].textContent = bmi.toFixed(2);

        // Calculate BMI Category
        const bmiCategory = getBMICategory(bmi, age);
        cells[9].textContent = bmiCategory;

        // Calculate Height for Age (HFA)
        const hfa = calculateHFA(height, age);
        cells[10].textContent = hfa;

        // Generate Remarks
        const remarks = generateRemarks(bmiCategory, hfa);
        cells[11].textContent = remarks;

        // Add visual styling based on BMI category
        applyRowStyling(row, bmiCategory, hfa);
    });
    
    // Calculate and populate summary table after all individual calculations
    calculateSummaryTable();
}

// Add CSS for summary table highlighting
function addSummaryTableStyles() {
    const styles = `
        .highlight-high { background-color: #ffcccc !important; font-weight: bold; }
        .highlight-medium { background-color: #fff0cc !important; }
        .highlight-low { background-color: #e6ffe6 !important; }
        #summary-analysis { font-size: 0.85rem; }
    `;
    
    const styleSheet = document.createElement('style');
    styleSheet.textContent = styles;
    document.head.appendChild(styleSheet);
}

// Update your initialization function
function initializeCalculation() {
    try {
        // Add CSS for row styling
        addCalculationStyles();
        addSummaryTableStyles();

        // Calculate immediately
        calculateNutritionalData();

        // Recalculate every 5 seconds in case data changes
        setInterval(calculateNutritionalData, 5000);

        console.log('Nutritional data calculation initialized');
    } catch (error) {
        console.error('Error initializing calculation:', error);
    }
}

// You can also add a button to manually refresh the summary
function addSummaryRefreshButton() {
    const buttonContainer = document.querySelector('.mt-3.text-start');
    const refreshButton = document.createElement('button');
    refreshButton.type = 'button';
    refreshButton.className = 'btn btn-info ms-2';
    refreshButton.textContent = 'Refresh Summary';
    refreshButton.onclick = calculateSummaryTable;
    
    buttonContainer.appendChild(refreshButton);
}

// Call this in your initialization
addSummaryRefreshButton();
</script>
<script>
    // Simple Generate Report Function
function generateReport() {
    console.log('Generate Report function called');
    
    // First ensure all calculations are up to date
    calculateNutritionalData();
    
    // Small delay to ensure calculations complete
    setTimeout(() => {
        try {
            // Get all the data from the form
            const schoolName = document.querySelector('input[name="school_name"]')?.value || 'N/A';
            const district = document.querySelector('input[name="district"]')?.value || 'N/A';
            const division = document.querySelector('input[name="Division"]')?.value || 'N/A';
            const region = document.querySelector('input[name="region"]')?.value || 'N/A';
            const schoolId = document.querySelector('input[name="school_id"]')?.value || 'N/A';
            const gradeLevel = document.querySelector('input[name="Grade_level"]')?.value || 'N/A';
            const section = document.querySelector('input[name="section_name"]')?.value || 'N/A';
            const schoolYear = document.querySelector('input[name="school_year_name"]')?.value || 'N/A';
            
            // Get the current date
            const currentDate = new Date().toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            // Create a new window for printing
            const printWindow = window.open('', '_blank', 'width=1200,height=800');
            
            if (!printWindow) {
                alert('Please allow pop-ups for this site to generate the report.');
                return;
            }
            
            // Write the report content to the new window
            printWindow.document.write(`
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>SF8 Health and Nutrition Report</title>
                    <style>
                        * {
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                        }
                        
                        body {
                            font-family: Arial, sans-serif;
                            font-size: 12px;
                            line-height: 1.4;
                            color: #000;
                            padding: 20px;
                            background: white;
                        }
                        
                        .report-container {
                            max-width: 1100px;
                            margin: 0 auto;
                        }
                        
                        .report-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            margin-bottom: 20px;
                            border-bottom: 2px solid #0d6efd;
                            padding-bottom: 15px;
                        }
                        
                        .school-info {
                            flex: 1;
                            text-align: center;
                        }
                        
                        .school-info h2 {
                            font-size: 16px;
                            margin-bottom: 5px;
                            color: #000;
                        }
                        
                        .school-info h3 {
                            font-size: 14px;
                            margin-bottom: 5px;
                            color: #000;
                        }
                        
                        .text-muted {
                            color: #6c757d !important;
                            font-size: 11px;
                        }
                        
                        .logo-section {
                            display: flex;
                            gap: 20px;
                            align-items: center;
                        }
                        
                        .logo {
                            height: 80px;
                            width: auto;
                        }
                        
                        .school-details {
                            margin-bottom: 20px;
                            padding: 15px;
                            border: 1px solid #dee2e6;
                            border-radius: 5px;
                            background-color: #f8f9fa;
                        }
                        
                        .detail-row {
                            display: flex;
                            flex-wrap: wrap;
                            margin-bottom: 8px;
                        }
                        
                        .detail-item {
                            flex: 1;
                            min-width: 200px;
                            margin-bottom: 5px;
                        }
                        
                        .detail-label {
                            font-weight: bold;
                            margin-right: 5px;
                        }
                        
                        .table-section {
                            margin: 20px 0;
                        }
                        
                        .section-title {
                            text-align: center;
                            font-size: 14px;
                            font-weight: bold;
                            margin: 15px 0;
                            padding: 8px;
                            background-color: #f8f9fa;
                            border: 1px solid #dee2e6;
                        }
                        
                        .table-bordered {
                            border: 1px solid #000;
                            width: 100%;
                            border-collapse: collapse;
                            font-size: 10px;
                        }
                        
                        .table-bordered th,
                        .table-bordered td {
                            border: 1px solid #000;
                            padding: 4px;
                            text-align: center;
                            vertical-align: middle;
                        }
                        
                        .table-bordered th {
                            background-color: #f8f9fa !important;
                            font-weight: bold;
                        }
                        
                        .bg-warning {
                            background-color: #fff3cd !important;
                            font-weight: bold;
                        }
                        
                        .text-start {
                            text-align: left !important;
                        }
                        
                        .summary-section {
                            margin-top: 30px;
                        }
                        
                        .summary-title {
                            text-align: center;
                            font-size: 14px;
                            font-weight: bold;
                            margin: 20px 0 10px 0;
                        }
                        
                        .report-footer {
                            margin-top: 30px;
                            padding-top: 20px;
                            border-top: 1px solid #dee2e6;
                        }
                        
                        .signature-section {
                            display: flex;
                            justify-content: space-between;
                            margin-top: 40px;
                        }
                        
                        .signature {
                            text-align: center;
                            width: 200px;
                        }
                        
                        .signature-line {
                            width: 100%;
                            height: 1px;
                            background-color: #000;
                            margin: 30px 0 5px 0;
                        }
                        
                        .report-meta {
                            text-align: center;
                            margin-top: 20px;
                            font-size: 10px;
                            color: #6c757d;
                        }
                        
                        @media print {
                            body {
                                padding: 10px;
                                font-size: 11px;
                            }
                            
                            .table-bordered {
                                font-size: 9px;
                            }
                            
                            .school-details {
                                background: white !important;
                                border: 1px solid #000 !important;
                            }
                            
                            .section-title {
                                background: white !important;
                                border: 1px solid #000 !important;
                            }
                            
                            .logo {
                                height: 70px;
                            }
                        }
                        
                        @page {
                            size: landscape;
                            margin: 10mm;
                        }
                    </style>
                </head>
                <body>
                    <div class="report-container">
                        <!-- Header -->
                        <div class="report-header">
                            <div >
                                <div>
                                    <img src="../../assets/image/logo.png" alt="No Image" style="width: auto; height: 80px;">
                                </div>
                            </div>
                            <div class="school-info">
                                <h2>Department of Education</h2>
                                <h3>School Form 8 Learner's Basic Health and Nutrition Report (SF8)</h3>
                                <p class="text-muted">(this replaces Form 1, Master List & STS Form 2-Family Background and Profile)</p>
                            </div>
                            <div>
                                <div>
                                    <img src="../../assets/image/deped.png" alt="No Image"
                                            style="width: 150px; height: 70px; transform: translateX(-30px);">
                                </div>
                            </div>
                        </div>

                        <!-- School Information -->
                        <div class="school-details">
                            <div class="detail-row">
                                <div class="detail-item"><span class="detail-label">School Name:</span> ${schoolName}</div>
                                <div class="detail-item"><span class="detail-label">District:</span> ${district}</div>
                                <div class="detail-item"><span class="detail-label">Division:</span> ${division}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-item"><span class="detail-label">Region:</span> ${region}</div>
                                <div class="detail-item"><span class="detail-label">School ID:</span> ${schoolId}</div>
                                <div class="detail-item"><span class="detail-label">Grade Level:</span> ${gradeLevel}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-item"><span class="detail-label">Section:</span> ${section}</div>
                                <div class="detail-item"><span class="detail-label">School Year:</span> ${schoolYear}</div>
                                <div class="detail-item"><span class="detail-label">Date Generated:</span> ${currentDate}</div>
                            </div>
                        </div>

                        <!-- Main Data Table -->
                        <div class="table-section">
                            <div class="section-title">STUDENT HEALTH AND NUTRITION DATA</div>
                            ${document.querySelector('.table-bordered').outerHTML}
                        </div>

                        <!-- Summary Table -->
                        <div class="summary-section">
                            <div class="summary-title">SUMMARY TABLE</div>
                            ${document.querySelectorAll('.table-bordered')[1] ? document.querySelectorAll('.table-bordered')[1].outerHTML : ''}
                        </div>

                        <!-- Footer with Signatures -->
                        <div class="report-footer">
                            <div class="signature-section">
                                <div class="signature">
                                    <p>Prepared by:</p>
                                    <div class="signature-line"></div>
                                    <p>School Nurse/Health Coordinator</p>
                                </div>
                                <div class="signature">
                                    <p>Checked by:</p>
                                    <div class="signature-line"></div>
                                    <p>Class Adviser</p>
                                </div>
                                <div class="signature">
                                    <p>Noted by:</p>
                                    <div class="signature-line"></div>
                                    <p>School Principal</p>
                                </div>
                            </div>
                            
                            <div class="report-meta">
                                <p>Generated on: ${currentDate} | SF8 Health and Nutrition Report</p>
                            </div>
                        </div>
                    </div>
                </body>
                </html>
            `);
            
            printWindow.document.close();
            
        } catch (error) {
            console.error('Error generating report:', error);
            alert('Error generating report. Please check the console for details.');
        }
    }, 1000);
}

// Test function to verify the button works
function testGenerateReport() {
    console.log('Test function called - button is working!');
    alert('Generate Report button is working! The report will open in a new window.');
    generateReport();
}

// Initialize the application
function initializeApplication() {
    console.log('Initializing SF8 Application...');
    
    // Add CSS for row styling
    addCalculationStyles();
    addSummaryTableStyles();

    // Calculate data immediately
    calculateNutritionalData();

    console.log('SF8 Application initialized successfully');
}

// Make sure the functions are available globally
window.generateReport = generateReport;
window.testGenerateReport = testGenerateReport;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing application...');
    initializeApplication();
});

// If DOM is already loaded, initialize immediately
if (document.readyState === 'interactive' || document.readyState === 'complete') {
    console.log('DOM already ready, initializing immediately...');
    initializeApplication();
}
</script>