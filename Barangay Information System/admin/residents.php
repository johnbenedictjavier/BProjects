<?php
session_start();
if ($_SESSION['role'] !== 'admin') header("Location: ../login.php");
/** @var mysqli $conn */

require_once '../config/db.php';

$occ_query = "SELECT DISTINCT occupation FROM residents WHERE occupation != '' 
              UNION 
              SELECT DISTINCT occupation FROM resident_occupants WHERE occupation != '' 
              ORDER BY occupation ASC";
$occupations_list = $conn->query($occ_query);

if (isset($_POST['update_status'])) {
    $id = $_POST['resident_id'];
    $new_status = $_POST['new_status'];
    $conn->query("UPDATE residents SET status = '$new_status' WHERE resident_id = $id");
    exit;
}

if (isset($_POST['delete_resident'])) {
    $id = $_POST['resident_id'];
    $conn->query("DELETE FROM residents WHERE resident_id = $id");
    $conn->query("DELETE FROM resident_education WHERE resident_id = $id");
    $conn->query("DELETE FROM resident_employment WHERE resident_id = $id");
    $conn->query("DELETE FROM resident_occupants WHERE resident_id = $id");
    exit;
}

$filter = $_GET['filter'] ?? 'all'; 
$new_id = $_GET['new_id'] ?? null;

if ($new_id) {
    $query = "SELECT resident_id as id, first_name, last_name, present_address, status, 'Head' as type 
              FROM residents WHERE resident_id = '$new_id'";
} else {
    if ($filter == 'heads') {
        $query = "SELECT resident_id as id, first_name, last_name, present_address, status, 'Head' as type FROM residents ORDER BY last_name ASC, first_name ASC";
    } elseif ($filter == 'registered') {
        $query = "SELECT resident_id as id, first_name, last_name, present_address, status, 'Head' as type FROM residents WHERE status = 'Registered' ORDER BY last_name ASC, first_name ASC";
    } else {
        $query = "(SELECT resident_id as id, first_name, last_name, present_address, status, 'Head' as type FROM residents)
                  UNION 
                  (SELECT resident_id as id, full_name as first_name, '' as last_name, 'Household Member' as present_address, 'N/A' as status, 'Occupant' as type FROM resident_occupants)
                  ORDER BY last_name ASC, first_name ASC";
    }
}

$result = $conn->query($query);
$residents_data = [];
while($row = $result->fetch_assoc()) {
    $residents_data[] = $row;
}
include '../includes/header.php';
?>
<style>
    .modal-backdrop { display: none !important; }

    .filter-btn { border-radius: 20px; padding: 5px 20px; font-weight: 600; font-size: 0.85rem; transition: 0.3s; }
    .search-wrap { position: relative; max-width: 400px; }
    .search-wrap i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
    .search-wrap input { padding-left: 40px; border-radius: 10px; border: 1px solid #e2e8f0; }
    .type-badge { font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; }
    .type-head { background: #e0f2fe; color: #0369a1; }
    .type-occ { background: #f1f5f9; color: #475569; }
    .report-card { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: white; border: none; }

    #listPrintArea, #demographicPrintArea { display: none; } 

    @media print {
        @page { size: auto; margin: 10mm; }
        body * { visibility: hidden; }
        
        body.printing-list #listPrintArea, 
        body.printing-list #listPrintArea *,
        body.printing-demographic #demographicPrintArea, 
        body.printing-demographic #demographicPrintArea * { 
            visibility: visible; 
        }

        #listPrintArea, #demographicPrintArea { 
            display: block !important; 
            position: absolute; 
            left: 0; 
            top: 0; 
            width: 100%; 
        }

        .report-table { 
            display: table !important; 
            width: 100% !important; 
            border-collapse: collapse !important; 
            border: 1px solid #000 !important;
        }
        .report-table tr { display: table-row !important; }
        .report-table th, .report-table td { 
            display: table-cell !important; 
            border: 1px solid #000 !important; 
            padding: 8px !important;
        }
        .no-print { display: none !important; }
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-0">Resident Database Management</h3>
            <p class="text-muted small mb-0">View profiles, manage status, and generate demographic reports.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="registerresident.php" class="btn btn-primary px-4 shadow-sm"><i class="bi bi-plus-lg me-2"></i>Register New Resident</a>
        </div>
    </div>

    <div class="card report-card shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h5 class="fw-bold mb-1"><i class="bi bi-graph-up-arrow me-2"></i>Demographic Reporting Tool</h5>
                    <p class="text-white-50 small mb-0">Generate filtered PDF lists based on age ranges, civil status, and occupations for community analysis.</p>
                </div>
                <div class="col-md-5 text-md-end">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-toggle="modal" data-bs-target="#demographicModal">
                        <i class="bi bi-file-earmark-pdf-fill me-2 text-danger"></i>Generate Demographic PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center g-3">
                <div class="col-lg-7">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="residents.php?filter=all" class="btn filter-btn <?= $filter=='all'?'btn-primary':'btn-light border' ?>">All Records</a>
                        <a href="residents.php?filter=heads" class="btn filter-btn <?= $filter=='heads'?'btn-primary':'btn-light border' ?>">Heads of Household</a>
                        <a href="residents.php?filter=registered" class="btn filter-btn <?= $filter=='registered'?'btn-primary':'btn-light border' ?>">Registered Voters</a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="search-wrap ms-auto">
                        <i class="bi bi-search"></i>
                        <input type="text" id="liveSearch" class="form-control" placeholder="Search by name, address or ID...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
            <table class="table align-middle mb-0" id="resTable">
                <thead class="bg-light sticky-top">
                    <tr>
                        <th class="ps-4">Full Name</th>
                        <th>Record Type</th>
                        <th>Current Address</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($residents_data as $row): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold"><?= $row['last_name'] . ($row['last_name'] ? ', ' : '') . $row['first_name'] ?></div>
                            <small class="text-muted">ID: #<?= str_pad($row['id'], 5, '0', STR_PAD_LEFT) ?></small>
                        </td>
                        <td><span class="type-badge <?= $row['type']=='Head' ? 'type-head' : 'type-occ' ?>"><?= $row['type'] ?></span></td>
                        <td class="small text-muted"><?= $row['present_address'] ?></td>
                        <td>
                            <span class="badge rounded-pill <?= $row['status']=='Registered' ? 'bg-success-subtle text-success' : 'bg-light text-dark' ?>">
                                <?= $row['status'] ?>
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <?php if($row['type'] == 'Head'): ?>
                                <button class="btn btn-sm btn-outline-primary border-0 view-btn" data-id="<?= $row['id'] ?>"><i class="bi bi-eye-fill"></i></button>
                                <a href="registerresident.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-pencil-square"></i></a>
                            <?php else: ?>
                                <span class="text-muted small italic">Occupant Only</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="demographicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-filter-circle me-2"></i>Report Parameters</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-uppercase">Gender</label>
                        <select class="form-select" id="repGender">
                            <option value="all">All Genders</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-uppercase">Civil Status</label>
                        <select class="form-select" id="repCivil">
                            <option value="all">All Statuses</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Separated">Separated</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-uppercase">Age Grouping (Based on Birthdate)</label>
                        <select class="form-select" id="repAge">
                            <option value="all">All Ages</option>
                            <option value="infant">Infant (Below 2 yrs)</option>
                            <option value="kid">Kid (2 - 12 yrs)</option>
                            <option value="teen">Teenager (13 - 19 yrs)</option>
                            <option value="adult">Adult (20 - 59 yrs)</option>
                            <option value="senior">Senior Citizen (60+ yrs)</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-uppercase">Occupation</label>
                        <select class="form-select" id="repOccupation">
                            <option value="all">All Occupations</option>
                            <?php while($occ = $occupations_list->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($occ['occupation']) ?>"><?= htmlspecialchars($occ['occupation']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" onclick="generateDemographicReport()" class="btn btn-dark px-4">
                    <span id="btnText">Generate & Print PDF</span>
                    <span id="btnLoader" class="spinner-border spinner-border-sm d-none"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="residentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white no-print">
                <h5 class="modal-title">Resident Profile Detail</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="d-flex flex-wrap justify-content-between mb-3 no-print gap-2">
                    <div class="btn-group">
                        <button type="button" id="censusMode" class="btn btn-sm btn-primary">Census View</button>
                        <button type="button" id="residencyMode" class="btn btn-sm btn-outline-primary">Residency Form</button>
                    </div>
                    <div class="btn-group">
                        <button type="button" onclick="window.print()" class="btn btn-sm btn-dark"><i class="bi bi-printer"></i> Print Record</button>
                        <a id="editLink" href="#" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i> Edit</a>
                        <button type="button" id="deleteBtn" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Delete</button>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="small fw-bold">Status:</label>
                        <select id="statusToggle" class="form-select form-select-sm" style="width: auto;">
                            <option value="Registered">Registered</option>
                            <option value="Not Registered">Unregistered</option>
                        </select>
                    </div>
                </div>
                <div id="printableArea" class="document-content shadow-sm mx-auto" style="max-width: 210mm;"></div>
            </div>
        </div>
    </div>
</div>

<div id="listPrintArea">
    <div class="text-center mb-4">
        <h4>REPUBLIC OF THE PHILIPPINES</h4>
        <h5 class="text-uppercase">Province of Batangas / Municipality of Lipa</h5>
        <h4 class="fw-bold mt-3">BARANGAY RESIDENT MASTERLIST</h4>
        <hr>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr><th>Name</th><th>Type</th><th>Address</th><th>Status</th></tr>
        </thead>
        <tbody>
            <?php foreach($residents_data as $row): ?>
            <tr>
                <td><?= $row['last_name'] . ($row['last_name'] ? ', ' : '') . $row['first_name'] ?></td>
                <td><?= $row['type'] ?></td>
                <td><?= $row['present_address'] ?></td>
                <td><?= $row['status'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="demographicPrintArea">
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    let currentId = null;

    $("#liveSearch").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#resTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    function loadDoc(mode) {
        $('#printableArea').html('<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>');
        $.get('fetch_resident_helper.php?id=' + currentId + '&mode=' + mode, function(data){
            $('#printableArea').html(data);
            $('#statusToggle').val($('#hiddenStatus').val());
        });
    }

    $('.view-btn').on('click', function(){
        currentId = $(this).data('id');
        $('#residentModal').modal('show');
        $('#editLink').attr('href', 'registerresident.php?id=' + currentId);
        $('#deleteBtn').data('id', currentId);
        $('#statusToggle').data('id', currentId);
        loadDoc('census');
    });

    $('#censusMode').click(function(){ loadDoc('census'); $(this).addClass('btn-primary').removeClass('btn-outline-primary'); $('#residencyMode').addClass('btn-outline-primary').removeClass('btn-primary'); });
    $('#residencyMode').click(function(){ loadDoc('residency'); $(this).addClass('btn-primary').removeClass('btn-outline-primary'); $('#censusMode').addClass('btn-outline-primary').removeClass('btn-primary'); });

    $('#statusToggle').on('change', function(){
        $.post('residents.php', { update_status: true, resident_id: currentId, new_status: $(this).val() }, function(){ location.reload(); });
    });

    $('#deleteBtn').on('click', function(){
        if(confirm("Permanently delete this record?")){
            $.post('residents.php', { delete_resident: true, resident_id: currentId }, function(){ location.reload(); });
        }
    });
});

function printFullList() {
    $('body').addClass('printing-list');
    window.print();
    window.onafterprint = function() {
        $('body').removeClass('printing-list');
    };
}

function generateDemographicReport() {
    const data = {
        gender: $('#repGender').val(),
        civil: $('#repCivil').val(),
        age: $('#repAge').val(),
        occupation: $('#repOccupation').val()
    };


    $.get('fetch_demographic_report.php', data, function(html) {
        var myModalEl = document.getElementById('demographicModal');
        var modal = bootstrap.Modal.getInstance(myModalEl);
        modal.hide();

        $('#demographicPrintArea').html(html);
        
        $('body').addClass('printing-demographic');

        setTimeout(() => {
            window.print();
            
            window.onafterprint = function() {
                $('body').removeClass('printing-demographic');
                $('#btnText').text('Generate & Print PDF');
                $('#btnLoader').addClass('d-none');
            };
        }, 300);
    });
}
</script>

<?php include '../includes/footer.php'; ?>