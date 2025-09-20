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
                      $form[0].reset(); // Reset form on success
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
      const classroomName = $form.find('[name="classroom_name"]').val().trim();
      const classroomType = $form.find('[name="classroom_type"]').val().trim();
      
      if (!classroomName || !classroomType) {
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
      const lrn = $form.find('[name="lrn"]').val().trim();
      const grade_level = $form.find('[name="grade_level"]').val().trim();
      const lastName = $form.find('[name="lastName"]').val().trim();
      const firstName = $form.find('[name="firstName"]').val().trim();
      const middleName = $form.find('[name="middleName"]').val().trim();
      const religion = $form.find('[name="religion"]').val().trim();
      const birthdate = $form.find('[name="birthdate"]').val().trim();
      
      if (!lrn ||
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
              $btn.prop("disabled", false).html('Create Account');// Fixed button text
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
    const subjects = $form.find('[name="subjects[]"]').map(function() {
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
  $(document).ready(function() {
        $('#adviserSelect').change(function() {
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
        success: function(response) {
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
        error: function() {
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

      $(document).on('click', '#personal_info', function() {
            document.getElementById('displayStudentInfo').style.display = 'flex';
            document.getElementById('displayAttendance').style.display = 'none';
            document.getElementById('displayMedical').style.display = 'none';
            document.getElementById('displayGrades').style.display = 'none';
            
            // Update active button
            $('.col-md-8 button').removeClass('Active');
            $(this).addClass('Active');
        });
        
        $(document).on('click', '#attendance', function() {
            document.getElementById('displayStudentInfo').style.display = 'none';
            document.getElementById('displayAttendance').style.display = 'flex';
            document.getElementById('displayMedical').style.display = 'none';
            document.getElementById('displayGrades').style.display = 'none';
            
            // Update active button
            $('.col-md-8 button').removeClass('Active');
            $(this).addClass('Active');
        });
        
        $(document).on('click', '#medical', function() {
            document.getElementById('displayStudentInfo').style.display = 'none';
            document.getElementById('displayAttendance').style.display = 'none';
            document.getElementById('displayMedical').style.display = 'flex';
            document.getElementById('displayGrades').style.display = 'none';
            
            // Update active button
            $('.col-md-8 button').removeClass('Active');
            $(this).addClass('Active');
        });
        
        $(document).on('click', '#grades', function() {
            document.getElementById('displayStudentInfo').style.display = 'none';
            document.getElementById('displayAttendance').style.display = 'none';
            document.getElementById('displayMedical').style.display = 'none';
            document.getElementById('displayGrades').style.display = 'flex';
            
            // Update active button
            $('.col-md-8 button').removeClass('Active');
            $(this).addClass('Active');
        });
  // Calculate age from birthdate
    $(document).on("change", "#birthdate", function() {
        const birthdate = new Date($(this).val());
        const today = new Date();
        let age = today.getFullYear() - birthdate.getFullYear();
        const monthDiff = today.getMonth() - birthdate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
            age--;
        }
        
        $("#age").val(age);
    });

  function showError (message) {
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
})
