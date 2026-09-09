<?php
include("../config/db.php");
/** @var mysqli $conn */

$id = $_GET['id'];
$mode = $_GET['mode'] ?? 'census';
$res = $conn->query("SELECT * FROM residents WHERE resident_id = $id")->fetch_assoc();

if ($mode == 'residency') { ?>
    <style>
        .cert-header { position: relative; margin-bottom: 50px; }
        .cert-header img { width: 80px; position: absolute; top: 0; }
        .cert-title { margin-top: 40px; margin-bottom: 40px; letter-spacing: 2px; }
        .cert-body { line-height: 1.8; font-size: 12pt; text-align: justify; }
        .cert-footer { margin-top: 80px; }
        .specimen { border-bottom: 1px solid #000; width: 250px; margin-top: 40px; font-size: 10pt; }
    </style>
    
    <div class="text-center cert-header">
        <img src="../assets/uploads/lgu_logo.png" style="left: 0;" alt="LGU">
        <img src="../assets/uploads/brgylogo2.png" style="right: 0;" alt="BRGY">
        <p class="mb-0">Republic of the Philippines</p>
        <p class="mb-0">Province of Batangas</p>
        <p class="mb-0">Municipality of Lipa</p>
        <h5 class="fw-bold mb-0">BARANGAY SYETE</h5>
        <hr style="border: 1px solid #000;">
        <h6 class="fw-bold mt-2">OFFICE OF THE BARANGAY CAPTAIN</h6>
    </div>

    <h3 class="text-center fw-bold cert-title">CERTIFICATE OF RESIDENCY</h3>

    <div class="cert-body">
        <p class="fw-bold mb-4">TO WHOM IT MAY CONCERN:</p>
        <p>
            This is to certify that <strong><?= ($res['gender'] == 'Female' ? ($res['civil_status'] == 'Married' ? 'MRS.' : 'MS.') : 'MR.') ?> <?= strtoupper($res['first_name'] . ' ' . $res['middle_name'] . ' ' . $res['last_name']) ?></strong>, 
            of legal age, <?= strtolower($res['civil_status']) ?>, Filipino citizen, whose specimen signature appears below, is a <strong>PERMANENT RESIDENT</strong> of this Barangay 7, Granja, Municipality of Lipa, Batangas.
        </p>
        <p>
            Based on records of this office, <?= ($res['gender'] == 'Female' ? 'she' : 'he') ?> has been residing at Barangay 7, Granja, Municipality of Lipa, Batangas.
        </p>
        <p>
            This <strong>CERTIFICATION</strong> is being issued upon the request of the above-named person for whatever legal purpose it may serve.
        </p>
        <p>
            Issued this <?= date('jS') ?> day of <?= date('F, Y') ?> at Barangay 7, Granja, Lipa City, Batangas, Philippines.
        </p>
    </div>

    <div class="specimen">
        <p class="mb-0 mt-5">Specimen Signature:</p>
    </div>

    <div class="cert-footer text-end">
        <p class="mb-0 fw-bold text-decoration-underline" style="margin-right: 40px;">HON. JOSHUA GARCIA</p>
        <p class="pe-5">Punong Barangay</p>
    </div>

    <div class="mt-5 small text-muted">
        <p>Note: "Not valid without official seal"</p>
    </div>

<?php } else { 
    $edu = $conn->query("SELECT * FROM resident_education WHERE resident_id = $id");
    $emp = $conn->query("SELECT * FROM resident_employment WHERE resident_id = $id");
    $occ = $conn->query("SELECT * FROM resident_occupants WHERE resident_id = $id");
?>
    <input type="hidden" id="hiddenStatus" value="<?= $res['status'] ?>">
    <div class="header-title text-center">
        <h5 class="mb-0">REPUBLIC OF THE PHILIPPINES</h5>
        <h6 class="mb-0">OFFICE OF THE BARANGAY SECRETARY</h6>
        <h4 class="fw-bold mt-2">RESIDENT CENSUS RECORD</h4>
    </div>

    <div class="row">
        <div class="col-8">
            <div class="data-row"><span class="data-label">FULL NAME:</span> <span class="data-value fw-bold"><?= strtoupper($res['last_name'] . ', ' . $res['first_name'] . ' ' . $res['middle_name']) ?></span></div>
            <div class="data-row"><span class="data-label">ADDRESS:</span> <span class="data-value"><?= $res['present_address'] ?></span></div>
            <div class="data-row"><span class="data-label">HOUSE NUMBER:</span> <span class="data-value"><?= $res['house_number'] ?></span></div>
        </div>
        <div class="col-4 text-end">
            <div class="border p-2 text-center" style="width: 100px; height: 100px; float: right; font-size: 8pt; color: #ccc;">2x2 PHOTO</div>
        </div>
    </div>

    <div class="section-label">I. Personal Information</div>
    <div class="row mt-2" style="font-size: 10pt;">
        <div class="col-4"><strong>Gender:</strong> <?= $res['gender'] ?></div>
        <div class="col-4"><strong>Civil Status:</strong> <?= $res['civil_status'] ?></div>
        <div class="col-4"><strong>Birthdate:</strong> <?= date('M d, Y', strtotime($res['birthdate'])) ?></div>
        <div class="col-4"><strong>Birthplace:</strong> <?= $res['birthplace'] ?></div>
        <div class="col-4"><strong>Religion:</strong> <?= $res['religion'] ?></div>
        <div class="col-4"><strong>Contact:</strong> <?= $res['contact_number'] ?></div>
    </div>

    <div class="section-label">II. Educational Attainment</div>
    <table class="table table-sm table-bordered mt-2">
        <thead><tr class="table-light"><th>Level</th><th>School Name</th><th>Address</th></tr></thead>
        <tbody>
            <?php while($e = $edu->fetch_assoc()): ?>
            <tr><td><?= $e['level'] ?></td><td><?= $e['school_name'] ?></td><td><?= $e['school_address'] ?></td></tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="section-label">III. Employment History</div>
    <table class="table table-sm table-bordered mt-2">
        <thead><tr class="table-light"><th>Duration</th><th>Company</th><th>Address</th></tr></thead>
        <tbody>
            <?php while($em = $emp->fetch_assoc()): ?>
            <tr><td><?= $em['duration'] ?></td><td><?= $em['company'] ?></td><td><?= $em['employer_address'] ?></td></tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="section-label">IV. Household Occupants</div>
    <table class="table table-sm table-bordered mt-2">
        <thead><tr class="table-light"><th>Name</th><th>Relationship</th><th>Age</th><th>Occupation</th></tr></thead>
        <tbody>
            <?php while($o = $occ->fetch_assoc()): ?>
            <tr><td><?= $o['full_name'] ?></td><td><?= $o['position_in_family'] ?></td><td><?= $o['age'] ?></td><td><?= $o['occupation'] ?></td></tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="d-flex justify-content-between mt-4">
        <div class="sig-line" style="border-top: 1px solid #000; width: 200px; margin-top: 40px; text-align: center; font-weight: bold; font-size: 9pt;">Resident Signature</div>
        <div class="sig-line" style="border-top: 1px solid #000; width: 200px; margin-top: 40px; text-align: center; font-weight: bold; font-size: 9pt;">Barangay Secretary</div>
    </div>
<?php } ?>