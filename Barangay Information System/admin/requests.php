<?php
session_start();

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

/** @var mysqli $conn */
include("../config/db.php");

if (isset($_POST['update_request'])) {

    $req_id = $_POST['request_id'];
    $status = $_POST['status'];
    $remarks = $_POST['remarks'];

    $res = $conn->query("SELECT establishment_id FROM establishment_requests WHERE request_id = '$req_id'");

    if ($res && $res->num_rows > 0) {

        $row = $res->fetch_assoc();
        $est_id = $row['establishment_id'];

        if ($status == 'Approved') {

            $conn->query("UPDATE establishments 
                          SET status = 'Inactive' 
                          WHERE establishment_id = '$est_id'");

        } else if ($status == 'Done') {

            $conn->query("UPDATE establishments 
                          SET status = 'Active' 
                          WHERE establishment_id = '$est_id'");
        }
    }

    $conn->query("UPDATE establishment_requests 
                  SET status = '$status', remarks = '$remarks' 
                  WHERE request_id = '$req_id'");

    header("Location: requests.php?updated=1");
    exit;
}

include("../includes/header.php");
?>

<div class="bms-page-header mb-4">
    <h3 class="fw-bold mb-1">Establishment Requests</h3>
    <p class="text-muted small">Review and manage business establishment permits and applications.</p>
</div>

<div class="row mb-3 g-2 px-4 pt-3">
    <div class="col-md-4">
        <div class="input-group shadow-sm rounded-pill overflow-hidden">
        <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-search"></i></span>
        <input type="text" id="liveSearch" class="form-control border-0 py-2" placeholder="Search name or type..."></div>
    </div>
    <div class="col-md-3">
        <?php
        $type_f = $_GET['type'] ?? 'all';
        $status_f = $_GET['status'] ?? 'all';
        
        $types_query = $conn->query("SELECT DISTINCT establishment_type FROM establishment_requests");
        ?>
        <select class="form-select border-0 shadow-sm rounded-pill py-2" onchange="location.href='?type=' + this.value + '&status=<?= $status_f ?>'">
            <option value="all" <?= $type_f == 'all' ? 'selected' : '' ?>>All Types</option>
            <?php while($t = $types_query->fetch_assoc()): ?>
                <option value="<?= $t['establishment_type'] ?>" <?= $type_f == $t['establishment_type'] ? 'selected' : '' ?>>
                    <?= $t['establishment_type'] ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="col-md-3">
        <select class="form-select border-0 shadow-sm rounded-pill py-2" onchange="location.href='?type=<?= $type_f ?>&status=' + this.value">
            <option value="all" <?= $status_f == 'all' ? 'selected' : '' ?>>All Statuses</option>
            <option value="Pending" <?= $status_f == 'Pending' ? 'selected' : '' ?>>Pending</option>
            <option value="Approved" <?= $status_f == 'Approved' ? 'selected' : '' ?>>Approved</option>
            <option value="Rejected" <?= $status_f == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
            <option value="Done" <?= $status_f == 'Done' ? 'selected' : '' ?>>Done</option>
        </select>
    </div>
    <div class="col-md-2">
        <a href="requests.php" class="btn btn-outline-secondary w-100 input-group shadow-sm rounded-pill overflow-hidden">Reset</a>
    </div>
</div>

<div class="card bms-card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table bms-table align-middle mb-0" id="resTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Resident Name</th>
                        <th>Type & Location</th>
                        <th>Attachment</th>
                        <th>Current Status</th>
                        <th class="text-center">Manage Request</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // 2. MODIFIED QUERY FOR PHP FILTERS
                    $sql = "SELECT r.*, res.first_name, res.last_name FROM establishment_requests r 
                            JOIN residents res ON r.resident_id = res.resident_id";
                    
                    $where = [];
                    if ($type_f !== 'all') $where[] = "r.establishment_type = '" . $conn->real_escape_string($type_f) . "'";
                    if ($status_f !== 'all') $where[] = "r.status = '" . $conn->real_escape_string($status_f) . "'";
                    
                    if (count($where) > 0) {
                        $sql .= " WHERE " . implode(" AND ", $where);
                    }
                    
                    $sql .= " ORDER BY r.date_submitted DESC";
                    $res = $conn->query($sql);
                    
                    if($res->num_rows == 0): ?>
                        <tr><td colspan="5" class="text-center p-5 text-muted">No requests found.</td></tr>
                    <?php endif; ?>

                    <?php while($row = $res->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark"><?= $row['last_name'] . ", " . $row['first_name'] ?></div>
                                <small class="text-muted">ID: #<?= $row['resident_id'] ?></small>
                            </td>
                            <td>
                                <div class="fw-medium"><?= $row['establishment_type'] ?></div>
                                <div class="text-muted x-small"><i class="bi bi-geo-alt"></i> <?= $row['location'] ?></div>
                            </td>
                            <td>
                                <a href="../assets/uploads/<?= $row['image_path'] ?>" target="_blank" class="btn bms-btn-view btn-sm">
                                    <i class="bi bi-file-earmark-image"></i> Letter
                                </a>
                                <a href="../assets/uploads/<?= $row['validid'] ?>" target="_blank" class="btn bms-btn-view btn-sm">
                                    <i class="bi bi-file-earmark-image"></i> Valid ID
                                </a>
                            </td>
                            <td>
                                <?php 
                                    $statusClass = "bg-secondary-subtle text-secondary border-secondary";
                                    if($row['status'] == 'Approved' || $row['status'] == 'Done') $statusClass = "bg-success-subtle text-success border-success";
                                    if($row['status'] == 'Rejected') $statusClass = "bg-danger-subtle text-danger border-danger";
                                ?>
                                <span class="badge rounded-pill px-3 border <?= $statusClass ?>">
                                    <?= $row['status'] ?>
                                </span>
                            </td>
                            <td class="pe-4">
                                <form method="POST" class="bms-update-form d-flex gap-2">
                                    <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                                    <textarea name="remarks" class="form-control bms-input-sm" placeholder="Admin remarks..." rows="2"><?= htmlspecialchars($row['remarks']) ?></textarea>
                                    
                                    <select name="status" class="form-select bms-select-sm">
                                        <option value="Pending" <?= $row['status']=='Pending'?'selected':'' ?>>Pending</option>
                                        <option value="Approved" <?= $row['status']=='Approved'?'selected':'' ?>>Approve</option>
                                        <option value="Rejected" <?= $row['status']=='Rejected'?'selected':'' ?>>Reject</option>
                                        <option value="Done" <?= $row['status']=='Done'?'selected':'' ?>>Done</option>
                                    </select>
                                    
                                    <button type="submit" name="update_request" class="btn bms-btn-save btn-sm">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    $("#liveSearch").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#resTable tbody tr").filter(function() {
            // Searches name and establishment type within the row
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>

<?php include("../includes/footer.php"); ?>