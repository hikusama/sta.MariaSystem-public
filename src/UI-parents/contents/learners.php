<style>
    img {
        width: 90px;
        height: 90px;
        border-radius: 50%;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="mx-2">
        <h4><i class="fa-solid fa-users-gear me-2"></i>Learners Management</h4>
    </div>
</div>

<div class="row mb-3 justify-content-between">
    <div class="col-md-4">
        <input type="text" id="searchInput" name="search" class="form-control"
            placeholder="Search by name, role, status, or date...">
    </div>
    <div class="col-md-3">
        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#AddNewAccount"
            id="add_new"><i class="fa fa-plus"></i> Create Learner Profile</button>
    </div>
</div>

<!-- Create learner profile modal -->
<div class="modal fade" id="AddNewAccount" tabindex="-1" aria-labelledby="AddNewAccountLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white mb-4">
                <h5 class="modal-title text-white" id="AddNewAccountLabel">Create New User Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                    onclick="location.reload()"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="studentAcc-form" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Student LRN <span class="text-danger">*</span></label>
                            <input type="text" name="lrn" pattern="\d{12}" maxlength="12" inputmode="numeric" required
                                class="form-control" placeholder="Enter 12-digit LRN">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <select required name="grade_level" id="" class="form-select">
                                <option value="">Select Grade Level</option>
                                <option value="Grade 1">Grade 1</option>
                                <option value="Grade 2">Grade 2</option>
                                <option value="Grade 3">Grade 3</option>
                                <option value="Grade 4">Grade 4</option>
                                <option value="Grade 5">Grade 5</option>
                                <option value="Grade 6">Grade 6</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Nickname</label>
                            <input type="text" class="form-control" placeholder="student nickname" name="nickname">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sex</label>
                            <select name="sex" id="" class="form-select">
                                <option value="">Select student sex</option>
                                <option value="MALE">Male</option>
                                <option value="FEMALE">Female</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="firstName" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Middle Name <span class="text-danger">*</span></label>
                            <input required type="text" class="form-control" name="middleName">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="lastName" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Suffix</label>
                            <select class="form-select" name="suffix">
                                <option value="" disabled selected>Select suffix (optional)</option>
                                <option value="Jr">Jr</option>
                                <option value="Sr">Sr</option>
                                <option value="II">II</option>
                                <option value="III">III</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Religion <span class="text-danger">*</span></label>
                            <select required name="religion" id="religion" class="form-select">
                                <option value="">Select Religion</option>
                                <option value="Roman Catholic">Roman Catholic</option>
                                <option value="Iglesia ni Cristo">Iglesia ni Cristo</option>
                                <option value="Evangelical">Evangelical</option>
                                <option value="Islam">Islam</option>
                                <option value="Seventh-day Adventist">Seventh-day Adventist</option>
                                <option value="Aglipayan (IFI)">Aglipayan (IFI)</option>
                                <option value="Baptist">Baptist</option>
                                <option value="Born Again Christian">Born Again Christian</option>
                                <option value="Jehovah's Witness">Jehovah's Witness</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Birth date <span class="text-danger">*</span></label>
                            <input required type="date" name="birthdate" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Birth place</label>
                            <input type="text" name="birthplace" class="form-control" placeholder="Birth Place">
                        </div>
                        <div class="col-md-8 mt-2">
                            <div class="alert alert-light"
                                style="border: 1px solid #d1ecf1; background-color: #e8f4fd; color: #0c5460;">
                                <i class="fa fa-info-circle me-2"></i>
                                <strong>Note:</strong> Your enrollment request will be verified by the school
                                administration. You will receive a notification once the verification is complete.
                            </div>
                        </div>
                        <div class="col-md-4 d-flex flex-column justify-content-center align-items-center">
                            <img src="../../assets/image/users.png" class="w-50 h-auto">
                            <input type="file" class="form-control" name="student_profile">
                        </div>
                    </div>

                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-primary px-5">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mt-2 gap-3 px-4">
    <?php
        $stmt = $pdo->prepare("SELECT * FROM student WHERE guardian_id = '$user_id'");
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($students as $student) :
    ?>  
    <div class="row col-md-5 border shadow rounded p-0 py-2">
        <div class="col-md-4 border-end m-0 p-0 d-flex flex-column align-items-center justify-content-center">
            <?php if($student["student_profile"] !== '') { ?>
                <img src="../../assets/image/uploads/<?php echo $student["student_profile"];?>">
            <?php } else { ?>
                <img src="../../assets/image/users.png">
            <?php } ?>
            <label class="form-label m-0 p-0">LRN: <?= htmlspecialchars($student["lrn"]) ?></label>
        </div>
        <div class="col-md-8 d-flex flex-column align-items-start justify-content-center px-1">
            <span>Name: 
                <strong>
                    <?= htmlspecialchars($student["fname"]) . " " . htmlspecialchars(substr($student["mname"], 0, 1)) . ". " . htmlspecialchars($student["lname"]) ?>
                </strong>
            </span>
            <span>Grade Level: <strong><?= htmlspecialchars($student["gradeLevel"]) ?></strong></span>
            <span>Birth Day: <strong><?= htmlspecialchars($student["birthdate"]) ?></strong></span>
            <span>Enrolment Status: <strong><?php if($student["enrolment_status"] == ''){echo 'Pending';}else{echo htmlspecialchars($student["enrolment_status"]);}?></strong></span>
            
            <!-- BUTTONS AREA -->
            <div class="buttons w-100 d-flex justify-content-end pe-2 mt-1 pt-2 gap-2 border-top">
                <a href="index.php?page=contents/profile&student_id=<?= htmlspecialchars($student["student_id"]) ?>">
                    <button class="btn btn-sm m-0 btn-info">Profile</button>
                </a>
                <a href="index.php?page=contents/form&student_id=<?= htmlspecialchars($student["student_id"]) ?>">
                    <button class="btn btn-sm m-0 btn-danger">Enrolment Form</button>
                </a>

                <?php
                    // Construct report card filename
                    $lrn = $student["lrn"];
                    $fname = preg_replace("/[^A-Za-z0-9]/", "", strtolower($student["fname"]));
                    $lname = preg_replace("/[^A-Za-z0-9]/", "", strtolower($student["lname"]));
                    $grade = str_replace(" ", "", strtolower($student["gradeLevel"]));
                    $reportFile = "C:/xampp/htdocs/sta.MariaSystem/sf9_files/{$lrn}_{$fname}_{$lname}_{$grade}.xlsx";
                    
                    if (file_exists($reportFile)) {
                        $webPath = "../../sf9_files/{$lrn}_{$fname}_{$lname}_{$grade}.xlsx";
                ?>
                    <!--  <a href="<?= $webPath ?>" download>
                        <button class="btn btn-sm m-0 btn-success">Download Report Card</button>
                    </a> -->
                <a href="index.php?page=contents/sf9_view&student_id=<?= htmlspecialchars($student['student_id']) ?>">
    <button class="btn btn-sm m-0 btn-primary">View Report Card</button>
</a>

                <?php
                    } else {
                ?>
                    <button class="btn btn-sm m-0 btn-secondary" disabled title="Report card not available">
                        Download Report Card
                    </button>
                <?php
                    }
                ?>
            </div>
        </div>
    </div>
    <?php endforeach ?>
</div>
