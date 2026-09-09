<?php
session_start();
include("config/db.php"); 

$count_residents = $conn->query("SELECT COUNT(*) FROM residents")->fetch_row()[0] ?? 0;
$Oresidents = $conn->query("SELECT COUNT(*) FROM resident_occupants")->fetch_row()[0] ?? 0;
$allresidents = $count_residents + $Oresidents;
$count_requests = $conn->query("SELECT COUNT(*) FROM establishment_requests")->fetch_row()[0] ?? 0;
$count_announcements = $conn->query("SELECT COUNT(*) FROM announcements")->fetch_row()[0] ?? 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $sql = "SELECT users.*, residents.status 
            FROM users 
            LEFT JOIN residents ON users.resident_id = residents.resident_id 
            WHERE users.username = '$username' AND users.password = '$password'";
    
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if ($user['status'] === 'Not Registered') {
            $error = "Account not registered yet.";
        } else {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['resident_id'] = $user['resident_id'];
            
            if ($user['role'] == 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: resident/r_dashboard.php");
            }
            exit;
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Barangay Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: url("assets/uploads/brgy.png") center / cover no-repeat;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .login-container {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            min-height: 600px;
            width: 100%;
        }

        .info-side {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .form-side {
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #ddd;
        }

        .btn-login {
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            background: #1e3c72;
            border: none;
            transition: 0.3s;
        }

        .btn-login:hover {
            background: #2a5298;
            transform: translateY(-2px);
        }

        .feature-icon {
            font-size: 1.5rem;
            margin-right: 15px;
            color: #ffc107;
        }

        @media (max-width: 991px) {
            .info-side { display: none; }
            body { padding: 20px; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row login-container g-0">
        <div class="col-lg-6 info-side">
            <img src="assets/uploads/brgylogo2.png" style="width: 8rem; margin-bottom: 10px;">
            <h1 class="fw-bold mb-3">Barangay Registry System</h1>
            <p class="lead mb-5 text-white-50">Empowering our community through a digital, transparent, and efficient management system.</p>
            
            <div class="row">
                <div class="col-6">
                    <div class="stat-card">
                        <h3 class="fw-bold mb-0"><?= number_format($allresidents) ?></h3>
                        <small class="text-white-50">Registered Residents</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="stat-card">
                        <h3 class="fw-bold mb-0"><?= number_format($count_requests) ?></h3>
                        <small class="text-white-50">Requests Processed</small>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-check-circle-fill feature-icon"></i>
                    <span>Real-time Barangay Announcements</span>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-check-circle-fill feature-icon"></i>
                    <span>Easy Establishment Permitting</span>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-check-circle-fill feature-icon"></i>
                    <span>Secure Resident Database</span>
                </div>
            </div>
        </div>

        <div class="col-lg-6 form-side">
            <div class="text-center mb-4 d-lg-none">
                <h2 class="fw-bold text-primary">Barangay Registry System</h2>
            </div>

                <div class="mb-3">
                    <a href="index.php" class="text-primary fw-bold text-decoration-none">Back to Home</a>
                </div>
            
            <h3 class="fw-bold mb-2">Welcome Back co-Barangay!</h3>
            <p class="text-muted mb-4">Please log in to your account to continue.</p>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?= $error ?></div>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                        <input type="text" name="username" class="form-control bg-light border-start-0" placeholder="Enter username" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control bg-light border-start-0" placeholder="Enter password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100 mb-3 text-white">
                    Sign In <i class="bi bi-arrow-right ms-2"></i>
                </button>

                <div class="text-center mt-3">
                    <span class="text-muted">New in our community?</span> 
                    <a href="register2.php" class="text-primary fw-bold text-decoration-none ms-1">Register Now</a>
                </div>

            </form>

            <div class="mt-5 pt-4 border-top text-center text-muted">
                <small>&copy; 2026 Barangay Management System. All Rights Reserved.</small>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>