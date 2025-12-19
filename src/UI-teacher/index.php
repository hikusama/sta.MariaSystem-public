<?php include "../../header.php";


require_once __DIR__ . '/../../tupperware.php';
$result = checkURI('teacher', 1);
if ($result['res']) {
    header($result['uri']);
    exit;
}
?>

<?php include 'nav-head.php'; ?>

<title><?php echo get_option('system_title'); ?></title>
<div class="d-flex d-justify-between">
    <?php include 'nav-sidebar.php'; ?>
    <div class="w-100 mt-2">
        <?php include 'content.php'; ?>
    </div>
</div>
<?php include '../../footer.php' ?>