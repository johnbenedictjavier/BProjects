<?php
session_start();
include("../config/db.php");
/** @var mysqli $conn */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $new_username = mysqli_real_escape_string($conn, $_POST['username']);
    $new_password = $_POST['new_password'];
    $current_password_input = $_POST['current_password'];

    $user_query = $conn->query("SELECT * FROM users WHERE user_id = $user_id");
    $user = $user_query->fetch_assoc();

    if ($current_password_input === $user['password']) {
        
        $update_fields = "username = '$new_username'";
        
        if (!empty($new_password)) {
            $update_fields .= ", password = '$new_password'";
        }

        $sql = "UPDATE users SET $update_fields WHERE user_id = $user_id";
        
        if ($conn->query($sql)) {
            session_unset();
            session_destroy();
            
            echo "<script>
                alert('Profile updated successfully! Please log in again with your new credentials.');
                window.location.href = '../login.php'; 
            </script>";
            exit();
        } else {
            echo "Error updating record: " . $conn->error;
        }

    } else {
        echo "<script>
            alert('Error: Current password does not match.');
            window.history.back();
        </script>";
    }
}
?>