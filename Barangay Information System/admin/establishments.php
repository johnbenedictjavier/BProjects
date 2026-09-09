<?php
session_start();
if ($_SESSION['role'] !== 'admin') header("Location: ../index.php");
/** @var mysqli $conn */
include("../config/db.php");

if (isset($_POST['add_establishment'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $owner = mysqli_real_escape_string($conn, $_POST['owner_name']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $image_name = "";
    if (!empty($_FILES['image']['name'])) {
        $image_name = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../assets/uploads/" . $image_name);
    }

    $query = "INSERT INTO establishments (name, type, owner_name, location, contact, status, image) 
              VALUES ('$name', '$type', '$owner', '$location', '$contact', '$status', '$image_name')";
    
    if ($conn->query($query)) {
        header("Location: establishments.php?success=1");
        exit;
    }
}

if (isset($_POST['edit_establishment'])) {
    $id = $_POST['establishment_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $owner = mysqli_real_escape_string($conn, $_POST['owner_name']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    if (!empty($_FILES['image']['name'])) {
        $image_name = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../assets/uploads/" . $image_name);
        $conn->query("UPDATE establishments SET name='$name', type='$type', owner_name='$owner', location='$location', contact='$contact', status='$status', image='$image_name' WHERE establishment_id='$id'");
    } else {
        $conn->query("UPDATE establishments SET name='$name', type='$type', owner_name='$owner', location='$location', contact='$contact', status='$status' WHERE establishment_id='$id'");
    }

    if ($status == 'Active') {
        $conn->query("UPDATE establishment_requests 
                      SET status = 'Done' 
                      WHERE establishment_id = '$id' AND status = 'Approved'");

    } elseif ($status == 'Inactive') {
        $remarks = mysqli_real_escape_string($conn, "Sorry, the establishment is now inactive for a while.");
        
        $conn->query("UPDATE establishment_requests 
                      SET status = 'Rejected', 
                          remarks = '$remarks' 
                      WHERE establishment_id = '$id' 
                      AND status NOT IN ('Done', 'Approved')");
    }

    header("Location: establishments.php?updated=1");
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $res = $conn->query("SELECT image FROM establishments WHERE establishment_id = $id");
    $row = $res->fetch_assoc();
    if(!empty($row['image']) && file_exists("../assets/uploads/" . $row['image'])) {
        unlink("../assets/uploads/" . $row['image']);
    }
    $conn->query("DELETE FROM establishments WHERE establishment_id = $id");
    header("Location: establishments.php?deleted=1");
    exit;
}

include("../includes/header.php");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="bms-page-header">
        <h3 class="fw-bold mb-1">Registered Establishments</h3>
        <p class="text-muted small">Manage official business records in the Barangay.</p>
    </div>
    <button class="btn btn-primary shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addEstablishmentModal">
        <i class="bi bi-plus-lg me-2"></i> Add Establishment
    </button>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-5">
        <div class="input-group shadow-sm rounded-pill overflow-hidden">
            <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-search"></i></span>
            <input type="text" id="searchInput" class="form-control border-0 py-2" placeholder="Search by name, owner, or location...">
        </div>
    </div>
    <div class="col-md-3">
        <select id="statusFilter" class="form-select border-0 shadow-sm rounded-pill py-2">
            <option value="">All Status</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>
    </div>
    <div class="col-md-4">
        <select id="typeFilter" class="form-select border-0 shadow-sm rounded-pill py-2">
            <option value="">All Types</option>
            <?php 
            $types_res = $conn->query("SELECT DISTINCT type FROM establishments ORDER BY type ASC");
            while($t = $types_res->fetch_assoc()): ?>
                <option value="<?= $t['type'] ?>"><?= $t['type'] ?></option>
            <?php endwhile; ?>
        </select>
    </div>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        New establishment added successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card bms-card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table bms-table align-middle mb-0" id="establishmentTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Establishment Name</th>
                        <th>Type</th>
                        <th>Owner</th>
                        <th>Contact & Location</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = $conn->query("SELECT * FROM establishments ORDER BY date_added DESC");
                    if($res->num_rows == 0): ?>
                        <tr class="no-data"><td colspan="6" class="text-center p-5 text-muted">No establishments registered yet.</td></tr>
                    <?php endif; ?>

                    <?php while($row = $res->fetch_assoc()): ?>
                        <tr class="establishment-row">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <?php if(!empty($row['image'])): ?>
                                        <img src="../assets/uploads/<?= $row['image'] ?>" class="rounded me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-bold text-primary establishment-name"><?= $row['name'] ?></div>
                                        <small class="text-muted">Registered: <?= date('M d, Y', strtotime($row['date_added'])) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border type-label"><?= $row['type'] ?></span></td>
                            <td><div class="fw-medium owner-name"><?= $row['owner_name'] ?></div></td>
                            <td>
                                <div class="small"><i class="bi bi-telephone me-1"></i> <?= $row['contact'] ?></div>
                                <div class="text-muted x-small text-truncate location-text" style="max-width: 150px;"><i class="bi bi-geo-alt me-1"></i> <?= $row['location'] ?></div>
                            </td>
                            <td>
                                <span class="badge rounded-pill px-3 status-badge <?= $row['status'] == 'Active' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $row['status'] ?>
                                </span>
                            </td>
                            <td class="text-center pe-4">
                                <button class="btn btn-outline-primary btn-sm rounded-circle edit-btn" 
                                        data-id="<?= $row['establishment_id'] ?>"
                                        data-name="<?= htmlspecialchars($row['name']) ?>"
                                        data-type="<?= $row['type'] ?>"
                                        data-owner="<?= htmlspecialchars($row['owner_name']) ?>"
                                        data-contact="<?= $row['contact'] ?>"
                                        data-location="<?= htmlspecialchars($row['location']) ?>"
                                        data-status="<?= $row['status'] ?>"
                                        data-bs-toggle="modal" data-bs-target="#editModal">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="?delete=<?= $row['establishment_id'] ?>" 
                                   class="btn btn-outline-danger btn-sm rounded-circle" 
                                   onclick="return confirm('Are you sure you want to delete this record?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <tr id="noResults" style="display: none;">
                        <td colspan="6" class="text-center p-5 text-muted">No establishments match your search criteria.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addEstablishmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title d-flex align-items-center">
                    <i class="bi bi-building-add me-2"></i> Register New Establishment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Business Information</h6>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold text-muted">Business Name</label>
                            <input type="text" name="name" class="form-control form-control-lg bg-light border-0 shadow-sm" placeholder="e.g. Juan's Variety Store" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Status</label>
                            <select name="status" class="form-select form-select-lg bg-light border-0 shadow-sm">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Establishment Type</label>
                            <input type="text" name="type" class="form-control bg-light border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Upload Photo</label>
                            <input type="file" name="image" class="form-control bg-light border-0 shadow-sm" accept="image/*">
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Owner & Contact Details</h6>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-semibold text-muted">Full Name of Owner</label>
                            <input type="text" name="owner_name" class="form-control bg-light border-0 shadow-sm" placeholder="Enter owner's complete name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Contact Number</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-white border-0"><i class="bi bi-phone"></i></span>
                                <input type="text" name="contact" class="form-control bg-light border-0" placeholder="09xxxxxxxxx" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Exact Location</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-white border-0"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" name="location" class="form-control bg-light border-0" placeholder="Street / Purok" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 p-3">
                    <button type="button" class="btn btn-link text-decoration-none text-muted mx-2" data-bs-dismiss="modal">Discard</button>
                    <button type="submit" name="add_establishment" class="btn btn-primary px-5 rounded-pill shadow">
                        <i class="bi bi-check-circle me-2"></i>Save Establishment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title d-flex align-items-center">
                    <i class="bi bi-pencil-square me-2"></i> Update Records
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" name="establishment_id" id="edit_id">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold text-muted">Business Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control form-control-lg bg-light border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Current Status</label>
                            <select name="status" id="edit_status" class="form-select form-select-lg bg-light border-0 shadow-sm">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Type</label>
                            <input type="text" name="type" id="edit_type" class="form-control bg-light border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Update Image <small class="text-info ms-1">(Leave blank to keep current)</small></label>
                            <input type="file" name="image" class="form-control bg-light border-0 shadow-sm" accept="image/*">
                        </div>
                        
                        <div class="col-12 mt-4 pt-2 border-top">
                            <label class="form-label small fw-semibold text-muted">Owner Name</label>
                            <input type="text" name="owner_name" id="edit_owner" class="form-control bg-light border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Contact Number</label>
                            <input type="text" name="contact" id="edit_contact" class="form-control bg-light border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Location</label>
                            <input type="text" name="location" id="edit_location" class="form-control bg-light border-0 shadow-sm" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 p-3">
                    <button type="button" class="btn btn-link text-decoration-none text-muted mx-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_establishment" class="btn btn-dark px-5 rounded-pill shadow">
                        Update Database
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('edit_id').value = this.dataset.id;
        document.getElementById('edit_name').value = this.dataset.name;
        document.getElementById('edit_type').value = this.dataset.type;
        document.getElementById('edit_owner').value = this.dataset.owner;
        document.getElementById('edit_contact').value = this.dataset.contact;
        document.getElementById('edit_location').value = this.dataset.location;
        document.getElementById('edit_status').value = this.dataset.status;
    });
});

// Real-time Automatic Search and Filter Logic
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const typeFilter = document.getElementById('typeFilter');
const tableRows = document.querySelectorAll('.establishment-row');
const noResults = document.getElementById('noResults');

function filterTable() {
    const searchValue = searchInput.value.toLowerCase();
    const statusValue = statusFilter.value;
    const typeValue = typeFilter.value;
    let hasVisibleRows = false;

    tableRows.forEach(row => {
        const name = row.querySelector('.establishment-name').textContent.toLowerCase();
        const owner = row.querySelector('.owner-name').textContent.toLowerCase();
        const location = row.querySelector('.location-text').textContent.toLowerCase();
        const status = row.querySelector('.status-badge').textContent.trim();
        const type = row.querySelector('.type-label').textContent.trim();

        const matchesSearch = name.includes(searchValue) || owner.includes(searchValue) || location.includes(searchValue);
        const matchesStatus = statusValue === "" || status === statusValue;
        const matchesType = typeValue === "" || type === typeValue;

        if (matchesSearch && matchesStatus && matchesType) {
            row.style.display = "";
            hasVisibleRows = true;
        } else {
            row.style.display = "none";
        }
    });

    noResults.style.display = hasVisibleRows ? "none" : "";
}

searchInput.addEventListener('input', filterTable);
statusFilter.addEventListener('change', filterTable);
typeFilter.addEventListener('change', filterTable);
</script>

<?php include("../includes/footer.php"); ?>