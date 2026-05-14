<?php
require_once '../session.php';
require_once '../conn.php';
if($_SESSION["role"] !== "Admin"){
        header("Location: ../registerLogin/login.php");
        exit();
    }
// Rest of your page code
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Homepage</title>
<style>
    body{
    background-color: #FFFDF1;
    font-family: "Josefin Sans", sans-serif;
    }
    .banner{
    margin: 20px;
    }
    table {
        border-collapse: collapse;
        table-layout: fixed;
        margin-top: 20px;
    }
    table, th, td {
        border: none;
    }
    td {
        height: 100px;
        width: 200px;
        text-align: center;
        vertical-align: middle;
        overflow: hidden;
    }
    .button {
        border: none;
        color: white;
        background-color: #80B155;
        width: 340px;  
        height: 110px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        cursor: pointer;
        margin: 0;
        border-radius: 10px;
        font-weight: bold;
    }
    /*---------------UI---------------------------------------------------------------------------*/
    /* MOBILE */
    @media (max-width: 600px){
table {
    display: block;
    width: 100% !important;
    table-layout: auto !important;
  }

  tr {
    display: grid;
    grid-template-columns: repeat(3, 1fr); 
    gap: 10px;
    width: 100%;
    margin-bottom: 10px;
    justify-items: center;
  }

  td {
    display: block;
    width: 100% !important;
    height: auto !important;
  }

  .button {
    width: 100% !important;
    aspect-ratio: 1 / 1; 
    height: auto !important;
    font-size: 12px; 
    padding: 5px;
    word-wrap: break-word; 
  }


  tr td:only-child {
    grid-column: 2; 
  }
    }
    
    /* TABLET  */
    @media (min-width: 601px) and (max-width: 1200px) {

    }
    
    /* DESKTOP  */
    @media (min-width: 1200px) {

    }
</style>
</head>
<body>
    <?php include "adminTaskbar.php"; ?>
    <div id ="banner">
    <?php include "../eventbanner.php"; ?>    
    </div>
<!-- Change button name and file path if needed -->
<div id= "button">
    <table style="width:100%">
    <tr>
        <td><a href="admin User Management.php"><button class="button button1">User Management</button></a></td>
        <td><a href="admin Challenge Analysis Report.php"><button class="button button1">Reports</button></a></td>
        <td><a href="rewardManagement.php"><button class="button button1">Rewards Management</button></a></td>
    </tr>
</table>
    <table style="width:100%">
    <tr>
        <td><a href="challengeManagement.php"><button class="button button1">Challenge Management</button></a></td>
        <td><a href="adminProposal.php"><button class="button button1">Proposals</button></a></td>
        <td><a href="verifyProof.php"><button class="button button1">Proof Verification</button></a></td>
    </tr>
    </table>
    <table style="width:100%">
    <tr>
        <td><a href="quizManagement.php"><button class="button button1">Daily Quiz Management</button></a></td>
    </tr>
    </table>
</div>
</body>
</html>