<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="mx-2">
        <h4><i class="fa-solid fa-users-gear me-2"></i>Learners Management</h4>
    </div>
</div>
<div class="row mb-3  justify-content-between">
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
                <form class="row g-3" id="studentAcc-form" method="post">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Student LRN <span class="text-danger">*</span></label>
                            <input required type="text" class="form-control" placeholder="must be 12 digit lrn...." name="lrn">
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
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Birth place</label>
                            <input type="text" name="birthplace" class="form-control" placeholder="Birth PLace">
                            </select>
                        </div>
                        <div class="col-md-8 mt-2">
                            <div class="alert alert-light"
                                style="border: 1px solid #d1ecf1; background-color: #e8f4fd; color: #0c5460;">
                                <i class="fa fa-info-circle me-2"></i>
                                <strong>Note:</strong> Your enrollment request will be verified by the school
                                administration.
                                You will receive a notification once the verification is complete.
                            </div>
                        </div>
                        <div class="col-md-4 d-flex flex-column justify-content-center align-items-center">
                            <img src="../../assets/image/users.png" class="w-50 h-auto">
                            <input type="file" class="form-control" name="student_profile">
                        </div>
                    </div>

                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-primary px-5">
                            Create Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>