<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('admin', 2);

if ($result['res']) {
    header($result['uri']);
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "stamariadb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    if ($search === '') {
        $sql = "SELECT * FROM sections ORDER BY section_grade_level, section_name LIMIT 20";
        $stmt = $conn->prepare($sql);
    } else {
        $searchLike = "%{$search}%";
        $sql = "SELECT * FROM sections 
                WHERE section_name LIKE ? OR section_grade_level LIKE ?
                ORDER BY section_grade_level, section_name
                LIMIT 20";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $searchLike, $searchLike);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr class='section-row' 
                     data-id='" . htmlspecialchars($row['section_id']) . "' 
                     data-grade='" . htmlspecialchars($row['section_grade_level']) . "'
                     data-section='" . htmlspecialchars($row['section_name']) . "'>
                    <td>" . htmlspecialchars($row['section_name']) . "</td>
                    <td>" . htmlspecialchars($row['section_grade_level']) . "</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='2' class='text-center text-muted'>No sections found.</td></tr>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SF5 - Report on Promotion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fa;
            font-family: "Segoe UI", sans-serif;
        }

        .card {
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .table thead {
            background-color: #0056b3;
            color: #fff;
        }

        .table-hover tbody tr:hover {
            background-color: #eaf2ff;
            cursor: pointer;
        }

        .scroll-container {
            max-height: 480px;
            overflow-y: auto;
        }

        .scroll-container::-webkit-scrollbar {
            width: 8px;
        }

        .scroll-container::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
        }

        .header-bar {
            background-color: #FF3860;
            color: white;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-bar h4 {
            margin: 0;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .header-bar input {
            width: 280px;
            border-radius: 10px;
            border: 1px solid #ced4da;
        }
    </style>
</head>

<body>

    <div class="container mt-4">

        <!-- Header -->
        <div class="header-bar">
            <h4>SF5 - Report on Promotion and Level of Proficiency</h4>
            <input
                type="text"
                id="searchInput"
                class="form-control"
                placeholder="Search Section or Grade Level...">
        </div>

        <!-- Table Container -->
        <div class="card">
            <div class="card-body p-0">
                <div class="scroll-container">
                    <table class="table table-hover table-striped align-middle text-center mb-0">
                        <thead>
                            <tr>
                                <th>Section</th>
                                <th>Grade Level</th>
                            </tr>
                        </thead>
                        <tbody id="sectionTable">
                            <?php
                            $query = "SELECT * FROM sections ORDER BY section_grade_level, section_name LIMIT 20";
                            $result = $conn->query($query);
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr class='section-row'
                                         data-id='" . htmlspecialchars($row['section_id']) . "'
                                         data-grade='" . htmlspecialchars($row['section_grade_level']) . "'
                                         data-section='" . htmlspecialchars($row['section_name']) . "'>
                                        <td>" . htmlspecialchars($row['section_name']) . "</td>
                                        <td>" . htmlspecialchars($row['section_grade_level']) . "</td>
                                      </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='2' class='text-muted py-3'>No sections found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script>
        // Live search (AJAX)
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const search = this.value.trim();
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'contents/sf5.php?ajax=1&search=' + encodeURIComponent(search), true);
            xhr.onload = function() {
                if (this.status === 200) {
                    document.getElementById('sectionTable').innerHTML = this.responseText;
                    attachRowClickEvents();
                }
            };
            xhr.send();
        });

        // Clickable rows redirect
        function attachRowClickEvents() {
            document.querySelectorAll('.section-row').forEach(row => {
                row.addEventListener('click', function() {
                    const sectionId = this.dataset.id;
                    const gradeLevel = this.dataset.grade;
                    const sectionName = this.dataset.section;
                    if (sectionId && gradeLevel && sectionName) {
                        window.location.href = '<?php echo BASE_FR; ?>/src/UI-Admin/contents/schoolform5.php?student_id=' + studentId;

                        +
                        `?section_id=${encodeURIComponent(sectionId)}` +
                        `&grade=${encodeURIComponent(gradeLevel)}` +
                        `&section=${encodeURIComponent(sectionName)}`;
                    }
                });
            });
        }
        attachRowClickEvents();
    </script>

</body>

</html>