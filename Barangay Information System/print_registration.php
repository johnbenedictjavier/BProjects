<?php
include("config/db.php");

$id = $_GET['id'] ?? 0;
$res = $conn->query("SELECT * FROM residents WHERE resident_id = '$id'");
$data = $res->fetch_assoc();

if (!$data) { die("Invalid Resident ID"); }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registration Form - <?= $data['last_name'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print { .no-print { display: none; } body { padding: 0; } .print-container { border: none !important; } }
        body { background: #eee; padding: 20px; font-family: 'Times New Roman', serif; }
        .print-container { background: white; max-width: 850px; margin: auto; padding: 40px; border: 1px solid #000; }
        .header-section { text-align: center; line-height: 1.2; }
        .table-data th { background-color: #f8f9fa !important; width: 30%; }
    </style>
</head>
<body>

<div class="no-print text-center mb-4">
    <button onclick="window.print()" class="btn btn-success btn-lg">Print / Save as PDF</button>
    <a href="index.php" class="btn btn-secondary btn-lg">Return to Login</a>
</div>

<div class="print-container">
    <div class="header-section">
        <h6>Republic of the Philippines</h6>
        <h6>Province of [Your Province]</h6>
        <h6>City/Municipality of [Your City]</h6>
        <h5><strong>BARANGAY [YOUR BARANGAY NAME]</strong></h5>
        <hr>
        <h4 class="mt-4"><strong>RESIDENT REGISTRATION RECORD</strong></h4>
    </div>

    <div class="text-end mb-3">
        <strong>Resident ID:</strong> BRGY-<?= date("Y") ?>-<?= str_pad($data['resident_id'], 4, '0', STR_PAD_LEFT) ?>
    </div>

    <table class="table table-bordered table-data">
        <tr>
            <th colspan="2" class="bg-light">PERSONAL INFORMATION</th>
        </tr>
        <tr>
            <th>Full Name</th>
            <td><?= strtoupper($data['last_name'] . ", " . $data['first_name'] . " " . $data['middle_name']) ?></td>
        </tr>
        <tr>
            <th>Birthdate</th>
            <td><?= date("F d, Y", strtotime($data['birthdate'])) ?></td>
        </tr>
        <tr>
            <th>Gender</th>
            <td><?= $data['gender'] ?></td>
        </tr>
        <tr>
            <th>Civil Status</th>
            <td><?= $data['civil_status'] ?></td>
        </tr>
        <tr>
            <th>Occupation</th>
            <td><?= $data['occupation'] ?></td>
        </tr>
        <tr>
            <th colspan="2" class="bg-light">CONTACT & ADDRESS</th>
        </tr>
        <tr>
            <th>Contact Number</th>
            <td><?= $data['contact_number'] ?></td>
        </tr>
        <tr>
            <th>Full Address</th>
            <td><?= $data['address'] ?></td>
        </tr>
        <tr>
            <th>House Number / Household ID</th>
            <td>#<?= $data['house_number'] ?> / HH-<?= $data['household_id'] ?></td>
        </tr>
        <tr>
            <th>Registration Date</th>
            <td><?= date("M d, Y h:i A") ?></td>
        </tr>
    </table>

    <div class="mt-5">
        <p style="font-size: 0.9rem;"><em>I hereby certify that the information provided above is true and correct to the best of my knowledge.</em></p>
        
        <div class="row mt-5 pt-3">
            <div class="col-6 text-center">
                <div style="border-top: 1px solid black; width: 80%; margin: auto;"></div>
                <small>Resident Signature over Printed Name</small>
            </div>
            <div class="col-6 text-center">
                <div style="border-top: 1px solid black; width: 80%; margin: auto;"></div>
                <small>Barangay Official / Secretary</small>
            </div>
        </div>
    </div>
</div>

<script>
    window.onload = function() { window.print(); }
</script>

</body>
</html>