<?php
if(session_status() === PHP_SESSION_NONE) session_start();
if ($_SESSION['role'] !== 'resident') header("Location: ../login.php");

/** @var mysqli $conn */
require_once '../config/db.php'; 
include '../includes/header.php';

$my_id = $_SESSION['resident_id'];
?>

<link rel="stylesheet" href="../assets/css/res_list.css">

<div class="res-list-container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h3 class="res-list-title">My Establishment Requests</h3>
            <p class="text-muted mb-0">Track the status and history of your applications.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="submit_request.php" class="res-list-btn-new">
                <i class="bi bi-plus-lg"></i> New Request
            </a>
        </div>
    </div>

    <div class="res-list-card">
        <div class="table-responsive">
            <table class="table res-list-table align-middle">
                <thead>
                    <tr>
                        <th>Date Submitted</th>
                        <th>Establishment Type</th>
                        <th>Attachments</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Admin Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = $conn->query("SELECT * FROM establishment_requests WHERE resident_id = '$my_id' ORDER BY date_submitted DESC");
                    
                    if ($res && $res->num_rows > 0):
                        while($row = $res->fetch_assoc()): 
                            $status = $row['status'];
                            $badge_class = "res-status-pending";
                            if($status == 'Approved') $badge_class = "res-status-approved";
                            if($status == 'Rejected') $badge_class = "res-status-rejected";
                        ?>
                            <tr>
                                <td class="res-list-date">
                                    <span class="d-block fw-bold text-dark"><?= date('M d, Y', strtotime($row['date_submitted'])) ?></span>
                                    <small class="text-muted"><?= date('h:i A', strtotime($row['date_submitted'])) ?></small>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($row['establishment_type']) ?></span>
                                </td>
                                <td>
                                    <a href="../assets/uploads/<?= $row['image_path'] ?>" target="_blank" class="btn bms-btn-view btn-sm">
                                        <i class="bi bi-file-earmark-image"></i> Letter
                                    </a>
                                    <a href="../assets/uploads/<?= $row['validid'] ?>" target="_blank" class="btn bms-btn-view btn-sm">
                                        <i class="bi bi-file-earmark-image"></i> Valid ID
                                    </a>
                                </td>
                                <td class="res-list-loc">
                                    <span class="text-truncate d-inline-block" style="max-width: 200px;">
                                        <?= htmlspecialchars($row['location']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="res-status-badge <?= $badge_class ?>">
                                        <?= $status ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="res-list-remarks">
                                        <?= $row['remarks'] ? htmlspecialchars($row['remarks']) : '<span class="text-muted small italic">Waiting for review...</span>' ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; 
                    else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="res-list-empty">
                                    <i class="bi bi-folder2-open d-block mb-2"></i>
                                    <p class="mb-0">You haven't submitted any requests yet.</p>
                                    <a href="submit_request.php" class="btn btn-link text-primary p-0">Click here to start.</a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>