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
    /* img{
        width: 100px;
        height: 100px;
        border-radius: 50%;
    } */
    p{
        color: #000;
        margin: 0;
    }
    
</style>
<?php
    $query = "SELECT * FROM users 
    WHERE user_id = :user_id;";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id'=>$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<nav id="sidebar" class="navbarHide shadow" style="z-index: 30;">
    <div class="w-100 h-auto d-flex flex-column align-items-center px-2 pb-2 mt-3">
        <img src="../../assets/image/users.png" alt="" style="width: 100px; height: 100px; border-radius: 50%;">
        <p class="text-center fw-bold"><?php echo ucwords(strtolower($result["firstname"] . ' ' . $result["lastname"])); ?></p>
    </div>
    <div style="width: 240px; border-radius: 5px;" id="navigationsSlide">
        <div class="sidebar-list m-2 mt-3">
            <a href="index.php?page=home" class="nav-item p-2 rounded-1 nav-home ">
                <span class=""><i class=""></i></span> Dashboard
            </a>
            <a href="index.php?page=contents/learners" class="nav-item p-2 rounded-1 nav-learners nav-profile">
                <span class=""><i class=""></i></span> Child Management (Learners) 
            </a>
            <a href="index.php?page=contents/feeback" class="nav-item p-2 rounded-1 nav-feeback">
                <span class=""><i class=""></i></span> Feedback 
            </a>
            <a href="index.php?page=contents/settings" class="nav-item p-2 rounded-1 nav-settings">
                <span class=""><i class=""></i></span> Account Settings 
            </a>
            <!-- <a href="index.php?page=contents/request_form" class="nav-item nav-request_form">
                <span class=""><i class=""></i></span> Logoout
            </a> -->
            
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
