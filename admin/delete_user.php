<?php
    require_once "../session.php";      
    require_once "../conn.php";

    if($_SESSION["role"] !== "Admin"){
        header("Location: ../registerLogin/login.php");
        exit();
    }
    
if (isset($con) && !isset($conn)) { $conn = $con; } 
elseif (isset($conn) && !isset($con)) { $con = $conn; }

if (!isset($conn) || !$conn) {
    die("Database connection failed.");
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    
    $sql = "UPDATE user SET is_deleted = 1 WHERE user_id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        
        
        $stmt->bind_param("s", $id);

        if ($stmt->execute()) {
        
            echo "<script>alert('User deleted successfully.'); window.location.href='../admin/admin User Management.php';</script>";
        } else {
            echo "<script>alert('Error deleting user: " . $stmt->error . "'); window.location.href='../admin/admin User Management.php';</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('SQL Error: " . $conn->error . "'); window.location.href='../admin/admin User Management.php';</script>";
    }
} else {

    header("Location:../admin/admin User Management.php");
}
?>