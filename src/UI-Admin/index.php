<?php include "../../header.php"; 

require_once __DIR__ . '/../../tupperware.php';
$result = checkURI('admin', 1);

if ($result['res']) {
    header($result['uri']);
    exit;
}
?>

<?php include 'nav-head.php'; ?>

<title><?php echo get_option('system_title'); ?></title>
<div class="d-flex d-justify-between body">
    <?php include 'nav-sidebar.php'; ?>
    <div class="mt-3 w-100 ">
        <?php include 'content.php'; ?>
    </div>
</div>
<?php include '../../footer.php' ?>