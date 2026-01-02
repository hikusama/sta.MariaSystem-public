<?php
require_once __DIR__ . '/../tupperware.php';

$student_id   = (int) $_GET['student_id'];
$selectedYear = (int) $_GET['year'];

/* ===== FETCH ATTENDANCE ===== */
$attendanceData = [];

$stmt = $pdo->prepare("
    SELECT *
    FROM attendance
    WHERE student_id = :student_id
");
$stmt->execute(['student_id' => $student_id]);

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $record) {
    $date = date('Y-m-d', strtotime($record['morning_attendance']));

    $attendanceData[$date] = [
        'morning'   => $record['attendance_type'],
        'afternoon' => $record['A_attendance_type'],
        'summary'   => $record['attendance_summary'],
        'recorded'  => $record['attendance_at'],
    ];
}

/* ===== JULY → JAN ===== */
$months = [
    ['month'=>7,  'year'=>$selectedYear],
    ['month'=>8,  'year'=>$selectedYear],
    ['month'=>9,  'year'=>$selectedYear],
    ['month'=>10, 'year'=>$selectedYear],
    ['month'=>11, 'year'=>$selectedYear],
    ['month'=>12, 'year'=>$selectedYear],
    ['month'=>1,  'year'=>$selectedYear + 1],
];

foreach ($months as $m):

$monthIndex  = $m['month'];
$year        = $m['year'];
$monthName   = date("F", mktime(0,0,0,$monthIndex,1,$year));
$daysInMonth = date("t", mktime(0,0,0,$monthIndex,1,$year));
$firstDay    = date("w", mktime(0,0,0,$monthIndex,1,$year));

/* count days recorded */
$daysRecorded = 0;
foreach ($attendanceData as $d => $r) {
    if (date('Y-m', strtotime($d)) === sprintf('%04d-%02d',$year,$monthIndex)) {
        $daysRecorded++;
    }
}
?>

<div class="month-card mb-5">
    <h6 class="fw-semibold mb-3 d-flex justify-content-between">
        <span><?= $monthName ?> <?= $year ?></span>
        <small class="text-muted"><?= $daysRecorded ?> days recorded</small>
    </h6>

    <!-- day headers -->
    <div class="days-header mb-2">
        <div class="d-flex">
            <?php foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d): ?>
                <div class="day-header text-center" style="width:14.28%"><?= $d ?></div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="calendar-grid mb-3">
        <div class="d-flex flex-wrap">

        <?php for($i=0;$i<$firstDay;$i++): ?>
            <div class="day-cell empty" style="width:14.28%"></div>
        <?php endfor; ?>

        <?php for($day=1;$day<=$daysInMonth;$day++):
            $dateStr   = sprintf('%04d-%02d-%02d',$year,$monthIndex,$day);
            $dayOfWeek = ($firstDay + $day - 1) % 7;

            $cellClass = 'day-cell';
            $tooltip   = '';
            $status    = $attendanceData[$dateStr] ?? null;

            if ($status) {
                switch (strtolower($status['summary'])) {
                    case 'present':
                        $cellClass .= ' present';
                        $tooltip = 'Present - All Day';
                        break;
                    case 'absent':
                        $cellClass .= ' absent';
                        $tooltip = 'Absent - All Day';
                        break;
                    case 'late':
                        $cellClass .= ' late';
                        $tooltip = 'Late - All Day';
                        break;
                    case 'half-day':
                        $cellClass .= ' half-day';
                        $tooltip = 'Half Day';
                        break;
                    case 'half-day-late':
                        $cellClass .= ' half-day-late';
                        $tooltip = 'Half Day Late';
                        break;
                }
            } else {
                $cellClass .= ' no-record';
                $tooltip = 'No attendance record';
            }

            if ($dayOfWeek === 0 || $dayOfWeek === 6) $cellClass .= ' weekend';
            if ($dateStr === date('Y-m-d')) $cellClass .= ' today';
        ?>

        <div class="<?= $cellClass ?>" style="width:14.28%" title="<?= $tooltip ?>">
            <?= $day ?>

            <?php if ($status): ?>
            <div class="day-indicators">
                <?php if ($status['morning']): ?>
                    <span class="indicator <?= strtolower($status['morning']) ?>"></span>
                <?php endif; ?>
                <?php if ($status['afternoon']): ?>
                    <span class="indicator <?= strtolower($status['afternoon']) ?>"></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if (($firstDay + $day) % 7 === 0): ?>
            </div><div class="d-flex flex-wrap">
        <?php endif; ?>

        <?php endfor; ?>

        </div>
    </div>
</div>

<?php endforeach; ?>
