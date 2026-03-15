<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================
   USER ROUTING
========================= */
if (isset($_SESSION['user_id'], $_SESSION['user_role'])) {

    if ($_SESSION['user_role'] === 'TEACHER') {
        header('Location: ../src/UI-teacher/index.php');
        exit;
    }

    if ($_SESSION['user_role'] === 'PARENT') {
        header('Location: ../src/UI-parents/index.php');
        exit;
    }

    session_unset();
    session_destroy();
}

/* =========================
   ADMIN ROUTING
========================= */
if (isset($_SESSION['admin_id'], $_SESSION['admin_role'])) {
    header('Location: ../src/UI-Admin/index.php');
    exit;
}
include '../header.php';
?>

<style>
    main {
        background: url('../assets/image/bg.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    .g-recaptcha {
        justify-content: center;
        display: flex;
    }

    .button-eye {
        position: absolute;
        right: .7rem;
        bottom: .7rem;
        background: none;
        border: none;
        cursor: pointer;
    }
</style>
<main class="p-0 d-flex justify-content-center align-items-center w-100 h-100">
    <div class="shadow rounded-3 col-md-3 bg-white " style="width: 21rem;">
        <div class="card-header bg-danger text-white text-center shadow p-2 py-3 rounded-top-2">
            <h4 class="card-title text-white mt-1 loginAccess">LOGIN</h4>
        </div>
        <div class="card-body shadow loginBody p-2">
            <form action="../authentication/auth.php" class="form-floating mt-2 p-3" method="post">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
                <input type="hidden" name="loginAuth" value="true">
                <div class="mb-3">
                    <input type="text" class="form-control" name="username" placeholder="Username:">
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" name="password" placeholder="Password: ">
                    <i class="fa-solid fa-eye button-eye" id="password-toggle"></i>
                </div>
                <div class="g-recaptcha" data-sitekey="6LdSd4csAAAAAKy34idc9xXPwnk0BTLCieym-NXj"></div>
                <div class="m-0 text-center d-flex flex-column ">
                    <button type="submit" class="btn btn-danger mb-0 buttonLogin p-1 py-2"
                        style="color: #fff !important;"><i class="bi bi-person-plus-fill me-1"></i>Login</button>
                    <!-- <label for="" class="m-0 mt-2" data-bs-toggle="modal" data-bs-target="#changePassword" style="color: #000 !important; cursor: pointer;">forgot Password?</label> -->
                </div>

                <div class="col-12 text-center mt-1">

                    <div class="">
                        <span style="color: #000 !important;">Don't have an account? </span><a href="register.php"
                            class="text-decoration-none fw-bold text-danger">Sign Up</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const passwordInput = document.querySelector('input[name="password"]');
        const passwordToggle = document.getElementById('password-toggle');

        if (passwordInput && passwordToggle) {
            // Add padding to password input to prevent text overlap
            passwordInput.style.paddingRight = '40px';

            // Wrap the password input in a relative container if not already
            if (passwordInput.parentNode.style.position !== 'relative') {
                passwordInput.parentNode.style.position = 'relative';
            }

            // Toggle password visibility
            passwordToggle.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Toggle eye icon
                if (type === 'text') {
                    this.className = 'fa-solid fa-eye-slash button-eye';
                    this.title = 'Hide password';
                } else {
                    this.className = 'fa-solid fa-eye button-eye';
                    this.title = 'Show password';
                }
            });

            // Add hover effect
            passwordToggle.addEventListener('mouseenter', function() {
                this.style.color = '#495057';
            });

            passwordToggle.addEventListener('mouseleave', function() {
                this.style.color = '#6c757d';
            });
        }
    });
</script>
<?php if (
    isset($_GET['incorrect']) ||
    isset($_GET['restricted']) ||
    isset($_GET['validation']) ||
    isset($_GET['recaptcha'])
): ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const messages = {
                incorrect: {
                    icon: 'error',
                    title: 'Incorrect username or password, please try again!'
                },
                recaptcha: {
                    icon: 'error',
                    title: 'Recaptcha verification failed.'
                },
                restricted: {
                    icon: 'error',
                    title: 'Access restricted please contact admin.'
                },
                validation: {
                    icon: 'error',
                    title: 'Please fill in all required fields correctly.'
                }

            };

            for (const key in messages) {
                const value = new URLSearchParams(window.location.search).get(key);
                if (value) {
                    Swal.fire({
                        toast: true,
                        icon: messages[key].icon,
                        title: messages[key].title,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didClose: () => removeUrlParams([key])
                    });
                    break;
                }
            }

            function removeUrlParams(params) {
                const url = new URL(window.location);
                params.forEach(param => url.searchParams.delete(param));
                window.history.replaceState({}, document.title, url.toString());
            }
        });
    </script>
<?php endif; ?>
<?php include '../footer.php' ?>