<?php 
include 'authentication/functions.php';
include 'authentication/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo get_option('system_description')?>">
    <title>sta.Maria web system</title>
    <link rel="icon" href="<?php echo base_url() ?>/assets/image/logo2.png" type="image/x-icon">
    <?php render_styles()?>
    <script src="<?php echo base_url() ?>/assets/js/sweetalert2.min.js"></script> 
    <script>
        var base_url = '<?php echo base_url() ?>';
    </script>
    <?php render_scripts() ?>
</head>
    <body class="bg-light-300">


    