<?php 
    require_once "../session.php";
    require_once "../conn.php";
    require_once "../commonFunctions.php";

    if($_SESSION["role"] !== "Admin"){
        header("Location: ../registerLogin/login.php");
        exit();
    }

    $challenge_id = $_GET["id"];
    $ach_id = $_GET["id2"];

    if(!isset($challenge_id) || !isset($ach_id)){
        echo "<script>alert('Please select a challenge to remove');
        window.location.href='challengeManagement.php'; </script>";
        exit();
    }

    if(!mysqli_query($con, "UPDATE challenge 
                            SET is_deleted = 1
                            WHERE challenge_id = '$challenge_id'")){
        die("Error: ".mysqli_error($con));
    }

    if(!mysqli_query($con, "UPDATE achievement 
                            SET is_deleted = 1
                            WHERE ach_id = '$ach_id'")){
        die("Error: ".mysqli_error($con));
    }

    echo "<script>alert('Challenge removed.');
        window.location.href='challengeManagement.php';</script>";
?>