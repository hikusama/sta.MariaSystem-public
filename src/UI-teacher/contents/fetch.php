<?php
require_once __DIR__ . '/../../../tupperware.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST["action"])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request'
        ]);
        exit();
    }
    $action = $_POST["action"];

    if ($action === "fetch_sections") {

        $search = trim($_POST['search'] ?? '');
        $sy = trim($_POST['school_year'] ?? '');

        $where = [];
        $params = [];
        if ($sy) {
            $where[] = " sections.school_year_id = ?";
            $params[] = $sy;
        }
        if ($search) {
            $where[] = " sections.section_name LIKE ?";
            $s = "%$search%";
            $params[] = $s;
        }
        $sql = "SELECT * FROM sections LEFT JOIN school_year ON sections.school_year_id = school_year.school_year_id ";
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY sections.created_date DESC;";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $count = 0;
        $html = '';
        if ($sections) {
            foreach ($sections as $section) {
                $count += 1;
                $html .= "<tr class='section-row' 
                                         data-id='" . htmlspecialchars($row['section_id']) . "'
                                         data-grade='" . htmlspecialchars($row['section_grade_level']) . "'
                                         data-section='" . htmlspecialchars($row['section_name']) . "'
                                         data-name='" . htmlspecialchars(strtolower($row['section_name'])) . "'
                                         data-grade='" . htmlspecialchars(strtolower($row['section_grade_level'])) . "'>
                                        <td width='5%'>" . $count++ . "</td>
                                        <td width='60%'>
                                            <div class='d-flex align-items-center'>
                                                <div class='avatar-placeholder me-2'>
                                                    <i class='fa-solid fa-layer-group text-secondary'></i>
                                                </div>
                                                <div>
                                                    <strong>" . htmlspecialchars($row['section_name']) . "</strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td width='35%'>
                                            <span class='badge bg-info'>" . htmlspecialchars($row['section_grade_level']) . "</span>
                                        </td>
                                      </tr>";
            }
        }
        echo json_encode([
            'rows' => $html,
            'hasData' => !empty($sections),
        ]);
    }
} else {
    echo 'Not found';
    exit(404);
}
