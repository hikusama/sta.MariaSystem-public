<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('admin', 2);

if ($result['res']) {
    header($result['uri']);
    exit;
}
?>
<style>
.dashboard-admin {
    max-width: 1200px;
    margin: auto;
    padding: 20px;
    overflow-y: scroll;
    height: 90vh;
}

.header-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
    text-align: center;
}

.header-card h3 {
    font-size: 2rem;
    margin-bottom: 10px;
    font-weight: 600;
}

.header-card p {
    font-size: 1.1rem;
    opacity: 0.9;
    margin-bottom: 0;
}

.section-header {
    position: relative;
    padding-left: 15px;
    margin-bottom: 25px;
}

.section-header::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 4px;
    background: linear-gradient(180deg, #4e73df, #224abe);
    border-radius: 2px;
}

.section-header h4 {
    color: #333;
    font-weight: 600;
    margin-bottom: 5px;
}

.action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.action-card {
    background: white;
    border: none;
    border-radius: 12px;
    padding: 25px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    text-decoration: none;
    display: block;
    position: relative;
    overflow: hidden;
    height: 100%;
}

.action-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 5px;
    background: linear-gradient(90deg, #4e73df, #224abe);
}

.action-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    text-decoration: none;
}

.action-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4e73df, #224abe);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    color: white;
    font-size: 24px;
}

.action-card h5 {
    color: #333;
    font-weight: 600;
    margin-bottom: 10px;
    font-size: 1.2rem;
}

.action-card .action-code {
    display: inline-block;
    background: linear-gradient(135deg, #4e73df, #224abe);
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: 600;
    margin-bottom: 10px;
    font-size: 1rem;
}

.action-card .action-desc {
    color: #666;
    font-size: 0.875rem;
    line-height: 1.4;
    margin-bottom: 0;
}

/* Different colors for each card */
#SF5::before { background: linear-gradient(90deg, #4e73df, #224abe); }
#SF2::before { background: linear-gradient(90deg, #1cc88a, #13855c); }
#SF8::before { background: linear-gradient(90deg, #36b9cc, #258391); }
#SF9::before { background: linear-gradient(90deg, #f6c23e, #dda20a); }
#SF10::before { background: linear-gradient(90deg, #e74a3b, #be2617); }

#SF5 .action-icon { background: linear-gradient(135deg, #4e73df, #224abe); }
#SF2 .action-icon { background: linear-gradient(135deg, #1cc88a, #13855c); }
#SF8 .action-icon { background: linear-gradient(135deg, #36b9cc, #258391); }
#SF9 .action-icon { background: linear-gradient(135deg, #f6c23e, #dda20a); }
#SF10 .action-icon { background: linear-gradient(135deg, #e74a3b, #be2617); }

#SF5 .action-code { background: linear-gradient(135deg, #4e73df, #224abe); }
#SF2 .action-code { background: linear-gradient(135deg, #1cc88a, #13855c); }
#SF8 .action-code { background: linear-gradient(135deg, #36b9cc, #258391); }
#SF9 .action-code { background: linear-gradient(135deg, #f6c23e, #dda20a); }
#SF10 .action-code { background: linear-gradient(135deg, #e74a3b, #be2617); }

/* Responsive adjustments */
@media (max-width: 768px) {
    .action-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }
    
    .header-card {
        padding: 20px;
    }
    
    .header-card h3 {
        font-size: 1.5rem;
    }
}

@media (max-width: 576px) {
    .action-grid {
        grid-template-columns: 1fr;
    }
    
    .dashboard-admin {
        padding: 15px;
    }
}
</style>

<div class="dashboard-admin">
    <!-- Header Card -->
    <div class="header-card">
        <h3><i class="fa-solid fa-file-export me-2"></i>Exporting Data Form</h3>
        <p>Welcome to Sta. Maria Central School - SY: 2025-2026</p>
    </div>

    <!-- Quick Actions Section -->
    <div class="quick-actions">
        <div class="section-header">
            <h4>School Forms</h4>
            <p class="text-muted">Select a form to export or view data</p>
        </div>
        
        <div class="action-grid">
            <a href="index.php?page=contents/sf1" class="action-card" id="SF1">
                <div class="action-icon">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
                <span class="action-code">SF1</span>
                <h5>School Register</h5>
                <p class="action-desc">Report on Promotion and Level of Progress & Achievement</p>
            </a>
            
            <a href="index.php?page=contents/sf4" class="action-card" id="SF4">
                <div class="action-icon">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
                <span class="action-code">SF4</span>
                <h5>Daily Attendance</h5>
                <p class="action-desc">Monthly Learners movement and attendance form</p>
            </a>
    </section>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effect to all cards
    const actionCards = document.querySelectorAll('.action-card');
    
    actionCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Add click animation
    actionCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Add click feedback
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });
});
</script>