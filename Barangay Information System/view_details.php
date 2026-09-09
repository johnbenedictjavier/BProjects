<?php
include("config/db.php");

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: Resident ID is missing.");
}

$resident_id = $conn->real_escape_string($_GET['id']);

$res_query = $conn->query("SELECT * FROM residents WHERE resident_id = $resident_id");
$resident = $res_query->fetch_assoc();

if (!$resident) {
    die("Error: Resident record not found.");
}

$edu_query = $conn->query("SELECT * FROM resident_education WHERE resident_id = $resident_id");
$emp_query = $conn->query("SELECT * FROM resident_employment WHERE resident_id = $resident_id");
$occ_query = $conn->query("SELECT * FROM resident_occupants WHERE resident_id = $resident_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Census: <?= $resident['last_name'] . ", " . $resident['first_name'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bs-primary: #0f172a; 
            --bs-secondary: #64748b;
        }
        body { 
            background-color: #f1f5f9; 
            font-family: 'Inter', sans-serif; 
            color: #1e293b;
        }
        .action-bar {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 15px 0;
            margin-bottom: 30px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .census-document { 
            background: white; 
            padding: 50px; 
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            max-width: 1000px;
            margin: 0 auto;
        }
        .official-header {
            border-bottom: 3px double #e2e8f0;
            margin-bottom: 30px;
            padding-bottom: 20px;
        }
        .section-title { 
            background: #f8fafc;
            color: var(--bs-primary);
            padding: 10px 15px; 
            font-weight: 700; 
            margin-top: 35px; 
            margin-bottom: 20px; 
            text-transform: uppercase; 
            font-size: 0.85rem;
            letter-spacing: 1px;
            border-left: 4px solid var(--bs-primary);
            display: flex;
            align-items: center;
        }
        .section-title i { margin-right: 10px; opacity: 0.7; }
        .label-text { 
            font-weight: 600; 
            color: #64748b; 
            font-size: 0.75rem; 
            text-transform: uppercase;
            display: block;
            margin-bottom: 2px;
        }
        .value-text { 
            display: block; 
            padding-bottom: 8px;
            margin-bottom: 15px; 
            font-size: 1rem; 
            color: #0f172a;
            border-bottom: 1px solid #f1f5f9;
        }
        .table { font-size: 0.9rem; }
        .table thead { background-color: #f8fafc; }
        .table thead th { font-weight: 600; color: #475569; border-bottom-width: 1px; }
        
        .signature-line {
            border-top: 1px solid #000;
            width: 250px;
            margin: 50px auto 10px auto;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: white; padding: 0; }
            .census-document { box-shadow: none; border: none; width: 100%; max-width: 100%; padding: 20px; }
            .section-title { background: #eee !important; }
        }
    </style>
</head>
<body>

<div class="action-bar no-print">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="view_residents.php" class="btn btn-link text-decoration-none text-muted">
            <i class="fa fa-arrow-left me-2"></i> Back to Database
        </a>
        <div>
            <button onclick="window.print()" class="btn btn-primary px-4 shadow-sm">
                <i class="fa fa-print me-2"></i> Print or Save PDF
            </button>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="census-document">
        
        <div class="official-header text-center">
            <h6 class="mb-1 fw-bold text-uppercase" style="letter-spacing: 2px;">Republic of the Philippines</h6>
            <h4 class="mb-1 fw-bold">BARANGAY RESIDENT CENSUS</h4>
            <p class="text-muted small mb-0">Office of the Barangay Secretary • Household Management System</p>
        </div>

        <div class="row align-items-end mb-4">
            <div class="col-md-8">
                <h2 class="fw-bold mb-0 text-primary"><?= $resident['first_name'] . " " . $resident['last_name'] ?></h2>
                <p class="text-muted mb-0">Record ID: <span class="fw-bold">#<?= str_pad($resident['resident_id'], 6, '0', STR_PAD_LEFT) ?></span></p>
            </div>
            <div class="col-md-4 text-md-end">
                <span class="badge bg-light text-dark border p-2">Date Recorded: <?= date("M d, Y", strtotime($resident['created_at'] ?? 'now')) ?></span>
            </div>
        </div>

        <div class="section-title"><i class="fa fa-user-circle"></i> I. Personal Identification</div>
        <div class="row">
            <div class="col-md-4">
                <span class="label-text">Last Name</span>
                <span class="value-text"><?= $resident['last_name'] ?></span>
            </div>
            <div class="col-md-4">
                <span class="label-text">First Name</span>
                <span class="value-text"><?= $resident['first_name'] ?></span>
            </div>
            <div class="col-md-4">
                <span class="label-text">Middle Name</span>
                <span class="value-text"><?= $resident['middle_name'] ?: '—' ?></span>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-8">
                <span class="label-text">Present Address</span>
                <span class="value-text fw-semibold"><i class="fa fa-map-marker-alt text-danger me-2"></i> <?= $resident['present_address'] ?> (<?= $resident['address_type'] ?>)</span>
            </div>
            <div class="col-md-4">
                <span class="label-text">Provincial Address</span>
                <span class="value-text text-muted small"><?= $resident['provincial_address'] ?: 'None Reported' ?></span>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-3">
                <span class="label-text">Gender</span>
                <span class="value-text"><?= $resident['gender'] ?></span>
            </div>
            <div class="col-md-3">
                <span class="label-text">Civil Status</span>
                <span class="value-text"><?= $resident['civil_status'] ?></span>
            </div>
            <div class="col-md-3">
                <span class="label-text">Date of Birth</span>
                <span class="value-text"><?= date("M d, Y", strtotime($resident['birthdate'])) ?></span>
            </div>
            <div class="col-md-3">
                <span class="label-text">Place of Birth</span>
                <span class="value-text text-truncate"><?= $resident['birthplace'] ?></span>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-3">
                <span class="label-text">Contact No.</span>
                <span class="value-text fw-bold"><?= $resident['contact_number'] ?></span>
            </div>
            <div class="col-md-3">
                <span class="label-text">Religion</span>
                <span class="value-text"><?= $resident['religion'] ?></span>
            </div>
            <div class="col-md-3">
                <span class="label-text">House # / HH ID</span>
                <span class="value-text"><?= $resident['house_number'] ?> / <?= $resident['household_id'] ?: 'N/A' ?></span>
            </div>
            <div class="col-md-3">
                <span class="label-text">Biometrics</span>
                <span class="value-text small text-muted"><?= $resident['height'] ?>cm / <?= $resident['weight'] ?>kg</span>
            </div>
        </div>

        <div class="section-title"><i class="fa fa-graduation-cap"></i> II. Educational Attainment</div>
        <div class="table-responsive">
            <table class="table table-hover border">
                <thead>
                    <tr>
                        <th width="20%">Level</th>
                        <th width="40%">School Name</th>
                        <th width="40%">School Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($edu_query->num_rows > 0): while($edu = $edu_query->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-bold"><?= $edu['level'] ?></td>
                            <td><?= $edu['school_name'] ?></td>
                            <td class="text-muted"><?= $edu['school_address'] ?></td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="3" class="text-center py-3 text-muted">No educational records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="section-title"><i class="fa fa-briefcase"></i> III. Employment Record</div>
        <div class="table-responsive">
            <table class="table table-hover border">
                <thead>
                    <tr>
                        <th width="25%">Inclusive Dates</th>
                        <th width="35%">Employer / Company</th>
                        <th width="40%">Office Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($emp_query->num_rows > 0): while($emp = $emp_query->fetch_assoc()): ?>
                        <tr>
                            <td><?= $emp['duration'] ?></td>
                            <td class="fw-bold"><?= $emp['company'] ?></td>
                            <td><?= $emp['employer_address'] ?></td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="3" class="text-center py-3 text-muted">No employment history provided.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="section-title"><i class="fa fa-users"></i> IV. Other Household Occupants</div>
        <div class="table-responsive">
            <table class="table table-hover border">
                <thead class="text-center">
                    <tr>
                        <th class="text-start">Full Name</th>
                        <th>Relationship</th>
                        <th>Age</th>
                        <th>Occupation</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($occ_query->num_rows > 0): while($occ = $occ_query->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-bold"><?= $occ['full_name'] ?></td>
                            <td class="text-center"><?= $occ['position_in_family'] ?></td>
                            <td class="text-center"><?= $occ['age'] ?></td>
                            <td class="text-center"><?= $occ['occupation'] ?></td>
                            <td class="text-center"><span class="small"><?= $occ['civil_status'] ?></span></td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="5" class="text-center py-3 text-muted">No other occupants listed for this household.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-5 pt-5 text-center">
            <div class="row">
                <div class="col-6">
                    <div class="signature-line"></div>
                    <p class="fw-bold mb-0">RESIDENT SIGNATURE</p>
                    <p class="text-muted small">Date Signed</p>
                </div>
                <div class="col-6">
                    <div class="signature-line"></div>
                    <p class="fw-bold mb-0">BARANGAY OFFICIAL</p>
                    <p class="text-muted small">Registrar / Secretary</p>
                </div>
            </div>
        </div>

        <div class="mt-5 pt-4 text-center border-top">
            <p class="text-muted" style="font-size: 0.65rem;">
                This is a computer-generated document. The information contained herein is treated with strict confidentiality under the Data Privacy Act of 2012.
            </p>
        </div>
    </div>
</div>

</body>
</html>