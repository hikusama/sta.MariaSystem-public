<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('teacher', 2);
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
        $sql = "SELECT * FROM student ORDER BY lname, fname LIMIT 7";
        $stmt = $conn->prepare($sql);
    } else {
        $searchLike = "%{$search}%";
        $sql = "SELECT * FROM student 
                WHERE lrn LIKE ? OR fname LIKE ? OR mname LIKE ? OR lname LIKE ? OR gradeLevel LIKE ? OR sex LIKE ?
                ORDER BY lname, fname";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssss', $searchLike, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr class='student-row' 
                     data-id='" . htmlspecialchars($row['student_id']) . "'
                     data-lrn='" . htmlspecialchars(strtolower($row['lrn'])) . "'
                     data-fname='" . htmlspecialchars(strtolower($row['fname'])) . "'
                     data-lname='" . htmlspecialchars(strtolower($row['lname'])) . "'
                     data-grade='" . htmlspecialchars(strtolower($row['gradeLevel'])) . "'
                     data-sex='" . htmlspecialchars(strtolower($row['sex'])) . "'
                     data-status='" . htmlspecialchars(strtolower($row['enrolment_status'])) . "'>
                    <td width='15%'>
                        <div class='d-flex align-items-center justify-content-center'>
                            <div class='avatar-placeholder me-2'>
                                <i class='fa-solid fa-id-card text-info'></i>
                            </div>
                            <div>
                                <strong>" . htmlspecialchars($row['lrn']) . "</strong>
                            </div>
                        </div>
                    </td>
                    <td width='15%'>" . htmlspecialchars($row['fname']) . "</td>
                    <td width='15%'>" . htmlspecialchars($row['mname']) . "</td>
                    <td width='15%'>" . htmlspecialchars($row['lname']) . "</td>
                    <td width='10%'>
                        <span class='badge bg-primary'>" . htmlspecialchars($row['gradeLevel']) . "</span>
                    </td>
                    <td width='10%'>
                        <span class='badge bg-" . (strtolower($row['sex']) == 'male' ? 'info' : 'warning') . "'>
                            " . htmlspecialchars($row['sex']) . "
                        </span>
                    </td>
                    <td width='20%'>
                        <span class='badge bg-" . ($row['enrolment_status'] == 'active' ? 'success' : 'secondary') . "'>
                            <i class='fa-solid fa-circle fa-xs me-1'></i>
                            " . htmlspecialchars($row['enrolment_status']) . "
                        </span>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='7' class='text-center py-3'>No students found.</td></tr>";
    }

    exit; // stop execution for AJAX
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SF9 - Learner's Progress Report Card</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f5f7fa;
            font-family: "Segoe UI", sans-serif;
        }

        .scroll-feedback {
            height: 80vh;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .table-container-wrapper {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }

        .table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
            cursor: pointer;
        }

        .avatar-placeholder {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .empty-state {
            padding: 3rem 1rem;
        }

        .empty-state i {
            opacity: 0.5;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .input-group-text {
            border-right: none;
        }

        #searchInput:focus {
            box-shadow: none;
            border-color: #86b7fe;
        }

        .scroll-feedback::-webkit-scrollbar {
            width: 8px;
        }

        .scroll-feedback::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .scroll-feedback::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .scroll-feedback::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        @media (max-width: 768px) {
            .scroll-feedback {
                height: auto;
                overflow: visible;
            }

            .table-responsive {
                font-size: 0.75rem;
            }

            .btn-sm {
                padding: 0.2rem 0.4rem;
                font-size: 0.7rem;
            }
        }
    </style>
</head>

<body>

    <div class="container-fluid py-3">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="mx-2">
                <h4><i class="fa-solid fa-graduation-cap me-2"></i>SF9 - Learner's Progress Report Card</h4>
            </div>
        </div>

        <div class="scroll-feedback">
            <!-- Search Section -->
            <div class="row mb-3 justify-content-between align-items-center">
                <div class="col-md-12">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                        <input type="text" class="form-control" name="search" id="searchInput"
                            placeholder="Search by LRN, Name, Grade Level, Sex, or Enrolment Status...">
                    </div>
                </div>
            </div>

            <!-- Students Table -->
            <div class="table-container-wrapper p-0">
                <!-- Fixed Header -->
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover" style="font-size: 0.875rem;">
                        <thead class="table-light">
                            <tr>
                                <th width="15%">LRN</th>
                                <th width="15%">First Name</th>
                                <th width="15%">Middle Name</th>
                                <th width="15%">Last Name</th>
                                <th width="10%">Grade Level</th>
                                <th width="10%">Sex</th>
                                <th width="20%">Enrolment Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <!-- Scrollable Body -->
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.875rem;">
                        <tbody id="studentTable">
                            <?php
                            $query = "SELECT * FROM student ORDER BY lname, fname LIMIT 7";
                            $result = $conn->query($query);
                            if ($result && $result->num_rows > 0) {
                                $count = 1;
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr class='student-row' 
                                         data-id='" . htmlspecialchars($row['student_id']) . "'
                                         data-lrn='" . htmlspecialchars(strtolower($row['lrn'])) . "'
                                         data-fname='" . htmlspecialchars(strtolower($row['fname'])) . "'
                                         data-lname='" . htmlspecialchars(strtolower($row['lname'])) . "'
                                         data-grade='" . htmlspecialchars(strtolower($row['gradeLevel'])) . "'
                                         data-sex='" . htmlspecialchars(strtolower($row['sex'])) . "'
                                         data-status='" . htmlspecialchars(strtolower($row['enrolment_status'])) . "'>
                                        <td width='15%'>
                                            <div class='d-flex align-items-center justify-content-center'>
                                                <div class='avatar-placeholder me-2'>
                                                    <i class='fa-solid fa-id-card text-info'></i>
                                                </div>
                                                <div>
                                                    <strong>" . htmlspecialchars($row['lrn']) . "</strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td width='15%'>" . htmlspecialchars($row['fname']) . "</td>
                                        <td width='15%'>" . htmlspecialchars($row['mname']) . "</td>
                                        <td width='15%'>" . htmlspecialchars($row['lname']) . "</td>
                                        <td width='10%'>
                                            <span class='badge bg-primary'>" . htmlspecialchars($row['gradeLevel']) . "</span>
                                        </td>
                                        <td width='10%'>
                                            <span class='badge bg-" . (strtolower($row['sex']) == 'male' ? 'info' : 'warning') . "'>
                                                " . htmlspecialchars($row['sex']) . "
                                            </span>
                                        </td>
                                        <td width='20%'>
                                            <span class='badge bg-" . ($row['enrolment_status'] == 'active' ? 'success' : 'secondary') . "'>
                                                <i class='fa-solid fa-circle fa-xs me-1'></i>
                                                " . htmlspecialchars($row['enrolment_status']) . "
                                            </span>
                                        </td>
                                      </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center py-3'>No students found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div id="noResults" class="text-center py-5 d-none">
                    <div class="empty-state">
                        <i class="fa-solid fa-user-graduate fa-3x text-muted mb-3"></i>
                        <h5>No students found</h5>
                        <p class="text-muted">Try adjusting your search</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const studentTable = document.getElementById('studentTable');
            const noResultsDiv = document.getElementById('noResults');
            let studentRows = document.querySelectorAll('.student-row');

            // Live search
            searchInput.addEventListener('input', function() {
                const search = this.value.trim();
                const xhr = new XMLHttpRequest();

                xhr.open('GET', 'contents/sf9.php?ajax=1&search=' + encodeURIComponent(search), true);

                xhr.onload = function() {
                    if (this.status === 200) {
                        studentTable.innerHTML = this.responseText;
                        attachRowClickEvents();
                        studentRows = document.querySelectorAll('.student-row');

                        // Show/hide no results message
                        if (studentRows.length === 0 || (studentRows.length === 1 && studentRows[0].querySelector('td').textContent.includes('No students'))) {
                            studentTable.style.display = 'none';
                            noResultsDiv.classList.remove('d-none');
                        } else {
                            studentTable.style.display = '';
                            noResultsDiv.classList.add('d-none');
                        }
                    }
                };

                xhr.send();
            });

            // Search with Enter key
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    this.dispatchEvent(new Event('input'));
                }
            });

            // Focus styling
            searchInput.addEventListener('focus', function() {
                this.parentElement.classList.add('border-primary', 'border-2');
            });

            searchInput.addEventListener('blur', function() {
                this.parentElement.classList.remove('border-primary', 'border-2');
            });

            // Clickable rows redirect
            function attachRowClickEvents() {
                document.querySelectorAll('.student-row').forEach(row => {
                    row.addEventListener('click', function() {
                        const studentId = this.getAttribute('data-id');
                        if (studentId) {
                            window.location.href = '<?php echo BASE_FR; ?>/src/UI-teacher/contents/schoolform9.php?student_id=' + studentId;
                        }
                    });
                });
            }

            // Initialize
            attachRowClickEvents();
        });
    </script>

</body>

</html>