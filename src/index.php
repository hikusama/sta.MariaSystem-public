<?php include '../header.php'; ?>
<style>
    main{
        background: url('../assets/image/bg.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
</style>
<main class="p-0 d-flex justify-content-center align-items-center w-100 h-100">
    <div class="shadow rounded-3 col-md-3 bg-white">
        <div class="card-header bg-danger text-white text-center shadow p-2 py-3 rounded-top">
            <h4 class="card-title text-white mt-1 loginAccess">LOGIN</h4>
        </div>
        <div class="card-body  shadow loginBody">
            <form action="../authentication/auth.php" class="form-floating mt-2 p-3" method="post">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
                <input type="hidden" name="loginAuth" value="true">
                <div class="mb-3">
                    <input type="text" class="form-control" name="username" placeholder="Username:">
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" name="password" placeholder="Password: ">
                </div>
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
        <!-- FORGOT PASSWORD MODAL -->
        <div class="modal fade" id="changePassword" tabindex="-1" aria-labelledby="passwordModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="../auth/authentications.php" class="modal-content">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
                    <input type="hidden" name="usersForgottenPass" value="true">
                    <div class="modal-header modalBG">
                        <h5 class="modal-title text-start w-100" id="passwordModalLabel" style="color: #fff;">Enter
                            your username:</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label for="usernameConfim">Username:</label>
                        <input type="text" name="usernameAuth" id="usernameConfim" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<?php include '../footer.php' ?>