$(document).ready(function () {


    $('html').css('scroll-behavior', 'smooth')
    $('#systemLogo').on('change', function (event) {
        const fileInput = event.target
        const preview = $('.preview')

        preview.empty()

        const files = fileInput.files
        for (const file of files) {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader()
                reader.onload = function (e) {
                    preview.attr('src', e.target.result)
                    $('input[name=system_logo]').attr('value', e.target.result)
                }
                reader.readAsDataURL(file)
            } else {
                const para = $('<p>').text(
                    `File ${file.name} is not a valid image file.`
                )
                preview.append(para)
            }
        }
    })

    $('body').on('click', '#logout', function (e) {
        e.preventDefault()
        const $this = $(this)
        $.ajax({
            url: base_url + 'authentication/action.php?action=logout',
            method: 'POST',
            dataType: 'json',
            beforeSend: function () {
                $this.text('Logging out...')
            },
            success: function (response) {
                if (response.status == 1) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href =
                            base_url + (response.redirect_url || 'index.php')
                    })
                } else {
                    showError(response.message)
                }
            },
            error: function () {
                console.error('AJAX error')
            }
        })
    })

    $(document).on("submit", "#Account-form", function (e) {
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        // Validate passwords match
        const password = $form.find('[name="password"]').val();
        const cpassword = $form.find('[name="cpassword"]').val();

        if (password !== cpassword) {
            Swal.fire({
                title: "Error",
                text: "Passwords do not match!",
                icon: "error",
                toast: true,
                position: "top-end",
                timer: 3000,
                showConfirmButton: false
            });
            $form.data("isSubmitted", false);
            return;
        }

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=Account_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload(); // Reload page on success
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Create Account');
            }
        });
    });
    $(document).on("submit", "#classroom-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        // Basic validation
        //   const classroomName = $form.find('[name="school_year_name"]').val();
        //   const classroomType = $form.find('[name="classroom_type"]').val().trim();

        //   if (!classroomName || !classroomType) {
        //       Swal.fire({
        //           title: "Error",
        //           text: "Please fill in all required fields",
        //           icon: "error",
        //           toast: true,
        //           position: "top-end",
        //           timer: 3000,
        //           showConfirmButton: false,
        //       });
        //       $form.data("isSubmitted", false);
        //       return;
        //   }

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=classroom_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Create Classroom'); // Fixed button text
            }
        });
    });
    $(document).on("submit", "#section-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        // Basic validation
        const section_name = $form.find('[name="section_name"]').val().trim();
        const grade_level = $form.find('[name="grade_level"]').val().trim();
        const section_status = $form.find('[name="section_status"]').val().trim();

        if (!section_name || !grade_level || !section_status) {
            Swal.fire({
                title: "Error",
                text: "Please fill in all required fields",
                icon: "error",
                toast: true,
                position: "top-end",
                timer: 3000,
                showConfirmButton: false,
            });
            $form.data("isSubmitted", false);
            return;
        }

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=section_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Create Classroom'); // Fixed button text
            }
        });
    });
    $(document).on("submit", "#sy-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        // Basic validation
        const schoolYear_name = $form.find('[name="schoolYear_name"]').val().trim();
        const status = $form.find('[name="status"]').val().trim();

        if (!schoolYear_name || !status) {
            Swal.fire({
                title: "Error",
                text: "Please fill in all required fields",
                icon: "error",
                toast: true,
                position: "top-end",
                timer: 3000,
                showConfirmButton: false,
            });
            $form.data("isSubmitted", false);
            return;
        }

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=schoolYear_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Create Classroom'); // Fixed button text
            }
        });
    });
    $(document).on("submit", "#subjects-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        // Basic validation
        const subject_name = $form.find('[name="subject_name"]').val().trim();
        const subject_code = $form.find('[name="subject_code"]').val().trim();
        const grade_level = $form.find('[name="grade_level"]').val().trim();
        const subject_units = $form.find('[name="subject_units"]').val().trim();
        const subjects_status = $form.find('[name="subjects_status"]').val().trim();

        if (!subject_name || !subject_code || !grade_level || !subject_units || !subjects_status) {
            Swal.fire({
                title: "Error",
                text: "Please fill in all required fields",
                icon: "error",
                toast: true,
                position: "top-end",
                timer: 3000,
                showConfirmButton: false,
            });
            $form.data("isSubmitted", false);
            return;
        }

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=subjects_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Create Classroom'); // Fixed button text
            }
        });
    });
    $(document).on("click", ".assign-teacher-btn", function () {
        const classroomID = $(this).data("id");
        $("#assgnTeacher").modal("show");
        $("#classroomIdInput").val(classroomID);
    });
    $(document).on("submit", "#assign-teacher-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        // Basic validation
        const classroom_id = $form.find('[name="classroom_id"]').val().trim();
        const section_id = $form.find('[name="section_id"]').val().trim();
        const grade_level = $form.find('[name="grade_level"]').val().trim();
        const teacher_name = $form.find('[name="teacher_name"]').val().trim();
        const schoolYear_id = $form.find('[name="schoolYear_id"]').val().trim();

        if (!classroom_id || !section_id ||
            !grade_level || !teacher_name || !schoolYear_id) {
            Swal.fire({
                title: "Error",
                text: "Please fill in all required fields",
                icon: "error",
                toast: true,
                position: "top-end",
                timer: 3000,
                showConfirmButton: false,
            });
            $form.data("isSubmitted", false);
            return;
        }

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=assignTeacher_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Create Classroom'); // Fixed button text
            }
        });
    });
    $(document).on("submit", "#studentAcc-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        // Basic validation
        const sex = $form.find('[name="sex"]').val().trim();
        const lrn = $form.find('[name="lrn"]').val().trim();
        const grade_level = $form.find('[name="grade_level"]').val().trim();
        const lastName = $form.find('[name="lastName"]').val().trim();
        const firstName = $form.find('[name="firstName"]').val().trim();
        const middleName = $form.find('[name="middleName"]').val().trim();
        const religion = $form.find('[name="religion"]').val().trim();
        const birthdate = $form.find('[name="birthdate"]').val().trim();

        if (!lrn ||
            !sex ||
            !grade_level ||
            !lastName || !firstName || !middleName
            || !religion || !birthdate) {
            Swal.fire({
                title: "Error",
                text: "Please fill in all required fields",
                icon: "error",
                toast: true,
                position: "top-end",
                timer: 3000,
                showConfirmButton: false,
            });
            $form.data("isSubmitted", false);
            return;
        }

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=studentAcc_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        // $form[0].reset(); // Reset form on success
                        // $('#createClassrooms').modal('hide'); // Close modal
                        // loadClassrooms(); // Refresh classroom list
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Create Account');// Fixed button text
            }
        });
    });
    $(document).on("submit", "#feedback-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);


        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=feedback_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        // $form[0].reset(); // Reset form on success
                        // $('#createClassrooms').modal('hide'); // Close modal
                        // loadClassrooms(); // Refresh classroom list
                        $form[0].reset();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Submited');// Fixed button text
            }
        });
    });
    $(document).on('click', '#open_enrolment', function () {
        const student_id = $(this).data('id');
        const student_name = $(this).data('name') || 'Student';

        $('#student_id').val(student_id);
        $('#display_student_id').text(student_id);

        // Update modal title with student info
        $('#AddNewAccountLabel').html('Approve Enrolment for Student #' + student_id);

        // Reset the form and subject selects
        $('#enrolment-form')[0].reset();
        $('#subjectSelectContainer').empty();

        // Show the modal
        $('#AddNewAccount').modal('show');
    });
    // Enrolment Forms =============================================
    $('#AddNewAccount').on('hidden.bs.modal', function () {
        $('#enrolment-form')[0].reset();
        $('#subjectSelectContainer').empty();
    });
    $(document).on("submit", "#enrolment-form", function (e) {
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        // Basic validation
        const section_name = $form.find('[name="section_name"]').val().trim();
        const adviser_id = $form.find('[name="adviser_id"]').val().trim();
        const schoolyear_id = $form.find('[name="schoolyear_id"]').val().trim();
        const grade_level = $form.find('[name="grade_level"]').val().trim();
        const student_id = $form.find('[name="student_id"]').val().trim();
        const subjects = $form.find('[name="subjects[]"]').map(function () {
            return $(this).val();
        }).get();

        // Check if any subjects were selected
        if (subjects.length === 0) {
            Swal.fire({
                title: "Error",
                text: "No subjects available for this grade level",
                icon: "error",
                toast: true,
                position: "top-end",
                timer: 3000,
                showConfirmButton: false,
            });
            $form.data("isSubmitted", false);
            return;
        }

        if (!section_name || !adviser_id || !schoolyear_id || !grade_level || !student_id) {
            console.log("\nsec:" + section_name + "\nadv:" + adviser_id + "\nsy:" + schoolyear_id + "\ngra:" + grade_level + "\nstud:" + student_id);

            Swal.fire({
                title: "Error",
                text: "Please fill in all required fields",
                icon: "error",
                toast: true,
                position: "top-end",
                timer: 3000,
                showConfirmButton: false,
            });
            $form.data("isSubmitted", false);
            return;
        }

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=enrolment_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        $form[0].reset();
                        $('#subjectListContainer').empty();
                        $('#AddNewAccount').modal('hide');
                        // Refresh the page to show updated enrolment status
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Approve Enrolment');
            }
        });
    });
    $(document).ready(function () {
        $('#adviserSelect').change(function () {
            const selectedOption = $(this).find('option:selected');
            const sectionName = selectedOption.data('section');
            $('#section_name').val(sectionName);
        });
    });
    $(document).on('click', '#rejectionBtn', function () {
        const studentID = $(this).data('id');
        $('#studentID').val(studentID);
        $('#rejectEnrolment').modal('show');
    });
    $(document).on('click', '#approvalBtn', function () {
        const studentID = $(this).data('id');
        $('#student_id').val(studentID);
        $('#AddNewAccount').modal('show');
    });
    $(document).on("submit", "#rejectEnrolment-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        // Basic validation
        const studentID = $form.find('[name="studentID"]').val().trim();

        if (!studentID) {
            Swal.fire({
                title: "Error",
                text: "Please fill in all required fields",
                icon: "error",
                toast: true,
                position: "top-end",
                timer: 3000,
                showConfirmButton: false,
            });
            $form.data("isSubmitted", false);
            return;
        }

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=rejectEnrolment_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Rejected'); // Fixed button text
            }
        });
    });
    // School YEar Activation and Deactivation
    $(document).on('click', '#activationBtn', function () {
        const school_year_id = $(this).data('id');
        $('#school_year_id').val(school_year_id);
        $('#activateSY').modal('show');
    });
    $(document).on("submit", "#enrollstud-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        // Basic validation
        const student_id = $form.find('[name="student_id"]').val().trim();

        if (!student_id) {
            Swal.fire({
                title: "Error",
                text: "Please fill in all required fields",
                icon: "error",
                toast: true,
                position: "top-end",
                timer: 3000,
                showConfirmButton: false,
            });
            $form.data("isSubmitted", false);
            return;
        }

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=enrollstud_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Enrolled');
            }
        });
    });
    $(document).on("submit", "#reenrollstud-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        // Basic validation
        const student_id = $form.find('[name="student_id"]').val().trim();

        if (!student_id) {
            Swal.fire({
                title: "Error",
                text: "Please fill in all required fields",
                icon: "error",
                toast: true,
                position: "top-end",
                timer: 3000,
                showConfirmButton: false,
            });
            $form.data("isSubmitted", false);
            return;
        }

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=reenrollstud_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Enrolled');
            }
        });
    });
    $(document).on("submit", "#activateSY-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        // Basic validation
        const school_year_id = $form.find('[name="school_year_id"]').val().trim();

        if (!school_year_id) {
            Swal.fire({
                title: "Error",
                text: "Please fill in all required fields",
                icon: "error",
                toast: true,
                position: "top-end",
                timer: 3000,
                showConfirmButton: false,
            });
            $form.data("isSubmitted", false);
            return;
        }

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=activationSY_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Activated'); // Fixed button text
            }
        });
    });
    $(document).on('click', '#deactivationBtn', function () {
        const schoolyear_id = $(this).data('id');
        $('#schoolyear_id').val(schoolyear_id);
        $('#DeactivateSY').modal('show');
    });
    $(document).on("submit", "#DeactivateSY-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        // Basic validation
        const schoolyear_id = $form.find('[name="school_year_id"]').val().trim();

        if (!schoolyear_id) {
            Swal.fire({
                title: "Error",
                text: "Please fill in all required fields",
                icon: "error",
                toast: true,
                position: "top-end",
                timer: 3000,
                showConfirmButton: false,
            });
            $form.data("isSubmitted", false);
            return;
        }

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=DeactivationSY_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Deactivated'); // Fixed button text
            }
        });
    });
    $(document).on("submit", "#stduentEnrolment-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=stduentEnrolment_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {

                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Update'); // Changed from 'Deactivated' to 'Update'
            }
        });
    });
    $(document).on("submit", "#sfFour-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();

        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);
        e.preventDefault();

        let monthVal = $("#month_attendance").val();

        // create hidden input and append to form
        $form.append(
            $('<input>')
                .attr('type', 'hidden')
                .attr('name', 'report_for_the_month_of')
                .val(monthVal)
        );

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=sfFour_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {

                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Update'); // Changed from 'Deactivated' to 'Update'
            }
        });
    });
    $(document).on("submit", "#sfEight-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        const month = document.getElementById('month_attendance').value;

        if (!month) {
            return false;
        }

        const year = new Date().getFullYear();
        const monthMap = {
            'JANUARY': '01',
            'FEBRUARY': '02',
            'MARCH': '03',
            'APRIL': '04',
            'MAY': '05',
            'JUNE': '06',
            'JULY': '07',
            'AUGUST': '08',
            'SEPTEMBER': '09',
            'OCTOBER': '10',
            'NOVEMBER': '11',
            'DECEMBER': '12'
        };



        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=sfEight_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {

                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Update'); // Changed from 'Deactivated' to 'Update'
            }
        });
    });

    $('#saveBtn').click(function (e) {
        e.preventDefault();
        const lockConf = document.getElementById('saveModal');
        const svm = new bootstrap.Modal(lockConf);
        svm.hide()
        $('#saveGradess').submit();
    });
    $(document).on("submit", "#displayStudentInfo", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=displayStudentInfo",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Update'); // Changed from 'Deactivated' to 'Update'
            }
        });
    });
    $(document).on("submit", "#student-update-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=student_update_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Update'); // Changed from 'Deactivated' to 'Update'
            }
        });
    });
    $(document).on("submit", "#medical-update", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=medical_update",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {

                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Update'); // Changed from 'Deactivated' to 'Update'
            }
        });
    });

    //   classrooms 
    $(document).on('click', '.deleteClassroomBtn', function () {
        const classroom_id = $(this).data('id');
        $('#classroom_id').val(classroom_id);
        $('#deleteClassroom').modal('show');
    });
    $(document).on("submit", "#deleteClassroom-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=deleteClassroom_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Deleted'); // Changed from 'Deactivated' to 'Update'
            }
        });
    });
    $(document).on('click', '.editClassroomsBtn', function () {
        const classroom_id = $(this).data('id');
        $('#classroom_ids').val(classroom_id);
        $('#editClassroom').modal('show');
    });
    $(document).on('click', '.editClassroomsBtn', function () {
        const classroom_id = $(this).data('id');

        $.ajax({
            url: base_url + "authentication/action.php?action=getClassroomById",
            type: "POST",
            data: { classroom_id: classroom_id },
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    $('#classroom_id').val(response.data.room_id);
                    $('#room_status').val(response.data.room_status);
                    $('#classroom_name').val(response.data.room_name);
                    $('#classroom_type').val(response.data.room_type);
                    $('#editClassroom').modal('show');
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error"
                    });
                }
            },
            error: function () {
                Swal.fire({
                    title: "Connection Error",
                    text: "Please try again.",
                    icon: "error"
                });
            }
        });
    });
    $(document).on("submit", "#editClassroom-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=editClassroom_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Update');
            }
        });
    });


    // sections
    $(document).on("submit", "#deleteSection-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=deleteSection_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Deleted'); // Changed from 'Deactivated' to 'Update'
            }
        });
    });

    $(document).on('click', '.deleteSectionBtn', function () {
        const section_id = $(this).data('id');
        $('#section_id').val(section_id);
        $('#deleteSection').modal('show');
        // alert(section_id);
    });
    $(document).on('click', '.editSectionBtn', function () {
        const section_id = $(this).data('id');
        $('#section_ids').val(section_id);
        $('#editSections').modal('show');
    });

    $(document).on('click', '.editSectionBtn', function () {
        const section_id = $(this).data('id');
        $('#section_ids').val(section_id);

        $.ajax({
            url: base_url + "authentication/action.php?action=getSectionById",
            type: "POST",
            data: { section_id: section_id },
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    $('#section_ids').val(response.data.section_id); // hidden input
                    $('#section_name').val(response.data.section_name);
                    $('#section_grade_level').val(response.data.section_grade_level);
                    $('#section_status').val(response.data.section_status);
                    $('#editSections').modal('show');
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error"
                    });
                }
            },
            error: function () {
                Swal.fire({
                    title: "Connection Error",
                    text: "Please try again.",
                    icon: "error"
                });
            }
        });
    });

    $(document).on("submit", "#editSection-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=editSection_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Update'); // Changed from 'Deactivated' to 'Update'
            }
        });
    });


    //   school year

    $(document).on('click', '.deleteSchoolyearBtn', function () {
        const school_year_id = $(this).data('id');
        $('#school_year_id_delete').val(school_year_id);
        $('#deleteSchoolYear').modal('show');
    });
    $(document).on("submit", "#deleteSchoolyear-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=deleteSchoolYear_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Deleted'); // Changed from 'Deactivated' to 'Update'
            }
        });
    });

    $(document).on('click', '.editSchoolyearBtn', function () {
        const school_year_id = $(this).data('id');
        $('#school_year_id_edit').val(school_year_id);
        $('#editSchoolyear').modal('show');
    });
    $(document).on('click', '.editSchoolyearBtn', function () {
        const school_year_id = $(this).data('id');

        $.ajax({
            url: base_url + "authentication/action.php?action=getSchoolYearById",
            type: "POST",
            data: { school_year_id: school_year_id },
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    $('#school_year_id_edit').val(response.data.school_year_id);
                    $('#school_year_status').val(response.data.school_year_status);
                    $('#school_year_name').val(response.data.school_year_name);
                    $('#editSchoolyear').modal('show');
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error"
                    });
                }
            },
            error: function () {
                Swal.fire({
                    title: "Connection Error",
                    text: "Please try again.",
                    icon: "error"
                });
            }
        });
    });

    //   Subjects

    $(document).on("submit", "#deleteSubject-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=deleteSubject_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Deleted'); // Changed from 'Deactivated' to 'Update'
            }
        });
    });

    $(document).on('click', '.editSubjectBtn', function () {
        const subject_id = $(this).data('id');
        $('#subject_id_edit').val(subject_id);
        $('#editSubjects').modal('show');
    });
    $(document).on('click', '.editSubjectBtn', function () {
        const subject_id = $(this).data('id');

        $.ajax({
            url: base_url + "authentication/action.php?action=getSubjectsById",
            type: "POST",
            data: { subject_id: subject_id },
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    $('#grade_level').val(response.data.grade_level);
                    $('#subject_name').val(response.data.subject_name);
                    $('#subject_code').val(response.data.subject_code);
                    $('#subject_units').val(response.data.subject_units);
                    $('#subjects_status').val(response.data.subjects_status);
                    $('#editSubjects').modal('show');
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error"
                    });
                }
            },
            error: function () {
                Swal.fire({
                    title: "Connection Error",
                    text: "Please try again.",
                    icon: "error"
                });
            }
        });
    });

    $(document).on("submit", "#editSubjects-form", function (e) {
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        const originalText = $btn.html();
        $btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=editSubjects_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        $('#editSchoolyear').modal('hide');
                        location.reload(); // optional, or update table row dynamically
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html(originalText);
            }
        });
    });
    const timeInput = document.getElementById('timeInput');
    const sessionSelect = document.getElementById('sessionFilter');
    function fetchStudents(page = 1) {
        const fd = new FormData();
        fd.append('ajax', 1);
        fd.append('search', document.getElementById('searchInput').value);
        fd.append('session', sessionSelect.value);
        fd.append('page', page);

        const tbody = document.getElementById('studentTableBody');
        tbody.innerHTML = `<tr><td colspan="7" class="text-center">Loading...</td></tr>`;

        fetch('contents/attendance.php', {
            method: 'POST',
            body: fd
        })
            .then(r => r.json())
            .then(data => {
                tbody.innerHTML = data.html;
                document.getElementById('noResults').classList.toggle('d-none', data.hasData);
                currentPage = page;
            });
    }

    $(document).on("click", ".attendance-form button", function (e) {
        e.preventDefault();

        const $btn = $(this);
        const $form = $btn.closest("form");
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);

        const typeMap = { 'P': 'Present', 'A': 'Absent', 'L': 'Late', "confirm": 'confirm', "cancel": 'cancel' };
        const type = typeMap[$form.data("type")];


        let session = document.getElementById('sessionFilter').value;
        const time = document.getElementById('timeInput').value;
        if (type === 'cancel') {
            session = 'cancel'
        } else if (type === 'confirm') {
            session = 'confirm'
        }

        // if (!time) {
        //     Swal.fire("Error", "Please select a time.", "error");
        //     $form.data("isSubmitted", false);
        //     return;
        // }

        const formData = new FormData($form[0]);
        formData.append("type", type);
        formData.append("session", session);
        formData.append("time", time);

        $.ajax({
            url: base_url + "authentication/action.php?action=attendance",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",

            success(res) {
                Swal.fire({
                    title: res.status ? "Success!" : "Error",
                    text: res.message,
                    icon: res.status ? "success" : "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
                fetchStudents(1)
                if (res.status) $form[0].reset();
            },

            error() {
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },

            complete() {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html($btn.text());
            }
        });
    });

    $(document).on('click', '#personal_info', function () {
        document.getElementById('displayStudentInfo').style.display = 'flex';
        document.getElementById('displayAttendance').style.display = 'none';
        document.getElementById('displayMedical').style.display = 'none';
        document.getElementById('displayGrades').style.display = 'none';

        // Update active button
        $('.col-md-8 button').removeClass('Active');
        $(this).addClass('Active');
    });

    $(document).on('click', '#attendance', function () {
        document.getElementById('displayStudentInfo').style.display = 'none';
        document.getElementById('displayAttendance').style.display = 'flex';
        document.getElementById('displayMedical').style.display = 'none';
        document.getElementById('displayGrades').style.display = 'none';

        // Update active button
        $('.col-md-8 button').removeClass('Active');
        $(this).addClass('Active');
    });

    $(document).on('click', '#medical', function () {
        document.getElementById('displayStudentInfo').style.display = 'none';
        document.getElementById('displayAttendance').style.display = 'none';
        document.getElementById('displayMedical').style.display = 'flex';
        document.getElementById('displayGrades').style.display = 'none';

        // Update active button
        $('.col-md-8 button').removeClass('Active');
        $(this).addClass('Active');
    });

    $(document).on('click', '#grades', function () {
        document.getElementById('displayStudentInfo').style.display = 'none';
        document.getElementById('displayAttendance').style.display = 'none';
        document.getElementById('displayMedical').style.display = 'none';
        document.getElementById('displayGrades').style.display = 'flex';

        // Update active button
        $('.col-md-8 button').removeClass('Active');
        $(this).addClass('Active');
    });
    // Calculate age from birthdate
    $(document).on("change", "#birthdate", function () {
        const birthdate = new Date($(this).val());
        const today = new Date();
        let age = today.getFullYear() - birthdate.getFullYear();
        const monthDiff = today.getMonth() - birthdate.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
            age--;
        }

        $("#age").val(age);
    });

    $(document).on('change', '.status-select', function () {
        let $form = $(this).closest('.status-form'); // find the form for THIS row
        let formData = new FormData($form[0]);

        $.ajax({
            url: base_url + "authentication/action.php?action=status_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            }
        });
    });
    $(document).on('change', '.status-enrolment-select', function () {
        let $form = $(this).closest('.status-enrolment-form'); // find the form for THIS row
        let formData = new FormData($form[0]);

        $.ajax({
            url: base_url + "authentication/action.php?action=status_enrolment_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            }
        });
    });

    // feed back: 
    $(document).on("submit", "#deleteFeedback-form", function (e) {
        // alert('Button Submit');
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);


        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: base_url + "authentication/action.php?action=deleteFeedback_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('<i class="fa-solid fa-check"></i>'); // Fixed button text
            }
        });
    });



    function showError(message) {
        Swal.fire({
            title: 'Failed',
            text: message,
            icon: 'error',
            toast: true,
            position: 'top-end',
            timer: 3000,
            showConfirmButton: false
        })
        //$form.removeClass("processing");
    }
});
