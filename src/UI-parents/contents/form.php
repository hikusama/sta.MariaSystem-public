<?php
if (isset($_GET['student_id'])) {
    $learner_id = $_GET['student_id'];
}
$query = "SELECT * FROM student WHERE student_id = '$learner_id'";
$stmt = $pdo->prepare($query);
$stmt->execute();
$studentInfo = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<style>
    #enrollforms{
        max-height: 80vh !important;
        overflow-y: auto;
        overflow-x: hidden !important;
    }
</style>


<div class="modal-header bg-danger " style="border-bottom: 1px solid #ddd;">
    <div class="">
            <button class="btn text-white btn-success btn-sm m-0 " id="enrollBtn_parent" type="button">Back</button>
        </div>
    <!-- <h5 class="modal-title text-white">BASIC EDUCATION ENROLLMENT FORM</h5> -->
    <div class="m-0"><!-- <button type="button" class="btn m-0 btn-sm btn-danger" id="printSectionBtn">Print</button> --></div>
        
    <div class="d-flex  float-end gap-5">
        <h6 class="modal-title text-white">
            Status : <span id="enrollmentStatus" class="status_pending">Pending</span>
        </h6>
        
    </div>

</div>


<div id="enrollforms">
    <form id="childForm" method="POST" enctype="multipart/form-data">
        <input type="hidden" value="<?php echo $learner_id; ?>" data-id="<?php echo $learner_id; ?>" name="learnerIdInput" id="learnerIdInput">
        <div class="modal-body d-flex col-md-12 col-12 flex-wrap align-items-satrt justify-content-between gap-2">

          
            <!-- <div class="d-flex flex-column col-md-2 col-11 ">
                <label>PSA birth Certificate</label>
                <input type="text" name="psa" id="psa" placeholder="PSA birth . (if available)" class="form-control">
            </div> -->
            <div class="d-flex flex-column col-md-3 col-11 ">
                <label class="m-0 mt-1">Last name</label>
                <input type="text" id="lname" name="lname" placeholder="Last name" class="form-control">
            </div>
            <div class="d-flex flex-column col-md-3 col-11 ">
                <label class="m-0 mt-1">First name</label>
                <input type="text" id="fname" name="fname" placeholder="Last name" class="form-control">
            </div>
            <div class="d-flex flex-column col-md-3 col-11 ">
                <label class="m-0 mt-1">Middle name</label>
                <input type="text" id="mname" name="mname" placeholder="Last name" class="form-control">
            </div>
            <div class="d-flex flex-column col-md-2 col-11">
                <label class="m-0 mt-1">name extention</label>
                <input type="text" id="suffix" name="suffix" placeholder="Last name" class="form-control">
            </div>
            <div class="d-flex flex-column col-md-3 col-11 ">
                <label>LRN:</label>
                <input type="text" name="lrn" id="lrn" placeholder="LRN (12 digits only)"
                    maxlength="12" class="form-control" pattern="\d{12}" required>
            </div>
            <div class="d-flex flex-column col-md-3 col-11">
                <label class="m-0 mt-1">Birth date</label>
                <input type="date" name="birthdate" id="birthdate" placeholder="Birth date" class="form-control">
            </div>
            <div class="d-flex flex-column col-md-3 col-11">
                <label class="m-0 mt-1">Age</label>
                <input type="text" name="age" id="age" placeholder="Age" readonly class="form-control">
            </div>
            <div class="d-flex flex-column col-md-2 col-11">
                <label class="m-0 mt-1">Sex</label>
                <select name="gender" id="gender" class="form-select">
                    <option value="">Select Gender</option>
                    <option value="Male">MALE</option>
                    <option value="Female">FEMALE</option>
                </select>
            </div>
            <div class="d-flex flex-column col-md-3 col-11">
                <label class="m-0 mt-1">Birth Place</label>
                <input type="text" name="birth_place" id="birth_place" placeholder="Birth Place (city/Muntinlupa)" class="form-control">
            </div>
            <div class="d-flex flex-column col-md-3 col-11">
                <label class="m-0 mt-1">Religion</label>
                <select name="religious" id="religious" class="form-select">
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
                </select>
            </div>

            <div class="d-flex flex-column col-md-4 col-11 ms-2">
                <label class="m-0 mt-1">Mother Tongue</label>
                <select name="tongue" id="tongue" class="form-select">
                    <option value="">Select Mother Tongue</option>
                    <option value="Tagalog">Tagalog</option>
                    <option value="Cebuano">Cebuano</option>
                    <option value="Ilocano">Ilocano</option>
                    <option value="Hiligaynon">Hiligaynon</option>
                    <option value="Bicolano">Bicolano</option>
                    <option value="Kapampangan">Kapampangan</option>
                    <option value="Pangasinan">Pangasinan</option>
                    <option value="Waray">Waray</option>
                    <option value="Maranao">Maranao</option>
                    <option value="Tausug">Tausug</option>
                    <option value="Others">Others</option>
                </select>
            </div>

            <h5 class="mt-4">Current Address</h5>
            <div class="d-flex flex-wrap col-md-12 mb-3">
                <div class="col-md-3 col-11 pe-2">
                    <label>House No.</label>
                    <input type="text" name="current_house_no" id="current_house_no" class="form-control">
                </div>
                <div class="col-md-3 col-11 pe-2">
                    <label>Sitio/Street</label>
                    <input type="text" name="current_street" id="current_street" class="form-control">
                </div>
                <div class="col-md-3 col-11 pe-2">
                    <label>Barangay</label>
                    <input type="text" name="current_barangay" id="current_barangay" class="form-control">
                </div>
                <div class="col-md-3 col-11 pe-2">
                    <label>Municipality/City</label>
                    <input type="text" name="current_city" id="current_city" class="form-control">
                </div>
                <div class="col-md-3 col-11 pe-2 mt-2">
                    <label>Province</label>
                    <input type="text" name="current_province" id="current_province" class="form-control">
                </div>
                <div class="col-md-3 col-11 pe-2 mt-2">
                    <label>Country</label>
                    <input type="text" name="current_country" id="current_country" class="form-control" value="Philippines">
                </div>
                <div class="col-md-3 col-11 pe-2 mt-2">
                    <label>Zip Code</label>
                    <input type="text" name="current_zip" id="current_zip" class="form-control">
                </div>
            </div>


    </form>
</div>

<form id="addition-info" method="POST">
    <input type="hidden" value="<?php echo $learner_id; ?>" data-id="<?php echo $learner_id; ?>" name="learnerIdInput" id="learnerIdInput">
    <div class="row mb-4">
        <label class="fs-5 mb-2">With diagnosis from Licensed Medical Specialist</label>

        <!-- First Column -->
        <div class="col-md-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="diagnosis[]" value="ADHD" id="diagnosisADHD">
                <label class="form-check-label" for="diagnosisADHD">ADHD</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Autism" id="diagnosisAutism">
                <label class="form-check-label" for="diagnosisAutism">Autism Spectrum Disorder</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Behavior" id="diagnosisBehavior">
                <label class="form-check-label" for="diagnosisBehavior">Emotional/Behavioral Disorder</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Hearing" id="diagnosisHearing">
                <label class="form-check-label" for="diagnosisHearing">Hearing Impairment</label>
            </div>
        </div>

        <!-- Second Column -->
        <div class="col-md-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Learning" id="diagnosisLearning">
                <label class="form-check-label" for="diagnosisLearning">Learning Disability</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Orthopedic" id="diagnosisOrthopedic">
                <label class="form-check-label" for="diagnosisOrthopedic">Orthopedic/Physical Handicap</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Speech" id="diagnosisSpeech">
                <label class="form-check-label" for="diagnosisSpeech">Speech/Language Disorder</label>
            </div>
        </div>

        <!-- Third Column -->
        <div class="col-md-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Intellectual" id="diagnosisIntellectual">
                <label class="form-check-label" for="diagnosisIntellectual">Intellectual Disability</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Cancer" id="diagnosisCancer">
                <label class="form-check-label" for="diagnosisCancer">Non-Cancer</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="diagnosis[]" value="Health" id="diagnosisHealth">
                <label class="form-check-label" for="diagnosisHealth">Chronic Health Problem</label>
            </div>
        </div>
    </div>


    <!-- With Manifestations -->
    <div class="mt-3 col-md-12">
        <label class="w-100 fs-5">a.2 With Manifestations</label>
        <div class="row">
            <div class="col-md-4">
                <div class="form-check form-check-label"><input class="form-check-input " type="checkbox" name="manifestations[]"
                        value="Difficulty in Applying Knowledge"> Difficulty in Applying Knowledge</div>
                <div class="form-check form-check-label"><input class="form-check-input " type="checkbox" name="manifestations[]"
                        value="Difficulty in Communicating"> Difficulty in Communicating</div>
                <div class="form-check form-check-label"><input class="form-check-input " type="checkbox" name="manifestations[]"
                        value="Difficulty in Interpersonal Behavior"> Difficulty in Interpersonal Behavior</div>
                <div class="form-check form-check-label"><input class="form-check-input " type="checkbox" name="manifestations[]"
                        value="Difficulty in Behavior"> Difficulty in Behavior</div>
            </div>
            <div class="col-md-4">
                <div class="form-check form-check-label"><input class="form-check-input " type="checkbox" name="manifestations[]"
                        value="Difficulty in Mobility (Walking, Climbing)"> Difficulty in Mobility (Walking, Climbing)</div>
                <div class="form-check form-check-label"><input class="form-check-input " type="checkbox" name="manifestations[]"
                        value="Difficulty in Performing Adaptive Self-Care"> Difficulty in Performing Adaptive Self-Care</div>
            </div>
            <div class="col-md-4">
                <div class="form-check form-check-label"><input class="form-check-input " type="checkbox" name="manifestations[]"
                        value="Difficulty in Remembering, Concentrating, Playing Attention"> Difficulty in Remembering, Concentrating, Playing Attention</div>
                <div class="form-check form-check-label"><input class="form-check-input " type="checkbox" name="manifestations[]"
                        value="Difficulty in Seeing"> Difficulty in Seeing</div>
                <div class="form-check form-check-label"><input class="form-check-input " type="checkbox" name="manifestations[]"
                        value="Difficulty in Hearing"> Difficulty in Hearing</div>
            </div>
        </div>
    </div>

    <!-- PWD ID -->
    <div class="mt-3 col-md-12">
        <label class="w-100 fs-5">b. Does the learner have a PWD ID?</label><br>
        <input type="radio" name="has_pwd_id" value="yes" id="pwd_yes">
        <label for="pwd_yes">Yes</label>

        <input type="radio" name="has_pwd_id" value="no" id="pwd_no" class="ms-3">
        <label for="pwd_no">No</label>
        <input type="text" name="has_pwd_id_specific" placeholder="If Yes, please specify"
            class="form-control w-90 ms-3">
    </div>



    <!-- BALIK-ARAL -->
    <h5 class="mt-3 col-md-12">6. For Returning Learner (Balik-Aral)</h5>
    <div class="row col-md-12 col-12">
        <div class="col-md-3">
            <label>Last Grade Level Completed</label>
            <input type="text" name="last_grade_level" class="form-control">
        </div>
        <div class="col-md-3">
            <label>Last School Year Completed</label>
            <input type="text" name="last_sy" class="form-control">
        </div>
        <div class="col-md-3">
            <label>Last School Attended</label>
            <input type="text" name="last_school" class="form-control">
        </div>
    </div>


    <!-- Distance Learning -->
    <h5 class="mt-3 col-md-12">7. Distance Learning Preference</h5>
    <label class="w-100 fs-5">Preferred Learning Mode</label>
    <div class="row col-md-12">
        <div class="col-md-3">
            <div class="form-check form-check-label"><input class="form-check-input" type="checkbox" name="learning_mode[]"
                    value="Blended"> Blended (Combination)</div>
            <div class="form-check form-check-label"><input class="form-check-input" type="checkbox" name="learning_mode[]"
                    value="Homeschooling"> Homeschooling</div>
            <div class="form-check form-check-label"><input class="form-check-input" type="checkbox" name="learning_mode[]"
                    value="ModularPrint"> Modular (Print)</div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-check-label"><input class="form-check-input" type="checkbox" name="learning_mode[]"
                    value="Radio"> Radio-Based Instruction</div>
            <div class="form-check form-check-label"><input class="form-check-input" type="checkbox" name="learning_mode[]"
                    value="TV"> Educational Television</div>
            <div class="form-check form-check-label"><input class="form-check-input" type="checkbox" name="learning_mode[]"
                    value="ModularDigital"> Modular (Digital)</div>
        </div>
    </div>
    <div class="d-flex flex-column col-md-12 mt-3">
        <label class="fs-6">Belonging to any Indigenous Peoples (IP) Community / Indigenous Cultural Community?</label>
        <div class="d-flex align-items-center gap-2">
            <input type="radio" name="is_ip" value="Yes" id="ip_yes"> <label for="ip_yes" class="me-2">Yes</label>
            <input type="radio" name="is_ip" value="No" id="ip_no"> <label for="ip_no">No</label>
            <input type="text" name="ip_specify" placeholder="If Yes, please specify"
                class="form-control w-90 ms-3">
        </div>
    </div>

    <div class="d-flex flex-column col-md-12 mt-2">
        <label class="fs-6">Is your family a beneficiary of 4Ps?</label>
        <div class="d-flex align-items-center gap-2">
            <input type="radio" name="is_4ps" value="Yes" id="4ps_yes"> <label for="4ps_yes"
                class="me-2">Yes</label>
            <input type="radio" name="is_4ps" value="No" id="4ps_no"> <label for="4ps_no">No</label>
            <input type="text" name="household_id" placeholder="If Yes, write the 4Ps Household ID Number"
                class="form-control w-90 ms-3">
        </div>
    </div>

</form>


<div id="printArea" hidden> <!-- Function of this printbtn on teacher js -->
    <h2>This section will be printed only.</h2>
</div>