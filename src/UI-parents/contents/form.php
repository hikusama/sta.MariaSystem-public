<?php
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
<style>
#enrollforms {
    max-height: 80vh !important;
    overflow-y: auto;
    overflow-x: hidden !important;
}
</style>

<div class="modal-header bg-danger " style="border-bottom: 1px solid #ddd;">
    <div class="">
        <a href="index.php?page=contents/learners"><button class="btn text-white btn-success btn-sm m-0 ">Back</button></a>
    </div>
    <div class="d-flex float-end gap-5">
        <h6 class="modal-title text-white">
            Status : <span id="enrollmentStatus" class="status_pending">Pending</span>
        </h6>
    </div>
</div>

<div id="enrollforms">
    <form id="stduentEnrolment-form" method="POST">
        <input type="hidden" name="student_id" value="<?php echo $learner_id; ?>" name="learnerIdInput" id="learnerIdInput">
        <div class="modal-body d-flex col-md-12 col-12 flex-wrap align-items-satrt justify-content-between gap-2">

            <!-- Personal Information -->
            <div class="d-flex flex-column col-md-3 col-11 ">
                <label class="m-0 mt-1">First name</label>
                <input type="text" value="<?= htmlspecialchars($studentInfo["fname"] ?? '') ?>" id="fname" name="fname" placeholder="First name" class="form-control">
            </div>
            <div class="d-flex flex-column col-md-3 col-11 ">
                <label class="m-0 mt-1">Middle name</label>
                <input type="text" value="<?= htmlspecialchars($studentInfo["mname"] ?? '') ?>" id="mname" name="mname" placeholder="Middle name" class="form-control">
            </div>
            <div class="d-flex flex-column col-md-3 col-11 ">
                <label class="m-0 mt-1">Last name</label>
                <input type="text" id="lname" value="<?= htmlspecialchars($studentInfo["lname"] ?? '') ?>" name="lname" placeholder="Last name" class="form-control">
            </div>
            <div class="d-flex flex-column col-md-2 col-11">
                <label class="m-0 mt-1">Name extension</label>
                <input type="text" id="suffix" value="<?= htmlspecialchars($studentInfo["suffix"] ?? '') ?>" name="suffix" placeholder="Name extension" class="form-control">
            </div>
            <div class="d-flex flex-column col-md-3 col-11 ">
                <label>LRN:</label>
                <input type="text" value="<?= htmlspecialchars($studentInfo["lrn"] ?? '') ?>" name="lrn" id="lrn" placeholder="LRN (12 digits only)" maxlength="12" class="form-control" pattern="\d{12}" required>
            </div>
            <div class="d-flex flex-column col-md-3 col-11">
                <label class="m-0 mt-1">Birth date</label>
                <input type="date" value="<?= htmlspecialchars($studentInfo["birthdate"] ?? '') ?>" name="birthdate" id="birthdate" placeholder="Birth date" class="form-control">
            </div>
            <div class="d-flex flex-column col-md-3 col-11">
                <label class="m-0 mt-1">Age</label>
                <input type="text" name="age" id="age" placeholder="Age" readonly class="form-control" value="<?= htmlspecialchars($studentInfo["age"] ?? '') ?>">
            </div>
            <div class="d-flex flex-column col-md-2 col-11">
                <label class="m-0 mt-1">Sex</label>
                <select name="gender" id="gender" class="form-select">
                    <option value="">Select Gender</option>
                    <option value="Male" <?= ($studentInfo["sex"] ?? '') == 'Male' ? 'selected' : '' ?>>MALE</option>
                    <option value="Female" <?= ($studentInfo["sex"] ?? '') == 'Female' ? 'selected' : '' ?>>FEMALE</option>
                </select>
            </div>
            <div class="d-flex flex-column col-md-3 col-11">
                <label class="m-0 mt-1">Birth Place</label>
                <input type="text" value="<?= htmlspecialchars($studentInfo["birthplace"] ?? '') ?>" name="birth_place" id="birth_place" placeholder="Birth Place (city/Muntinlupa)" class="form-control">
            </div>
            <div class="d-flex flex-column col-md-3 col-11">
                <label class="m-0 mt-1">Religion</label>
                <select name="religious" id="religious" class="form-select">
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
            </div>

            <div class="d-flex flex-column col-md-4 col-11 ms-2">
                <label class="m-0 mt-1">Mother Tongue</label>
                <select name="tongue" id="tongue" class="form-select">
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
            </div>

            <!-- Current Address -->
            <h5 class="mt-4">Current Address</h5>
            <div class="d-flex flex-wrap col-md-12 mb-3">
                <div class="col-md-3 col-11 pe-2">
                    <label>House No.</label>
                    <input type="text" name="current_house_no" id="current_house_no" value="<?= htmlspecialchars($studentInfo["house_no"] ?? '') ?>" class="form-control">
                </div>
                <div class="col-md-3 col-11 pe-2">
                    <label>Sitio/Street</label>
                    <input type="text" name="current_street" id="current_street" value="<?= htmlspecialchars($studentInfo["street"] ?? '') ?>" class="form-control">
                </div>
                <div class="col-md-3 col-11 pe-2">
                    <label>Barangay</label>
                    <input type="text" name="current_barangay" id="current_barangay" value="<?= htmlspecialchars($studentInfo["barnagay"] ?? '') ?>" class="form-control">
                </div>
                <div class="col-md-3 col-11 pe-2">
                    <label>Municipality/City</label>
                    <input type="text" name="current_city" id="current_city" value="<?= htmlspecialchars($studentInfo["city"] ?? '') ?>" class="form-control">
                </div>
                <div class="col-md-3 col-11 pe-2 mt-2">
                    <label>Province</label>
                    <input type="text" name="current_province" id="current_province" value="<?= htmlspecialchars($studentInfo["province"] ?? '') ?>" class="form-control">
                </div>
                <div class="col-md-3 col-11 pe-2 mt-2">
                    <label>Country</label>
                    <input type="text" name="current_country" id="current_country" value="<?= htmlspecialchars($studentInfo["country"] ?? 'Philippines') ?>" class="form-control">
                </div>
                <div class="col-md-3 col-11 pe-2 mt-2">
                    <label>Zip Code</label>
                    <input type="text" name="current_zip" id="current_zip" value="<?= htmlspecialchars($studentInfo["zip_code"] ?? '') ?>" class="form-control">
                </div>
            </div>

            <!-- Diagnosis Checkboxes -->
            <div class="row mb-4">
                <label class="fs-5 mb-2">With diagnosis from Licensed Medical Specialist</label>
                <?php
                $diagnosis = isset($studentInfo["diagnosis"]) ? explode(',', $studentInfo["diagnosis"]) : [];
                ?>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="diagnosis[]" value="ADHD" id="diagnosisADHD" <?= in_array('ADHD', $diagnosis) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="diagnosisADHD">ADHD</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Autism" id="diagnosisAutism" <?= in_array('Autism', $diagnosis) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="diagnosisAutism">Autism Spectrum Disorder</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Behavior" id="diagnosisBehavior" <?= in_array('Behavior', $diagnosis) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="diagnosisBehavior">Emotional/Behavioral Disorder</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Hearing" id="diagnosisHearing" <?= in_array('Hearing', $diagnosis) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="diagnosisHearing">Hearing Impairment</label>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Learning" id="diagnosisLearning" <?= in_array('Learning', $diagnosis) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="diagnosisLearning">Learning Disability</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Orthopedic" id="diagnosisOrthopedic" <?= in_array('Orthopedic', $diagnosis) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="diagnosisOrthopedic">Orthopedic/Physical Handicap</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Speech" id="diagnosisSpeech" <?= in_array('Speech', $diagnosis) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="diagnosisSpeech">Speech/Language Disorder</label>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Intellectual" id="diagnosisIntellectual" <?= in_array('Intellectual', $diagnosis) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="diagnosisIntellectual">Intellectual Disability</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Cancer" id="diagnosisCancer" <?= in_array('Cancer', $diagnosis) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="diagnosisCancer">Non-Cancer</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Health" id="diagnosisHealth" <?= in_array('Health', $diagnosis) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="diagnosisHealth">Chronic Health Problem</label>
                    </div>
                </div>
            </div>

            <!-- With Manifestations -->
            <div class="mt-3 col-md-12">
                <label class="w-100 fs-5">a.2 With Manifestations</label>
                <?php
                $manifestations = isset($studentInfo["manifestations"]) ? explode(',', $studentInfo["manifestations"]) : [];
                ?>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-check form-check-label">
                            <input class="form-check-input" type="checkbox" name="manifestations[]" value="Difficulty in Applying Knowledge" <?= in_array('Difficulty in Applying Knowledge', $manifestations) ? 'checked' : '' ?>>
                            Difficulty in Applying Knowledge
                        </div>
                        <div class="form-check form-check-label">
                            <input class="form-check-input" type="checkbox" name="manifestations[]" value="Difficulty in Communicating" <?= in_array('Difficulty in Communicating', $manifestations) ? 'checked' : '' ?>>
                            Difficulty in Communicating
                        </div>
                        <div class="form-check form-check-label">
                            <input class="form-check-input" type="checkbox" name="manifestations[]" value="Difficulty in Interpersonal Behavior" <?= in_array('Difficulty in Interpersonal Behavior', $manifestations) ? 'checked' : '' ?>>
                            Difficulty in Interpersonal Behavior
                        </div>
                        <div class="form-check form-check-label">
                            <input class="form-check-input" type="checkbox" name="manifestations[]" value="Difficulty in Behavior" <?= in_array('Difficulty in Behavior', $manifestations) ? 'checked' : '' ?>>
                            Difficulty in Behavior
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-check-label">
                            <input class="form-check-input" type="checkbox" name="manifestations[]" value="Difficulty in Mobility (Walking, Climbing)" <?= in_array('Difficulty in Mobility (Walking, Climbing)', $manifestations) ? 'checked' : '' ?>>
                            Difficulty in Mobility (Walking, Climbing)
                        </div>
                        <div class="form-check form-check-label">
                            <input class="form-check-input" type="checkbox" name="manifestations[]" value="Difficulty in Performing Adaptive Self-Care" <?= in_array('Difficulty in Performing Adaptive Self-Care', $manifestations) ? 'checked' : '' ?>>
                            Difficulty in Performing Adaptive Self-Care
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-check-label">
                            <input class="form-check-input" type="checkbox" name="manifestations[]" value="Difficulty in Remembering, Concentrating, Playing Attention" <?= in_array('Difficulty in Remembering, Concentrating, Playing Attention', $manifestations) ? 'checked' : '' ?>>
                            Difficulty in Remembering, Concentrating, Playing Attention
                        </div>
                        <div class="form-check form-check-label">
                            <input class="form-check-input" type="checkbox" name="manifestations[]" value="Difficulty in Seeing" <?= in_array('Difficulty in Seeing', $manifestations) ? 'checked' : '' ?>>
                            Difficulty in Seeing
                        </div>
                        <div class="form-check form-check-label">
                            <input class="form-check-input" type="checkbox" name="manifestations[]" value="Difficulty in Hearing" <?= in_array('Difficulty in Hearing', $manifestations) ? 'checked' : '' ?>>
                            Difficulty in Hearing
                        </div>
                    </div>
                </div>
            </div>

            <!-- PWD ID -->
            <div class="mt-3 col-md-12">
                <label class="w-100 fs-5">b. Does the learner have a PWD ID?</label><br>
                <?php
                $hasPwdId = $studentInfo["pwd_id"] ?? '';
                $pwdYesChecked = (!empty($hasPwdId) && $hasPwdId !== 'No') ? 'checked' : '';
                $pwdNoChecked = (empty($hasPwdId) || $hasPwdId === 'No') ? 'checked' : '';
                ?>
                <input type="radio" name="has_pwd_id" value="yes" id="pwd_yes" <?= $pwdYesChecked ?>>
                <label for="pwd_yes">Yes</label>

                <input type="radio" name="has_pwd_id" value="no" id="pwd_no" class="ms-3" <?= $pwdNoChecked ?>>
                <label for="pwd_no">No</label>
                <input type="text" name="has_pwd_id_specific" placeholder="If Yes, please specify" value="<?= ($pwdYesChecked && $hasPwdId !== 'Yes') ? htmlspecialchars($hasPwdId) : '' ?>" class="form-control w-90 ms-3">
            </div>

            <!-- BALIK-ARAL -->
            <h5 class="mt-3 col-md-12">6. For Returning Learner (Balik-Aral)</h5>
            <div class="row col-md-12 col-12">
                <div class="col-md-3">
                    <label>Last Grade Level Completed</label>
                    <input type="text" name="last_grade_level" value="<?= htmlspecialchars($studentInfo["balik_aral"] ?? '') ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Last School Year Completed</label>
                    <input type="text" name="last_sy" value="<?= htmlspecialchars($studentInfo["last_sy"] ?? '') ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Last School Attended</label>
                    <input type="text" name="last_school" value="<?= htmlspecialchars($studentInfo["last_school"] ?? '') ?>" class="form-control">
                </div>
            </div>

            <!-- Distance Learning -->
            <h5 class="mt-3 col-md-12">7. Distance Learning Preference</h5>
            <label class="w-100 fs-5">Preferred Learning Mode</label>
            <?php
            $learningMode = isset($studentInfo["learning_mode"]) ? explode(',', $studentInfo["learning_mode"]) : [];
            ?>
            <div class="row col-md-12">
                <div class="col-md-3">
                    <div class="form-check form-check-label">
                        <input class="form-check-input" type="checkbox" name="learning_mode[]" value="Blended" <?= in_array('Blended', $learningMode) ? 'checked' : '' ?>> Blended (Combination)
                    </div>
                    <div class="form-check form-check-label">
                        <input class="form-check-input" type="checkbox" name="learning_mode[]" value="Homeschooling" <?= in_array('Homeschooling', $learningMode) ? 'checked' : '' ?>> Homeschooling
                    </div>
                    <div class="form-check form-check-label">
                        <input class="form-check-input" type="checkbox" name="learning_mode[]" value="ModularPrint" <?= in_array('ModularPrint', $learningMode) ? 'checked' : '' ?>> Modular (Print)
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-check-label">
                        <input class="form-check-input" type="checkbox" name="learning_mode[]" value="Radio" <?= in_array('Radio', $learningMode) ? 'checked' : '' ?>> Radio-Based Instruction
                    </div>
                    <div class="form-check form-check-label">
                        <input class="form-check-input" type="checkbox" name="learning_mode[]" value="TV" <?= in_array('TV', $learningMode) ? 'checked' : '' ?>> Educational Television
                    </div>
                    <div class="form-check form-check-label">
                        <input class="form-check-input" type="checkbox" name="learning_mode[]" value="ModularDigital" <?= in_array('ModularDigital', $learningMode) ? 'checked' : '' ?>> Modular (Digital)
                    </div>
                </div>
            </div>

            <!-- Indigenous Peoples -->
            <div class="d-flex flex-column col-md-12 mt-3">
                <label class="fs-6">Belonging to any Indigenous Peoples (IP) Community / Indigenous Cultural Community?</label>
                <?php
                $isIp = $studentInfo["indigenous_people"] ?? '';
                $ipYesChecked = (!empty($isIp) && $isIp !== 'No') ? 'checked' : '';
                $ipNoChecked = (empty($isIp) || $isIp === 'No') ? 'checked' : '';
                ?>
                <div class="d-flex align-items-center gap-2">
                    <input type="radio" name="is_ip" value="Yes" id="ip_yes" <?= $ipYesChecked ?>> 
                    <label for="ip_yes" class="me-2">Yes</label>
                    <input type="radio" name="is_ip" value="No" id="ip_no" <?= $ipNoChecked ?>> 
                    <label for="ip_no">No</label>
                    <input type="text" name="ip_specify" placeholder="If Yes, please specify" value="<?= ($ipYesChecked && $isIp !== 'Yes') ? htmlspecialchars($isIp) : '' ?>" class="form-control w-90 ms-3">
                </div>
            </div>

            <!-- 4Ps -->
            <div class="d-flex flex-column col-md-12 mt-2">
                <label class="fs-6">Is your family a beneficiary of 4Ps?</label>
                <?php
                $is4ps = $studentInfo["fourPs"] ?? '';
                $fourPsYesChecked = (!empty($is4ps) && $is4ps !== 'No') ? 'checked' : '';
                $fourPsNoChecked = (empty($is4ps) || $is4ps === 'No') ? 'checked' : '';
                ?>
                <div class="d-flex align-items-center gap-2">
                    <input type="radio" name="is_4ps" value="Yes" id="4ps_yes" <?= $fourPsYesChecked ?>> 
                    <label for="4ps_yes" class="me-2">Yes</label>
                    <input type="radio" name="is_4ps" value="No" id="4ps_no" <?= $fourPsNoChecked ?>> 
                    <label for="4ps_no">No</label>
                    <input type="text" name="household_id" placeholder="If Yes, write the 4Ps Household ID Number" value="<?= ($fourPsYesChecked && $is4ps !== 'Yes') ? htmlspecialchars($is4ps) : '' ?>" class="form-control w-90 ms-3">
                </div>
            </div>
            <?php
                if($studentInfo["enrolment_status"] == 'Pending'){
                    echo '<div class="col-md-12 d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-danger px-5">Update</button>
                    </div>';
                }else{
            ?>
            <?php } ?>
        </div>
    </form>
</div>

<div id="printArea" hidden>
    <h2>This section will be printed only.</h2>
</div>

<script>
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
document.addEventListener('DOMContentLoaded', function() {
    const birthdate = document.getElementById('birthdate').value;
    if (birthdate) {
        const age = calculateAge(birthdate);
        document.getElementById('age').value = age;
    }
    
    // Highlight radio buttons based on their checked state
    const radioButtons = document.querySelectorAll('input[type="radio"]');
    radioButtons.forEach(radio => {
        if (radio.checked) {
            radio.parentElement.classList.add('text-primary', 'fw-bold');
        }
    });
    
    // Add event listeners to highlight selected radio buttons
    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            // Remove highlighting from all radios in the same group
            const groupName = this.name;
            document.querySelectorAll(`input[name="${groupName}"]`).forEach(r => {
                r.parentElement.classList.remove('text-primary', 'fw-bold');
            });
            
            // Add highlighting to the selected radio
            if (this.checked) {
                this.parentElement.classList.add('text-primary', 'fw-bold');
            }
        });
    });
});
</script>

<style>
/* Style for highlighted radio buttons */
input[type="radio"]:checked + label,
.form-check-input:checked ~ .form-check-label {
    color: #0d6efd !important;
    font-weight: bold !important;
}

/* Style for checked checkboxes */
.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>