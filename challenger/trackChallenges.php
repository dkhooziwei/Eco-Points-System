<?php 
    require_once "../session.php";
    require_once "../conn.php";
    require_once "../commonFunctions.php";

    if($_SESSION["role"] !== "Challenger"){
        header("Location: ../registerLogin/login.php");
        exit();
    }

    if(isset($_POST["challenge_id"])){
        $challenge_id = $_POST["challenge_id"];
        $challenger_id = $_SESSION["mySession"];
        $participation_id = get_next_id($con, "challenge_participation", "CP", "participation_id");
        $joined_date = date("Y-m-d");

        $sql = "INSERT INTO challenge_participation (
        participation_id,
        challenger_id,
        challenge_id,
        status,
        joined_date,
        completion_date,
        current_progress)

        VALUES (
        '$participation_id',
        '$challenger_id',
        '$challenge_id',
        'Joined',
        '$joined_date',
        NULL,
        0)";

        if(!mysqli_query($con, $sql)){
            die("Error: " . mysqli_error($con));
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Challenger - Track Challenges</title>
    <link href="../commonStyle.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito+Sans&display=swap');

        #challenges {
            font-weight: bold;
            font-size: 30px;
            padding: 20px;
        }

        #challenges_box {
            width: 90%;
            padding: 10px;
            display: flex;
            flex-direction: column;
            margin: 0 auto;
            gap: 25px;
        }

        .challenge-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            background: linear-gradient(to right, #d6ecaaff, #ffffffff);
            border-radius: 10px;
            box-shadow: 0 0 10px #6e974aff;
            box-sizing: border-box;
        }

        .challenge-left {
            display: flex;
            flex-direction: column;
            width: 50%;
            padding: 15px;
        }

        .challenge-right {
            display: flex;
            flex-direction: column;
            padding: 15px;
            width: 50%;
            height: 100%;
            gap: 10px;
        }

        .challenge-name {
            font-weight: bold;
            font-size: 22px;
        }

        .due-date {
            font-weight: bold;
            color: #d13c1bff;
            font-size: 18px;
        }

        .challenge-progress {
            padding: 10px;
            display: flex;
            justify-content: flex-end;
        }

        .progress-label {
            font-weight: bold;
            font-size: 18px;
            width: 20%;
        }
        
        .progress-bar {
            width: 70%;
            max-width: 400px;
            height: 30px;
            background: linear-gradient(to left, #C1D95C, #8ac866ff);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px #498428;
            border: 1px solid #6c6c6cff;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(to right, #294421ff, #508a2eff);
            width: 0;
            transition: width 0.4s ease;
        }

        .buttons {
            padding: 5px;
            display: flex;
            justify-content: flex-end;
            gap: 30px;
        }

        .upload-proof-btn {
            width: 130px;
            height: 40px;
            border-radius: 5px;
            cursor: pointer;
            font-family: "Nunito Sans", sans-serif;
            font-weight: bold;
            font-size: 18px;

            color: #ffffffff;
            background-color: #6ea629ff;
            border: 3px solid #598324ff;
            box-shadow: 0 0 5px #598324ff;
        }

        .upload-proof-btn:hover {
            background-color: #87b949ff;
        }

        .btn {
            width: 130px;
            height: 40px;
            border-radius: 5px;
            cursor: pointer;
            font-family: "Nunito Sans", sans-serif;
            font-weight: bold;
            font-size: 18px;
        }

        .btn.joined, .btn.completed {
            color: rgba(67, 67, 67, 1);
            background-color: rgba(199, 199, 199, 1);
            border: 3px solid rgba(112, 112, 112, 1);
            cursor: not-allowed;
        }

        .btn.not_joined {
            color: #407222ff;
            background-color: #C1D95C;
            border: 3px solid #498428;
            box-shadow: 0 0 5px #498428;
        }

        .btn.not_joined:hover {
            background-color: #90c760ff;
        }

        @media (max-width: 600px){
            #challenges {
                font-size: 24px;
                padding: 15px;
            }

            #challenges_box {
                width: 96%;
                gap: 20px;
            }

            .challenge-wrapper {
                flex-direction: column;
                padding: 10px;
                gap: 0;
            }

            .challenge-right {
                width: 100%;
                padding: 0;
            }
            
            .challenge-left {
                width: 100%;
                padding: 10px;
            }

            .challenge-name {
                font-size: 18px;
                margin-left: 3%;
            }

            .due-date {
                font-size: 16px;
                margin-left: 3%;
            }

            .challenge-progress {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }

            .progress-bar {
                width: 90%;
                max-width: none;
                height: 25px;
            }

            .btn {
                width: 120px;
                font-size: 16px;
                height: 40px;
            }

            .upload-proof-btn {
                width: 120px;
                font-size: 16px;
                height: 40px;
            }

            .buttons {
                width: 100%;
                flex-direction: row;
                gap: 10px;
                align-items: center;
                padding: 10px 0;
            }
        }

        @media (min-width: 601px) and (max-width: 1000px){
            #challenges {
                font-size: 26px;
                padding: 18px;
            }

            #challenges_box {
                width: 98%;
                gap: 20px;
            }

            .challenge-wrapper {
                flex-direction: row;
                padding: 12px;
                gap: 20px;
            }

            .challenge-left {
                width: 40%;
            }
            
            .challenge-right {
                width: 60%;
            }

            .challenge-name{
                font-size: 20px;
            }

            .challenge-progress {
                gap: 20px;
            }
            .progress-bar {
                max-width: 300px;
                height: 28px;
            }

            .progress-label {
                font-size: 17px;
            }

            .btn, .upload-proof-btn {
                font-size: 16px;
                height: 38px;
                width: 120px;
            }

            .buttons {
                justify-content: flex-end;
                gap: 20px;
            }
        }

    </style>
</head>
<body>
    <?php include "challengerTaskbar.php"; ?>
    <br>
    <label id="challenges">Challenges</label>
    <br><br>
    <div id="challenges_box">
        <?php 
            $sql = "SELECT * FROM challenge 
                    WHERE ((CURRENT_DATE() > start_date AND end_date IS NULL)
                    OR CURRENT_DATE() BETWEEN start_date AND end_date)
                    AND is_deleted = 0";
            $result = mysqli_query($con, $sql);

            if(!$result){
                die("Error: ".mysqli_error($con));
            }

            if(mysqli_num_rows($result) == 0){
                    echo "<p>No challenges found.</p>";
                }
                else{
                    while($row = mysqli_fetch_assoc($result)){
                        echo "<div class='challenge-wrapper'>"; //each contains one challenge

                        echo "<div class='challenge-left'>";
                        echo "<label class='challenge-name'>".$row['name']."</label><br>";
                        
                        if($row["end_date"]){
                            echo "<p class='due-date'>END AT: ".$row["end_date"]."</p>";
                        }

                        echo "</div>";

                        echo "<div class='challenge-right'>"; 

                        $challenge_id = $row["challenge_id"];
                        $challenger_id = $_SESSION["mySession"];
                        $sql2 = "SELECT * FROM challenge_participation
                                WHERE challenge_id = '$challenge_id' 
                                AND challenger_id = '$challenger_id'";
                            
                        $result2 = mysqli_query($con, $sql2);
                        if(!$result2){
                            die("Error: ".mysqli_error($con));
                        }
                        
                        $row_count = mysqli_num_rows($result2);
                        $row2 = mysqli_fetch_assoc($result2);

                        $challenge_progress = number_format(0, 2);
                        if($row_count == 1){
                            $challenge_progress = $row2["current_progress"];
                        }
                        echo "<div class='challenge-progress'>
                                <label class='progress-label'>".$challenge_progress."%</label>
                                <div class='progress-bar'>
                                    <div class='progress-fill' style='width:".$challenge_progress."%;'></div>
                                </div>
                            </div>";

                        echo "<div class='buttons'>";
                        $challenge_type = $row["type"];

                        echo "<div class='upload-proof-container'></div>"; //placeholder for the upload proof button
                        if($challenge_type === "Event" && $row_count == 1 && $row2["status"] == "Joined"){
                            echo "<a href='uploadProof.php?id=$challenge_id'>
                                    <button class='upload-proof-btn'>Upload Proof</button>
                                </a>";
                        }

                        $status = "";
                        $disabled = "";
                        $message = "";
                        if($row_count == 0){
                            $status = "not_joined";
                            $message = "Join Now";
                        }
                        else if($row_count == 1 && $row2["status"] == "Joined"){
                            $status = "joined";
                            $message = "Joined";
                            $disabled = "disabled";
                        }
                        else{
                            $status = "completed";
                            $message = "Completed";
                            $disabled = "disabled";
                        }
                        
                        echo "<button class='btn $status' $disabled onclick='join_challenge(this, \"$challenge_id\", \"$challenge_type\")'>$message</button>";
                        echo "</div>";
                        echo "</div>";
                        echo "</div>";
                    }
                }
        ?>
    </div>

    <script>
        function join_challenge(button, challenge_id, challenge_type){
            if(!confirm("Are you sure you want to join this challenge?")){
                return;
            }

            button.classList.remove("not_joined");
            button.classList.add("joined");
            button.textContent = "Joined";
            button.disabled = true;

            //to allow instant UI updates without refreshing the page
            fetch(window.location.href, {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"}, 
                //tells the server how the data is formatted, same format as normal HTML form submission

                body: "challenge_id=" + challenge_id
            })
            .then(response => response.text())
            .then(data => {
                console.log("Server response:", data);
            })
            .catch(err => console.error(err));

            if(challenge_type == "Event"){
                const container = button.parentElement.querySelector(".upload-proof-container");
                container.innerHTML = `<a href='uploadProof.php?id=${challenge_id}'>
                                            <button class='upload-proof-btn'>
                                                Upload Proof
                                            </button>
                                        </a>`;
            }
        }
    </script>
</body>
</html>