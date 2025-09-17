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
      const section_desc = $form.find('[name="section_desc"]').val().trim();
      
      if (!section_name || !grade_level || !section_desc) {
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
