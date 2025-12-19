<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('teacher', 2);
if ($result['res']) {
    header($result['uri']);
    exit;
}
if (isset($_GET['student_id'])) {
    $learner_id = $_GET['student_id'];
}
$query = "SELECT * FROM student
INNER JOIN stuEnrolmentInfo ON student.student_id = stuEnrolmentInfo.student_id
WHERE student.student_id = '$learner_id'";
$stmt = $pdo->prepare($query);
$stmt->execute();
$studentInfo = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<main>
    <section class="container-fluid py-4" style="max-height: 85vh; overflow-y: auto;">
        
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="section-header">
                        <h1 class="h4 text-gray-800 mb-0"><i class="fa-solid fa-user-graduate me-2"></i>Student Enrolment</h1>
                        <p class="text-muted">Update student enrolment information</p>
                    </div>
                    <div class="sy-badge">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fa-solid fa-clipboard-check fa-2x text-danger"></i>
                            <div>
                                <small class="d-block mb-1">Enrolment Status</small>
                                <h4 class="mb-0 fw-bold" id="enrollmentStatus">Pending</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Information Card -->
        <div class="row mb-4 animate-card">
            <div class="col-12">
                <div class="parent-info-card bg-danger text-white" style="border-radius: 15px;">
                    <div class="row align-items-center p-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon" style="background: rgba(255,255,255,0.2); border: 2px solid white;">
                                    <i class="fa-solid fa-id-card text-white"></i>
                                </div>
                                <div>
                                    <h2 class="h4 mb-1">Student Information</h2>
                                    <p class="mb-0">Update enrolment details for <?= htmlspecialchars($studentInfo["fname"] ?? 'Student') ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <div class="d-flex justify-content-md-end gap-3">
                                <div>
                                    <a href="index.php?page=contents/enrolment">
                                        <button class="btn btn-light px-4">
                                            <i class="fa-solid fa-arrow-left me-2"></i>Back to Enrolment
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enrolment Form -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-4">
                        <form id="stduentEnrolment-form" method="POST">
                            <input type="hidden" name="student_id" value="<?php echo $learner_id; ?>" name="learnerIdInput" id="learnerIdInput">
                            
                            <!-- Form Header -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="stat-icon danger">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                        <h4 class="mb-0 text-danger">Personal Information</h4>
                                    </div>
                                    <p class="text-muted">Basic student details and identification</p>
                                </div>
                            </div>

                            <!-- Personal Information -->
                            <div class="row g-3 mb-4">
                                <div class="col-xl-3 col-lg-4 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" value="<?= htmlspecialchars($studentInfo["fname"] ?? '') ?>" 
                                               id="fname" name="fname" placeholder="First name" 
                                               class="form-control border-0 bg-light">
                                        <label for="fname" class="text-muted">
                                            <i class="fa-solid fa-signature me-2"></i>First name
                                        </label>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-4 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" value="<?= htmlspecialchars($studentInfo["mname"] ?? '') ?>" 
                                               id="mname" name="mname" placeholder="Middle name" 
                                               class="form-control border-0 bg-light">
                                        <label for="mname" class="text-muted">
                                            <i class="fa-solid fa-signature me-2"></i>Middle name
                                        </label>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-4 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" id="lname" value="<?= htmlspecialchars($studentInfo["lname"] ?? '') ?>" 
                                               name="lname" placeholder="Last name" 
                                               class="form-control border-0 bg-light">
                                        <label for="lname" class="text-muted">
                                            <i class="fa-solid fa-signature me-2"></i>Last name
                                        </label>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-lg-4 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" id="suffix" value="<?= htmlspecialchars($studentInfo["suffix"] ?? '') ?>" 
                                               name="suffix" placeholder="Name extension" 
                                               class="form-control border-0 bg-light">
                                        <label for="suffix" class="text-muted">
                                            <i class="fa-solid fa-plus me-2"></i>Name extension
                                        </label>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-4 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" value="<?= htmlspecialchars($studentInfo["lrn"] ?? '') ?>" 
                                               name="lrn" id="lrn" placeholder="LRN (12 digits only)" 
                                               maxlength="12" class="form-control border-0 bg-light" 
                                               pattern="\d{12}" required>
                                        <label for="lrn" class="text-muted">
                                            <i class="fa-solid fa-id-badge me-2"></i>LRN
                                        </label>
                                    </div>
                                    <small class="text-muted mt-1 d-block">12 digits only</small>
                                </div>
                                <div class="col-xl-3 col-lg-4 col-md-6">
                                    <div class="form-floating">
                                        <input type="date" value="<?= htmlspecialchars($studentInfo["birthdate"] ?? '') ?>" 
                                               name="birthdate" id="birthdate" placeholder="Birth date" 
                                               class="form-control border-0 bg-light">
                                        <label for="birthdate" class="text-muted">
                                            <i class="fa-solid fa-cake-candles me-2"></i>Birth date
                                        </label>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-lg-4 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" name="age" id="age" placeholder="Age" readonly 
                                               class="form-control border-0 bg-light" 
                                               value="<?= htmlspecialchars($studentInfo["age"] ?? '') ?>">
                                        <label for="age" class="text-muted">
                                            <i class="fa-solid fa-calendar-days me-2"></i>Age
                                        </label>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-lg-4 col-md-6">
                                    <div class="form-floating">
                                        <select name="gender" id="gender" class="form-select border-0 bg-light">
                                            <option value="">Select Gender</option>
                                            <option value="Male" <?= ($studentInfo["sex"] ?? '') == 'Male' ? 'selected' : '' ?>>MALE</option>
                                            <option value="Female" <?= ($studentInfo["sex"] ?? '') == 'Female' ? 'selected' : '' ?>>FEMALE</option>
                                        </select>
                                        <label for="gender" class="text-muted">
                                            <i class="fa-solid fa-venus-mars me-2"></i>Sex
                                        </label>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-4 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" value="<?= htmlspecialchars($studentInfo["birthplace"] ?? '') ?>" 
                                               name="birth_place" id="birth_place" 
                                               placeholder="Birth Place (city/Muntinlupa)" 
                                               class="form-control border-0 bg-light">
                                        <label for="birth_place" class="text-muted">
                                            <i class="fa-solid fa-location-dot me-2"></i>Birth Place
                                        </label>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-4 col-md-6">
                                    <div class="form-floating">
                                        <select name="religious" id="religious" class="form-select border-0 bg-light">
                                            <option value="">Select Religion</option>
                                            <option value="Roman Catholic" <?= ($studentInfo["religion"] ?? '') == 'Roman Catholic' ? 'selected' : '' ?>>Roman Catholic</option>
                                            <option value="Iglesia ni Cristo" <?= ($studentInfo["religion"] ?? '') == 'Iglesia ni Cristo' ? 'selected' : '' ?>>Iglesia ni Cristo</option>
                                            <option value="Evangelical" <?= ($studentInfo["religion"] ?? '') == 'Evangelical' ? 'selected' : '' ?>>Evangelical</option>
                                            <option value="Islam" <?= ($studentInfo["religion"] ?? '') == 'Islam' ? 'selected' : '' ?>>Islam</option>
                                            <option value="Seventh-day Adventist" <?= ($studentInfo["religion"] ?? '') == 'Seventh-day Adventist' ? 'selected' : '' ?>>Seventh-day Adventist</option>
                                            <option value="Aglipayan (IFI)" <?= ($studentInfo["religion"] ?? '') == 'Aglipayan (IFI)' ? 'selected' : '' ?>>Aglipayan (IFI)</option>
                                            <option value="Baptist" <?= ($studentInfo["religion"] ?? '') == 'Baptist' ? 'selected' : '' ?>>Baptist</option>
                                            <option value="Born Again Christian" <?= ($studentInfo["religion"] ?? '') == 'Born Again Christian' ? 'selected' : '' ?>>Born Again Christian</option>
                                            <option value="Jehovah's Witness" <?= ($studentInfo["religion"] ?? '') == 'Jehovah\'s Witness' ? 'selected' : '' ?>>Jehovah's Witness</option>
                                        </select>
                                        <label for="religious" class="text-muted">
                                            <i class="fa-solid fa-hands-praying me-2"></i>Religion
                                        </label>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-4 col-md-6">
                                    <div class="form-floating">
                                        <select name="tongue" id="tongue" class="form-select border-0 bg-light">
                                            <option value="">Select Mother Tongue</option>
                                            <option value="Tagalog" <?= ($studentInfo["mother_tongue"] ?? '') == 'Tagalog' ? 'selected' : '' ?>>Tagalog</option>
                                            <option value="Cebuano" <?= ($studentInfo["mother_tongue"] ?? '') == 'Cebuano' ? 'selected' : '' ?>>Cebuano</option>
                                            <option value="Ilocano" <?= ($studentInfo["mother_tongue"] ?? '') == 'Ilocano' ? 'selected' : '' ?>>Ilocano</option>
                                            <option value="Hiligaynon" <?= ($studentInfo["mother_tongue"] ?? '') == 'Hiligaynon' ? 'selected' : '' ?>>Hiligaynon</option>
                                            <option value="Bicolano" <?= ($studentInfo["mother_tongue"] ?? '') == 'Bicolano' ? 'selected' : '' ?>>Bicolano</option>
                                            <option value="Kapampangan" <?= ($studentInfo["mother_tongue"] ?? '') == 'Kapampangan' ? 'selected' : '' ?>>Kapampangan</option>
                                            <option value="Pangasinan" <?= ($studentInfo["mother_tongue"] ?? '') == 'Pangasinan' ? 'selected' : '' ?>>Pangasinan</option>
                                            <option value="Waray" <?= ($studentInfo["mother_tongue"] ?? '') == 'Waray' ? 'selected' : '' ?>>Waray</option>
                                            <option value="Maranao" <?= ($studentInfo["mother_tongue"] ?? '') == 'Maranao' ? 'selected' : '' ?>>Maranao</option>
                                            <option value="Tausug" <?= ($studentInfo["mother_tongue"] ?? '') == 'Tausug' ? 'selected' : '' ?>>Tausug</option>
                                            <option value="Others" <?= ($studentInfo["mother_tongue"] ?? '') == 'Others' ? 'selected' : '' ?>>Others</option>
                                        </select>
                                        <label for="tongue" class="text-muted">
                                            <i class="fa-solid fa-language me-2"></i>Mother Tongue
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Current Address Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="stat-icon warning">
                                            <i class="fa-solid fa-house"></i>
                                        </div>
                                        <h4 class="mb-0 text-warning">Current Address</h4>
                                    </div>
                                    <p class="text-muted">Student's current residence information</p>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-xl-3 col-lg-4 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" name="current_house_no" id="current_house_no" 
                                               value="<?= htmlspecialchars($studentInfo["house_no"] ?? '') ?>" 
                                               class="form-control border-0 bg-light">
                                        <label for="current_house_no" class="text-muted">
                                            <i class="fa-solid fa-hashtag me-2"></i>House No.
                                        </label>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-4 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" name="current_street" id="current_street" 
                                               value="<?= htmlspecialchars($studentInfo["street"] ?? '') ?>" 
                                               class="form-control border-0 bg-light">
                                        <label for="current_street" class="text-muted">
                                            <i class="fa-solid fa-road me-2"></i>Sitio/Street
                                        </label>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-4 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" name="current_barangay" id="current_barangay" 
                                               value="<?= htmlspecialchars($studentInfo["barnagay"] ?? '') ?>" 
                                               class="form-control border-0 bg-light">
                                        <label for="current_barangay" class="text-muted">
                                            <i class="fa-solid fa-location-dot me-2"></i>Barangay
                                        </label>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-4 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" name="current_city" id="current_city" 
                                               value="<?= htmlspecialchars($studentInfo["city"] ?? '') ?>" 
                                               class="form-control border-0 bg-light">
                                        <label for="current_city" class="text-muted">
                                            <i class="fa-solid fa-city me-2"></i>Municipality/City
                                        </label>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-4 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" name="current_province" id="current_province" 
                                               value="<?= htmlspecialchars($studentInfo["province"] ?? '') ?>" 
                                               class="form-control border-0 bg-light">
                                        <label for="current_province" class="text-muted">
                                            <i class="fa-solid fa-map me-2"></i>Province
                                        </label>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-4 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" name="current_country" id="current_country" 
                                               value="<?= htmlspecialchars($studentInfo["country"] ?? 'Philippines') ?>" 
                                               class="form-control border-0 bg-light">
                                        <label for="current_country" class="text-muted">
                                            <i class="fa-solid fa-earth-americas me-2"></i>Country
                                        </label>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-4 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" name="current_zip" id="current_zip" 
                                               value="<?= htmlspecialchars($studentInfo["zip_code"] ?? '') ?>" 
                                               class="form-control border-0 bg-light">
                                        <label for="current_zip" class="text-muted">
                                            <i class="fa-solid fa-mailbox me-2"></i>Zip Code
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Medical Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="stat-icon info">
                                            <i class="fa-solid fa-heart-pulse"></i>
                                        </div>
                                        <h4 class="mb-0 text-info">Medical Information</h4>
                                    </div>
                                    <p class="text-muted">Health status and special needs assessment</p>
                                </div>
                            </div>

                            <!-- Diagnosis Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <label class="h6 mb-3 text-gray-700">
                                        <i class="fa-solid fa-stethoscope me-2"></i>With diagnosis from Licensed Medical Specialist
                                    </label>
                                    <?php
                                    $diagnosis = isset($studentInfo["diagnosis"]) ? explode(',', $studentInfo["diagnosis"]) : [];
                                    ?>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="diagnosis[]" value="ADHD" id="diagnosisADHD" <?= in_array('ADHD', $diagnosis) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="diagnosisADHD">ADHD</label>
                                            </div>
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Autism" id="diagnosisAutism" <?= in_array('Autism', $diagnosis) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="diagnosisAutism">Autism Spectrum Disorder</label>
                                            </div>
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Behavior" id="diagnosisBehavior" <?= in_array('Behavior', $diagnosis) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="diagnosisBehavior">Emotional/Behavioral Disorder</label>
                                            </div>
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Hearing" id="diagnosisHearing" <?= in_array('Hearing', $diagnosis) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="diagnosisHearing">Hearing Impairment</label>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Learning" id="diagnosisLearning" <?= in_array('Learning', $diagnosis) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="diagnosisLearning">Learning Disability</label>
                                            </div>
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Orthopedic" id="diagnosisOrthopedic" <?= in_array('Orthopedic', $diagnosis) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="diagnosisOrthopedic">Orthopedic/Physical Handicap</label>
                                            </div>
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Speech" id="diagnosisSpeech" <?= in_array('Speech', $diagnosis) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="diagnosisSpeech">Speech/Language Disorder</label>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Intellectual" id="diagnosisIntellectual" <?= in_array('Intellectual', $diagnosis) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="diagnosisIntellectual">Intellectual Disability</label>
                                            </div>
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Cancer" id="diagnosisCancer" <?= in_array('Cancer', $diagnosis) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="diagnosisCancer">Non-Cancer</label>
                                            </div>
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Health" id="diagnosisHealth" <?= in_array('Health', $diagnosis) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="diagnosisHealth">Chronic Health Problem</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Manifestations Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <label class="h6 mb-3 text-gray-700">
                                        <i class="fa-solid fa-clipboard-list me-2"></i>With Manifestations
                                    </label>
                                    <?php
                                    $manifestations = isset($studentInfo["manifestations"]) ? explode(',', $studentInfo["manifestations"]) : [];
                                    ?>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="manifestations[]" value="Difficulty in Applying Knowledge" <?= in_array('Difficulty in Applying Knowledge', $manifestations) ? 'checked' : '' ?>>
                                                Difficulty in Applying Knowledge
                                            </div>
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="manifestations[]" value="Difficulty in Communicating" <?= in_array('Difficulty in Communicating', $manifestations) ? 'checked' : '' ?>>
                                                Difficulty in Communicating
                                            </div>
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="manifestations[]" value="Difficulty in Interpersonal Behavior" <?= in_array('Difficulty in Interpersonal Behavior', $manifestations) ? 'checked' : '' ?>>
                                                Difficulty in Interpersonal Behavior
                                            </div>
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="manifestations[]" value="Difficulty in Behavior" <?= in_array('Difficulty in Behavior', $manifestations) ? 'checked' : '' ?>>
                                                Difficulty in Behavior
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="manifestations[]" value="Difficulty in Mobility (Walking, Climbing)" <?= in_array('Difficulty in Mobility (Walking, Climbing)', $manifestations) ? 'checked' : '' ?>>
                                                Difficulty in Mobility (Walking, Climbing)
                                            </div>
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="manifestations[]" value="Difficulty in Performing Adaptive Self-Care" <?= in_array('Difficulty in Performing Adaptive Self-Care', $manifestations) ? 'checked' : '' ?>>
                                                Difficulty in Performing Adaptive Self-Care
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="manifestations[]" value="Difficulty in Remembering, Concentrating, Playing Attention" <?= in_array('Difficulty in Remembering, Concentrating, Playing Attention', $manifestations) ? 'checked' : '' ?>>
                                                Difficulty in Remembering, Concentrating, Playing Attention
                                            </div>
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="manifestations[]" value="Difficulty in Seeing" <?= in_array('Difficulty in Seeing', $manifestations) ? 'checked' : '' ?>>
                                                Difficulty in Seeing
                                            </div>
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="manifestations[]" value="Difficulty in Hearing" <?= in_array('Difficulty in Hearing', $manifestations) ? 'checked' : '' ?>>
                                                Difficulty in Hearing
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- PWD ID Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <label class="h6 mb-3 text-gray-700">
                                        <i class="fa-solid fa-wheelchair me-2"></i>Does the learner have a PWD ID?
                                    </label>
                                    <?php
                                    $hasPwdId = $studentInfo["pwd_id"] ?? '';
                                    $pwdYesChecked = (!empty($hasPwdId) && $hasPwdId !== 'No') ? 'checked' : '';
                                    $pwdNoChecked = (empty($hasPwdId) || $hasPwdId === 'No') ? 'checked' : '';
                                    ?>
                                    <div class="d-flex align-items-center gap-4 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="has_pwd_id" value="yes" id="pwd_yes" <?= $pwdYesChecked ?>>
                                            <label class="form-check-label" for="pwd_yes">Yes</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="has_pwd_id" value="no" id="pwd_no" <?= $pwdNoChecked ?>>
                                            <label class="form-check-label" for="pwd_no">No</label>
                                        </div>
                                    </div>
                                    <div class="form-floating">
                                        <input type="text" name="has_pwd_id_specific" placeholder="If Yes, please specify" 
                                               value="<?= ($pwdYesChecked && $hasPwdId !== 'Yes') ? htmlspecialchars($hasPwdId) : '' ?>" 
                                               class="form-control border-0 bg-light">
                                        <label for="has_pwd_id_specific" class="text-muted">
                                            <i class="fa-solid fa-circle-info me-2"></i>If Yes, please specify
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Returning Learner Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="stat-icon success">
                                            <i class="fa-solid fa-rotate-left"></i>
                                        </div>
                                        <h4 class="mb-0 text-success">For Returning Learner (Balik-Aral)</h4>
                                    </div>
                                    <p class="text-muted">Previous education information</p>
                                    
                                    <div class="row g-3">
                                        <div class="col-xl-4 col-lg-4 col-md-6">
                                            <div class="form-floating">
                                                <input type="text" name="last_grade_level" 
                                                       value="<?= htmlspecialchars($studentInfo["balik_aral"] ?? '') ?>" 
                                                       class="form-control border-0 bg-light">
                                                <label class="text-muted">
                                                    <i class="fa-solid fa-graduation-cap me-2"></i>Last Grade Level Completed
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-lg-4 col-md-6">
                                            <div class="form-floating">
                                                <input type="text" name="last_sy" 
                                                       value="<?= htmlspecialchars($studentInfo["last_sy"] ?? '') ?>" 
                                                       class="form-control border-0 bg-light">
                                                <label class="text-muted">
                                                    <i class="fa-solid fa-calendar me-2"></i>Last School Year Completed
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-lg-4 col-md-6">
                                            <div class="form-floating">
                                                <input type="text" name="last_school" 
                                                       value="<?= htmlspecialchars($studentInfo["last_school"] ?? '') ?>" 
                                                       class="form-control border-0 bg-light">
                                                <label class="text-muted">
                                                    <i class="fa-solid fa-school me-2"></i>Last School Attended
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Distance Learning Preference -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="stat-icon purple">
                                            <i class="fa-solid fa-laptop-house"></i>
                                        </div>
                                        <h4 class="mb-0 text-purple">Distance Learning Preference</h4>
                                    </div>
                                    <p class="text-muted">Preferred learning modes and methods</p>
                                    
                                    <label class="h6 mb-3 text-gray-700">
                                        <i class="fa-solid fa-chalkboard-user me-2"></i>Preferred Learning Mode
                                    </label>
                                    <?php
                                    $learningMode = isset($studentInfo["learning_mode"]) ? explode(',', $studentInfo["learning_mode"]) : [];
                                    ?>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="learning_mode[]" value="Blended" <?= in_array('Blended', $learningMode) ? 'checked' : '' ?>> 
                                                Blended (Combination)
                                            </div>
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="learning_mode[]" value="Homeschooling" <?= in_array('Homeschooling', $learningMode) ? 'checked' : '' ?>> 
                                                Homeschooling
                                            </div>
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="learning_mode[]" value="ModularPrint" <?= in_array('ModularPrint', $learningMode) ? 'checked' : '' ?>> 
                                                Modular (Print)
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="learning_mode[]" value="Radio" <?= in_array('Radio', $learningMode) ? 'checked' : '' ?>> 
                                                Radio-Based Instruction
                                            </div>
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="learning_mode[]" value="TV" <?= in_array('TV', $learningMode) ? 'checked' : '' ?>> 
                                                Educational Television
                                            </div>
                                            <div class="form-check form-check-label mb-2">
                                                <input class="form-check-input" type="checkbox" name="learning_mode[]" value="ModularDigital" <?= in_array('ModularDigital', $learningMode) ? 'checked' : '' ?>> 
                                                Modular (Digital)
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="stat-icon warning">
                                            <i class="fa-solid fa-users"></i>
                                        </div>
                                        <h4 class="mb-0 text-warning">Additional Information</h4>
                                    </div>
                                    <p class="text-muted">Community and social information</p>

                                    <!-- Indigenous Peoples -->
                                    <div class="mb-4">
                                        <label class="h6 mb-3 text-gray-700">
                                            <i class="fa-solid fa-hands-holding-child me-2"></i>Belonging to any Indigenous Peoples (IP) Community?
                                        </label>
                                        <?php
                                        $isIp = $studentInfo["indigenous_people"] ?? '';
                                        $ipYesChecked = (!empty($isIp) && $isIp !== 'No') ? 'checked' : '';
                                        $ipNoChecked = (empty($isIp) || $isIp === 'No') ? 'checked' : '';
                                        ?>
                                        <div class="d-flex align-items-center gap-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="is_ip" value="Yes" id="ip_yes" <?= $ipYesChecked ?>> 
                                                <label class="form-check-label" for="ip_yes">Yes</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="is_ip" value="No" id="ip_no" <?= $ipNoChecked ?>> 
                                                <label class="form-check-label" for="ip_no">No</label>
                                            </div>
                                        </div>
                                        <div class="form-floating">
                                            <input type="text" name="ip_specify" placeholder="If Yes, please specify" 
                                                   value="<?= ($ipYesChecked && $isIp !== 'Yes') ? htmlspecialchars($isIp) : '' ?>" 
                                                   class="form-control border-0 bg-light">
                                            <label for="ip_specify" class="text-muted">
                                                <i class="fa-solid fa-circle-info me-2"></i>If Yes, please specify
                                            </label>
                                        </div>
                                    </div>

                                    <!-- 4Ps -->
                                    <div class="mb-4">
                                        <label class="h6 mb-3 text-gray-700">
                                            <i class="fa-solid fa-hand-holding-heart me-2"></i>Is your family a beneficiary of 4Ps?
                                        </label>
                                        <?php
                                        $is4ps = $studentInfo["fourPs"] ?? '';
                                        $fourPsYesChecked = (!empty($is4ps) && $is4ps !== 'No') ? 'checked' : '';
                                        $fourPsNoChecked = (empty($is4ps) || $is4ps === 'No') ? 'checked' : '';
                                        ?>
                                        <div class="d-flex align-items-center gap-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="is_4ps" value="Yes" id="4ps_yes" <?= $fourPsYesChecked ?>> 
                                                <label class="form-check-label" for="4ps_yes">Yes</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="is_4ps" value="No" id="4ps_no" <?= $fourPsNoChecked ?>> 
                                                <label class="form-check-label" for="4ps_no">No</label>
                                            </div>
                                        </div>
                                        <div class="form-floating">
                                            <input type="text" name="household_id" placeholder="If Yes, write the 4Ps Household ID Number" 
                                                   value="<?= ($fourPsYesChecked && $is4ps !== 'Yes') ? htmlspecialchars($is4ps) : '' ?>" 
                                                   class="form-control border-0 bg-light">
                                            <label for="household_id" class="text-muted">
                                                <i class="fa-solid fa-id-card me-2"></i>4Ps Household ID Number (if applicable)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <?php if($studentInfo["enrolment_status"] == 'active'): ?>
                                <div class="alert alert-info">
                                    <i class="fa-solid fa-info-circle me-2"></i>This enrolment is already active and cannot be modified.
                                </div>
                            <?php else: ?>
                                <div class="row mt-5 pt-4 border-top">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-end gap-3">
                                            <a href="index.php?page=contents/enrolment" class="btn btn-outline-secondary px-4">
                                                <i class="fa-solid fa-times me-2"></i>Cancel
                                            </a>
                                            <button type="submit" class="btn btn-gradient-danger px-5">
                                                <i class="fa-solid fa-floppy-disk me-2"></i>Update Enrolment
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="text-center text-muted">
                    <small class="last-updated">Last updated: <?= date('F j, Y, g:i a') ?></small>
                    <p class="mt-2 mb-0">Student Enrolment System • School Management System</p>
                </div>
            </div>
        </div>
        
    </section>
</main>

<style>
    /* Modal Gradient Background */
    .bg-gradient-danger {
        background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%) !important;
    }
    
    .btn-gradient-danger {
        background: linear-gradient(135deg, #dc3545, #a71d2a);
        border: none;
        color: white;
        padding: 12px 35px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-gradient-danger:hover {
        background: linear-gradient(135deg, #c82333, #8a1c2a);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        color: white;
    }
    
    /* Form Styling */
    .form-control.bg-light, .form-select.bg-light {
        background-color: #f8f9fa !important;
        border: none;
        border-radius: 10px;
        padding: 1rem 0.75rem;
        transition: all 0.3s ease;
    }
    
    .form-control.bg-light:focus, .form-select.bg-light:focus {
        background-color: #fff !important;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1) !important;
        border-color: #dc3545 !important;
    }
    
    /* Card Styling */
    .card {
        border-radius: 15px;
        overflow: hidden;
    }
    
    /* Stat Icon Styling */
    .stat-icon {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }
    
    .stat-icon.danger { background: linear-gradient(135deg, #dc3545, #a71d2a); }
    .stat-icon.warning { background: linear-gradient(135deg, #ffc107, #e0a800); }
    .stat-icon.info { background: linear-gradient(135deg, #0dcaf0, #0aa2c0); }
    .stat-icon.success { background: linear-gradient(135deg, #198754, #0f5132); }
    .stat-icon.purple { background: linear-gradient(135deg, #6f42c1, #4e2a8c); }
    
    /* Form Floating Labels */
    .form-floating>.form-control:focus~label,
    .form-floating>.form-control:not(:placeholder-shown)~label,
    .form-floating>.form-select:focus~label,
    .form-floating>.form-select:not(:placeholder-shown)~label {
        color: #dc3545;
        font-weight: 500;
    }
    
    /* Button Hover Effects */
    .btn-outline-secondary:hover {
        background-color: #dc3545;
        border-color: #dc3545;
        color: white;
    }
    
    /* Checkbox and Radio Styling */
    .form-check-input:checked {
        background-color: #dc3545;
        border-color: #dc3545;
    }
    
    .form-check-input:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    }
    
    /* Animation */
    .animate-card {
        animation: fadeInUp 0.6s ease-out;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Custom Scrollbar */
    section::-webkit-scrollbar {
        width: 8px;
    }
    
    section::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    section::-webkit-scrollbar-thumb {
        background: #dc3545;
        border-radius: 10px;
    }
    
    section::-webkit-scrollbar-thumb:hover {
        background: #c82333;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to calculate age from birthdate
    function calculateAge(birthdate) {
        const today = new Date();
        const birthDate = new Date(birthdate);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age;
    }

    // Calculate age when birthdate changes
    document.getElementById('birthdate').addEventListener('change', function() {
        const age = calculateAge(this.value);
        document.getElementById('age').value = age;
    });

    // Initialize age on page load
    const birthdate = document.getElementById('birthdate').value;
    if (birthdate) {
        const age = calculateAge(birthdate);
        document.getElementById('age').value = age;
    }

    // Form submission handling
    const enrolmentForm = document.getElementById('stduentEnrolment-form');
    if (enrolmentForm) {
        enrolmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate required fields
            const lrn = document.getElementById('lrn');
            if (!lrn.value.match(/^\d{12}$/)) {
                showAlert('Please enter a valid 12-digit LRN', 'error');
                lrn.focus();
                return;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Updating...';
            submitBtn.disabled = true;
            
            // Simulate form submission (replace with actual AJAX call)
            setTimeout(() => {
                // In real implementation, use fetch or XMLHttpRequest
                // const formData = new FormData(this);
                // fetch('update_enrolment.php', {
                //     method: 'POST',
                //     body: formData
                // })
                // .then(response => response.json())
                // .then(data => {
                //     if(data.success) {
                //         showAlert('Enrolment updated successfully!', 'success');
                //         setTimeout(() => {
                //             window.location.href = 'index.php?page=contents/enrolment';
                //         }, 1500);
                //     } else {
                //         showAlert('Error: ' + data.message, 'error');
                //         submitBtn.innerHTML = originalText;
                //         submitBtn.disabled = false;
                //     }
                // })
                // .catch(error => {
                //     showAlert('Network error. Please try again.', 'error');
                //     submitBtn.innerHTML = originalText;
                //     submitBtn.disabled = false;
                // });
                
                // For demo purposes - show success
                showAlert('Enrolment updated successfully!', 'success');
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 1500);
            }, 1500);
        });
    }
    
    // Show alert function
    function showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 10px;
            border: none;
        `;
        
        const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle';
        alertDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-${icon} me-3 fs-5 text-${type === 'error' ? 'danger' : type}"></i>
                <div class="flex-grow-1">${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
    
    // Update time
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        const lastUpdated = document.querySelector('.last-updated');
        if (lastUpdated) {
            lastUpdated.textContent = `Last updated: ${timeString}`;
        }
    }
    
    // Update time every minute
    setInterval(updateTime, 60000);
    
    // Initialize time on load
    updateTime();
});
</script>