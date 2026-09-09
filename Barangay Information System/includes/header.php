<?php
if(session_status() === PHP_SESSION_NONE) session_start();
/** @var mysqli $conn */
include("../config/db.php"); 

$page = basename($_SERVER['PHP_SELF']); 
$role = $_SESSION['role'] ?? 'resident';
$user_id = $_SESSION['user_id'] ?? 0;
$resident_id = $_SESSION['resident_id'] ?? 0;

$res = null;
if ($resident_id > 0) {
    $res = $conn->query("SELECT * FROM residents WHERE resident_id = $resident_id")->fetch_assoc();
    $edu = $conn->query("SELECT * FROM resident_education WHERE resident_id = $resident_id");
    $emp = $conn->query("SELECT * FROM resident_employment WHERE resident_id = $resident_id");
    $occ = $conn->query("SELECT * FROM resident_occupants WHERE resident_id = $resident_id");
} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Management System</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .bms-sidebar-header { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem 1rem; text-align: center; border-bottom: 1px solid rgba(0,0,0,0.05); }
        .bms-sidebar-logo { width: 130px; height: 130px; object-fit: contain; }
        .bms-sidebar-header span { font-weight: 700; letter-spacing: 1px; font-size: 1.1rem; }
        details summary { list-style: none; cursor: pointer; outline: none; }
        details summary::-webkit-details-marker { display: none; }
        summary.bms-nav-link { display: flex; align-items: center; justify-content: space-between; }
        summary.bms-nav-link::after { content: "\F282"; font-family: "bootstrap-icons"; font-size: 0.7rem; transition: transform 0.2s; }
        details[open] summary.bms-nav-link::after { transform: rotate(180deg); }
        .bms-submenu-box { background: rgba(0, 0, 0, 0.05); border-radius: 4px; margin: 0 10px 10px 10px; padding: 5px 0; }
        .ps-4-5 { padding-left: 2.8rem !important; }
        .bms-user-pill { cursor: pointer; padding: 5px 15px; border-radius: 50px; transition: 0.2s; }
        .bms-user-pill:hover { background: rgba(0,0,0,0.05); }

        .section-label { background: #f8f9fa; font-weight: bold; padding: 5px 10px; margin-top: 15px; border-left: 4px solid #198754; font-size: 11pt; }
        .data-row { margin-bottom: 5px; font-size: 10pt; }
        .data-label { font-weight: 600; color: #555; width: 130px; display: inline-block; }
    </style>
</head>
<body>

<aside id="bms-sidebar">
    <div class="bms-sidebar-header">
        <img src="../assets/uploads/brgylogo2.png" alt="Barangay Logo" class="bms-sidebar-logo">
        <span style="font-size: .9rem;">Barangay Registry <br> System</span>
    </div>

    <nav class="mt-3">
        <?php 
            $is_res_group = ($page == 'residents.php' || $page == 'report.php' || $page == 'registerresident.php');
            $is_req_group = ($page == 'establishments.php' || $page == 'requests.php');
            $is_my_req_group = ($page == 'submit_request.php' || $page == 'my_requests.php');
        ?>

        <?php if($role == 'admin'): ?>
            <a href="dashboard.php" class="bms-nav-link <?= $page=='dashboard.php'?'bms-active':'' ?>">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a>
            <details <?= $is_res_group ? 'open' : '' ?>>
                <summary class="bms-nav-link <?= $is_res_group ? 'bms-active' : '' ?>">
                    <span><i class="bi bi-people"></i> Residents</span>
                </summary>
                <div class="bms-submenu-box">
                    <a href="residents.php" class="bms-nav-link ps-4-5 <?= $page=='residents.php'?'text-success fw-bold':'' ?>">Residents List</a>
                    <!-- <a href="report.php" class="bms-nav-link ps-4-5 <?= $page=='report.php'?'text-success fw-bold':'' ?>">Demographic Reports</a> -->
                    <a href="registerresident.php" class="bms-nav-link ps-4-5 <?= $page=='registerresident.php'?'text-success fw-bold':'' ?>">Register Resident</a>
                </div>
            </details>
            <a href="announcements.php" class="bms-nav-link <?= $page=='announcements.php'?'bms-active':'' ?>">
                <i class="bi bi-megaphone"></i> Announcements
            </a>
            <a href="calendar.php" class="bms-nav-link <?= $page=='calendar.php'?'bms-active':'' ?>">
                <i class="bi bi-calendar"></i> Calendar
            </a>
            <!-- <a href="analytics.php" class="bms-nav-link <?= $page=='analytics.php'?'bms-active':'' ?>">
                <i class="bi bi-lightbulb"></i> Analytics
            </a> -->
            <details <?= $is_req_group ? 'open' : '' ?>>
                <summary class="bms-nav-link <?= $is_req_group ? 'bms-active' : '' ?>">
                    <span><i class="bi bi-file-earmark-check"></i> Requests</span>
                </summary>
                <div class="bms-submenu-box">
                    <a href="establishments.php" class="bms-nav-link ps-4-5 <?= $page=='establishments.php'?'text-success fw-bold':'' ?>">Establishments</a>
                    <a href="requests.php" class="bms-nav-link ps-4-5 <?= $page=='requests.php'?'text-success fw-bold':'' ?>">Request List</a>
                </div>
            </details>

        <?php else: ?>
            <a href="r_dashboard.php" class="bms-nav-link <?= $page=='r_dashboard.php'?'bms-active':'' ?>">
                <i class="bi bi-house-door"></i> Home
            </a>
            <a href="r_announcements.php" class="bms-nav-link <?= $page=='r_announcements.php'?'bms-active':'' ?>">
                <i class="bi bi-megaphone"></i> Barangay Announcements
            </a>
            <details <?= $is_my_req_group ? 'open' : '' ?>>
                <summary class="bms-nav-link <?= $is_my_req_group ? 'bms-active' : '' ?>">
                    <span><i class="bi bi-journal-text"></i> My Requests</span>
                </summary>
                <div class="bms-submenu-box">
                    <a href="submit_request.php" class="bms-nav-link ps-4-5 <?= $page=='submit_request.php'?'text-success fw-bold':'' ?>">New Request</a>
                    <a href="my_requests.php" class="bms-nav-link ps-4-5 <?= $page=='my_requests.php'?'text-success fw-bold':'' ?>">Request History</a>
                </div>
            </details>
        <?php endif; ?>

        <a href="#" class="bms-nav-link bms-logout-btn text-danger" data-bs-toggle="modal" data-bs-target="#bmsLogoutModal">
            <i class="bi bi-box-arrow-left"></i> Sign Out
        </a>
    </nav>
</aside>

<main class="bms-main-wrapper">
    <header class="bms-top-nav">
        <button class="btn btn-light d-md-none" onclick="bmsToggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        <div class="d-none d-md-block text-muted small fw-medium">
            System Dashboard &bull; <?= date('l, M d Y') ?>
        </div>

        <div class="bms-user-pill" data-bs-toggle="modal" data-bs-target="#profileModal">
            <i class="bi bi-person-circle text-success"></i>
            <span><?= ($role == 'resident') 
    ? strtoupper($res['last_name'] . ', ' . $res['first_name'] . ' ' . $res['middle_name']) 
    : 'Admin' ?></span>
            <i class="bi bi-caret-down-fill small ms-1 text-muted"></i>
        </div>
    </header>

    <div class="bms-page-content">

<div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="bi bi-person-vcard me-2"></i> My Profile & Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <ul class="nav nav-tabs px-3 pt-2" id="profileTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#census-tab">Census Record</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#settings-tab">Account Settings</button>
                    </li>
                </ul>
                <div class="tab-content p-4">
                    <div class="tab-pane fade show active" id="census-tab">
                        <?php if($res): ?>
                            <div class="alert alert-warning py-2 small">
                                <i class="bi bi-info-circle"></i> To edit residency or personal info, please visit the Barangay Hall.
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="data-row"><span class="data-label">FULL NAME:</span> <span class="fw-bold"><?= strtoupper($res['last_name'] . ', ' . $res['first_name'] . ' ' . $res['middle_name']) ?></span></div>
                                    <div class="data-row"><span class="data-label">ADDRESS:</span> <span><?= $res['present_address'] ?></span></div>
                                </div>
                            </div>
                            
                            <div class="section-label">I. Personal Details</div>
                            <div class="row mt-2 small">
                                <div class="col-6 mb-1"><strong>Gender:</strong> <?= $res['gender'] ?></div>
                                <div class="col-6 mb-1"><strong>Birthdate:</strong> <?= date('M d, Y', strtotime($res['birthdate'])) ?></div>
                                <div class="col-6 mb-1"><strong>Civil Status:</strong> <?= $res['civil_status'] ?></div>
                                <div class="col-6 mb-1"><strong>Contact:</strong> <?= $res['contact_number'] ?></div>
                            </div>

                            <div class="section-label">II. Household Occupants</div>
                            <table class="table table-sm table-bordered mt-2 small">
                                <thead class="table-light"><tr><th>Name</th><th>Relation</th><th>Occupation</th></tr></thead>
                                <tbody>
                                    <?php while($o = $occ->fetch_assoc()): ?>
                                    <tr><td><?= $o['full_name'] ?></td><td><?= $o['position_in_family'] ?></td><td><?= $o['occupation'] ?></td></tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-center text-muted">No resident record found linked to this account.</p>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="settings-tab">
                        <form action="../includes/process_profile_update.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Username</label>
                                <input type="text" name="username" class="form-control" value="<?= $_SESSION['username'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">New Password</label>
                                <input type="password" name="new_password" class="form-control" placeholder="Leave blank to keep current">
                            </div>
                            <div class="p-3 bg-light border rounded">
                                <label class="form-label text-danger fw-bold small">Current Password Verification</label>
                                <p class="text-muted" style="font-size: 0.8rem;">You must provide your current password to save changes.</p>
                                <input type="password" name="current_password" class="form-control" placeholder="Confirm Current Password" required>
                            </div>
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-success px-4">Update Account</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="bmsLogoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bms-modal-round shadow">
            <div class="modal-body text-center p-5">
                <div class="bms-icon-circle mb-4 text-danger" style="font-size: 3rem;">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h4 class="fw-bold">Logout Confirmation</h4>
                <p class="text-muted">Are you sure you want to end your session?</p>
                <div class="d-flex justify-content-center gap-2 mt-4">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <a href="../login.php" class="btn btn-danger px-4">Yes, Logout</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function bmsToggleSidebar() {
        document.getElementById('bms-sidebar').classList.toggle('bms-mobile-active');
    }
</script>
</body>
</html>