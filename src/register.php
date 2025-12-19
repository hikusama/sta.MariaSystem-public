<?php include '../header.php'; 
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


?>
<style>
    body {
        background: url('../assets/image/bg.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    main {
        height: 100vh !important;
        overflow-y: scroll;
        scrollbar-width: none;
        align-items: center !important;
    }

    @media screen and (max-width: 768px) {
        .login-page {
            padding: 2rem 0 !important;
        }

        main {
            align-items: start !important;
        }
    }
</style>
<main class="login-page p-0 d-flex justify-content-center w-100 h-100">
    <div class="card-header h-fit shadow p-0 m-0 rounded-3 col-md-5 col-11 rounded rounded-top bg-white">
        <div class="card-header  py-2 text-white bg-danger text-center rounded-top">
            <h4 class="mt-1 text-white">Registration</h4>
        </div>
        <div class="card-body h-auto">
            <form class="row g-1 p-3" action="../authentication/auth.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
                <input type="hidden" name="resgiter" value="true">
                <input type="hidden" name="academicRank" value="STUDENT">
                <h5 class="ms-1 my-1">Personal Information</h5>
                <!-- Account Type -->
                <div class="col-md-3">
                    <label class="form-label">First Name</label>
                    <input required placeholder="First Name" type="text" class="form-control" name="firstName">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Middle Name</label>
                    <input required placeholder="Middle Name" type="text" class="form-control" name="middleName">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Last Name</label>
                    <input required placeholder="Last Name" type="text" class="form-control" name="lastName">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Suffix</label>
                    <select class="form-select" name="suffix">
                        <option value="" disabled selected>Select suffix (optional) </option>
                        <option value="Jr">Jr</option>
                        <option value="Sr">Sr</option>
                        <option value="II">II</option>
                        <option value="III">III</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input required placeholder="Email" type="email" class="form-control" name="email">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Relationship with student</label>
                    <select name="relationship" id="" class="form-select">
                        <option value="">Select relationship</option>
                        <option value="Father">Father</option>
                        <option value="Mother">Mother</option>
                        <option value="Guardian">Guardian</option>
                    </select>
                </div>
                <h5 class="ms-1 my-1 mt-3">Account Information</h5>
                <div class="col-md-4">
                    <label class="form-label">Username</label>
                    <input required placeholder="Username" type="text" class="form-control" name="username">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Password</label>
                    <input required placeholder="Password" type="password" class="form-control" name="password">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Confirm Password</label>
                    <input required placeholder="Confirm Password" type="password" class="form-control"
                        name="cpassword">
                    <p style="display: none; position: absolute; color: red;" id="passwordNotMatch">Password
                        not match</p>
                </div>
                <div class="col-12 text-center mt-4 ">
                    <button type="submit" class="btn btn-danger px-5 m-0 w-100" style="color: #fff !important;">
                        <i class="bi bi-person-plus-fill me-1"></i> Sign-up
                    </button>
                    <div class="mt-2 mb-3">
                        <span>Already have an account? </span><a href="index.php"
                            class="text-decoration-none fw-bold">Sign In</a>
                    </div>
                </div>

            </form>
        </div>
    </div>
</main>
<?php if (
    isset($_GET['email']) ||
    isset($_GET['password']) ||
    isset($_GET['username']) ||
    isset($_GET['create'])
): ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const messages = {
                email: {
                    icon: 'error',
                    title: 'Email already exist!'
                },
                password: {
                    icon: 'error',
                    title: 'Password not match!'
                },
                username: {
                    icon: 'error',
                    title: 'Username already exist!'
                },
                noActiveSchoolYear: {
                    icon: 'error',
                    title: 'Registration is closed - no active school year.'
                },
                create: {
                    icon: 'success',
                    title: 'Account Created successfully!'
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
<?php include '../footer.php'; ?>