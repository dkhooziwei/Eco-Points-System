<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if(!isset($_SESSION["mySession"]) || !isset($_SESSION["role"])) {
        header("Location: ../registerLogin/login.php");
        exit();
    }
?>