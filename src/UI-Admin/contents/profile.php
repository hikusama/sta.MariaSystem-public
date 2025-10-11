<?php
    isset($_GET["student_id"]) ? $student_id = $_GET["student_id"] : '';
    $query = "SELECT student.*, users.*, stuenrolmentinfo.*, parents_info.* FROM student
    INNER JOIN users ON student.guardian_id = users.user_id
    INNER JOIN stuenrolmentinfo ON student.student_id = stuenrolmentinfo.student_id 
    INNER JOIN parents_info ON student.student_id = parents_info.student_id 
    WHERE student.student_id = '$student_id'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $student_info = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<style>
/* Custom styling for the tab buttons */
.col-md-8 button {
    padding: 10px 20px;
    border-radius: 5px;
    transition: all 0.3s ease;
    font-weight: 500;
    color: #495057;
    position: relative;
    cursor: pointer;
}

.col-md-8 button:hover {
    background-color: #e9ecef;
    color: #dc3545;
}

.col-md-8 button.Active {
    color: #dc3545;
    font-weight: 600;
}

.col-md-8 button.Active::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    width: 100%;
    height: 3px;
    background-color: #dc3545;
    border-radius: 3px;
}

/* Content area styling */
#displayStudentInfo,
#displayAttendance,
#displayGrades {
    height: 600px !important;
    padding: 20px;
    border-radius: 8px;
    background-color: #f8f9fa;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    overflow-y: scroll;
}

/* Sidebar styling */
.col-md-4 .border.rounded.shadow {
    padding: 20px;
    background-color: white;
}

.col-md-4 img {
    border-radius: 8px;
    margin-bottom: 15px;
}

.col-md-4 span {
    display: block;
    margin-bottom: 10px;
    font-size: 15px;
}
</style>
<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="mx-2 col-md-4">
        <h4><i class="fa-solid fa-user me-2"></i>Learners Profile</h4>
    </div>
    <div class="col-md-8 d-flex justify-content-between px-5">
        <button id="personal_info" class="border-0 bg-transparent Active">Personal Information</button>
        <button id="attendance" class="border-0 bg-transparent">Attendance</button>
        <button id="medical" class="border-0 bg-transparent">Medical</button>
        <button id="grades" class="border-0 bg-transparent">Grades</button>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="col-md-11 border rounded shadow d-flex flex-column align-items-center justify-conten-center">
            <div class="col-md-12">
                <a href="index.php?page=contents/learners" class="btn btn-sm btn-danger">Back</a>
            </div>

            <img src="../../assets/image/users.png" style="width: 200px; height: auto;">
            <span>Lrn: <strong><?= $student_info["lrn"] ?></strong></span>
            <span>Stduent: <strong><?= htmlSpecialChars($student_info["fname"]) . " " .
                    htmlspecialchars(substr($student_info["mname"], 0,1)) . ". " .
                    htmlspecialchars($student_info["lname"]) ?></strong></span>
            <span>Guardian: <strong><?= htmlSpecialChars($student_info["firstname"]) . " " .
                    htmlspecialchars(substr($student_info["middlename"], 0,1)) . ". " .
                    htmlspecialchars($student_info["lastname"]) ?></strong></span>
        </div>
    </div>
    <div class="col-md-8">
        <form id="displayStudentInfo" class="student-Info gap-2" style="display: flex; flex-wrap: wrap !important;">
            <input type="hidden" name="student_id" value="<?= $student_info["student_id"] ?>">
            <div class="col-md-3">
                <label class="form-label">First Name</label>
                <input type="text" readonly name="fname" class="form-control"
                    value="<?= htmlspecialchars($student_info["fname"]) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Middle Name</label>
                <input type="text" readonly name="mname" class="form-control"
                    value="<?= htmlspecialchars($student_info["mname"]) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Last Name</label>
                <input type="text" readonly name="lname" class="form-control"
                    value="<?= htmlspecialchars($student_info["lname"]) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">suffix</label>
                <input type="text" readonly name="suffix" class="form-control"
                    value="<?= htmlspecialchars($student_info["suffix"] ?? 'NA') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Grade Level</label>
                <input type="text" readonly name="gradeLevel" class="form-control"
                    value="<?= htmlspecialchars($student_info["gradeLevel"]) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">LRN</label>
                <input type="text" readonly name="lrn" class="form-control"
                    value="<?= htmlspecialchars($student_info["lrn"]) ?>">
            </div>
            <div class="d-flex flex-column col-md-2 col-11">
                <label class="m-0 mt-1">Sex</label>
                <select name="gender" id="gender" class="form-select">
                    <option value="">Select Gender</option>
                    <option value="MALE" <?= ($student_info["sex"] ?? '') == 'MALE' ? 'selected' : '' ?>>MALE</option>
                    <option value="FEMALE" <?= ($student_info["sex"] ?? '') == 'FEMALE' ? 'selected' : '' ?>>FEMALE
                    </option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Birth Date</label>
                <input type="date" name="birthdate" class="form-control"
                    value="<?= htmlspecialchars($student_info["birthdate"] ?? 'NA') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Birth Place</label>
                <input type="text" name="birthplace" class="form-control"
                    value="<?= htmlspecialchars($student_info["birthplace"] ?? 'NA') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Age</label>
                <input type="text" readonly name="age" class="form-control">
            </div>
            <div class="d-flex flex-column col-md-3 col-11">
                <label class="m-0 mt-1">Religion</label>
                <select name="religion" id="religious" class="form-select">
                    <option value="">Select Religion</option>
                    <option value="Roman Catholic"
                        <?= ($student_info["religion"] ?? '') == 'Roman Catholic' ? 'selected' : '' ?>>Roman Catholic
                    </option>
                    <option value="Iglesia ni Cristo"
                        <?= ($student_info["religion"] ?? '') == 'Iglesia ni Cristo' ? 'selected' : '' ?>>Iglesia ni
                        Cristo</option>
                    <option value="Evangelical"
                        <?= ($student_info["religion"] ?? '') == 'Evangelical' ? 'selected' : '' ?>>Evangelical</option>
                    <option value="Islam" <?= ($student_info["religion"] ?? '') == 'Islam' ? 'selected' : '' ?>>Islam
                    </option>
                    <option value="Seventh-day Adventist"
                        <?= ($student_info["religion"] ?? '') == 'Seventh-day Adventist' ? 'selected' : '' ?>>
                        Seventh-day Adventist</option>
                    <option value="Aglipayan (IFI)"
                        <?= ($student_info["religion"] ?? '') == 'Aglipayan (IFI)' ? 'selected' : '' ?>>Aglipayan (IFI)
                    </option>
                    <option value="Baptist" <?= ($student_info["religion"] ?? '') == 'Baptist' ? 'selected' : '' ?>>
                        Baptist</option>
                    <option value="Born Again Christian"
                        <?= ($student_info["religion"] ?? '') == 'Born Again Christian' ? 'selected' : '' ?>>Born Again
                        Christian</option>
                    <option value="Jehovah's Witness"
                        <?= ($student_info["religion"] ?? '') == 'Jehovah\'s Witness' ? 'selected' : '' ?>>Jehovah's
                        Witness</option>
                </select>
            </div>
            <div class="d-flex flex-column col-md-3 col-11 ms-2">
                <label class="m-0 mt-1">Mother Tongue</label>
                <select name="mother_tongue" id="tongue" class="form-select">
                    <option value="">Select Mother Tongue</option>
                    <option value="Tagalog"
                        <?= ($student_info["mother_tongue"] ?? '') == 'Tagalog' ? 'selected' : '' ?>>Tagalog</option>
                    <option value="Cebuano"
                        <?= ($student_info["mother_tongue"] ?? '') == 'Cebuano' ? 'selected' : '' ?>>Cebuano</option>
                    <option value="Ilocano"
                        <?= ($student_info["mother_tongue"] ?? '') == 'Ilocano' ? 'selected' : '' ?>>Ilocano</option>
                    <option value="Hiligaynon"
                        <?= ($student_info["mother_tongue"] ?? '') == 'Hiligaynon' ? 'selected' : '' ?>>Hiligaynon
                    </option>
                    <option value="Bicolano"
                        <?= ($student_info["mother_tongue"] ?? '') == 'Bicolano' ? 'selected' : '' ?>>Bicolano</option>
                    <option value="Kapampangan"
                        <?= ($student_info["mother_tongue"] ?? '') == 'Kapampangan' ? 'selected' : '' ?>>Kapampangan
                    </option>
                    <option value="Pangasinan"
                        <?= ($student_info["mother_tongue"] ?? '') == 'Pangasinan' ? 'selected' : '' ?>>Pangasinan
                    </option>
                    <option value="Waray" <?= ($student_info["mother_tongue"] ?? '') == 'Waray' ? 'selected' : '' ?>>
                        Waray</option>
                    <option value="Maranao"
                        <?= ($student_info["mother_tongue"] ?? '') == 'Maranao' ? 'selected' : '' ?>>Maranao</option>
                    <option value="Tausug" <?= ($student_info["mother_tongue"] ?? '') == 'Tausug' ? 'selected' : '' ?>>
                        Tausug</option>
                    <option value="Others" <?= ($student_info["mother_tongue"] ?? '') == 'Others' ? 'selected' : '' ?>>
                        Others</option>
                </select>
            </div>
            <!-- PARENTS INFORMATIONS -->
             <div class="col-md-12">
                <strong class="fs-5">Parent Information</strong>
            </div>
            <div class="col-md-3">
                <label class="form-label">Father's First Name</label>
                <input type="text" name="f_firstname" class="form-control" value="<?= htmlspecialchars($student_info["f_firstname"] ?? '') ?>">
            </div>
             <div class="col-md-3">
                <label class="form-label">Father's Middle Name</label>
                <input type="text" name="f_middlename" class="form-control" value="<?= htmlspecialchars($student_info["f_middlename"] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Father's Last Name</label>
                <input type="text" name="f_lastname" class="form-control" value="<?= htmlspecialchars($student_info["f_lastname"] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Father's suffix</label>
                <input type="text" name="f_suffix" class="form-control" value="<?= htmlspecialchars($student_info["f_suffix"] ?? '') ?>">
            </div>

             <div class="col-md-3">
                <label class="form-label">Mothers's Maiden First Name</label>
                <input type="text" name="m_firstname" class="form-control" value="<?= htmlspecialchars($student_info["m_firstname"] ?? '') ?>">
            </div>
             <div class="col-md-3">
                <label class="form-label">Mothers's Maiden Middle Name</label>
                <input type="text" name="m_middlename" class="form-control" value="<?= htmlspecialchars($student_info["m_middlename"] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Mothers's Maiden Last Name</label>
                <input type="text" name="m_lastname" class="form-control" value="<?= htmlspecialchars($student_info["m_lastname"] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Guardian's First Name</label>
                <input type="text" name="g_firstname" class="form-control" value="<?= htmlspecialchars($student_info["g_firstname"] ?? '') ?>">
            </div>
             <div class="col-md-3">
                <label class="form-label">Guardian's Middle Name</label>
                <input type="text" name="g_middlename" class="form-control" value="<?= htmlspecialchars($student_info["g_middlename"] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Guardian's Last Name</label>
                <input type="text" name="g_lastname" class="form-control" value="<?= htmlspecialchars($student_info["g_lastname"] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Guardian's suffix</label>
                <input type="text" name="g_suffix" class="form-control" value="<?= htmlspecialchars($student_info["g_suffix"] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Guardian Relationship</label>
                <input type="text" name="g_relationship" class="form-control" value="<?= htmlspecialchars($student_info["g_relationship"] ?? '') ?>">
            </div>
             <div class="col-md-4">
                <label class="form-label">Contact NUmber (Parent/Guardian)</label>
                <input type="text" name="p_contact" class="form-control" value="<?= htmlspecialchars($student_info["p_contact"] ?? '') ?>">
            </div>
            
            <div class="col-md-12">
                <strong class="fs-5">Student Address</strong>
            </div>
            <div class="col-md-3">
                <label class="form-label">House No</label>
                <input type="text" name="house_no" class="form-control"
                    value="<?= htmlspecialchars($student_info["house_no"] ?? 'NA') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Street</label>
                <input type="text" name="street" class="form-control"
                    value="<?= htmlspecialchars($student_info["street"] ?? 'NA') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Barangay</label>
                <input type="text" name="barnagay" class="form-control"
                    value="<?= htmlspecialchars($student_info["barnagay"] ?? 'NA') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">City/Mutinlupa</label>
                <input type="text" name="city" class="form-control"
                    value="<?= htmlspecialchars($student_info["city"] ?? 'NA') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Province</label>
                <input type="text" name="province" class="form-control"
                    value="<?= htmlspecialchars($student_info["province"] ?? 'NA') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Country</label>
                <input type="text" name="country" class="form-control"
                    value="<?= htmlspecialchars($student_info["country"] ?? 'NA') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Zip Code</label>
                <input type="text" name="zip_code" class="form-control"
                    value="<?= htmlspecialchars($student_info["zip_code"] ?? 'NA') ?>">
            </div>
            <div class="col-md-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-danger mt-2 text-white px-5 fw-bold">Update</button>
            </div>

        </form>
        <?php
            require_once "../../authentication/config.php";

            $student_id = $_GET['student_id'] ?? null;

            // Fetch student info
            if ($student_id) {
                $query = "SELECT student.*, users.*, stuenrolmentinfo.* FROM student
                        INNER JOIN users ON student.guardian_id = users.user_id
                        INNER JOIN stuenrolmentinfo ON student.student_id = stuenrolmentinfo.student_id 
                        WHERE student.student_id = :student_id";
                $stmt = $pdo->prepare($query);
                $stmt->execute([':student_id' => $student_id]);
                $student_info = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            // Fetch attendance
            $attendanceData = [];
            if ($student_id) {
                $stmt = $pdo->prepare("SELECT * FROM attendance WHERE student_id = :student_id");
                $stmt->execute([':student_id' => $student_id]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $attendanceData[] = $row;
                }
            }

            // Function to get class based on attendance logic
            function getAttendanceClass($record, $dateStr) {
                $morning = ($record['morning_attendance'] && $record['morning_attendance'] !== "0000-00-00 00:00:00")
                            ? date("Y-m-d", strtotime($record['morning_attendance'])) : null;
                $afternoon = ($record['afternoon_attendance'] && $record['afternoon_attendance'] !== "0000-00-00 00:00:00")
                            ? date("Y-m-d", strtotime($record['afternoon_attendance'])) : null;
                $type = strtolower($record['attendance_type'] ?? '');

                if ($morning === $dateStr && $afternoon === $dateStr && $type === "present") return "present"; // green
                if ($morning === $dateStr && ($afternoon !== $dateStr || $type === "absent")) return "half-morning"; // yellow
                if (($morning !== $dateStr || $type === "absent") && $afternoon === $dateStr) return "half-afternoon"; // gray
                if ($type === "late") return "late"; // blue
                if ($type === "absent" && $morning !== $dateStr && $afternoon !== $dateStr) return "absent"; // red

                return "";
            }

            $currentYear = date("Y");
            $months = [
                "January","February","March","April","May","June",
                "July","August","September","October","November","December"
            ];
            ?>

        <style>
        .attendance-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .month {
            width: calc(50% - 10px);
        }

        /* Two months per row */
        .days-grid {
            display: grid;
            grid-template-columns: repeat(7, 40px);
            gap: 5px;
            margin-bottom: 20px;
        }

        .day {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .present {
            background: green !important;
            color: white !important;
        }

        .half-morning {
            background: green !important;
            color: white !important;
        }

        .half-afternoon {
            background: green !important;
            color: white !important;
        }

        .absent {
            background: red !important;
            color: white !important;
        }

        .late {
            background: blue !important;
            color: white !important;
        }
        </style>

        <div id="displayAttendance" class="attendance-container">
            <?php
                foreach ($months as $monthIndex => $monthName) {
                    echo "<div class='month'>";
                    echo "<h3>$monthName $currentYear</h3>";
                    echo "<div class='days-grid'>";
                    $daysInMonth = date("t", strtotime("$currentYear-" . ($monthIndex + 1) . "-01"));

                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        $dateStr = sprintf("%04d-%02d-%02d", $currentYear, $monthIndex + 1, $day);
                        $class = "day";

                        foreach ($attendanceData as $record) {
                            $attClass = getAttendanceClass($record, $dateStr);
                            if ($attClass) { $class .= " $attClass"; break; }
                        }

                        echo "<div class='$class'>$day</div>";
                    }

                    echo "</div></div>";
                }
                ?>
        </div>


        <div id="displayMedical" class="medical" style="display:none">
            <form id="medical-update" class="row h-auto">
                <input type="hidden" name="student_id" value="<?= $student_info["student_id"] ?>">

                <div class="col-md-4 h-auto">
                    <label class="form-label">Weight (kg)</label>
                    <input type="text" value="<?= htmlspecialchars($student_info["weight"]) ?>" class="form-control"
                        name="weight" placeholder="weight (kg)">
                </div>

                <div class="col-md-4 h-auto">
                    <label class="form-label">Height (m)</label>
                    <input type="text" value="<?= htmlspecialchars($student_info["height"]) ?>" class="form-control"
                        name="height" placeholder="height (m)">
                </div>

                <div class="col-md-4 h-auto">
                    <label class="form-label">Height² (m²)</label>
                    <input type="text" value="<?= htmlspecialchars($student_info["height_squared"]) ?>"
                        class="form-control" name="height_squared" placeholder="height² (m²)">
                </div>

                <div class="col-md-5 h-auto mt-4 d-flex align-items-center">
                    <strong class="w-50">BMI Result:</strong>
                    <input type="text" id="bm-result" readonly value="" class="form-control">
                </div>

                <div class="col-md-7 h-auto mt-4 d-flex align-items-center">
                    <strong class="w-50">BMI Category:</strong>
                    <input type="text" id="bm-category" readonly value="" class="form-control">
                </div>

                <div class="col-md-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-danger px-5 text-white mt-3">Update</button>
                </div>
            </form>

        </div>
        <div id="displayGrades" class="gading-system" style="display:none">

        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const birthdateInput = document.querySelector('input[name="birthdate"]');
    const ageInput = document.querySelector('input[name="age"]');

    function calculateAge() {
        const birthdate = birthdateInput.value;
        if (!birthdate) {
            ageInput.value = "";
            return;
        }

        const today = new Date();
        const dob = new Date(birthdate);
        let age = today.getFullYear() - dob.getFullYear();
        const monthDiff = today.getMonth() - dob.getMonth();

        // Adjust if birthday hasn't occurred yet this year
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
            age--;
        }

        ageInput.value = age;
    }

    // Run once on load if birthdate already has a value
    calculateAge();

    // Update age whenever birthdate changes
    birthdateInput.addEventListener("change", calculateAge);
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const weightInput = document.querySelector('input[name="weight"]');
    const heightInput = document.querySelector('input[name="height"]');
    const heightSqInput = document.querySelector('input[name="height_squared"]');
    const bmiInput = document.getElementById("bm-result");
    const categoryInput = document.getElementById("bm-category");

    function calculateBMI() {
        const weight = parseFloat(weightInput.value);
        const height = parseFloat(heightInput.value);

        if (!isNaN(weight) && !isNaN(height) && height > 0) {
            const heightSq = (height * height).toFixed(2);
            heightSqInput.value = heightSq;

            const bmi = (weight / (height * height)).toFixed(2);
            bmiInput.value = bmi;

            // Category logic
            let category = "";
            if (bmi < 18.5) category = "Underweight";
            else if (bmi < 25) category = "Normal";
            else if (bmi < 30) category = "Overweight";
            else category = "Obese";

            categoryInput.value = category;
        } else {
            heightSqInput.value = "";
            bmiInput.value = "";
            categoryInput.value = "";
        }
    }

    weightInput.addEventListener("input", calculateBMI);
    heightInput.addEventListener("input", calculateBMI);

    // Run once on page load
    calculateBMI();
});
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
    const heightInput = document.querySelector('input[name="height"]');

    heightInput.addEventListener("blur", function () {
        let value = parseFloat(heightInput.value);
        if (!isNaN(value) && value > 3) { 
            // if value looks like cm, convert to m
            heightInput.value = (value / 100).toFixed(2);
        }
    });
});
</script>
