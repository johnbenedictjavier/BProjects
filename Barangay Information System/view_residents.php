<?php
include("config/db.php");
$query = "SELECT r.*, (SELECT COUNT(*) FROM resident_occupants WHERE resident_id = r.resident_id) as family_count 
          FROM residents r ORDER BY last_name ASC";
$res = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Management | Barangay Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f6;
            color: #334155;
        }
        .main-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .table thead th {
            background-color: #f8fafc;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: #64748b;
            border-top: none;
            padding: 15px;
        }
        .table tbody td {
            vertical-align: middle;
            padding: 15px;
            font-size: 0.9rem;
        }
        .btn-view {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            color: #2563eb;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-view:hover {
            background-color: #2563eb;
            color: white;
        }
        .badge-family {
            background-color: #e0f2fe;
            color: #0369a1;
            font-weight: 600;
            padding: 0.5em 0.8em;
        }
        .search-input {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding-left: 40px;
        }
        .search-icon {
            position: absolute;
            left: 15px;
            top: 10px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="register.php" class="text-decoration-none"><i class="fa fa-arrow-left me-1"></i> Back to Registration</a></li>
            <li class="breadcrumb-item active">Resident List</li>
        </ol>
    </nav>

    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h3 class="fw-bold mb-0">Barangay Residents</h3>
            <p class="text-muted small">Manage and view census information for all registered residents.</p>
        </div>
        <div class="col-md-6">
            <div class="d-flex gap-2 justify-content-md-end">
                <div class="position-relative">
                    <i class="fa fa-search search-icon"></i>
                    <input type="text" id="tableSearch" class="form-control search-input" placeholder="Search residents...">
                </div>
                <a href="register.php" class="btn btn-primary px-4 shadow-sm">
                    <i class="fa fa-plus me-2"></i>Add New
                </a>
            </div>
        </div>
    </div>

    <div class="card main-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="residentTable">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Present Address</th>
                        <th class="text-center">Family Size</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $res->fetch_assoc()): 
                        $id = $row['resident_id'];
                        $total_family = $row['family_count'] + 1;
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="fa fa-user text-secondary"></i>
                                </div>
                                <div>
                                    <span class="fw-bold d-block"><?= $row['last_name'] . ", " . $row['first_name'] ?></span>
                                    <span class="text-muted x-small" style="font-size: 0.75rem;">ID: #<?= str_pad($id, 5, '0', STR_PAD_LEFT) ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="text-muted">
                            <i class="fa fa-map-marker-alt me-1 small"></i> <?= $row['present_address'] ?>
                        </td>
                        <td class="text-center">
                            <span class="badge rounded-pill badge-family">
                                <i class="fa fa-users me-1"></i> <?= $total_family ?> Members
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="view_details.php?id=<?= $id ?>" class="btn btn-sm btn-view px-3 shadow-sm">
                                <i class="fa fa-eye me-1"></i> View Full Census
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            <small class="text-muted">Showing <?= $res->num_rows ?> total residents</small>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function(){
        $("#tableSearch").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#residentTable tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>

</body>
</html>