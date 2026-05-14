<?php 
    require_once "../session.php";
    require_once "../conn.php";

    if($_SESSION["role"] !== "Admin"){
        header("Location: ../registerLogin/login.php");
        exit();
    }

    $proof_id = $_GET["id"];

    if(!isset($proof_id)){
        echo "<script>alert('Please select a proof to view');
        window.location.href='verifyProof.php'; </script>";
        exit();
    }

    if(isset($_POST["approve_btn"])){
        echo "<script>
            if(confirm('Are you sure you want to approve this submission?')){
                window.location.href='approveProof.php?id=$proof_id';
            }
        </script>";
    }

    if(isset($_POST["reject_btn"])){
        echo "<script>
            if(confirm('Are you sure you want to reject this submission?')){
                window.location.href='rejectProof.php?id=$proof_id';
            }
        </script>";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Proof Details</title>
    <link href="../commonStyle.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito+Sans&display=swap');

        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }
        
        .proof-top {
            padding: 20px;
            display: flex;
            font-size: 18px;
            font-weight: bold;
            margin-left: 15%;
            align-items: center;
            color: #334627ff;
            text-shadow: 1px 1px 1px #97c47bff;
        }

        #date_time {
            margin-left: auto;
            margin-right: 20%;
        }

        .details-wrapper {
            width: 70%;
            padding: 25px;
            margin: auto;
            display: flex;
            flex-direction: column;
            border: 2px solid #adbaa6ff;
            border-radius: 10px;
            box-shadow: 0 0 5px #adbaa6ff;
            background: white;
        }

        #submission_details {
            font-size: 18px;
            font-weight: bold; 
            margin-left: 10px;
        }

        .details {
            margin-left: 5px;
            margin-top: 15px;
            padding: 25px;
            width: 90%;
            height: 100%;
            border: 3px solid #6b9552ff;
            border-radius: 10px;
            box-shadow: 0 0 5px #6b9552ff;
            background: radial-gradient(circle, #bad8a9ff, #ffffffff);

            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .details label {
            font-size: 18px;
            font-weight: bold;
            color: #294d13ff;
        }

        .button-link {
            display: inline-block;
            padding: 15px 20px;
            border: 3px solid #385229ff;
            background: #a5cf8eff;
            border-radius: 10px;
            margin-left: 10px;

            text-decoration: none;
            font-weight: bold;
            font-size: 18px;
            color: #425837ff;
        }

        .button-link:hover {
            background: #d4f0c4ff;
        }

        .buttons {
            margin-top: 40px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .buttons button {
            width: 210px;
            height: 45px;
            border: 3px solid #396720ff;
            background: linear-gradient(to right, #1f3d0fff, #406c29ff);
            border-radius: 10px;
            color: white;
            font-weight: bold;
            font-family: "Nunito Sans", sans-serif;
            font-size: 15px;

            cursor: pointer;
        }

        .buttons button:hover {
            background: linear-gradient(to right, #366a1aff, #5f9045ff);
        }

        form.button-right {
            display: flex;
            margin-left: auto;
            gap: 20px;
        }

        .button-right button {
            font-size: 16px;
            width: 120px;
        }

        #approve_btn {
            border: 3px solid #4a7531ff;
            background: linear-gradient(to right, #4a7531ff, #77ab59ff);
            color: #ffffffff;
        }

        #approve_btn:hover {
            background: linear-gradient(to right, #84b26cff, #7ea567ff);
        }

        #reject_btn {
            border: 3px solid #971f1fff;
            background: linear-gradient(to right, #971f1fff, #cf4c4cff);
            color: #ffffffff;
        }

        #reject_btn:hover {
            background: linear-gradient(to right, #b03a3aff, #cb6565ff);
        }

        .popup-box {
            width: 400px;
            max-height: 0; /* height collapsed */
            opacity: 0; /* invisible */
            overflow: hidden; /* hide content overflow */
            padding: 0;
            margin-top: 0;

            background: white;
            border: 2px solid #84b26cff;
            border-radius: 10px;
            box-shadow: 0 0 8px #84b26cff;

            transition: all 0.2s ease; /* smooth animation */
        }

        .popup-box.show {
            max-height: 500px;
            opacity: 1;
            padding: 15px;
        }

        .popup-content h3 {
            margin-top: 0;
        }

        @media (max-width: 600px){
            .proof-top {
                margin-left: 0;
                font-size: 14px;
            }

            #date_time {
                margin-right: 0;
            }

            .details label {
                font-size: 14px;
            }

            .details-wrapper {
                width: 90%;
                padding: 15px;
            }

            .details {
                width: 90%;
                padding: 15px;
                margin-left: 0;
            }

            .button-link {
                font-size: 16px;
                padding: 12px 15px;
            }

            .buttons button {
                font-size: 14px;
                width: 100%;
            }

            form.button-right {
                width: 100%;
                justify-content: space-between;
            }

            .popup-box {
                width: 260px;
            }
        }

        @media (min-width: 601px) and (max-width: 1000px){
            .button-right button {
                width: 100px;
            }
        }

    </style>
</head>
<body>
    <?php 
        include "adminTaskbar.php";

        $sql = "SELECT * FROM proof
            INNER JOIN challenge
            ON proof.challenge_id = challenge.challenge_id
            WHERE proof.proof_id = '$proof_id'";

        $result = mysqli_query($con, $sql);
        if(!$result){
            die("Error: ".mysqli_error($con));
        }

        $row = mysqli_fetch_assoc($result);

        $date = $row["date"];
        $time = $row["time"];
        $challenger_id = $row["challenger_id"];
        $challenge_id = $row["challenge_id"];
        $file_path = $row["file_path"];
        $status = $row["status"];
        $challenge_name = $row["name"];
        $date = $row["date"];
        $time = $row["time"];
    ?>

    <div class="proof-top">
        <label>Submission ID: <?= $proof_id ?></label>
        <label id="date_time">Submitted at: <br><?= $date." ".$time ?></label>
    </div>

    <div class="details-wrapper">
        <label id="submission_details">Submission Details</label>

        <div class="details">
            <label>Challenger ID: <?= $challenger_id ?></label><br>
            <label>Challenge Name: <?= $challenge_name ?></label><br>
            <label>E-Certificate Submitted: 
                <a href="../<?= $file_path ?>" target="_blank" class="button-link"><?= $proof_id ?></a>
            </label><br>
            <label>Status: <?= $status ?></label>

            <?php 
                if($row["status"] === "Pending"){
                    echo '<div class="buttons">
                        <button id="view_previous_btn">View Previous Submissions</button>

                        <form method="post" class="button-right">
                            <button type="submit" id="approve_btn" name="approve_btn">Approve</button>
                            <button type="submit" id="reject_btn" name="reject_btn">Reject</button>
                        </form>
                    </div>';
                }
            ?>

            <div id="previous_popup" class="popup-box">
                <div class="popup-content">
                    <h3>Previous Submissions</h3>

                    <?php 
                        $sql2 = "SELECT proof_id FROM proof 
                                WHERE challenger_id = '$challenger_id'
                                AND challenge_id = '$challenge_id'
                                AND (date < '$date' OR (date = '$date' AND time < '$time'))";
                        
                        $result = mysqli_query($con, $sql2);

                        if(!$result){
                            die("Error: ".mysqli_error($con));
                        }

                        if(mysqli_num_rows($result) == 0){
                            echo "<p>No previous submissions.</p>";
                        }
                        else{
                            while($row = mysqli_fetch_assoc($result)){
                                $proof_id = $row["proof_id"];
                                echo "<a href='viewProof.php?id=$proof_id' class='button-link' target='_blank'>
                                        $proof_id</a>";
                            }
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        const view_previous_btn = document.getElementById("view_previous_btn");
        const previous_popup = document.getElementById("previous_popup");

        view_previous_btn.addEventListener("click", () => {
            previous_popup.classList.toggle("show");
        });
    </script>
</body>
</html>