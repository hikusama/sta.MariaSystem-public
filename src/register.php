<?php include '../header.php'; ?>
<style>
    main{
        background: url('../assets/image/bg.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
</style>
<main class="login-page p-0 d-flex justify-content-center align-items-center w-100 h-100">
    <div class="card-header shadow p-0 m-0 rounded-3 col-md-5 col-11 rounded rounded-top bg-white">
        <div class="card-header  py-2 text-white bg-danger text-center rounded-top">
            <h4 class="mt-1 text-white">Registration</h4>
        </div>
        <div class="card-body h-auto" >
            <form class="row g-1 p-3" action="../authentication/auth.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
                <input type="hidden" name="resgiter" value="true">
                <input type="hidden" name="academicRank" value="STUDENT">
                <h5 class="ms-1 my-1">Personal Information</h5>
                <!-- Account Type -->
                <div class="col-md-3">
                    <label class="form-label">Last Name</label>
                    <input required placeholder="Last Name" type="text" class="form-control" name="lastName">
                </div>
                <div class="col-md-3">
                    <label class="form-label">First Name</label>
                    <input required placeholder="First Name" type="text" class="form-control" name="firstName">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Middle Name</label>
                    <input required placeholder="Middle Name" type="text" class="form-control" name="middleName">
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
<?php include '../footer.php'; ?>