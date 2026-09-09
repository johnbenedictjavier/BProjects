<?php
include("../config/db.php");
/** @var mysqli $conn */

$id = $_GET['id'];
$res = $conn->query("SELECT * FROM residents WHERE resident_id = $id")->fetch_assoc();
$edu = $conn->query("SELECT * FROM resident_education WHERE resident_id = $id");
$emp = $conn->query("SELECT * FROM resident_employment WHERE resident_id = $id");
$occ = $conn->query("SELECT * FROM resident_occupants WHERE resident_id = $id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Census_Record_<?= $res['last_name'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            @page { size: A4; margin: 1cm; }
            .no-print { display: none !important; }
            body { background: white !important; font-size: 10pt !important; }
            .container { width: 100% !important; max-width: 100% !important; padding: 0 !important; }
            .card { border: none !important; }
        }
        body { background: #f4f7f6; font-family: 'Times New Roman', serif; }
        .document-page { background: white; padding: 40px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 210mm; min-height: 297mm; margin: 20px auto; }
        .header-title { border-bottom: 2px solid #000; margin-bottom: 20px; padding-bottom: 10px; }
        .section-label { background: #eee; font-weight: bold; padding: 3px 10px; text-transform: uppercase; font-size: 9pt; margin-top: 15px; border: 1px solid #ddd; }
        .data-row { border-bottom: 1px solid #eee; padding: 4px 0; }
        .data-label { font-weight: bold; color: #555; width: 140px; display: inline-block; font-size: 9pt; }
        .data-value { color: #000; font-size: 10pt; }
        table { font-size: 9pt !important; margin-top: 10px; }
        .signature-box { margin-top: 40px; }
        .sig-line { border-top: 1px solid #000; width: 200px; margin-top: 40px; text-align: center; font-weight: bold; font-size: 9pt; }
    </style>
</head>
<body>

<div class="text-center py-3 no-print">
    <button onclick="window.print()" class="btn btn-dark"><i class="bi bi-printer"></i> Print Official Document</button>
    <a href="residents.php" class="btn btn-outline-secondary">Back to List</a>
</div>

<div class="document-page">
    <div class="header-title text-center">
        <h5 class="mb-0">REPUBLIC OF THE PHILIPPINES</h5>
        <h6 class="mb-0">OFFICE OF THE BARANGAY SECRETARY</h6>
        <h4 class="fw-bold mt-2">RESIDENT CENSUS RECORD</h4>
    </div>

    <div class="row">
        <div class="col-8">
            <div class="data-row"><span class="data-label">FULL NAME:</span> <span class="data-value fw-bold"><?= strtoupper($res['last_name'] . ', ' . $res['first_name'] . ' ' . $res['middle_name']) ?></span></div>
            <div class="data-row"><span class="data-label">ADDRESS:</span> <span class="data-value"><?= $res['present_address'] ?></span></div>
            <div class="data-row"><span class="data-label">HOUSEHOLD ID:</span> <span class="data-value"><?= $res['household_id'] ?></span></div>
        </div>
        <div class="col-4 text-end">
            <div class="border p-2 text-center" style="width: 100px; height: 100px; float: right; font-size: 8pt; color: #ccc;">2x2 PHOTO</div>
        </div>
    </div>

    <div class="section-label">I. Personal Information</div>
    <div class="row mt-2">
        <div class="col-4"><strong>Gender:</strong> <?= $res['gender'] ?></div>
        <div class="col-4"><strong>Civil Status:</strong> <?= $res['civil_status'] ?></div>
        <div class="col-4"><strong>Birthdate:</strong> <?= date('M d, Y', strtotime($res['birthdate'])) ?></div>
        <div class="col-4"><strong>Birthplace:</strong> <?= $res['birthplace'] ?></div>
        <div class="col-4"><strong>Religion:</strong> <?= $res['religion'] ?></div>
        <div class="col-4"><strong>Contact:</strong> <?= $res['contact_number'] ?></div>
    </div>

    <div class="section-label">II. Educational Attainment</div>
    <table class="table table-sm table-bordered">
        <thead><tr class="table-light"><th>Level</th><th>School Name</th><th>Address</th></tr></thead>
        <tbody>
            <?php while($e = $edu->fetch_assoc()): ?>
            <tr><td><?= $e['level'] ?></td><td><?= $e['school_name'] ?></td><td><?= $e['school_address'] ?></td></tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="section-label">III. Employment History</div>
    <table class="table table-sm table-bordered">
        <thead><tr class="table-light"><th>Duration</th><th>Company</th><th>Address</th></tr></thead>
        <tbody>
            <?php while($em = $emp->fetch_assoc()): ?>
            <tr><td><?= $em['duration'] ?></td><td><?= $em['company'] ?></td><td><?= $em['employer_address'] ?></td></tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="section-label">IV. Household Occupants</div>
    <table class="table table-sm table-bordered">
        <thead><tr class="table-light"><th>Name</th><th>Relationship</th><th>Age</th><th>Occupation</th></tr></thead>
        <tbody>
            <?php while($o = $occ->fetch_assoc()): ?>
            <tr><td><?= $o['full_name'] ?></td><td><?= $o['position_in_family'] ?></td><td><?= $o['age'] ?></td><td><?= $o['occupation'] ?></td></tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="signature-box d-flex justify-content-between">
        <div class="sig-line">Resident Signature</div>
        <div class="sig-line">Barangay Secretary</div>
    </div>

    <div class="text-center mt-5" style="font-size: 7pt; color: #999; border-top: 1px dashed #ccc; padding-top: 10px;">
        Generated on <?= date('Y-m-d H:i:s') ?> • Barangay Management System Official Document
    </div>
</div>

</body>
</html>