<style>
    .sidebar-list a.active {
        background-color: #dc3545 !important;
        font-weight: bold;
        color: snow;
    }

    .sidebar-list {
        background-color: transparent !important;
    }

    .sidebar-list a {
        background-color: #f1f1f1ff !important;
        color: #000;
        margin: 5px !important;
    }


    .toggle-section {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
        transform: translateX(20px);
    }

    .toggle-section.show {
        max-height: 500px;
        /* Adjust based on your content */
        display: block;
    }

    .toggle-btn {
        background: #f8f9fa;
        border: none;
        font-weight: 500;
        cursor: pointer;
        width: 95%;
        text-align: left;
        padding: 10px;
        transform: translateX(5px);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .toggle-btn:hover {
        background: #e9ecef;
    }

    .toggle-btn i {
        transition: transform 0.3s ease;
    }

    .toggle-btn.collapsed i {
        transform: rotate(0deg);
    }

    .toggle-btn:not(.collapsed) i {
        transform: rotate(180deg);
    }

    .sidebar-list {
        overflow-y: auto !important;
        max-height: 65vh !important;
    }

    .sidebar-list::-webkit-scrollbar {
        display: none !important;
    }
</style>

<?php
$query = "SELECT * FROM admin limit 1";
$stmt = $pdo->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$profile = $result["admin_picture"];
?>
<nav id="sidebar" class="navbarHide shadow" style="z-index: 55;">
    <div style="width: 240px; border-radius: 5px;">
        <div class="profile w-100 h-auto d-flex flex-column align-items-center px-2 pb-2 mt-3" id="profile_slide">
            <div class="adprof">
                <?php if ($profile) { ?>
                    <img src="../../authentication/uploads/<?= $profile; ?>" alt="" style="width: 90px; height: 90px; border-radius: 50%;">
                <?php } else { ?>
                    <img src="../../assets/image/users.png" alt="" style="width: 90px; height: 90px; border-radius: 50%;">
                <?php } ?>
                <a class="contad" href="index.php?page=contents/admincontroll">
                    <div class="admc"><i class="fa-brands fa-expeditedssl"></i> Controll</div>
                    <p>Administrator</p>
                </a>
            </div>
            <p class="fw-bold text-center text-black"><?php echo ucwords(strtolower($result["admin_lastname"] . ', ' . $result["admin_firstname"])); ?></p>
        </div>
        <div class="sidebar-list m-2">
            <a href="index.php?page=home" class="text-black nav-item rounded-1 p-2 nav-home">
                <span class=""><i class=""></i></span> Dashboard
            </a>
            <a href="index.php?page=contents/users" class="text-black nav-item rounded-1 p-2 nav-users nav-usersProfile">
                <span class=""><i class=""></i></span> User Management
            </a>
            <a href="index.php?page=contents/learners" class="text-black nav-item rounded-1 p-2 nav-learners nav-profile">
                <span class=""><i class=""></i></span> Students Manage
            </a>
            <a href="index.php?page=contents/assign" class="text-black nav-item rounded-1 p-2 nav-assign">
                <span class=""><i class=""></i></span> Classroom Management
            </a>
            <a href="index.php?page=contents/enrolment" class="text-black nav-item rounded-1 p-2 nav-enrolment">
                <span class=""><i class=""></i></span> Enrollment
            </a>

            <!-- Academic Setup with Toggle -->
            <button class="toggle-btn rounded-2" data-target="hr-section">
                Academic Setup
                <i class="toggle-icon fa-solid fa-caret-down"></i>
            </button>
            <div id="hr-section" class="toggle-section w-90">
                <a href="index.php?page=contents/classroom" class="text-black nav-item rounded-1 p-2 nav-classroom">
                    <span class=""><i class=""></i> Classrooms</span>
                </a>
                <a href="index.php?page=contents/sections" class="text-black nav-item rounded-1 p-2 nav-sections">
                    <span class=""><i class=""></i> Sections</span>
                </a>
                <a href="index.php?page=contents/school_year" class="text-black nav-item rounded-1 p-2 nav-school_year">
                    <span class=""><i class=""></i> School Year</span>
                </a>
                <a href="index.php?page=contents/subjects" class="text-black nav-item rounded-1 p-2 nav-subjects">
                    <span class=""><i class=""></i> Subjects</span>
                </a>
            </div>

            <a href="index.php?page=contents/feedback" class="text-black nav-item rounded-1 p-2 nav-feedback">
                <span class=""><i class=""></i></span> Feedbacks
            </a>
            <a href="index.php?page=contents/datas" class="text-black nav-item rounded-1 p-2 nav-datas nav-sf1 nav-sf4">
                <span class=""><i class=""></i></span> Generate Reports
            </a>
            <a href="index.php?page=contents/settings" class="text-black nav-item rounded-1 p-2 nav-settings">
                <span class=""><i class=""></i></span> Account Settings
            </a>
            <div class="trf2">
                <button onclick="LogoutButton()" class="d-flex gap-2 align-items-center justify-content-center">
                    <p class="m-0">Logout</p>
                    <div style="border: none; background: none;" class="">
                        <i class="fas fa-sign-out-alt text-black ms-1" style="font-size: 17px !important;"></i>
                    </div>
                </button>
            </div>
        </div>
    </div>
</nav>

<script>
    // Set active navigation item
    const page = '<?php echo isset($_GET["page"]) ? $_GET["page"] : "home"; ?>';
    const slug = page.split('/').pop();
    const navItem = document.querySelector('.nav-' + slug);
    if (navItem) {
        navItem.classList.add('active');

        // Auto-expand Academic Setup if one of its children is active
        const academicItems = ['classroom', 'sections', 'school_year', 'subjects'];
        if (academicItems.includes(slug)) {
            const toggleSection = document.getElementById('hr-section');
            const toggleBtn = document.querySelector('[data-target="hr-section"]');
            const toggleIcon = toggleBtn.querySelector('.toggle-icon');

            if (toggleSection && toggleBtn) {
                toggleSection.classList.add('show');
                toggleBtn.classList.remove('collapsed');
                toggleIcon.innerHTML = '<i class="fa-solid fa-caret-up"></i>';
            }
        }
    }
</script>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const toggleButtons = document.querySelectorAll(".toggle-btn");

        toggleButtons.forEach(button => {
            button.addEventListener("click", () => {
                const targetId = button.getAttribute("data-target");
                const targetSection = document.getElementById(targetId);
                const icon = button.querySelector(".toggle-icon");

                // Toggle visibility
                targetSection.classList.toggle("show");

                // Toggle collapsed class and icon
                if (targetSection.classList.contains("show")) {
                    button.classList.remove("collapsed");
                    icon.innerHTML = '<i class="fa-solid fa-caret-up"></i>';
                } else {
                    button.classList.add("collapsed");
                    icon.innerHTML = '<i class="fa-solid fa-caret-down"></i>';
                }
            });
        });
    });
</script>