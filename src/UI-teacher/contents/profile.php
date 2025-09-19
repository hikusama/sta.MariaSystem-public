<?php
    isset($_GET["student_id"]) ? $student_id = $_GET["student_id"] : '';
    $query = "SELECT * FROM student WHERE student_id = '$student_id'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $student_info = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="mx-2">
        <h4><i class="fa-solid fa-user me-2  "></i>Learners Profile</h4>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="col-md-11 border rounded shadow d-flex flex-column align-items-center justify-conten-center">
            <img src="../../assets/image/users.png" style="width: 200px; height: auto;">
            <span>Lrn: <strong><?= $student_info["lrn"] ?></strong></span>
            <span>Stduent: <strong><?= htmlSpecialChars($student_info["fname"]) . " " .
                htmlspecialchars(substr($student_info["mname"], 0,1)) . ". " .
                htmlspecialchars($student_info["lname"]) ?></strong></span>
            <span>Guardian: <strong> DEPAMYLO ANGELO</strong></span>
        </div>
    </div>
    <div class="col-md-8">
        <div class="col-md-11 border rounded shadow d-flex flex-column align-items-center justify-conten-center">
        </div>
    </div>
</div>