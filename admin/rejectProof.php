<?php 
    require_once "../session.php";
    require_once "../conn.php";
    require_once "../commonFunctions.php";

    if($_SESSION["role"] !== "Admin"){
        header("Location: ../registerLogin/login.php");
        exit();
    }

    $proof_id = $_GET["id"];

    if(!isset($proof_id)){
        echo "<script>alert('Please select a proof to reject');
        window.location.href='viewProof.php?=$proof_id'; </script>";
        exit();
    }

    $sql = "UPDATE proof SET status = 'Rejected' WHERE proof_id = '$proof_id'";
    if(!mysqli_query($con, $sql)){
        die("Rejection Failed: ".mysqli_error($con));
    }

    echo "<script>alert('Submission rejected!');
            window.location.href='viewProof.php?id=$proof_id';</script>";
?>