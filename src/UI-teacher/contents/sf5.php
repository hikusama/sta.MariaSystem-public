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
                     data-section='" . htmlspecialchars($row['section_name']) . "'
                     data-name='" . htmlspecialchars(strtolower($row['section_name'])) . "'
                     data-grade='" . htmlspecialchars(strtolower($row['section_grade_level'])) . "'>
                    <td width='5%'>1</td>
                    <td width='20%'>
                        <div class='d-flex align-items-center'>
                            <div class='avatar-placeholder me-2'>
                                <i class='fa-solid fa-layer-group text-secondary'></i>
                            </div>
                            <div>
                                <strong>" . htmlspecialchars($row['section_name']) . "</strong>
                            </div>
                        </div>
                    </td>
                    <td width='15%'>
                        <span class='badge bg-info'>" . htmlspecialchars($row['section_grade_level']) . "</span>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='3' class='text-center text-muted py-3'>No sections found.</td></tr>";
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
@media (max-width: 768px) {
    .scroll-feedback {
        height: auto;
        overflow: visible;
    }
}
</style>
</head>
<body>

<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="mx-2">
            <h4><i class="fa-solid fa-chart-line me-2"></i>SF5 - Report on Promotion and Level of Proficiency</h4>
        </div>
    </div>

    <div class="scroll-feedback">
        <!-- Search Section -->
        <div class="row mb-3 justify-content-between align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                    <input type="text" class="form-control" name="search" id="searchInput" 
                           placeholder="Search sections by name or grade level...">
                </div>
            </div>
            <div class="col-md-4 text-end">
                <!-- Optional: Add action buttons here if needed -->
            </div>
        </div>

        <!-- Sections Table -->
        <div class="table-container-wrapper p-0">
            <!-- Fixed Header -->
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover" style="font-size: 0.875rem;">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="60%">Section Name</th>
                            <th width="35%">Grade Level</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <!-- Scrollable Body -->
            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.875rem;">
                    <tbody id="sectionTable">
                        <?php
                        $query = "SELECT * FROM sections ORDER BY section_grade_level, section_name LIMIT 20";
                        $result = $conn->query($query);
                        if ($result && $result->num_rows > 0) {
                            $count = 1;
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr class='section-row' 
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
                        } else {
                            echo "<tr><td colspan='3' class='text-center py-3'>No sections found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div id="noResults" class="text-center py-5 d-none">
                <div class="empty-state">
                    <i class="fa-solid fa-layer-group fa-3x text-muted mb-3"></i>
                    <h5>No sections found</h5>
                    <p class="text-muted">Try adjusting your search</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const sectionTable = document.getElementById('sectionTable');
    const noResultsDiv = document.getElementById('noResults');
    let currentRowCount = 0;
    
    // Load initial sections
    function loadSections(search = '') {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'contents/sf5.php?ajax=1&search=' + encodeURIComponent(search), true);
        xhr.onload = function() {
            if (this.status === 200) {
                sectionTable.innerHTML = this.responseText;
                attachRowClickEvents();
                updateRowNumbers();
                
                // Show/hide no results message
                const rows = sectionTable.querySelectorAll('.section-row');
                if (rows.length === 0) {
                    sectionTable.style.display = 'none';
                    noResultsDiv.classList.remove('d-none');
                } else {
                    sectionTable.style.display = '';
                    noResultsDiv.classList.add('d-none');
                    currentRowCount = rows.length;
                }
            }
        };
        xhr.send();
    }
    
    // Update row numbers
    function updateRowNumbers() {
        const rows = sectionTable.querySelectorAll('.section-row');
        rows.forEach((row, index) => {
            const firstCell = row.querySelector('td:first-child');
            if (firstCell) {
                firstCell.textContent = index + 1;
            }
        });
    }
    
    // Live search
    searchInput.addEventListener('input', function() {
        loadSections(this.value.trim());
    });
    
    
    // Enter key support
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            loadSections(this.value.trim());
        }
    });
    
    // Clickable rows redirect
function attachRowClickEvents() {
    document.querySelectorAll('.section-row').forEach(row => {
        row.addEventListener('click', function() {
            const sectionId = this.dataset.id;
            const gradeLevel = this.dataset.grade;
            const sectionName = this.dataset.section;
            if (sectionId && gradeLevel && sectionName) {
               const url = `<?php echo BASE_FR; ?>/src/UI-teacher/contents/schoolform5.php`
                    + `?section_id=${encodeURIComponent(sectionId)}`
                    + `&grade=${encodeURIComponent(gradeLevel)}`
                    + `&section=${encodeURIComponent(sectionName)}`;
                window.location.href = url;
            }
        });
    });
}
    
    // Focus styling
    searchInput.addEventListener('focus', function() {
        this.parentElement.classList.add('border-primary', 'border-2');
    });
    
    searchInput.addEventListener('blur', function() {
        this.parentElement.classList.remove('border-primary', 'border-2');
    });
    
    // Initialize
    attachRowClickEvents();
    updateRowNumbers();
});
</script>

</body>
</html>
