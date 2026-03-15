<?php
ob_start();
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('admin', 2);
if ($result['res']) {
    ob_end_clean();
    header($result['uri']);
    exit;
}

// Handle AJAX requests FIRST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accesssubmit'])) {
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    if ($isAjax) {
        ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        
        $newAcc = trim($_POST['access'] ?? '');
        
        if (empty($newAcc)) {
            echo json_encode(['success' => false, 'message' => 'Please select an access option.']);
        } elseif (!in_array($newAcc, ['allusers', 'onlyadmin', 'adminteacher'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid access option.']);
        } else {
            $pdo = $pdo ?? null;
            if ($pdo) {
                $update = $pdo->prepare("UPDATE accessibility SET accessible_to = ? WHERE id = 1");
                if ($update->execute([$newAcc])) {
                    echo json_encode(['success' => true, 'message' => 'Access settings updated successfully!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update settings.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Database connection error.']);
            }
        }
        exit;
    }
}

ob_end_clean();
$acc = $pdo->prepare("SELECT accessible_to FROM accessibility WHERE id = 1");
$acc->execute();
$accesible_to = $acc->fetch(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="mx-2">
        <h4><i class="fa-brands fa-expeditedssl"></i> Controll Users</h4>
    </div>
</div>
<style>
    .admcpanel button {
        background-color: #007bff;
        color: #fff;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        cursor: pointer;
        position: relative;
    }

    .admcpanel button span {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 10px;
        height: 10px;
        background-color: #fff;
        border-radius: 50%;
    }
</style>

<div class="row g-3 scroll-classes admcpanel">
    <div class="row mb-3 justify-content-between align-items-center">
        <form id="Accessibility" method="post">
            <h5>Who can access this?</h5>
            <div>
                <input type="radio" name="access" id="allusers" value="allusers" <?php if ($accesible_to['accessible_to'] === 'allusers') echo 'checked'; ?>>
                <label for="allusers">All Users (Including Parents)</label>
            </div>
            <div>
                <input type="radio" name="access" id="onlyadmin" value="onlyadmin" <?php if ($accesible_to['accessible_to'] === 'onlyadmin') echo 'checked'; ?>>
                <label for="onlyadmin">Only Admin</label>
            </div>
            <div>
                <input type="radio" name="access" id="adminteacher" value="adminteacher" <?php if ($accesible_to['accessible_to'] === 'adminteacher') echo 'checked'; ?>>
                <label for="adminteacher">Admin and Teachers</label>
            </div>
            <input type="hidden" name="accesssubmit" value="1">
            <input type="submit" value="Save" class="btn btn-primary mt-2" id="saveBtnAccessibility">
        </form>
    </div>
</div>
<script>
    ! function() {
        document.addEventListener('DOMContentLoaded', function() {
            let e = document.getElementById('Accessibility');
            if (e) {
                e.addEventListener('submit', function(t) {
                    t.preventDefault(), t.stopPropagation();
                    let n = document.querySelector('input[name="access"]:checked');
                    if (!n) return void Swal.fire({
                        icon: 'warning',
                        title: 'Warning!',
                        text: 'Please select an access option.',
                        confirmButtonColor: '#ffc107'
                    });
                    let o = new FormData(this),
                        s = document.getElementById('saveBtnAccessibility'),
                        i = s.value;
                    s.value = 'Saving...', s.disabled = !0, fetch('contents/admincontroll.php', {
                        method: 'POST',
                        body: o,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).then(e => e.ok ? e.json() : Promise.reject('Bad response')).then(e => {
                        Swal.fire({
                            icon: e.success ? 'success' : 'error',
                            title: e.success ? 'Success!' : 'Error!',
                            text: e.message,
                            confirmButtonColor: e.success ? '#28a745' : '#dc3545'
                        })
                    }).catch(e => {
                        console.error(e), Swal.fire({
                            icon: 'error',
                            title: 'Oops!',
                            text: 'An error occurred. Please try again.',
                            confirmButtonColor: '#dc3545'
                        })
                    }).finally(() => {
                        s.value = i, s.disabled = !1
                    })
                }, !1)
            }
        })
    }();
</script>