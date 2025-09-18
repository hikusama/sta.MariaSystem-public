<style>
   .sidebar-list a.active {
        background-color: #dc3545 !important;
        font-weight: bold;
        color: snow;
    }
    .sidebar-list{
        background-color: transparent !important;
    }
    .sidebar-list a{
        background-color: #f1f1f1ff !important;
        color: #000;
        margin: 5px !important;
    }
    .profile p{
        color: #000 !important;
    }
</style>
<?php
$query = "SELECT * FROM admin";
$stmt = $pdo->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$profile = $result["admin_picture"];
?>
<nav id="sidebar" class="navbarHide shadow" style="z-index: 55;" >
    <div style="width: 240px; border-radius: 5px;">
        <div class="profile w-100 h-auto d-flex flex-column align-items-center px-2 pb-2 mt-3" id="profile_slide">
            <?php if($profile) {?>
                <img src="../../authentication/uploads/<?= $profile; ?>" alt="" style="width: 90px; height: 90px; border-radius: 50%;">
            <?php }else{ ?>
                <img src="../../assets/image/users.png" alt="" style="width: 90px; height: 90px; border-radius: 50%;">
            <?php } ?>
            <p class="fw-bold text-center text-black"><?php echo ucwords(strtolower($result["admin_lastname"] . ', ' . $result["admin_firstname"])); ?></p>
        </div>
        <div class="sidebar-list m-2">
            <a href="index.php?page=home" class="text-black nav-item rounded-1 p-2 nav-home">
                <span class=""><i class=""></i></span> Dashboard
            </a>
            <a href="index.php?page=contents/users" class="text-black nav-item rounded-1 p-2 nav-users">
                <span class=""><i class=""></i></span> User Management 
            </a>
            <a href="index.php?page=contents/learners" class="text-black nav-item rounded-1 p-2 nav-learners">
                <span class=""><i class=""></i></span> Students
            </a>
            <a href="index.php?page=contents/assign" class="text-black nav-item rounded-1 p-2 nav-assign">
                <span class=""><i class=""></i></span> Class Management
            </a>
            <a href="index.php?page=contents/enrolment" class="text-black nav-item rounded-1 p-2 nav-enrolment">
                <span class=""><i class=""></i></span> Enrolment Process
            </a>
            <a href="index.php?page=contents/classroom" class="text-black nav-item rounded-1 p-2 nav-classroom">
                <span class=""><i class=""></i></span> Classroom Management
            </a>
            <a href="index.php?page=contents/settings" class="text-black nav-item rounded-1 p-2 nav-settings">
                <span class=""><i class=""></i></span> Account Settings 
            </a>
        </div>
    </div>
</nav>

<script>
    const page = '<?php echo isset($_GET["page"]) ? $_GET["page"] : "home"; ?>';
    const slug = page.split('/').pop(); 
    const navItem = document.querySelector('.nav-' + slug);
    if (navItem) {
        navItem.classList.add('active');
    }
</script>
