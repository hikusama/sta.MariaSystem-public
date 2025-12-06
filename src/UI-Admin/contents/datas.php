
    <style>
        
        .dashboard-admin {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }

        header {
            text-align: center;
            margin-bottom: 30px;
        }

        .quick-actions h4,
        .attendance-overview h4 {
            margin-top: 30px;
            margin-bottom: 10px;
        }

        .action-grid {
            display: flex;
            justify-content: space-around;
            gap: 15px;
            flex-wrap: wrap;
        }

        .action {
            background: white;
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            flex: 1 1 150px;
            cursor: pointer;
            transition: 0.3s;
            border: 1px solid #888;
        }

        .action:hover {
            background-color: #eef;
        }

        .action i {
            font-size: 30px;
            margin-bottom: 10px;
        }
    </style>

<div class="dashboard-admin">
    <header>
        <h3>Exporting Data Form</h3>
        <p>Welcome to Sta. Maria Central School - SY:2025-2026</p>
    </header>

    <section class="quick-actions">
        <h4>Quick Actions</h4>
        <div class="action-grid">
            <a href="index.php?page=contents/sf1" class="action" id="SF1"><i class="fa fa-folder-open fs-2"></i><p class="text-dark">SF1</p><span style="font-size: .8rem;">School Register</span></a>
            <a href="index.php?page=contents/sf4" class="action" id="SF4"><i class="fa fa-folder-open fs-2"></i><p class="text-dark">SF4</p><span style="font-size: .8rem;">Monthly Learners movement and attendance form</span></a>
            </div>
    </section>

    
</div>