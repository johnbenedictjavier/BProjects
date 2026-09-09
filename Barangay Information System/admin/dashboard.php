<?php
session_start();
if ($_SESSION['role'] !== 'admin') header("Location: ../login.php");
/** @var mysqli $conn */
require_once '../config/db.php';
include '../includes/header.php';

$total_residents = $conn->query("SELECT COUNT(*) FROM residents")->fetch_row()[0] ?? 0;
$Oresidents = $conn->query("SELECT COUNT(*) FROM resident_occupants")->fetch_row()[0] ?? 0;
$allresidents = $total_residents + $Oresidents;
$pending_requests = $conn->query("SELECT COUNT(*) FROM establishment_requests WHERE status='Pending'")->fetch_row()[0] ?? 0;
$total_announcements = $conn->query("SELECT COUNT(*) FROM announcements")->fetch_row()[0] ?? 0;
?>

<div class="bms-dashboard-header mb-4">
    <h3 class="fw-bold mb-1">Admin Dashboard</h3>
    <p class="text-muted small">Overview of community statistics and recent activity.</p>
</div>

<div class="row g-4 mb-5">
    
    <div class="col-md-6 col-lg-4"><a href="residents.php" style="text-decoration: none;">
        <div class="card bms-card bms-stat-card border-0 shadow-sm">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="bms-icon-box bms-bg-emerald-soft text-success me-3">
                    <i class="bi bi-people-fill fs-4"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small fw-bold text-uppercase">Total Residents</h6>
                    <h2 class="fw-bold mb-0"><?= number_format($allresidents) ?><span class="badge rounded-pill bg-secondary ms-1" style="font-size: 0.65rem; padding: 4px 8px;">
                    <?= number_format($total_residents) ?> heads
                    </span></h2>
                </div>
            </div>
        </div></a>
    </div>

    <div class="col-md-6 col-lg-4"><a href="requests.php" style="text-decoration: none;">
        <div class="card bms-card bms-stat-card border-0 shadow-sm">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="bms-icon-box bms-bg-yellow-soft text-warning me-3">
                    <i class="bi bi-file-earmark-text-fill fs-4"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small fw-bold text-uppercase">Pending Requests</h6>
                    <h2 class="fw-bold mb-0"><?= number_format($pending_requests) ?></h2>
                </div>
            </div>
        </div></a>
    </div>

    <div class="col-md-6 col-lg-4"><a href="announcements.php" style="text-decoration: none;">
        <div class="card bms-card bms-stat-card border-0 shadow-sm">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="bms-icon-box bms-bg-blue-soft text-primary me-3">
                    <i class="bi bi-megaphone-fill fs-4"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small fw-bold text-uppercase">Announcements</h6>
                    <h2 class="fw-bold mb-0"><?= number_format($total_announcements) ?></h2>
                </div>
            </div>
        </div>
    </div></a>
</div>

<div class="row">
    <div class="col-12">
        <div class="card bms-card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Recent Announcements</h5>
                <a href="announcements.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table bms-table align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Title</th>
                                <th>Category</th>
                                <th>Date Posted</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res = $conn->query("SELECT * FROM announcements ORDER BY date_posted DESC LIMIT 5");
                            if($res->num_rows == 0): ?>
                                <tr><td colspan="3" class="text-center p-4 text-muted">No recent announcements.</td></tr>
                            <?php endif; ?>

                            <?php while($row = $res->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark"><?= $row['title'] ?></td>
                                    <td>
                                        <span class="badge rounded-pill px-3 bg-info-subtle text-info border border-info">
                                            <?= $row['category'] ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        <?= date('M d, Y', strtotime($row['date_posted'])) ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>