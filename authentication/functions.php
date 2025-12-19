<?php
include "config.php";


$pdo = db_connect();

// function base_url()
// {
//     $pdo = db_connect();


//     if (isset($_SERVER['HTTPS'])) {
//         $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
//     } else {
//         $protocol = 'http';
//     }

//     $whitelist = array(
//         '127.0.0.1',
//         '192.168.100.12', 
//         '192.168.100.49',
//         '::1'
//     );
//     $local_hosts = ['localhost', '127.0.0.1', '::1'];
//     if (in_array($_SERVER['HTTP_HOST'], $local_hosts)) {
//         return $protocol . "://" . $_SERVER['HTTP_HOST'] . '/sta.MariaSystpem/';
//     }
//     return $protocol . "://" . $_SERVER['HTTP_HOST'] . '/';


//     // if (in_array($_SERVER['REMOTE_ADDR'], $whitelist)) {
//     //     return $base_url = $protocol . "://" . $_SERVER['SERVER_NAME'] . '/template/';
//     // }
//     // return $base_url = $protocol . "://" . $_SERVER['SERVER_NAME'] . '/';
// }
// function base_url()
// {
//     $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
//     $path = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'))[0];
//     return $protocol . '://' . $_SERVER['SERVER_NAME'] . '/' . $path . '/';
// }
function base_url(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . '://' . $host . '/sta.MariaSystem/';
}




function get_current_page()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];

    return $protocol . '://' . $host . $uri;
}

function render_styles()
{

    $styles = [
        // base_url() . 'assets/css/all.min.css',
        base_url() . 'assets/css/custom-bs.min.css',
        base_url() . 'assets/css/main.css',
        base_url() . 'assets/css/marco.css',
        base_url() . 'assets/css/icons.min.css',
        base_url() . 'assets/css/morris.css',
        base_url() . 'assets/css/dataTables.dataTables.min.css',
        base_url() . 'assets/libs/flatpickr/flatpickr.min.css'

    ];

    foreach ($styles as $style) {
        echo '<link rel="stylesheet" href="' . $style . '">';
    }
}

function render_json()
{

    $json = [base_url() . '../templates/manifest.json'];

    foreach ($json as $jsons) {
        echo '<link rel="manifest" href="' . $jsons . '">';
    }
}

function render_scripts()
{

    $scripts = [
        base_url() . 'assets/js/jquery.min.js',
        base_url() . 'assets/js/plugins/perfect-scrollbar.min.js',
        base_url() . 'assets/js/all.min.js',
        base_url() . 'assets/js/bootstrap.min.js',
        base_url() . 'assets/js/custom-bs.js',
        base_url() . 'assets/js/main.js',
        base_url() . 'assets/js/marco.js',
        base_url() . 'assets/js/chart.js',
        base_url() . 'assets/js/raphael.min.js',
        base_url() . 'assets/js/morris.min.js',
        base_url() . 'assets/js/jquery-3.7.1.min.js',
        base_url() . 'assets/js/dataTables.min.js',
        base_url() . 'assets/js/nav.js',
        base_url() . 'assets/libs/flatpickr/flatpickr.min.js',
        /* base_url() . 'assets/js/pdf.min.js' */
    ];

    foreach ($scripts as $script) {
        echo '<script type="text/javascript" src="' . $script . '"></script>';
    }
}

function get_option($key)
{
    try {
        $pdo = db_connect();

        $stmt = $pdo->prepare("SELECT system_title, system_description FROM system ");
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $row['' . $key . ''];
        }
        return '';
    } catch (PDOException $e) {
        error_log("Database error in get_option(): " . $e->getMessage());
        return '';
    }
}
function checkURI(string $allowedRole, int $times = 1): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $base = str_repeat('../', $times);

    // Always point back to main index.php
    $uri = 'Location: ' . $base . 'index.php';
    $res = false;

    /* =========================
       ADMIN SESSION
    ========================= */
    if (isset($_SESSION['admin_id'], $_SESSION['admin_role'])) {

        if (
            $allowedRole !== 'admin' ||
            $_SESSION['admin_role'] !== 'admin'
        ) {
            $res = true;
        }

        return compact('uri', 'res');
    }

    /* =========================
       USER SESSION
    ========================= */
    if (!isset($_SESSION['user_id'], $_SESSION['user_role'])) {
        $res = true; // not logged in
        return compact('uri', 'res');
    }

    // Role mismatch → kick back to index.php
    if (strtoupper($allowedRole) !== $_SESSION['user_role']) {
        $res = true;
    }

    return compact('uri', 'res');
}




function get_userData($key, $id)
{
    try {
        $pdo = db_connect();

        $stmt = $pdo->prepare("SELECT * FROM user_data WHERE user_id = ? ");
        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $row['' . $key . ''];
        }
        return '';
    } catch (PDOException $e) {
        error_log("Database error in get_option(): " . $e->getMessage());
        return '';
    }
}


function verify_init($id)
{
    try {
        $pdo = db_connect();

        $stmt = $pdo->prepare("SELECT employee_status FROM employee_data WHERE employee_id = ?");
        $stmt->execute([$id]);

        $status = $stmt->fetchColumn(); // returns just the employee_status value

        if ($status === 'pending') {
            $stmt2 = $pdo->prepare("SELECT employee_no FROM employee_data WHERE employee_id = ?");
            $stmt2->execute([$id]);
            $employeeNo = $stmt2->fetchColumn();

            $_SESSION['email_queue'] = $employeeNo;
            header('Location: ../../authentication/verification_form.php');
            exit;
        }
    } catch (PDOException $e) {
        error_log("Database error in verify_init(): " . $e->getMessage());
        return '';
    }
}
