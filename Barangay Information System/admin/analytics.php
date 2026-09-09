<?php
session_start();
if ($_SESSION['role'] !== 'admin') header("Location: ../login.php");
/** @var mysqli $conn */
require_once '../config/db.php';

// ==========================================
// 1. PRINT HANDLER (Triggered by Print Buttons)
// ==========================================
if (isset($_GET['print_category'])) {
    $cat = $_GET['print_category']; // gender, age, civil, occupation
    $val = $_GET['val'];
    
    $sql = "SELECT last_name, first_name, middle_name, address_type, contact_number FROM residents WHERE ";
    
    if ($cat == 'gender') $sql .= "gender = '$val'";
    elseif ($cat == 'civil') $sql .= "civil_status = '$val'";
    elseif ($cat == 'occupation') $sql .= "occupation = '$val'";
    elseif ($cat == 'age') {
        if ($val == 'minors') $sql .= "TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) < 18";
        elseif ($val == 'adults') $sql .= "TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 18 AND 59";
        elseif ($val == 'seniors') $sql .= "TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >= 60";
    } else { $sql .= "1=1"; }

    $res = $conn->query($sql);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Barangay Report - <?= strtoupper($val) ?></title>
        <style>
            body { font-family: Arial, sans-serif; margin: 30px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #000; padding: 10px; text-align: left; }
            th { background-color: #f2f2f2; }
            .header { text-align: center; margin-bottom: 30px; }
        </style>
    </head>
    <body onload="window.print()">
        <div class="header">
            <h2>BARANGAY DEMOGRAPHIC LIST</h2>
            <p>Category: <?= ucfirst($cat) ?> (<?= ucfirst($val) ?>) | Date: <?= date('m/d/Y') ?></p>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Full Name (Last, First, Middle)</th>
                    <th>Type of Ownership</th>
                    <th>Contact Number</th>
                </tr>
            </thead>
            <tbody>
                <?php while($item = $res->fetch_assoc()): ?>
                <tr>
                    <td><?= strtoupper($item['last_name']) . ", " . $item['first_name'] . " " . $item['middle_name'] ?></td>
                    <td><?= $item['address_type'] ?></td>
                    <td><?= $item['contact_number'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    exit;
}

include '../includes/header.php';

// Existing Counts
$total_residents = $conn->query("SELECT COUNT(*) FROM residents")->fetch_row()[0] ?? 0;
$Oresidents = $conn->query("SELECT COUNT(*) FROM resident_occupants")->fetch_row()[0] ?? 0;
$allresidents = $total_residents + $Oresidents;
$pending_requests = $conn->query("SELECT COUNT(*) FROM establishment_requests WHERE status='Pending'")->fetch_row()[0] ?? 0;
$total_establishments = $conn->query("SELECT COUNT(*) FROM establishments WHERE status='Active'")->fetch_row()[0] ?? 0;

// Gender Data
$gender_data = $conn->query("SELECT gender, COUNT(*) as count FROM residents GROUP BY gender");
$genders = []; $gender_counts = [];
while($row = $gender_data->fetch_assoc()) {
    $genders[] = $row['gender'];
    $gender_counts[] = $row['count'];
}

// Civil Status Data
$civil_data = $conn->query("SELECT civil_status, COUNT(*) as count FROM residents GROUP BY civil_status");
$status_labels = []; $status_counts = [];
while($row = $civil_data->fetch_assoc()) {
    $status_labels[] = $row['civil_status'];
    $status_counts[] = $row['count'];
}

// NEW: Occupation Data
$occ_query = $conn->query("SELECT occupation, COUNT(*) as count FROM residents WHERE occupation != '' GROUP BY occupation ORDER BY count DESC LIMIT 5");
$occ_labels = []; $occ_counts = [];
while($row = $occ_query->fetch_assoc()) {
    $occ_labels[] = $row['occupation'];
    $occ_counts[] = $row['count'];
}

// Age Data
$age_query = "SELECT 
    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) < 18 THEN 1 ELSE 0 END) AS minors,
    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 18 AND 59 THEN 1 ELSE 0 END) AS adults,
    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >= 60 THEN 1 ELSE 0 END) AS seniors
    FROM residents";
$age_res = $conn->query($age_query)->fetch_assoc();

$req_status_data = $conn->query("SELECT status, COUNT(*) as count FROM establishment_requests GROUP BY status");
$req_labels = []; $req_counts = [];
while($row = $req_status_data->fetch_assoc()) {
    $req_labels[] = $row['status'];
    $req_counts[] = $row['count'];
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="bms-dashboard-header mb-4">
    <h3 class="fw-bold mb-1">Analytics Dashboard</h3>
    <p class="text-muted small">Real-time demographic and establishment insights.</p>
</div>

<!-- Statistics Row -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 text-center">
            <h6 class="text-uppercase text-muted small fw-bold">Total Population</h6>
            <h2 class="fw-bold text-success"><?= number_format($allresidents) ?></h2>
            <small class="text-muted"><?= $total_residents ?> Residents | <?= $Oresidents ?> Occupants</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 text-center">
            <h6 class="text-uppercase text-muted small fw-bold">Active Establishments</h6>
            <h2 class="fw-bold text-primary"><?= $total_establishments ?></h2>
            <small class="text-muted">Currently Registered</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 text-center">
            <h6 class="text-uppercase text-muted small fw-bold">Pending Tasks</h6>
            <h2 class="fw-bold text-warning"><?= $pending_requests ?></h2>
            <small class="text-muted">Requests needing review</small>
        </div>
    </div>
    <div class="col-md-3">
        <?php $top_ann = $conn->query("SELECT title, likes FROM announcements ORDER BY likes DESC LIMIT 1")->fetch_assoc(); ?>
        <div class="card border-0 shadow-sm p-3 text-center">
            <h6 class="text-uppercase text-muted small fw-bold">Most Popular Post</h6>
            <h5 class="fw-bold text-truncate" title="<?= $top_ann['title'] ?>"><?= $top_ann['title'] ?? 'N/A' ?></h5>
            <small class="text-muted"><i class="bi bi-heart-fill text-danger"></i> <?= $top_ann['likes'] ?? 0 ?> Likes</small>
        </div>
    </div>
</div>

<!-- Resident Charts -->
<div class="row g-4 mb-5">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
                Resident Demographics
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">Print Demographic</button>
                    <ul class="dropdown-menu">
                        <li><h6 class="dropdown-header">Gender</h6></li>
                        <li><a class="dropdown-item" href="analytics.php?print_category=gender&val=Male" target="_blank">Male List</a></li>
                        <li><a class="dropdown-item" href="analytics.php?print_category=gender&val=Female" target="_blank">Female List</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Age Groups</h6></li>
                        <li><a class="dropdown-item" href="analytics.php?print_category=age&val=minors" target="_blank">Minors List</a></li>
                        <li><a class="dropdown-item" href="analytics.php?print_category=age&val=adults" target="_blank">Adults List</a></li>
                        <li><a class="dropdown-item" href="analytics.php?print_category=age&val=seniors" target="_blank">Seniors List</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <canvas id="genderChart"></canvas>
                    </div>
                    <div class="col-md-6">
                        <canvas id="ageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-bold py-3">Request Overview</div>
            <div class="card-body d-flex align-items-center">
                <canvas id="requestChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Civil Status & Occupation Row -->
<div class="row g-4 mb-5">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
                Civil Status Distribution
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">Print List</button>
                    <ul class="dropdown-menu">
                        <?php foreach($status_labels as $sl): ?>
                        <li><a class="dropdown-item" href="analytics.php?print_category=civil&val=<?= $sl ?>" target="_blank"><?= $sl ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <canvas id="civilChart" height="150"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
                Occupation Distribution
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">Print List</button>
                    <ul class="dropdown-menu shadow">
                        <?php foreach($occ_labels as $ol): ?>
                        <li><a class="dropdown-item" href="analytics.php?print_category=occupation&val=<?= urlencode($ol) ?>" target="_blank"><?= $ol ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <canvas id="occupationChart" height="150"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Announcement Table -->
<div class="row g-4 mb-5">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Top Announcements</h5>
            </div>
            <div class="card-body p-0">
                <table class="table bms-table align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Title</th>
                            <th>Likes</th>
                            <th>Category</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $top_list = $conn->query("SELECT * FROM announcements ORDER BY likes DESC LIMIT 4");
                        while($row = $top_list->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4"><?= $row['title'] ?></td>
                                <td><span class="badge bg-danger rounded-pill"><?= $row['likes'] ?></span></td>
                                <td><small class="text-muted"><?= $row['category'] ?></small></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const chartOptions = { responsive: true, plugins: { legend: { position: 'bottom' } } };

new Chart(document.getElementById('genderChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($genders) ?>,
        datasets: [{
            data: <?= json_encode($gender_counts) ?>,
            backgroundColor: ['#36A2EB', '#FF6384', '#FFCE56']
        }]
    },
    options: { plugins: { title: { display: true, text: 'Gender Distribution' } } }
});

new Chart(document.getElementById('ageChart'), {
    type: 'bar',
    data: {
        labels: ['Minors (<18)', 'Adults (18-59)', 'Seniors (60+)'],
        datasets: [{
            label: 'Population Count',
            data: [<?= (int)$age_res['minors'] ?>, <?= (int)$age_res['adults'] ?>, <?= (int)$age_res['seniors'] ?>],
            backgroundColor: '#10b981'
        }]
    },
    options: { plugins: { title: { display: true, text: 'Age Groups' } } }
});

new Chart(document.getElementById('requestChart'), {
    type: 'pie',
    data: {
        labels: <?= json_encode($req_labels) ?>,
        datasets: [{
            data: <?= json_encode($req_counts) ?>,
            backgroundColor: ['#ffc107', '#0dcaf0', '#198754', '#dc3545', '#6c757d']
        }]
    }
});

new Chart(document.getElementById('civilChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($status_labels) ?>,
        datasets: [{
            label: 'Count',
            data: <?= json_encode($status_counts) ?>,
            backgroundColor: '#6366f1'
        }]
    },
    options: { indexAxis: 'y' }
});

new Chart(document.getElementById('occupationChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($occ_labels) ?>,
        datasets: [{
            label: 'Count',
            data: <?= json_encode($occ_counts) ?>,
            backgroundColor: '#f59e0b'
        }]
    },
    options: { indexAxis: 'y' }
});
</script>

<?php include '../includes/footer.php'; ?>