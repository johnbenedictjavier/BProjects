<?php
if(session_status() === PHP_SESSION_NONE) session_start();
if ($_SESSION['role'] !== 'resident') header("Location: ../login.php");

/** @var mysqli $conn */
require_once '../config/db.php'; 

$msg = ""; $msg_type = "";

if (isset($_POST['submit_request'])) {
    $resident_id = $_SESSION['resident_id'];
    
    $est_id = mysqli_real_escape_string($conn, $_POST['establishment_id']);
    $checkRes = $conn->query("SELECT resident_id FROM residents WHERE resident_id = '$resident_id'");
    
    if ($checkRes->num_rows > 0) {
        $est_query = $conn->query("SELECT * FROM establishments WHERE establishment_id = '$est_id'");
        $est_data = $est_query->fetch_assoc();

        if ($est_data) {
            $est_type = mysqli_real_escape_string($conn, $est_data['type']);
            $location = mysqli_real_escape_string($conn, $est_data['location']);

            $image_name = "";
            if (!empty($_FILES['image']['name'])) {
                $image_name = time() . '_' . $_FILES['image']['name'];
                move_uploaded_file($_FILES['image']['tmp_name'], "../assets/uploads/" . $image_name);
            }

            $validid = "";
            if (!empty($_FILES['valid']['name'])) {
                $validid = time() . '_' . $_FILES['valid']['name'];
                move_uploaded_file($_FILES['valid']['tmp_name'], "../assets/uploads/" . $validid);
            }

            $sql = "INSERT INTO establishment_requests 
                    (resident_id, establishment_id, establishment_type, location, image_path, validid, status) 
                    VALUES 
                    ('$resident_id', '$est_id', '$est_type', '$location', '$image_name','$validid', 'Pending')";

            if ($conn->query($sql)) {
                header("Location: submit_request.php?success=1");                
                exit;
            } else {
                $msg = "Error saving request: " . $conn->error;
                $msg_type = "danger";
            }
        }
    } else {
        $msg = "Profile Error: Your Resident ID ($resident_id) does not exist in the official records.";
        $msg_type = "danger";
    }
}

include '../includes/header.php';
?>

<style>
    :root { --primary-blue: #0d6efd; --soft-bg: #f8f9fa; }
    body { background-color: #f4f7f6; }
    
    .market-card { 
        transition: all 0.3s ease; 
        border: none; 
        border-radius: 15px; 
        background: #fff; 
        overflow: hidden;
    }
    .market-card:hover { 
        transform: translateY(-10px); 
        box-shadow: 0 15px 30px rgba(0,0,0,0.08) !important; 
    }
    .market-img-container { height: 180px; position: relative; background: #eee; }
    .market-img { width: 100%; height: 100%; object-fit: cover; }
    
    .status-badge {
        position: absolute; top: 12px; right: 12px;
        padding: 5px 12px; border-radius: 20px;
        font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
    }
    .badge-occupied { background: rgba(220, 53, 69, 0.9); color: white; }
    .badge-available { background: rgba(25, 135, 84, 0.9); color: white; }

    .modal-content { border-radius: 20px; border: none; }
    .modal-header { border-bottom: 1px solid #eee; padding: 1.5rem; }
    .info-pill { background: #f0f7ff; color: #0056b3; padding: 12px; border-radius: 12px; border: 1px solid #d0e3ff; }
</style>

<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Establishments in Barangay Syete</h2>
        <p class="text-muted">Select an available establishment to apply for a permit.</p>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3">
            <i class="bi bi-check-circle-fill me-2"></i> Your application has been submitted and is now pending review.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if($msg != ""): ?>
        <div class="alert alert-<?= $msg_type ?> border-0 shadow-sm rounded-3"><?= $msg ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <?php
        $sql = "SELECT e.*, 
                (SELECT COUNT(*) FROM establishment_requests r WHERE r.establishment_id = e.establishment_id AND r.status = 'Approved') as approved_count
                FROM establishments e ORDER BY name ASC";
        $result = $conn->query($sql);

        while($est = $result->fetch_assoc()):
            $isInUse = ($est['approved_count'] > 0);
        ?>
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 shadow-sm market-card <?= $isInUse ? 'opacity-75' : '' ?>" 
                 <?= !$isInUse ? 'onclick=\'openModal('.json_encode($est).')\'' : '' ?>
                 style="cursor: <?= $isInUse ? 'not-allowed' : 'pointer' ?>;">
                
                <div class="market-img-container">
                    <?php if($isInUse): ?>
                        <span class="status-badge badge-occupied text-white">Occupied</span>
                    <?php else: ?>
                        <span class="status-badge badge-available">Available</span>
                    <?php endif; ?>

                    <?php if(!empty($est['image'])): ?>
                        <img src="../assets/uploads/<?= $est['image'] ?>" class="market-img">
                    <?php else: ?>
                        <div class="h-100 d-flex align-items-center justify-content-center bg-light text-muted">
                            <i class="bi bi-shop fs-1"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <small class="text-primary fw-bold text-uppercase" style="font-size: 0.7rem;"><?= $est['type'] ?></small>
                    <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($est['name']) ?></h6>
                    <p class="text-muted small mb-0"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($est['location']) ?></p>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<div class="modal fade" id="requestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" enctype="multipart/form-data" class="modal-content shadow">
            <div class="modal-header">
                <h5 class="fw-bold mb-0">Submit Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-4">
                <input type="hidden" name="establishment_id" id="form_est_id">
                
                <div class="mb-4 text-center">
                    <h4 class="fw-bold text-primary mb-1" id="display_name">---</h4>
                    <p class="text-muted" id="display_location">---</p>
                </div>

                <div class="info-pill mb-4">
                    <div class="row text-center">
                        <div class="col border-end">
                            <small class="d-block text-muted text-uppercase" style="font-size: 0.6rem;">Owner/In-Charge</small>
                            <span class="fw-bold" id="display_owner">---</span>
                        </div>
                        <div class="col">
                            <small class="d-block text-muted text-uppercase" style="font-size: 0.6rem;">Contact</small>
                            <span class="fw-bold" id="display_contact">---</span>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Letter of Intent / Description</label>
                    <input type="file" name="image" class="form-control bg-light border-0 shadow-sm" accept="image/*">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Valid ID</label>
                    <input type="file" name="valid" class="form-control bg-light border-0 shadow-sm" accept="image/*">
                </div>

                <div class="alert alert-light border-0 small text-muted">
                    <i class="bi bi-info-circle me-1"></i> By submitting, your request will be queued for Barangay approval.
                </div>
            </div>
            
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Close</button>
                <button type="submit" name="submit_request" class="btn btn-primary px-4 rounded-pill">Confirm Request</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(data) {
    document.getElementById('form_est_id').value = data.establishment_id;
    document.getElementById('display_name').innerText = data.name;
    document.getElementById('display_location').innerText = data.location;
    document.getElementById('display_owner').innerText = data.owner_name;
    document.getElementById('display_contact').innerText = data.contact;

    var myModal = new bootstrap.Modal(document.getElementById('requestModal'));
    myModal.show();
}
</script>

<?php include '../includes/footer.php'; ?>