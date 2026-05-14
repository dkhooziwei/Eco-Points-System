<?php
require_once '../session.php';
require_once '../conn.php';
if($_SESSION["role"] !== "Admin"){
        header("Location: ../registerLogin/login.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Announcement</title>
    <link rel="stylesheet" href="adminA.css">
</head>
<body>
    <?php include "adminTaskbar.php"; ?>
    <div id="c1">
        <p>Send an Announcement</p>
    </div>
    <div id = "c2">
        <form action="#" method="post" enctype="multipart/form-data">
        <label> Title: </label>
        <input type="text" name="title" required>
</br>
        <label> Content: </label>
        <textarea name="content" required></textarea>
</br>
        <label> Upload a photo: </label>
        <input type="file" name="announcement_photo" accept="image/*">
</div>
    <div class="button-container">
            <button type="submit" name="submitBtn">Submit</button>
    </div>
    </form>
<?php

    function get_next_id($con, $table, $prefix, $id_name) {
        $sql = "SELECT $id_name FROM $table 
                WHERE $id_name LIKE '$prefix%'
                ORDER BY $id_name DESC
                LIMIT 1";
        $result = mysqli_query($con, $sql);
        if($result){
            $row_count = mysqli_num_rows($result);
            if($row_count == 0){
                return $prefix . "001";
            } else {
                $row = mysqli_fetch_assoc($result);
                $number = intval(substr($row[$id_name], strlen($prefix)));
                return $prefix . str_pad($number + 1, 3, "0", STR_PAD_LEFT);
            }
        }
    }

    if (isset($_POST['submitBtn'])) {
        //include("conn.php");

        $ann_id = get_next_id($con, 'announcement', 'ANN', 'ann_id');
        $title = mysqli_real_escape_string($con, $_POST['title']);
        $content = mysqli_real_escape_string($con, $_POST['content']);
        $admin_id = $_SESSION['mySession']; 
        $created_at = date('Y-m-d H:i:s'); 


$db_file_path = ""; 

if (isset($_FILES['announcement_photo'])) {
    if ($_FILES['announcement_photo']['error'] !== 0) {
        $errorCode = $_FILES['announcement_photo']['error'];
        echo "<script>alert('Upload Error Code: $errorCode');</script>";
    } else {

        $target_dir = "../uploads/announcements/";
        
        $db_save_dir = "uploads/announcements/"; 

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (!is_writable($target_dir)) {
            die("Error: The directory $target_dir is not writable.");
        }

        $file_name = time() . "_" . basename($_FILES["announcement_photo"]["name"]);
        
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["announcement_photo"]["tmp_name"], $target_file)) {

            $db_file_path = $db_save_dir . $file_name; 
        } else {
            echo "<script>alert('Failed to move file.');</script>";
        }
    }
}

        $sql = "INSERT INTO announcement (ann_id, title, content, file_path, created_at, admin_id)
                VALUES ('$ann_id', '$title', '$content', '$db_file_path', '$created_at', '$admin_id')";

        if (mysqli_query($con, $sql)) {
            echo '<script>alert("Announcement posted successfully!");
                  window.location.href = "adminA.php";
                  </script>';
        } else {
            die('Error: ' . mysqli_error($con));
        }
        
        mysqli_close($con);
    }
?>
</body>
</html>