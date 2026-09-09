<?php
session_start();
/** @var mysqli $conn */
include("config/db.php");

$isUpdate = false;
$edit_id = $_GET['id'] ?? null;
$res = [];
$education_data = [];
$employment_data = [];
$occupant_data = [];

if ($edit_id) {
    $isUpdate = true;
    $res = $conn->query("SELECT * FROM residents WHERE resident_id = $edit_id")->fetch_assoc();
    $edu_res = $conn->query("SELECT * FROM resident_education WHERE resident_id = $edit_id");
    while($row = $edu_res->fetch_assoc()) $education_data[strtolower($row['level'])] = $row;
    
    $emp_res = $conn->query("SELECT * FROM resident_employment WHERE resident_id = $edit_id");
    while($row = $emp_res->fetch_assoc()) $employment_data[] = $row;

    $occ_res = $conn->query("SELECT * FROM resident_occupants WHERE resident_id = $edit_id");
    while($row = $occ_res->fetch_assoc()) $occupant_data[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fname = mysqli_real_escape_string($conn, $_POST['first_name']);
    $mname = mysqli_real_escape_string($conn, $_POST['middle_name']);
    $lname = mysqli_real_escape_string($conn, $_POST['last_name']);
    $addr  = mysqli_real_escape_string($conn, $_POST['present_address']);
    $addr_t = mysqli_real_escape_string($conn, $_POST['address_type']);
    $p_addr = mysqli_real_escape_string($conn, $_POST['provincial_address']);
    $gen   = mysqli_real_escape_string($conn, $_POST['gender']);
    $civ   = mysqli_real_escape_string($conn, $_POST['civil_status']);
    $bdate = mysqli_real_escape_string($conn, $_POST['birthdate']);
    $bplace = mysqli_real_escape_string($conn, $_POST['birthplace']);
    $h     = mysqli_real_escape_string($conn, $_POST['height']);
    $w     = mysqli_real_escape_string($conn, $_POST['weight']);
    $cont  = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $email = mysqli_real_escape_string($conn, $_POST['email_address']);
    $rel   = mysqli_real_escape_string($conn, $_POST['religion']);
    $occ_main   = mysqli_real_escape_string($conn, $_POST['occupation']);
    $hnum  = mysqli_real_escape_string($conn, $_POST['house_number']);

    if ($isUpdate) {
        $sql = "UPDATE residents SET first_name='$fname', middle_name='$mname', last_name='$lname', present_address='$addr', address_type='$addr_t', provincial_address='$p_addr', gender='$gen', civil_status='$civ', birthdate='$bdate', birthplace='$bplace', height='$h', weight='$w', contact_number='$cont', email_address='$email', religion='$rel', occupation='$occ_main', house_number='$hnum' WHERE resident_id=$edit_id";
        $conn->query($sql);
        $resident_id = $edit_id;
        $conn->query("DELETE FROM resident_education WHERE resident_id=$edit_id");
        $conn->query("DELETE FROM resident_employment WHERE resident_id=$edit_id");
        $conn->query("DELETE FROM resident_occupants WHERE resident_id=$edit_id");
    } else {
        $hh_id = "HH-" . date("Y") . "-" . rand(1000, 9999);
        $sqlRes = "INSERT INTO residents (first_name, middle_name, last_name, present_address, address_type, provincial_address, gender, civil_status, birthdate, birthplace, height, weight, contact_number, email_address, religion, occupation, house_number, household_id, status, created_at) 
                   VALUES ('$fname', '$mname', '$lname', '$addr', '$addr_t', '$p_addr', '$gen', '$civ', '$bdate', '$bplace', '$h', '$w', '$cont', '$email', '$rel', '$occ_main', '$hnum', '$hh_id', 'Not Registered', NOW())";
        $conn->query($sqlRes);
        $resident_id = $conn->insert_id; 
        
        $uname = mysqli_real_escape_string($conn, $_POST['username']);
        $pass  = $_POST['password']; 
        $conn->query("INSERT INTO users (username, password, role, resident_id) VALUES ('$uname', '$pass', 'resident', '$resident_id')");
    }

    $levels = ['Elementary', 'Highschool', 'Vocational', 'College'];
    foreach ($levels as $lvl) {
        $k = strtolower($lvl);
        $school = mysqli_real_escape_string($conn, $_POST['edu_school_'.$k]);
        $s_addr = mysqli_real_escape_string($conn, $_POST['edu_addr_'.$k]);
        if (!empty($school)) $conn->query("INSERT INTO resident_education (resident_id, level, school_name, school_address) VALUES ('$resident_id', '$lvl', '$school', '$s_addr')");
    }

    if (!empty($_POST['duration'])) {
        foreach ($_POST['duration'] as $key => $val) {
            if (!empty($val)) {
                $dur = mysqli_real_escape_string($conn, $val);
                $comp = mysqli_real_escape_string($conn, $_POST['compname'][$key]);
                $e_addr = mysqli_real_escape_string($conn, $_POST['emp_address'][$key]);
                $conn->query("INSERT INTO resident_employment (resident_id, duration, company, employer_address) VALUES ('$resident_id', '$dur', '$comp', '$e_addr')");
            }
        }
    }

    if (!empty($_POST['occ_name'])) {
        foreach ($_POST['occ_name'] as $key => $val) {
            if (!empty($val)) {
                $o_name = mysqli_real_escape_string($conn, $val);
                $o_pos = mysqli_real_escape_string($conn, $_POST['occ_pos'][$key]);
                $o_age = mysqli_real_escape_string($conn, $_POST['occ_age'][$key]);
                $o_bdate = mysqli_real_escape_string($conn, $_POST['occ_bdate'][$key]);
                $o_civ = mysqli_real_escape_string($conn, $_POST['occ_civ'][$key]);
                $o_occ = mysqli_real_escape_string($conn, $_POST['occ_occ'][$key]);
                $conn->query("INSERT INTO resident_occupants (resident_id, full_name, position_in_family, age, birthdate, civil_status, occupation) VALUES ('$resident_id', '$o_name', '$o_pos', '$o_age', '$o_bdate', '$o_civ', '$o_occ')");
            }
        }
    }

    echo "<script>window.location.href='login.php;</script>";
}
?>
<head>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<style>
        body {
            font-family: 'Inter', sans-serif;
            background: url("assets/uploads/brgy.png") center / cover no-repeat;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
        }
        .reg { margin-top: 80px;}
    .step-container { display: none; }
    .step-container.active { display: block; }
    .progress { height: 8px; margin-bottom: 30px; }
    .step-indicator { font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 10px; display: block; }
    .section-header { background: #f8fafc; border-left: 4px solid #2563eb; padding: 12px; font-weight: 700; margin-bottom: 20px; color: #1e293b; }
    #occupantTable { min-width: 1000px; }
    .table-responsive-wide { overflow-x: auto; width: 100%; }
</style>

<body>
<div class="container py-4 reg">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold m-0"><?= $isUpdate ? 'Update' : 'Barangay' ?> Census Form</h2>
                <a href="login.php" class="btn btn-outline-primary btn-sm">Cancel / Back to Log In</a>
            </div>

            <div class="card border-0 shadow-sm p-4">
                <span class="step-indicator" id="stepText">Step 1: Personal Identification</span>
                <div class="progress"><div id="progressBar" class="progress-bar bg-primary" style="width: 20%"></div></div>

                <form id="censusForm" method="POST">
                    <div class="step-container active" id="step1">
                        <div class="section-header">I. Personal Identification</div>
                        <div class="row g-3">
                            <div class="col-md-4"><label>First Name</label><input type="text" name="first_name" class="form-control" required></div>
                            <div class="col-md-4"><label>Middle Name</label><input type="text" name="middle_name" class="form-control" required></div>
                            <div class="col-md-4"><label>Last Name</label><input type="text" name="last_name" class="form-control" required></div>
                            <div class="col-md-3">
                                <label>Gender</label>
                                <select name="gender" class="form-select">
                                    <option <?= ($res['gender']??'')=='Male'?'selected':'' ?>>Male</option>
                                    <option <?= ($res['gender']??'')=='Female'?'selected':'' ?>>Female</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Civil Status</label>
                                <select name="civil_status" class="form-select">
                                    <?php foreach(['Single','Married','Widowed','Separated'] as $s): ?>
                                        <option <?= ($res['civil_status']??'')==$s?'selected':'' ?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3"><label>Birth Date</label><input type="date" name="birthdate" id="main_birthdate" class="form-control" value="<?= $res['birthdate']??'' ?>" required onchange="updateMainAge()"></div>
                            <div class="col-md-3"><label>Age</label><input type="text" id="main_age" class="form-control bg-light" readonly></div>
                            <div class="col-md-4"><label>Place of Birth</label><input type="text" name="birthplace" class="form-control" value="<?= $res['birthplace']??'' ?>"></div>
                            <div class="col-md-8"><label>Present Address</label><input type="text" name="present_address" class="form-control" value="<?= $res['present_address']??'' ?>" required></div>
                            <div class="col-md-4">
                                <label>Ownership</label>
                                <select name="address_type" class="form-select">
                                    <option <?= ($res['address_type']??'')=='Home Owner'?'selected':'' ?>>Home Owner</option>
                                    <option <?= ($res['address_type']??'')=='Boarder'?'selected':'' ?>>Boarder</option>
                                    <option <?= ($res['address_type']??'')=='Renter'?'selected':'' ?>>Renter</option>
                                </select>
                            </div>
                            <div class="col-md-4"><label>House Number</label><input type="text" name="house_number" class="form-control" value="<?= $res['house_number']??'' ?>"></div>
                            <div class="col-md-8"><label>Provincial Address</label><input type="text" name="provincial_address" class="form-control" value="<?= $res['provincial_address']??'' ?>"></div>
                            <div class="col-md-4"><label>Contact Number</label><input type="text" name="contact_number" class="form-control" value="<?= $res['contact_number']??'' ?>"></div>
                            <div class="col-md-4"><label>Email Address</label><input type="email" name="email_address" class="form-control" value="<?= $res['email_address']??'' ?>"></div>
                            <div class="col-md-2"><label>Height (cm)</label><input type="text" name="height" class="form-control" value="<?= $res['height']??'' ?>"></div>
                            <div class="col-md-2"><label>Weight (kg)</label><input type="text" name="weight" class="form-control" value="<?= $res['weight']??'' ?>"></div>
                        </div>
                    </div>

                    <div class="step-container" id="step2">
                        <div class="section-header">II. Educational Attainment</div>
                        <table class="table table-bordered">
                            <thead class="table-light"><tr><th>Level</th><th>School Name</th><th>Address</th></tr></thead>
                            <tbody>
                                <?php foreach(['Elementary', 'Highschool', 'Vocational', 'College'] as $lvl): $k = strtolower($lvl); ?>
                                <tr>
                                    <td class="fw-bold"><?= $lvl ?></td>
                                    <td><input type="text" name="edu_school_<?= $k ?>" class="form-control border-0" value="<?= $education_data[$k]['school_name']??'' ?>"></td>
                                    <td><input type="text" name="edu_addr_<?= $k ?>" class="form-control border-0" value="<?= $education_data[$k]['school_address']??'' ?>"></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="step-container" id="step3">
                        <div class="section-header">III. Employment Record</div>
                        <div class="row mb-3">
                            <div class="col-md-6"><label>Current Occupation</label><input type="text" name="occupation" class="form-control" value="<?= $res['occupation']??'' ?>"></div>
                            <div class="col-md-6"><label>Religion</label><input type="text" name="religion" class="form-control" value="<?= $res['religion']??'' ?>"></div>
                        </div>
                        <table class="table table-bordered" id="employmentTable">
                            <thead class="table-light"><tr><th>Duration</th><th>Company Name</th><th>Office Address</th></tr></thead>
                            <tbody>
                                <?php if($isUpdate && !empty($employment_data)): foreach($employment_data as $e): ?>
                                <tr>
                                    <td><input type="text" name="duration[]" class="form-control border-0" value="<?= $e['duration'] ?>"></td>
                                    <td><input type="text" name="compname[]" class="form-control border-0" value="<?= $e['company'] ?>"></td>
                                    <td><input type="text" name="emp_address[]" class="form-control border-0" value="<?= $e['employer_address'] ?>"></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td><input type="text" name="duration[]" class="form-control border-0"></td><td><input type="text" name="compname[]" class="form-control border-0"></td><td><input type="text" name="emp_address[]" class="form-control border-0"></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-light btn-sm border" onclick="addRow('employmentTable')">+ Add</button>
                    </div>

                    <div class="step-container" id="step4">
                        <div class="section-header">IV. Other House Occupants</div>
                        <div class="table-responsive-wide">
                            <table class="table table-bordered" id="occupantTable">
                                <thead class="table-light"><tr>
                                    <th style="width: 35%;">Full Name (LName, FName MName)</th>
                    <th style="width: 15%;">Position</th>
                    <th style="width: 8%;">Age</th>
                    <th style="width: 15%;">Birth Date</th>
                    <th style="width: 12%;">Status</th>
                    <th style="width: 15%;">Occupation</th>
                                </tr></thead>
                                <tbody>
                                    <?php if($isUpdate && !empty($occupant_data)): foreach($occupant_data as $o): ?>
                                    <tr>
                                        <td><input type="text" name="occ_name[]" class="form-control border-0" value="<?= $o['full_name'] ?>" placeholder="Last Name, First Name Middle Name"></td>
                                        <td><input type="text" name="occ_pos[]" class="form-control border-0" value="<?= $o['position_in_family'] ?>"></td>
                                        <td><input type="number" name="occ_age[]" class="form-control border-0 bg-light" value="<?= $o['age'] ?>" readonly></td>
                                        <td><input type="date" name="occ_bdate[]" class="form-control border-0" value="<?= $o['birthdate'] ?>" onchange="updateRowAge(this)"></td>
                                        <td>
                                            <select name="occ_civ[]" class="form-select border-0">
                                                <option value="Single" <?= $o['civil_status'] == 'Single' ? 'selected' : '' ?>>Single</option>
                                                <option value="Married" <?= $o['civil_status'] == 'Married' ? 'selected' : '' ?>>Married</option>
                                                <option value="Widowed" <?= $o['civil_status'] == 'Widowed' ? 'selected' : '' ?>>Widowed</option>
                                                <option value="Separated" <?= $o['civil_status'] == 'Separated' ? 'selected' : '' ?>>Separated</option>
                                            </select>
                                        </td>
                                        <td><input type="text" name="occ_occ[]" class="form-control border-0" value="<?= $o['occupation'] ?>"></td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr>
                                        <td><input type="text" name="occ_name[]" class="form-control border-0" placeholder="Last Name, First Name Middle Name"></td>
                                        <td><input type="text" name="occ_pos[]" class="form-control border-0"></td>
                                        <td><input type="number" name="occ_age[]" class="form-control border-0 bg-light" readonly></td>
                                        <td><input type="date" name="occ_bdate[]" class="form-control border-0" onchange="updateRowAge(this)"></td>
                                        <td>
                                            <select name="occ_civ[]" class="form-select border-0">
                                                <option>Single</option>
                                                <option>Married</option>
                                                <option>Widowed</option>
                                                <option>Separated</option>
                                            </select>
                                        </td>
                                        <td><input type="text" name="occ_occ[]" class="form-control border-0"></td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-light btn-sm border mt-2" onclick="addRow('occupantTable')">+ Add</button>
                    </div>

                    <div class="step-container" id="step5">
                        <div class="section-header">V. Account Security</div>
                        <?php if($isUpdate): ?>
                            <div class="alert alert-warning">Account security settings (password) are managed in the Users module.</div>
                        <?php else: ?>
                            <div class="row g-3">
                                <div class="col-md-6"><label>Username</label><input type="text" name="username" class="form-control" required></div>
                                <div class="col-md-6"><label>Password</label><input type="password" name="password" class="form-control" required></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-between mt-5 pt-3 border-top">
                        <button type="button" class="btn btn-light px-4" id="prevBtn" onclick="nextPrev(-1)" style="display:none;">Previous</button>
                        <button type="button" class="btn btn-primary px-4 ms-auto" id="nextBtn" onclick="nextPrev(1)">Next Step</button>
                        <button type="submit" class="btn btn-success px-5 ms-auto" id="submitBtn" style="display:none;"><?= $isUpdate ? 'Update' : 'Submit' ?> Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>

<script>
let currentStep = 0;
const steps = document.getElementsByClassName("step-container");

function showStep(n) {
    steps[n].classList.add("active");
    document.getElementById("prevBtn").style.display = (n == 0) ? "none" : "inline";
    if (n == (steps.length - 1)) {
        document.getElementById("nextBtn").style.display = "none";
        document.getElementById("submitBtn").style.display = "inline";
    } else {
        document.getElementById("nextBtn").style.display = "inline";
        document.getElementById("submitBtn").style.display = "none";
    }
    let pct = ((n + 1) / steps.length) * 100;
    document.getElementById("progressBar").style.width = pct + "%";
}

function nextPrev(n) {
    steps[currentStep].classList.remove("active");
    currentStep = currentStep + n;
    showStep(currentStep);
    window.scrollTo(0,0);
}

function calculateAge(birthDate) {
    if (!birthDate) return '';
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    return age;
}

function updateMainAge() {
    const bdate = document.getElementById('main_birthdate').value;
    document.getElementById('main_age').value = calculateAge(bdate);
}

function updateRowAge(input) {
    const row = input.closest('tr');
    const ageInput = row.querySelector('input[name="occ_age[]"]');
    ageInput.value = calculateAge(input.value);
}

function addRow(tableId) {
    var table = document.getElementById(tableId).getElementsByTagName('tbody')[0];
    var newRow = table.insertRow();
    var firstRowCells = table.rows[0].cells;
    for (var i = 0; i < firstRowCells.length; i++) {
        var cell = newRow.insertCell(i);
        var inputElement = firstRowCells[i].querySelector('input, select');
        var newElement = inputElement.cloneNode(true);
        newElement.value = "";
        cell.appendChild(newElement);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    updateMainAge();
    document.querySelectorAll('input[name="occ_bdate[]"]').forEach(input => updateRowAge(input));
});

showStep(0);
</script>
