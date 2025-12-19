<?php 
require_once __DIR__ . '/../../tupperware.php';
$result = checkURI('teacher', 1);
if ($result['res']) {
    header($result['uri']);
    exit;
}
?>

<div id="view-panel" class="container-fluid" style="height: calc(100vh - 100px);">
    <?php $page = isset($_GET['page']) ? $_GET['page'] : 'home'; ?>
    <?php include $page . '.php' ?>
</div>