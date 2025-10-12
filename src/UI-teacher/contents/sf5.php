<?php
// -------------------------
// Database connection
// -------------------------
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "stamariadb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// -------------------------
// AJAX handler
// -------------------------
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    if ($search === '') {
        $sql = "SELECT * FROM sections ORDER BY section_grade_level, section_name LIMIT 7";
        $stmt = $conn->prepare($sql);
    } else {
        $searchLike = "%{$search}%";
        $sql = "SELECT * FROM sections 
                WHERE section_name LIKE ? OR section_grade_level LIKE ?
                ORDER BY section_grade_level, section_name";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $searchLike, $searchLike);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr class='section-row' data-id='" . htmlspecialchars($row['section_id']) . "'>
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

<!-- Main Content -->
<div class="container mt-3">

    <!-- Header + Search bar -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="m-0">SF5 Report on Promotion and Level of Proficiency</h4>

        <div style="width: 280px;">
            <input 
                type="text" 
                id="searchInput" 
                class="form-control" 
                placeholder="Search Section or Grade Level..."
            >
        </div>
    </div>

    <!-- Section Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover table-striped align-middle">
                <thead class="table-primary text-center">
                    <tr>
                        <th>Section</th>
                        <th>Grade Level</th>
                    </tr>
                </thead>
                <tbody id="sectionTable" class="text-center">
                    <?php
                    $query = "SELECT * FROM sections ORDER BY section_grade_level, section_name LIMIT 7";
                    $result = $conn->query($query);
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr class='section-row' data-id='" . htmlspecialchars($row['section_id']) . "'>
                                    <td>" . htmlspecialchars($row['section_name']) . "</td>
                                    <td>" . htmlspecialchars($row['section_grade_level']) . "</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='2' class='text-center text-muted'>No sections found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- JS -->
<script>
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

function attachRowClickEvents() {
    const rows = document.querySelectorAll('.section-row');
    rows.forEach(row => {
        row.addEventListener('click', function() {
            const sectionId = this.getAttribute('data-id');
            if (sectionId) {
                window.location.href = '/sta.MariaSystem/src/UI-Admin/contents/schoolform5.php?section_id=' + sectionId;
            }
        });
    });
}

attachRowClickEvents();
</script>
