<?php
require("../session.php");
include("../conn.php");
include("../commonFunctions.php");

if(isset($_POST['submit_proposal'])){
    $partner_id = $_SESSION['partner_id'] ?? 'P001';
    $proposal_title = mysqli_real_escape_string($con, $_POST['title']);
    $proposal_desc = mysqli_real_escape_string($con, $_POST['description']);
    $submitted_date = date('Y-m-d');
    $status = 'Pending'; 

    $db_folder = "uploads/proposals/";
    $upload_dir = "../uploads/proposals/";
    $new_file_path = "";

    $new_id = get_next_id($con, 'proposal', 'PP', 'proposal_id');
     
    /*Insert proposal into database*/
    if (isset($_FILES['proposal_file']) && $_FILES['proposal_file']['error'] == 0){
        $extension = pathinfo($_FILES["proposal_file"]["name"], PATHINFO_EXTENSION);

        $file_name = $new_id . "." . $extension;
        $target_file = $upload_dir. $file_name;

        if(move_uploaded_file($_FILES["proposal_file"]["tmp_name"], $target_file)){
            $new_file_path = $db_folder . $file_name;
        }
    }

    $sql = "INSERT INTO proposal (proposal_id, partner_id, title, description, file_path, date, status) 
                         VALUES ('$new_id', '$partner_id', '$proposal_title', '$proposal_desc', '$new_file_path', '$submitted_date', '$status')";
        
    if(mysqli_query($con, $sql)){
        $_SESSION['status'] = "success";
        $_SESSION['message'] = "Proposal submitted successfully!";
        header("Location: submitProposal.php");
        exit();
    } else {
        $_SESSION['proposal_error'] = "Error submitting proposal. Please try again.";
        header("Location: submitProposal.php");
        exit();
    }
    
}
?>