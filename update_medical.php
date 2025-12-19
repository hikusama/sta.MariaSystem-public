<?php
header('Content-Type: application/json');
// Load DB connection from existing config
require_once __DIR__ . '/authentication/config.php';
$pdo = db_connect(); // uses the project's DB helper

try {
    // Simple input sanitization
    $student_id = isset($_POST['student_id']) ? (int) $_POST['student_id'] : null;
    if (!$student_id) {
        echo json_encode(['status' => 0, 'message' => 'Missing student_id']);
        exit;
    }

    $weight = isset($_POST['weight']) && $_POST['weight'] !== '' ? (float) $_POST['weight'] : null;
    $height = isset($_POST['height']) && $_POST['height'] !== '' ? (float) $_POST['height'] : null;

    // Compute derived values server-side when possible
    $height_squared = null;
    $bmi = null;
    if ($height && $height > 0) {
        $height_squared = round($height * $height, 2);
        if ($weight && $weight > 0) {
            $bmi = round($weight / ($height * $height), 2);
        }
    }

    // Fetch birthdate to compute age (years) for HFA/BMI-for-age categorization
    $age = null;
    $stmtAge = $pdo->prepare("SELECT birthdate FROM student WHERE student_id = :id LIMIT 1");
    $stmtAge->execute([':id' => $student_id]);
    $rowAge = $stmtAge->fetch(PDO::FETCH_ASSOC);
    if ($rowAge && !empty($rowAge['birthdate'])) {
        try {
            $birth = new DateTime($rowAge['birthdate']);
            $now = new DateTime();
            $diff = $now->diff($birth);
            $age = (int) $diff->y;
        } catch (Exception $e) {
            $age = null;
        }
    }

    // Helper: calculate HFA similar to client-side ranges
    $calculate_hfa = function($heightVal, $ageVal) {
        if (!$heightVal || !$ageVal) return null;
        $ranges = [
            5 => ['min' => 1.05, 'max' => 1.15],
            6 => ['min' => 1.10, 'max' => 1.22],
            7 => ['min' => 1.15, 'max' => 1.28],
            8 => ['min' => 1.20, 'max' => 1.34],
            9 => ['min' => 1.25, 'max' => 1.39],
            10 => ['min' => 1.30, 'max' => 1.44],
            11 => ['min' => 1.35, 'max' => 1.49],
            12 => ['min' => 1.40, 'max' => 1.55],
            13 => ['min' => 1.45, 'max' => 1.60],
            14 => ['min' => 1.50, 'max' => 1.65],
            15 => ['min' => 1.53, 'max' => 1.68],
            16 => ['min' => 1.55, 'max' => 1.70],
            17 => ['min' => 1.57, 'max' => 1.72],
            18 => ['min' => 1.58, 'max' => 1.73],
            19 => ['min' => 1.59, 'max' => 1.74]
        ];
        $ageKey = (int) $ageVal;
        if (!isset($ranges[$ageKey])) return null;
        $min = $ranges[$ageKey]['min'];
        $max = $ranges[$ageKey]['max'];
        if ($heightVal < $min - 0.05) return 'Severely Stunted';
        if ($heightVal < $min) return 'Stunted';
        if ($heightVal > $max + 0.05) return 'Tall';
        if ($heightVal > $max) return 'Above Average';
        return 'Normal';
    };

    // Helper: determine BMI category (age-aware)
    $calculate_bmi_category = function($bmiVal, $ageVal) {
        if ($bmiVal === null || $bmiVal === '') return null;
        $bmiVal = (float) $bmiVal;
        if ($ageVal !== null && $ageVal >= 2 && $ageVal < 19) {
            if ($bmiVal < 14.5) return 'Severely Underweight';
            if ($bmiVal < 16.5) return 'Underweight';
            if ($bmiVal <= 22.9) return 'Normal';
            if ($bmiVal <= 27.9) return 'Overweight';
            return 'Obese';
        } else {
            if ($bmiVal < 16) return 'Severely Underweight';
            if ($bmiVal < 18.5) return 'Underweight';
            if ($bmiVal < 25) return 'Normal';
            if ($bmiVal < 30) return 'Overweight';
            return 'Obese';
        }
    };

    // Helper: generate remarks
    $generate_remarks = function($bmiCat, $hfaCat) {
        $remarks = [];
        if (!$bmiCat && !$hfaCat) return null;
        if ($bmiCat && stripos($bmiCat, 'Severely Underweight') !== false) $remarks[] = 'Urgent nutritional intervention needed';
        else if ($bmiCat === 'Underweight') $remarks[] = 'Needs nutritional support';
        else if ($bmiCat === 'Overweight') $remarks[] = 'Monitor diet and increase physical activity';
        else if ($bmiCat === 'Obese') $remarks[] = 'Comprehensive weight management program needed';
        if ($hfaCat && stripos($hfaCat, 'Severely Stunted') !== false) $remarks[] = 'Urgent growth monitoring and intervention';
        else if ($hfaCat === 'Stunted') $remarks[] = 'Growth monitoring needed';
        else if ($hfaCat === 'Tall' || $hfaCat === 'Above Average') $remarks[] = 'Monitor growth pattern';
        if ($bmiCat === 'Normal' && $hfaCat === 'Normal') $remarks[] = 'Healthy - maintain current lifestyle';
        return count($remarks) ? implode(', ', $remarks) : null;
    };

    // Accept posted values but prefer server-computed if available
    $posted_bmi = isset($_POST['bmi']) && $_POST['bmi'] !== '' ? $_POST['bmi'] : null;
    $bmi_final = $bmi ?? ($posted_bmi !== null ? (float)$posted_bmi : null);

    $bmi_category = isset($_POST['bmi_category']) ? trim($_POST['bmi_category']) : null;
    $hfa = isset($_POST['hfa']) ? trim($_POST['hfa']) : null;
    $medical_remarks = isset($_POST['medical_remarks']) ? trim($_POST['medical_remarks']) : null;

    // If server can compute BMI category or HFA, prefer server calculation
    $bmi_category_server = $calculate_bmi_category($bmi_final, $age);
    if (!$bmi_category && $bmi_category_server) {
        $bmi_category = $bmi_category_server;
    }

    $hfa_server = $calculate_hfa($height, $age);
    if (!$hfa && $hfa_server) {
        $hfa = $hfa_server;
    }

    // Decide medical remarks: prefer explicit posted, otherwise generate (or leave null if no data)
    if (trim((string)$medical_remarks) === '') {
        $medical_remarks = $generate_remarks($bmi_category, $hfa);
    }

    // Convert sentinel values like 'Not Measured' to NULL for storage
    $convertNull = function($v) {
        if ($v === null) return null;
        $v = trim($v);
        if ($v === '' || strtolower($v) === 'not measured' || strtolower($v) === 'n/a') return null;
        return $v;
    };

    $bmi_category = $convertNull($bmi_category);
    $hfa = $convertNull($hfa);
    $medical_remarks = $convertNull($medical_remarks);

    // Update student table
    $sql = "UPDATE student SET
                weight = :weight,
                height = :height,
                height_squared = :height_squared,
                bmi = :bmi,
                bmi_category = :bmi_category,
                hfa = :hfa,
                medical_remarks = :medical_remarks,
                medical_updated_at = NOW()
            WHERE student_id = :student_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':weight' => $weight,
        ':height' => $height,
        ':height_squared' => $height_squared,
        ':bmi' => $bmi_final,
        ':bmi_category' => $bmi_category,
        ':hfa' => $hfa,
        ':medical_remarks' => $medical_remarks,
        ':student_id' => $student_id
    ]);

    // Return computed values to update UI
    $updated = [
        'height_squared' => $height_squared,
        'bmi' => $bmi_final,
        'bmi_category' => $bmi_category,
        'hfa' => $hfa,
        'medical_remarks' => $medical_remarks
    ];

    echo json_encode(['status' => 1, 'message' => 'Medical info saved', 'updated' => $updated]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 0, 'message' => 'DB error: ' . $e->getMessage()]);
} catch (Throwable $t) {
    http_response_code(500);
    echo json_encode(['status' => 0, 'message' => $t->getMessage()]);
}
