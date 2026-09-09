<?php
session_start();
if ($_SESSION['role'] !== 'admin') header("Location: ../login.php");
/** @var mysqli $conn */

require_once '../config/db.php';
include '../includes/header.php';

// Get Filters
$filter_gender = $_GET['gender'] ?? 'all';
$filter_status = $_GET['civil_status'] ?? 'all';
$filter_occupation = $_GET['occupation'] ?? '';
$filter_age = $_GET['age_group'] ?? 'all';

// Build Query for Residents
$where_clauses = ["1=1"];

if ($filter_gender !== 'all') {
    $where_clauses[] = "gender = '" . $conn->real_escape_string($filter_gender) . "'";
}
if ($filter_status !== 'all') {
    $where_clauses[] = "civil_status = '" . $conn->real_escape_string($filter_status) . "'";
}
if (!empty($filter_occupation)) {
    $where_clauses[] = "occupation LIKE '%" . $conn->real_escape_string($filter_occupation) . "%'";
}

// Age Group Logic
if ($filter_age !== 'all') {
    if ($filter_age == 'infant') $where_clauses[] = "TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) < 2";
    elseif ($filter_age == 'kid') $where_clauses[] = "TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 2 AND 12";
    elseif ($filter_age == 'teen') $where_clauses[] = "TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 13 AND 19";
    elseif ($filter_age == 'adult') $where_clauses[] = "TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 20 AND 59";
    elseif ($filter_age == 'senior') $where_clauses[] = "TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >= 60";
}

$where_sql = implode(" AND ", $where_clauses);

$query = "SELECT *, 
          TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) AS age 
          FROM residents 
          WHERE $where_sql 
          ORDER BY last_name ASC, first_name ASC";

$result = $conn->query($query);
$residents_data = [];
while($row = $result->fetch_assoc()) {
    $residents_data[] = $row;
}
?>

<style>
    .filter-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; }
    .type-badge { font-size: 0.75rem; padding: 3px 10px; border-radius: 20px; }
    
    #listPrintArea { display: none; } 

    @media print {
        @page { size: portrait; margin: 1cm; }
        body * { visibility: hidden; }
        #listPrintArea, #listPrintArea * { visibility: visible; }
        #listPrintArea { 
            display: block !important; 
            position: absolute; 
            left: 0; 
            top: 0; 
            width: 100%; 
            color: black !important;
        }
        .table-bordered th, .table-bordered td { border: 1px solid #000 !important; padding: 8px; }
    }
</style>

<div class="container-fluid py-4 no-print">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Demographic Report</h3>
            <p class="text-muted small">Filter and generate resident lists for the barangay</p>
        </div>
        <button onclick="window.print()" class="btn btn-dark shadow-sm">
            <i class="bi bi-printer me-2"></i>Print PDF List
        </button>
    </div>

    <!-- Filter Form -->
    <div class="card filter-card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="small fw-bold">Gender</label>
                    <select name="gender" class="form-select form-select-sm">
                        <option value="all">All Genders</option>
                        <option value="Male" <?= $filter_gender=='Male'?'selected':'' ?>>Male</option>
                        <option value="Female" <?= $filter_gender=='Female'?'selected':'' ?>>Female</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold">Civil Status</label>
                    <select name="civil_status" class="form-select form-select-sm">
                        <option value="all">All Status</option>
                        <option value="Single" <?= $filter_status=='Single'?'selected':'' ?>>Single</option>
                        <option value="Married" <?= $filter_status=='Married'?'selected':'' ?>>Married</option>
                        <option value="Widowed" <?= $filter_status=='Widowed'?'selected':'' ?>>Widowed</option>
                        <option value="Separated" <?= $filter_status=='Separated'?'selected':'' ?>>Separated</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold">Age Group</label>
                    <select name="age_group" class="form-select form-select-sm">
                        <option value="all">Any Age</option>
                        <option value="infant" <?= $filter_age=='infant'?'selected':'' ?>>Infant (0-1)</option>
                        <option value="kid" <?= $filter_age=='kid'?'selected':'' ?>>Kid (2-12)</option>
                        <option value="teen" <?= $filter_age=='teen'?'selected':'' ?>>Teen (13-19)</option>
                        <option value="adult" <?= $filter_age=='adult'?'selected':'' ?>>Adult (20-59)</option>
                        <option value="senior" <?= $filter_age=='senior'?'selected':'' ?>>Senior (60+)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold">Occupation</label>
                    <input type="text" name="occupation" class="form-select form-select-sm" value="<?= htmlspecialchars($filter_occupation) ?>" placeholder="e.g. Student, Driver">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Apply Filters</button>
                    <a href="report.php" class="btn btn-light border btn-sm w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- On-screen Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Full Name</th>
                        <th>Gender</th>
                        <th>Age</th>
                        <th>Civil Status</th>
                        <th>Occupation</th>
                        <th>Contact</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($residents_data)): ?>
                        <tr><td colspan="6" class="text-center py-4">No residents found matching these filters.</td></tr>
                    <?php endif; ?>
                    <?php foreach($residents_data as $row): ?>
                    <tr>
                        <td class="ps-4 fw-bold">
                            <?= $row['last_name'] ?>, <?= $row['first_name'] ?> <?= $row['middle_name'] ?>
                        </td>
                        <td><?= $row['gender'] ?></td>
                        <td><?= $row['age'] ?> yrs old</td>
                        <td><?= $row['civil_status'] ?></td>
                        <td><?= $row['occupation'] ?></td>
                        <td><?= $row['contact_number'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Print-Only Layout -->
<div id="listPrintArea">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="margin:0;">BARANGAY RESIDENT MASTERLIST</h2>
        <p style="margin:5px 0;">Generated on: <?= date('F d, Y') ?></p>
        <p style="font-size: 12px;">
            Filters: [ Gender: <?= ucfirst($filter_gender) ?> ] 
            [ Status: <?= ucfirst($filter_status) ?> ] 
            [ Age Group: <?= ucfirst($filter_age) ?> ]
        </p>
        <hr>
    </div>
    <table class="table table-bordered" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="text-align: left; border: 1px solid #000;">Full Name (Last, First, Middle)</th>
                <th style="text-align: center; border: 1px solid #000;">Age</th>
                <th style="text-align: center; border: 1px solid #000;">Gender</th>
                <th style="text-align: center; border: 1px solid #000;">Civil Status</th>
                <th style="text-align: left; border: 1px solid #000;">Occupation</th>
                <th style="text-align: center; border: 1px solid #000;">Contact Number</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($residents_data as $row): ?>
            <tr>
                <td style="border: 1px solid #000;">
                    <?= strtoupper($row['last_name']) ?>, <?= $row['first_name'] ?> <?= $row['middle_name'] ?>
                </td>
                <td style="text-align: center; border: 1px solid #000;"><?= $row['age'] ?></td>
                <td style="text-align: center; border: 1px solid #000;"><?= $row['gender'] ?></td>
                <td style="text-align: center; border: 1px solid #000;"><?= $row['civil_status'] ?></td>
                <td style="border: 1px solid #000;"><?= $row['occupation'] ?></td>
                <td style="text-align: center; border: 1px solid #000;"><?= $row['contact_number'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div style="margin-top: 30px; font-size: 10px;">
        Total Records Found: <?= count($residents_data) ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>