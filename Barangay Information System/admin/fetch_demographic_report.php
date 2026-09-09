<?php
require_once '../config/db.php';
/** @var mysqli $conn */

$gender = $conn->real_escape_string($_GET['gender'] ?? 'all');
$civil = $conn->real_escape_string($_GET['civil'] ?? 'all');
$age_group = $conn->real_escape_string($_GET['age'] ?? 'all');
$occ = $conn->real_escape_string($_GET['occupation'] ?? 'all');

$where_res = ["1=1"];
$where_occ = ["1=1"];

if($gender != 'all') {
    $where_res[] = "gender = '$gender'";
    $where_occ[] = "gender = '$gender'";
}
if($civil != 'all') { 
    $where_res[] = "civil_status = '$civil'"; 
    $where_occ[] = "civil_status = '$civil'"; 
}
if($occ != 'all') { 
    $where_res[] = "occupation = '$occ'"; 
    $where_occ[] = "occupation = '$occ'"; 
}

if($age_group != 'all') {
    $age_sql = "";
    if($age_group == 'infant') $age_sql = "TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) < 2";
    elseif($age_group == 'kid') $age_sql = "TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 2 AND 12";
    elseif($age_group == 'teen') $age_sql = "TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 13 AND 19";
    elseif($age_group == 'adult') $age_sql = "TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 20 AND 59";
    elseif($age_group == 'senior') $age_sql = "TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >= 60";
    $where_res[] = $age_sql; 
    $where_occ[] = $age_sql;
}

$res_cond = implode(" AND ", $where_res);
$occ_cond = implode(" AND ", $where_occ);


$query = "(SELECT last_name, first_name, middle_name, ownership, contact_number 
           FROM residents WHERE $res_cond)
          UNION
          (SELECT TRIM(SUBSTRING_INDEX(full_name, ',', 1)) as last_name, 
                  TRIM(SUBSTRING_INDEX(full_name, ',', -1)) as first_name, 
                  '' as middle_name, 
                  'Inhabitant' as ownership, 
                  CAST(contact_number AS CHAR) as contact_number 
           FROM resident_occupants WHERE $occ_cond)
          ORDER BY last_name ASC, first_name ASC";

$result = $conn->query($query);
?>

<div class="report-header text-center mb-4">
    <h2 style="margin:0; font-weight:bold; font-family: Arial, sans-serif; color: #000;">BARANGAY DEMOGRAPHIC REPORT</h2>
    <p style="margin:5px 0; font-family: Arial, sans-serif;">Generated: <?= date('F d, Y h:i A') ?></p>
    <div style="border-top:2px solid #000; border-bottom:2px solid #000; padding: 5px; font-size: 12px; margin-bottom: 20px; font-family: Arial, sans-serif; background-color: #f9f9f9;">
        <strong>FILTERS:</strong> 
        GENDER: <?= strtoupper($gender) ?> | 
        CIVIL: <?= strtoupper($civil) ?> | 
        AGE: <?= strtoupper($age_group) ?> | 
        OCCUPATION: <?= strtoupper($occ) ?>
    </div>
</div>

<table class="report-table">
    <thead>
        <tr>
            <th style="width: 50px;">#</th>
            <th>Full Name (Last, First, Middle)</th>
            <th style="width: 180px;">Ownership</th>
            <th style="width: 180px;">Contact Number</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        if($result && $result->num_rows > 0): 
            $count = 1;
            while($row = $result->fetch_assoc()): 
                $mname = !empty($row['middle_name']) ? ' ' . substr(trim($row['middle_name']), 0, 1) . '.' : '';
                $fullname = trim($row['last_name']) . ', ' . trim($row['first_name']) . $mname;
                
                $contact = trim($row['contact_number']);
                if(empty($contact) || $contact == '0' || $contact == 'N/A') {
                    $contact = '<span style="color: #999;">---</span>';
                }
        ?>
            <tr>
                <td style="text-align:center;"><?= $count++ ?></td>
                <td style="text-transform: uppercase; font-weight: bold;"><?= htmlspecialchars($fullname) ?></td>
                <td style="text-align:center;"><?= htmlspecialchars($row['ownership'] ?: 'N/A') ?></td>
                <td style="text-align:center; font-family: monospace;"><?= $contact ?></td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="4" style="text-align:center; padding: 30px;">No records match your filters.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<div style="margin-top: 15px; font-weight: bold; font-family: Arial, sans-serif; border-top: 1px solid #ccc; padding-top: 10px;">
    Total Records Count: <?= $result->num_rows ?>
</div>