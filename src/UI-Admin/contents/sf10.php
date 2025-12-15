<?php
// Database connection (adjust as needed)
$conn = new mysqli("localhost", "root", "", "stamariadb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if AJAX search
$search = '';
if (isset($_GET['ajax']) && $_GET['ajax'] == 1 && isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
}
?>

<div class="container mt-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="m-0">SF10 Learner’s Permanent Academic Record</h4>
        <div style="width: 280px;">
            <input 
                type="text" 
                id="searchInput" 
                class="form-control" 
                placeholder="Search LRN, Name, Grade, or Sex..."
                value="<?= htmlspecialchars($search) ?>"
            >
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover table-striped align-middle">
                <thead class="table-primary text-center">
                    <tr>
                        <th>LRN</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Last Name</th>
                        <th>Grade Level</th>
                        <th>Sex</th>
                        <th>Enrolment Status</th>
                    </tr>
                </thead>
                <tbody id="studentTable" class="text-center">
                    <?php
                    // Build query
                    if ($search != '') {
                        $query = "SELECT * FROM student 
                                  WHERE lrn LIKE '%$search%' 
                                     OR fname LIKE '%$search%' 
                                     OR mname LIKE '%$search%' 
                                     OR lname LIKE '%$search%' 
                                     OR gradeLevel LIKE '%$search%' 
                                     OR sex LIKE '%$search%' 
                                  ORDER BY lname, fname";
                    } else {
                        $query = "SELECT * FROM student ORDER BY lname, fname";
                    }

                    $result = $conn->query($query);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr class='student-row' data-id='" . htmlspecialchars($row['student_id']) . "'>
                                    <td>" . htmlspecialchars($row['lrn']) . "</td>
                                    <td>" . htmlspecialchars($row['fname']) . "</td>
                                    <td>" . htmlspecialchars($row['mname']) . "</td>
                                    <td>" . htmlspecialchars($row['lname']) . "</td>
                                    <td>" . htmlspecialchars($row['gradeLevel']) . "</td>
                                    <td>" . htmlspecialchars($row['sex']) . "</td>
                                    <td>" . htmlspecialchars($row['enrolment_status']) . "</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center text-muted'>No students found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    const search = this.value.trim();
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'contents/sf10.php?ajax=1&search=' + encodeURIComponent(search), true);
    xhr.onload = function() {
        if (this.status === 200) {
            document.getElementById('studentTable').innerHTML = this.responseText;
            attachRowClickEvents();
        }
    };
    xhr.send();
});

function attachRowClickEvents() {
    const rows = document.querySelectorAll('.student-row');
    rows.forEach(row => {
        row.addEventListener('click', function() {
            const studentId = this.getAttribute('data-id');
            if (studentId) {
                window.location.href = '/sta.MariaSystem/src/UI-Admin/contents/schoolform10.php?student_id=' + studentId;
            }
        });
    });
}

// Initialize row clicks
attachRowClickEvents();
</script>
