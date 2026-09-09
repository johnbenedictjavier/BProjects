<?php
session_start();
include("config/db.php");


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
    $occ   = mysqli_real_escape_string($conn, $_POST['occupation']);
    $hnum  = mysqli_real_escape_string($conn, $_POST['house_number']);
    $hh_id = "HH-" . date("Y") . "-" . rand(1000, 9999);

    $sqlRes = "INSERT INTO residents (first_name, middle_name, last_name, present_address, address_type, provincial_address, gender, civil_status, birthdate, birthplace, height, weight, contact_number, email_address, religion, occupation, house_number, household_id, created_at) 
               VALUES ('$fname', '$mname', '$lname', '$addr', '$addr_t', '$p_addr', '$gen', '$civ', '$bdate', '$bplace', '$h', '$w', '$cont', '$email', '$rel', '$occ', '$hnum', '$hh_id', NOW())";

    if ($conn->query($sqlRes)) {
        $resident_id = $conn->insert_id; 

        $levels = ['Elementary', 'Highschool', 'Vocational', 'College'];
        foreach ($levels as $lvl) {
            $key = strtolower($lvl);
            $school = mysqli_real_escape_string($conn, $_POST['edu_school_'.$key]);
            $s_addr = mysqli_real_escape_string($conn, $_POST['edu_addr_'.$key]);
            if (!empty($school)) {
                $conn->query("INSERT INTO resident_education (resident_id, level, school_name, school_address) VALUES ('$resident_id', '$lvl', '$school', '$s_addr')");
            }
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

        $uname = mysqli_real_escape_string($conn, $_POST['username']);
        $pass  = mysqli_real_escape_string($conn, $_POST['password']); 
        $conn->query("INSERT INTO users (username, password, resident_id) VALUES ('$uname', '$pass', '$resident_id')");

        echo "<script>alert('Census Record Saved Successfully!'); window.location='view_residents.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Barangay Census Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Inter', sans-serif; color: #334155; }
        .main-card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); background: #fff; }
        .section-header { background: #1e293b; color: #fff; padding: 10px 20px; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; border-radius: 8px; margin-top: 30px; margin-bottom: 20px; display: flex; align-items: center; }
        .section-header i { margin-right: 10px; color: #3b82f6; }
        label { font-weight: 600; font-size: 0.8rem; color: #64748b; margin-bottom: 5px; }
        .form-control, .form-select { border-radius: 8px; border: 1px solid #e2e8f0; padding: 10px 15px; font-size: 0.9rem; }
        .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        .table { border: 1px solid #f1f5f9; border-radius: 8px; overflow: hidden; }
        .table thead { background: #f8fafc; }
        .btn-primary { background: #2563eb; border: none; padding: 12px 25px; font-weight: 600; border-radius: 8px; }
        .btn-add { background: #f1f5f9; color: #475569; font-weight: 600; border: none; font-size: 0.8rem; }
    </style>
</head>
<body class="py-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="login.php" style="text-decoration: none;"><h2 class="fw-bold m-0 text-dark">Barangay Census Form</h2></a>
                <a href="view_residents.php" class="btn btn-outline-secondary btn-sm"><i class="fa fa-list me-2"></i>View Residents</a>
            </div>

            <div class="card main-card p-4 p-md-5">
                <form method="POST">
                    
                    <div class="section-header"><i class="fa fa-user"></i> I. Personal Identification</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Gender</label>
                            <select name="gender" class="form-select">
                                <option>Male</option><option>Female</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Civil Status</label>
                            <select name="civil_status" class="form-select">
                                <option>Single</option><option>Married</option><option>Widowed</option><option>Separated</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Birth Date</label>
                            <input type="date" name="birthdate" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Place of Birth</label>
                            <input type="text" name="birthplace" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Present Address</label>
                            <input type="text" name="present_address" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Ownership</label>
                            <select name="address_type" class="form-select">
                                <option>Home Owner</option><option>Boarder</option><option>Renter</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>House Number</label>
                            <input type="text" name="house_number" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label>Provincial Address</label>
                            <input type="text" name="provincial_address" class="form-control">
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <label>Contact Number</label>
                            <input type="text" name="contact_number" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>Email Address</label>
                            <input type="email" name="email_address" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label>Height (cm)</label>
                            <input type="text" name="height" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label>Weight (kg)</label>
                            <input type="text" name="weight" class="form-control">
                        </div>
                    </div>

                    <div class="section-header"><i class="fa fa-graduation-cap"></i> II. Educational Attainment</div>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead><tr><th>Level</th><th>School Name</th><th>Address</th></tr></thead>
                            <tbody>
                                <?php foreach(['Elementary', 'Highschool', 'Vocational', 'College'] as $lvl): $k = strtolower($lvl); ?>
                                <tr>
                                    <td class="fw-bold small text-muted"><?= $lvl ?></td>
                                    <td><input type="text" name="edu_school_<?= $k ?>" class="form-control border-0 bg-transparent"></td>
                                    <td><input type="text" name="edu_addr_<?= $k ?>" class="form-control border-0 bg-transparent"></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="section-header"><i class="fa fa-briefcase"></i> III. Employment Record</div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Current Occupation</label>
                            <input type="text" name="occupation" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Religion</label>
                            <input type="text" name="religion" class="form-control">
                        </div>
                    </div>
                    <table class="table table-bordered" id="employmentTable">
                        <thead><tr><th>Duration</th><th>Company Name</th><th>Office Address</th></tr></thead>
                        <tbody>
                            <tr>
                                <td><input type="text" name="duration[]" class="form-control border-0"></td>
                                <td><input type="text" name="compname[]" class="form-control border-0"></td>
                                <td><input type="text" name="emp_address[]" class="form-control border-0"></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-add btn-sm" onclick="addRow('employmentTable')"><i class="fa fa-plus me-1"></i>Add Row</button>

                    <div class="section-header"><i class="fa fa-users"></i> IV. Other House Occupants</div>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="occupantTable">
                            <thead><tr><th>Full Name</th><th>Position</th><th>Age</th><th>Birth Date</th><th>Civil Status</th><th>Occupation</th></tr></thead>
                            <tbody>
                                <tr>
                                    <td><input type="text" name="occ_name[]" class="form-control border-0"></td>
                                    <td><input type="text" name="occ_pos[]" class="form-control border-0"></td>
                                    <td><input type="number" name="occ_age[]" class="form-control border-0"></td>
                                    <td><input type="date" name="occ_bdate[]" class="form-control border-0"></td>
                                    <td><input type="text" name="occ_civ[]" class="form-control border-0"></td>
                                    <td><input type="text" name="occ_occ[]" class="form-control border-0"></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-add btn-sm" onclick="addRow('occupantTable')"><i class="fa fa-plus me-1"></i>Add Occupant</button>
                    </div>

                    <div class="section-header"><i class="fa fa-lock"></i> V. Account Security</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>

                    <div class="mt-5">
                        <button type="submit" class="btn btn-primary w-100 shadow-sm">SUBMIT OFFICIAL CENSUS RECORD</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
function addRow(tableId) {
    var table = document.getElementById(tableId).getElementsByTagName('tbody')[0];
    var newRow = table.insertRow();
    var cells = table.rows[0].cells.length;
    for (var i = 0; i < cells; i++) {
        var cell = newRow.insertCell(i);
        var input = table.rows[0].cells[i].getElementsByTagName('input')[0].cloneNode(true);
        input.value = "";
        cell.appendChild(input);
    }
}
</script>

</body>
</html>