<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('parent', 2);
if ($result['res']) {
    header($result['uri']);
    exit;
}


if (!isset($_GET['student_id'])) {
    die("Student ID not provided.");
}
if (!isset($_GET['school_year_name'])) {
    die("School year name not provided.");
}

$student_id = $_GET['student_id'];
$school_year_name = $_GET['school_year_name'];
$stmt = $pdo->prepare("SELECT * FROM sf9_data WHERE student_id = ? AND school_year = ?");
$stmt->execute([$student_id, $school_year_name]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Report Card - <?= htmlspecialchars($data['student_name']) ?></title>
    <link href="<?= base_url() ?>assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f3f4f6;
            color: #212529;
            font-size: 14px;
            overflow: hidden;
        }

        .scroll-container {
            height: 100vh;
            overflow-y: auto;
            padding: 40px;
        }

        .report-card {
            background: #ffffff;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            position: relative;
            max-width: 2100px;
            margin: auto;
        }

        .print-btn {
            position: absolute;
            top: 25px;
            right: 35px;
            background-color: #dc3545;
            border: none;
            color: #fff;
            font-weight: 500;
            padding: 10px 18px;
            border-radius: 6px;
            box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3);
            transition: all 0.25s ease;
        }

        .print-btn:hover {
            background-color: #bb2d3b;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(220, 53, 69, 0.5);
        }

        .header-info {
            margin-bottom: 35px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            line-height: 1.7;
        }

        .header-info div {
            width: 48%;
        }

        .header-info span {
            display: block;
            margin-bottom: 8px;
            font-size: 15.5px;
            color: #000;
            font-weight: 500;
            letter-spacing: 0.2px;
        }

        .header-info strong {
            color: #000;
            font-weight: 700;
            font-size: 16px;
        }

        h5 {
            margin-top: 50px;
            margin-bottom: 20px;
            color: #dc3545;
            font-weight: 600;
            text-align: center;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            background-color: #fff;
        }

        .table thead {
            background-color: #ffffffff;
            color: white;
        }

        .table th,
        .table td {
            padding: 12px 10px;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #dee2e6;
        }

        .table th.subject,
        .table td.subject {
            text-align: left;
            font-weight: 500;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        /* Adjust Behavior Table column widths */
        .behavior-table td {
            height: 65px;
            line-height: 1.6;
        }

        .behavior-table th.subject {
            width: 80%;
            /* wider for text */
        }

        .behavior-table th:not(.subject),
        .behavior-table td:not(.subject) {
            width: 8.5%;
            /* narrower for quarter columns */
        }

        @media (max-width: 768px) {
            .report-card {
                padding: 25px;
            }

            .header-info div {
                width: 100%;
            }
        }

        @media print {
            @page {
                size: landscape;
            }

            .scroll-container {
                overflow: visible;
            }

            .print-btn {
                display: none;
            }

            body {
                background: white;
            }
        }

        .legend-box {
            background: #fff3f3;
            border: 1px solid #dc3545;
            border-radius: 10px;
            padding: 12px 20px;
            font-size: 14px;
            color: #212529;
            box-shadow: 0 3px 8px rgba(220, 53, 69, 0.1);
            max-width: 95%;
            margin: auto;
        }

        .legend-box strong {
            color: #dc3545;
        }

        .legend-row {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 5px;
        }

        .legend-item {
            display: inline-block;
            font-weight: 500;
        }

        @media print {
            .legend-box {
                background: #ffffff;
                border: 1px solid #000;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    <?php
    $showAlert = false;
    $alertTitle = '';
    $alertMessage = '';

    if (!$student_id || !$school_year_name) {
        $showAlert = true;
        $alertTitle = 'Invalid Request';
        $alertMessage = 'Missing Student ID or School Year.';
    } elseif (!$data) {
        $showAlert = true;
        $alertTitle = 'Student Not Found';
        $alertMessage = 'No SF9 record exists for this student.';
    }
    ?>

    <?php if ($showAlert): ?>
        <!-- BLOCKING ALERT OVERLAY -->
        <div id="blockingAlert" class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
            style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 1050;">
            <div class="bg-white p-4 rounded shadow text-center" style="max-width: 400px;">
                <i class="fa-solid fa-triangle-exclamation fa-3x text-warning mb-3"></i>
                <h5 class="fw-bold text-danger mb-2"><?= htmlspecialchars($alertTitle) ?></h5>
                <p class="text-muted mb-3"><?= htmlspecialchars($alertMessage) ?></p>
                <button type="button" onclick="window.location.href='<?= base_url() ?>/src/UI-teacher/index.php?page=contents/sf9'" class="btn btn-primary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Go Back
                </button>
            </div>
        </div>

    <?php else: ?>
        <div class="scroll-container">
            <div class="report-card">

                <button onclick="window.print()" class="print-btn">🖨 Print Report Card</button>

                <div class="header-info">
                    <div>
                        <span><strong>Name:</strong> <?= htmlspecialchars($data['student_name']) ?></span>
                        <span><strong>LRN:</strong> <?= htmlspecialchars($data['lrn']) ?></span>
                        <span><strong>Sex:</strong> <?= htmlspecialchars($data['sex']) ?></span>
                        <span><strong>Age:</strong> <?= htmlspecialchars($data['age']) ?></span>
                    </div>
                    <div>
                        <span><strong>Grade Level:</strong> <?= htmlspecialchars($data['grade']) ?></span>
                        <span><strong>Section:</strong> <?= htmlspecialchars($data['section']) ?></span>
                        <span><strong>School Year:</strong> <?= htmlspecialchars($data['school_year']) ?></span>
                        <span><strong>Adviser:</strong> <?= htmlspecialchars($data['teacher']) ?></span>
                        <span><strong>Guardian:</strong> <?= htmlspecialchars($data['guardian']) ?></span>
                    </div>
                </div>

                <h5>Academic Performance</h5>
                <table class="table">
                    <thead>
                        <tr>
                            <th class="subject">Learning Areas</th>
                            <th>1st Quarter</th>
                            <th>2nd Quarter</th>
                            <th>3rd Quarter</th>
                            <th>4th Quarter</th>
                            <th>Final Rating</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        for ($i = 1; $i <= 15; $i++) {
                            $subject = $data["subject_$i"];
                            if ($subject) {
                                echo "<tr>
                                <td class='subject'>" . htmlspecialchars($subject) . "</td>
                                <td>" . htmlspecialchars($data["q1_$i"]) . "</td>
                                <td>" . htmlspecialchars($data["q2_$i"]) . "</td>
                                <td>" . htmlspecialchars($data["q3_$i"]) . "</td>
                                <td>" . htmlspecialchars($data["q4_$i"]) . "</td>
                                <td><strong>" . htmlspecialchars($data["final_$i"]) . "</strong></td>
                                <td>" . htmlspecialchars($data["remarks_$i"]) . "</td>
                                </tr>";
                            }
                        }
                        ?>
                        <tr>
                            <td colspan="5" class="text-end"><strong>General Average</strong></td>
                            <td colspan="2"><strong><?= htmlspecialchars($data['general_average']) ?></strong></td>
                        </tr>
                    </tbody>
                </table>
                <div class="legend-box text-center mt-4 mb-4">
                    <strong>LEGEND:</strong><br>
                    <span class="legend-item"><strong>90–100</strong> = Outstanding│</span>
                    <span class="legend-item"><strong>85–89</strong> = Very Satisfactory│</span>
                    <span class="legend-item"><strong>80–84</strong> = Satisfactory│</span>
                    <span class="legend-item"><strong>75–79</strong> = Fairly Satisfactory│</span>
                    <span class="legend-item"><strong>Below 75</strong> = Did Not Meet Expectation</span>
                </div>


                <h5>Observed Values / Behavior</h5>
                <table class="table behavior-table">
                    <thead>
                        <tr>
                            <th class="subject">Core Values / Behavior Statements</th>
                            <th>1st Quarter</th>
                            <th>2nd Quarter</th>
                            <th>3rd Quarter</th>
                            <th>4th Quarter</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        for ($b = 1; $b <= 7; $b++) {
                            $behavior = $data["behavior_$b"];
                            if ($behavior) {
                                echo "<tr>
        <td class='subject'>" . htmlspecialchars($behavior) . "</td>
        <td>" . htmlspecialchars($data["b{$b}_q1"]) . "</td>
        <td>" . htmlspecialchars($data["b{$b}_q2"]) . "</td>
        <td>" . htmlspecialchars($data["b{$b}_q3"]) . "</td>
        <td>" . htmlspecialchars($data["b{$b}_q4"]) . "</td>
        </tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>

                </table>

                <div class="legend-box text-center mt-3 mb-4">
                    <strong>LEGEND:</strong>
                    <span class="legend-item">AO = <strong>Always Observed│</strong></span>
                    <span class="legend-item">SO = <strong>Sometimes Observed│</strong></span>
                    <span class="legend-item">RO = <strong>Rarely Observed│</strong></span>
                    <span class="legend-item">NO = <strong>Not Observed</strong></span>
                </div>

                <h5>Attendance Record</h5>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Days of School</th>
                            <th>Days Present</th>
                            <th>Days Absent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $months = ['june', 'july', 'aug', 'sep', 'oct', 'nov', 'dec', 'jan', 'feb', 'mar'];
                        foreach ($months as $m) {
                            echo "<tr>
    <td>" . ucfirst($m) . "</td>
    <td>" . htmlspecialchars($data["days_school_$m"]) . "</td>
    <td>" . htmlspecialchars($data["days_present_$m"]) . "</td>
    <td>" . htmlspecialchars($data["days_absent_$m"]) . "</td>
    </tr>";
                        }
                        ?>
                    </tbody>
                </table>

            </div>
        </div>
    <?php endif; ?>

</body>

</html>