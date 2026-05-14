<?php 
    require_once "../session.php";
    require_once "../conn.php";
    require_once "../commonFunctions.php";

    if($_SESSION["role"] !== "Admin"){
        header("Location: ../registerLogin/login.php");
        exit();
    }

    $quiz_id = $_GET["id"];

    if(!isset($quiz_id)){
        echo "<script>alert('Please select a quiz to remove');
        window.location.href='quizManagement.php'; </script>";
        exit();
    }
    
    if(!mysqli_query($con, "UPDATE quiz 
                            SET is_deleted = 1
                            WHERE quiz_id = '$quiz_id'")){
        die("Error: ".mysqli_error($con));
    }

    echo "<script>alert('Quiz removed.');
        window.location.href='quizManagement.php';</script>";
?>