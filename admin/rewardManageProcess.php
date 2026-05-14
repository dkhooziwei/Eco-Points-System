<?php
require("../session.php");
include("../conn.php");
include("../commonFunctions.php");

if (isset($_POST['add_reward'])) {
    $reward_id = mysqli_real_escape_string($con, $_POST['reward_id']);
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $points = (int)$_POST['points'];
    $stock = (int)$_POST['stock'];

    
    $db_folder = "images/rewardsPIC/"; 
    $upload_dir = "../images/rewardsPIC/";
    $new_file_path = ""; 

    if (empty($reward_id)) {
        $new_id = get_next_id($con, 'reward', 'RW', 'reward_id');
        $is_new = true;
    } else {
        $is_new = false;
    }

    /* File Upload */
    if (isset($_FILES['reward_image']) && $_FILES['reward_image']['error'] == 0) {
        $extension = pathinfo($_FILES["reward_image"]["name"], PATHINFO_EXTENSION); //exp: jpg, png...
        $file_nameID = empty($reward_id) ? $new_id : $reward_id; //determine the ID use as the filename
        $file_name = $file_nameID . "." . $extension;
        $target_file = $upload_dir . $file_name;
        
        
        if (move_uploaded_file($_FILES["reward_image"]["tmp_name"], $target_file)) {
            $new_file_path = $db_folder . $file_name; 
        }
    }

    /* Add New Reward */
    if ($is_new){
        // If no image uploaded, use the default path
        $final_path = !empty($new_file_path) ? $new_file_path : "images/rewardsPIC/default.jpg";

        $sql = "INSERT INTO reward (reward_id, name, points, stock, file_path, is_deleted) 
                VALUES ('$new_id', '$name', $points, $stock, '$final_path', 0)";
    } else {
        /*Update Existing Reward */
        $sql = "UPDATE reward SET name= '$name', points= $points, stock= $stock";
        
        // ONLY update file_path if a new image was successfully moved
        if (!empty($new_file_path)) {
            $sql .= ", file_path='$new_file_path'";
        }
        $sql .= " WHERE reward_id='$reward_id' AND is_deleted = 0";
    }

    mysqli_query($con, $sql);
    header("Location: rewardManagement.php");
    exit;
}

/* Delete Reward */
if(isset($_GET['delete'])) {
    $reward_id = mysqli_real_escape_string($con, $_GET['delete']);
    $sql = "UPDATE reward SET is_deleted = 1 WHERE reward_id='$reward_id'";
    mysqli_query($con, $sql);
    header("Location: rewardManagement.php");
    exit;
}
?>