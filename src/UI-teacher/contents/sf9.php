<?php
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

    exit; // stop execution for AJAX
}
?>

<style>
/* Make SF9 header look like the SF5 header */
.d-flex.justify-content-between.align-items-center.mb-3 {
    background-color: #FF3860;
    color: #fff;
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 15px !important;
}

/* Header title styling */
.d-flex.justify-content-between.align-items-center.mb-3 h4 {
    margin: 0;
    font-weight: 600;
    font-size: 1.2rem;
    color: white;
}

/* Search input styling */
.d-flex.justify-content-between.align-items-center.mb-3 input {
    border-radius: 10px;
    border: 1px solid #ced4da;
}

    </style>

<div class="container mt-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="m-0">SF9 Learner's Progress Report Card</h4>

        <div style="width: 280px;">
            <input 
                type="text" 
                id="searchInput" 
                class="form-control" 
                placeholder="Search LRN, Name, Grade, or Sex..."
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
                    // Show 7 students by default
                    $query = "SELECT * FROM student ORDER BY lname, fname LIMIT 7";
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

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    const search = this.value.trim();
    const xhr = new XMLHttpRequest();

   
    xhr.open('GET', 'contents/sf9.php?ajax=1&search=' + encodeURIComponent(search), true);

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
               
                window.location.href = 'contents/schoolform9.php?student_id=' + encodeURIComponent(studentId);
            }
        });
    });
}
attachRowClickEvents();
</script>
