<?php
if(session_status() === PHP_SESSION_NONE) session_start();
if ($_SESSION['role'] !== 'resident') header("Location: ../login.php");

/** @var mysqli $conn */
require_once '../config/db.php';
include '../includes/header.php';

date_default_timezone_set('Asia/Manila'); 
$hour = date('H');
$greeting = ($hour < 12) ? "Good Morning" : (($hour < 17) ? "Good Afternoon" : "Good Evening");

$user_id = $_SESSION['user_id'];
$count_announcements = $conn->query("SELECT COUNT(*) FROM announcements")->fetch_row()[0] ?? 0;
$count_requests = $conn->query("SELECT COUNT(*) FROM establishment_requests WHERE resident_id = '$user_id'")->fetch_row()[0] ?? 0;

$res = $conn->query("SELECT resident_id FROM users WHERE user_id = $user_id")->fetch_assoc();
$resident_id = $res['resident_id'];
$resid = $conn->query("select * from residents where resident_id = '$resident_id'")->fetch_assoc();
?>

<style>
    :root {
        --res-primary: #4361ee;
        --res-secondary: #3f37c9;
        --res-bg: #f8f9fa;
        --glass: rgba(255, 255, 255, 0.9);
    }

    body { background-color: var(--res-bg); font-family: 'Inter', sans-serif; }

    .res-dash-wrapper { padding: 20px 0; }

    .welcome-banner {
        background: linear-gradient(135deg, var(--res-primary), var(--res-secondary));
        border-radius: 20px;
        padding: 40px;
        color: white;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(67, 97, 238, 0.3);
        position: relative;
        overflow: hidden;
    }
    .welcome-banner::after {
        content: '';
        position: absolute;
        top: -50px; right: -50px;
        width: 200px; height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .stat-card {
        background: white;
        border: none;
        border-radius: 16px;
        padding: 24px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        display: flex;
        align-items: center;
        gap: 20px;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 20px rgba(0,0,0,0.08);
    }
    .icon-box {
        width: 60px; height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .announcement-item {
        background: white;
        border-radius: 16px;
        border-left: 5px solid var(--res-primary);
        padding: 20px;
        margin-bottom: 15px;
        transition: 0.2s;
    }
    .announcement-item:hover {
        background: #fdfdfd;
        border-left-width: 8px;
    }
    .category-tag {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        color: var(--res-primary);
        background: rgba(67, 97, 238, 0.1);
        padding: 4px 12px;
        border-radius: 50px;
    }

    .action-btn {
        border-radius: 12px;
        padding: 15px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: 0.3s;
        text-decoration: none;
    }
    .btn-main { background: var(--res-primary); color: white; border: none; }
    .btn-main:hover { background: var(--res-secondary); transform: scale(1.02); color: white; }
    
    .btn-sub { background: white; color: var(--res-primary); border: 2px solid #eee; }
    .btn-sub:hover { border-color: var(--res-primary); color: var(--res-primary); }

    .help-card {
        background: #1e293b;
        color: white;
        border-radius: 16px;
        padding: 20px;
    }
</style>

<div class="container res-dash-wrapper">
    <div class="welcome-banner">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="fw-bold mb-2"><?= $greeting ?>, <?= $resid['first_name']; ?>! 👋</h1>
                <p class="opacity-75 mb-0">You have <?= $count_announcements ?> new community updates to check today.</p>
            </div>
            <div class="col-md-4 text-md-end d-none d-md-block">
                <i class="bi bi-shield-check" style="font-size: 4rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="stat-card">
                <div class="icon-box bg-primary text-white">
                    <i class="bi bi-megaphone"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0"><?= number_format($count_announcements); ?></h3>
                    <p class="text-muted mb-0">New Announcements</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card">
                <div class="icon-box bg-success text-white">
                    <i class="bi bi-file-earmark-check"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0"><?= number_format($count_requests); ?></h3>
                    <p class="text-muted mb-0">Active Requests</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h4 class="fw-bold mb-0">Recent Announcements</h4>
                <a href="r_announcements.php" class="text-decoration-none small fw-bold">View All</a>
            </div>

            <?php
            $res = $conn->query("SELECT * FROM announcements ORDER BY date_posted DESC LIMIT 3");
            if ($res && $res->num_rows > 0):
                while($a = $res->fetch_assoc()): ?>
                    <div class="announcement-item shadow-sm">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="category-tag"><?= htmlspecialchars($a['category']) ?></span>
                            <span class="text-muted small"><i class="bi bi-clock me-1"></i> <?= date('M d, Y', strtotime($a['date_posted'])) ?></span>
                        </div>
                        <h5 class="fw-bold text-dark"><?= htmlspecialchars($a['title']) ?></h5>
                        <p class="text-muted mb-0" style="font-size: 0.95rem; line-height: 1.6;">
                            <?= nl2br(htmlspecialchars($a['content'])) ?>
                        </p>
                    </div>
                <?php endwhile; 
            else: ?>
                <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="mb-3 opacity-50" alt="empty">
                    <p class="text-muted">No community updates at the moment.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Quick Services</h5>
                    <p class="small text-muted mb-4">Select a service below to process your requirements.</p>
                    
                    <div class="d-grid gap-3">
                        <a href="submit_request.php" class="action-btn btn-main">
                            <i class="bi bi-plus-circle"></i> New Request
                        </a>
                        <a href="my_requests.php" class="action-btn btn-sub">
                            <i class="bi bi-clock-history"></i> Track Requests
                        </a>
                    </div>
                </div>
            </div>

            <div class="help-card shadow-lg">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-2 text-warning">
                        <i class="bi bi-patch-question"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-bold">Need Assistance?</h6>
                        <p class="small mb-0 opacity-75">Visit the Barangay Office from 8:00 AM to 5:00 PM for walk-in inquiries.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>