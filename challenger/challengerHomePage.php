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

    if(isset($_POST["submit_btn"])){
        $attempt_id = get_next_id($con, "quiz_attempt", "QA", "attempt_id");
        $quiz_id = $_POST["quiz_id"];
        $challenger_id = $_SESSION["mySession"];
        $result = $_POST["is_correct"];
        $attempt_date = date("Y-m-d");

        $sql = "INSERT INTO quiz_attempt (
        attempt_id,
        quiz_id,
        challenger_id,
        result,
        attempt_date)

        VALUES (
        '$attempt_id',
        '$quiz_id',
        '$challenger_id',
        '$result',
        '$attempt_date')";

        if(!mysqli_query($con, $sql)){
            die("Error: ".mysqli_error($con));
        }

        if($result == "Correct"){
            $points = (int) $_POST["points"];
            $attempt_time = date("H:i:s");
            $pt_history_id = get_next_id($con, "point_history", "PH", "pt_history_id");
            $transaction_type = "Earn";
            $source_type = "Daily Quiz";

            $sql2 = "INSERT INTO point_history (
            pt_history_id,
            challenger_id,
            date,
            time,
            points,
            transaction_type,
            source_type,
            quiz_id)
        
            VALUES (
            '$pt_history_id',
            '$challenger_id',
            '$attempt_date',
            '$attempt_time',
            '$points',
            '$transaction_type',
            '$source_type',
            '$quiz_id')";

            if(!mysqli_query($con, $sql2)){
                die("Error: ".mysqli_error($con));
            }

            $sql3 = "SELECT * FROM challenger WHERE challenger_id = '$challenger_id'";
            $result = mysqli_query($con, $sql3);
            if(!$result){
                die("Error: ".mysqli_error($con));
            }

            $row = mysqli_fetch_assoc($result);
            $total_points = (int) $row["total_points"];
            $updated_points = $total_points + $points;
            $sql4 = "UPDATE challenger SET total_points = '$updated_points' WHERE challenger_id = '$challenger_id'";
            if(!mysqli_query($con, $sql4)){
                die("Error: ".mysqli_error($con));
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Challenger - Home</title>
    <link rel="stylesheet" href="../commonStyle.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito+Sans&display=swap');

        * {
            box-sizing: border-box;
        }

        #challenges_box {
            flex: 0 0 73%;
            height: 270px;
            min-height: 230px;
            padding: 15px;
            background: radial-gradient(circle, #d7f0c2ff, #ffffffff);
            border-radius: 10px;
            box-shadow: 0 0 10px #6e974aff;
        }

        .features-label {
            font-size: 20px;
            font-weight: bold;
            color: #336A29;
        }

        #challenges_box a, 
        #points_right a, 
        #badge_header a, 
        #rewards a {
            font-size: 18px;
            float: right;
            text-decoration: none;
            font-weight: 900;
            color: #316727ff;
            text-shadow: 1px 1px 4px #76cd66ff;
        }

        #challenges_box a:hover, 
        #points_right a:hover, 
        #badge_header a:hover, 
        #rewards a:hover {            
            color: #49973bff;
        }

        .challenges-row {
            display: flex;
            gap: 15px;
            width: 100%;
            overflow-x: auto;
            padding-bottom: 10px;
        }

        .challenge-wrapper {
            flex: 0 0 auto;
            width: 32%;
            height: 150px;
            display: flex;
            align-items: center;
            padding: 10px;
            background-color: white;
            border-radius: 10px;
            border: 1px solid #5c8a42ff;
            box-shadow: 0 0 5px #C1D95C;
            box-sizing: border-box;
        }

        .challenge-left {
            display: flex;
            flex-direction: column;
            width: 60%;
        }

        .challenge-name {
            min-height: 60%;
            font-weight: bold;
            font-size: 16px;
        }

        .donut-wrapper {
            width: 50%;
            display: flex;
            justify-content: flex-end;
        }

        .donut {
            --size: 120px;
            --thickness: 12px;

            width: var(--size);
            height: var(--size);
            border-radius: 50%;

            background:
                conic-gradient(
                    #498428 0% var(--percent),
                    #C1D95C 0% 100%
                );
            
                display: flex;
                align-items: center;
                justify-content: center;

                position: relative;
                margin: auto;
                box-shadow: 0 0 10px #498428;
        }

        .donut::before {
            content: "";
            width: calc(var(--size) - var(--thickness) * 2);
            height: calc(var(--size) - var(--thickness) * 2);
            background: #FFFDF1;
            border-radius: 50%;
            position: absolute;
            box-shadow: 0 0 5px #498428;
        }

        .donut span {
            position: relative;
            font-weight: bold;
            font-size: 20px;
            color: #4aa746ff;
        }

        .btn {
            width: 100px;
            height: 35px;
            border-radius: 5px;
            cursor: pointer;
            font-family: "Nunito Sans", sans-serif;
            font-weight: bold;
            font-size: 16px;
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
        }

        .btn.not_joined:hover {
            background-color: #90c760ff;
        }

        #points_wrapper, #badge_wrapper {
            width: 100%;
            height: 50%;
            min-height: 110px;
            background: radial-gradient(circle, #ffffffff, #e5f794ff);
            border-radius: 10px;
            box-shadow: 0 0 10px #6e974aff;
            padding: 10px;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        #badge_wrapper {
            flex-direction: column;
        }

        #points_label {
            font-weight: bold;
            font-size: 18px;
            color: #498428;
        }

        #points {
            font-size: 45px;
            font-weight: bold;
            color: #336A29;
            margin-left: 5%;
        }

        #points_right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            margin-left: auto;
            gap: 20px;
        }
        
        .dashboard-row {
            display: flex;
            flex-direction: row;
            gap: 15px;
            margin-bottom: 15px;
        }

        #badge_wrapper label {
            margin-left: 3%;
        }

        #badges {
            display: flex;
            flex-wrap: nowrap;
            justify-content: flex-start;
            align-items: center;
            gap: 25px;
            padding: 5px;
        }

        #badges img {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }

        #badge_header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            box-sizing: border-box;
        }

        .right-column {
            width: 30%;
            height: 100%;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        #daily_quiz {
            flex: 0 0 73%;
            padding: 15px;
            background: linear-gradient(to right, #ffffffff, #c4cf94ff, #8cb67eff);
            border-radius: 10px;
            box-shadow: 0 0 10px #6e974aff;
        } 

        #quiz {
            padding: 20px;
        }

        .options {
            display: block;
            margin-bottom: 3px;
        }

        #submit_btn {
            width: 170px;
            height: 40px;
            border-radius: 5px;
            cursor: pointer;
            font-family: "Nunito Sans", sans-serif;
            font-weight: bold;
            font-size: 16px;
            color: #407222ff;
            background-color: #C1D95C;
            border: 3px solid #498428;        
        }

        #submit_btn:hover {
            background-color: #90c760ff;
        }

        #submit_btn:disabled {
            color: #2e2e2dff;
            background-color: #b2b2b1ff;
            border: 3px solid #2e2e2dff;           
            cursor: not-allowed;
        }

        .dashboard-row2 {
            margin-top: 15px;
            display: flex;
            flex-direction: row;
            gap: 15px;
            margin-bottom: 15px;
        }

        #rewards {
            flex: 0 0 26%;
            padding: 15px;
            background: radial-gradient(circle, #d7f0c2ff, #ffffffff);
            border-radius: 10px;
            box-shadow: 0 0 10px #6e974aff;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .reward-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: white;
            border-radius: 10px;
            border: 2px solid #C1D95C;
            box-shadow: 0 0 5px #C1D95C;
            box-sizing: border-box;
            padding: 10px;
            gap: 15px;
        }

        .reward-wrapper img {
            width: 70px;
            height: 70px;
        }

        #rewards_right {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .rewards-label {
            font-size: 18px;
            font-weight: bold;
        }

        .points-label {
            font-weight: 
        }

        #rewards a {
            text-align: center;
        }

        @media (max-width: 600px){
            body {
                margin: 0;
            }

            .dashboard-row,
            .dashboard-row2 {
                display: flex;
                flex-direction: column;
                gap: 15px;
            }

            #points_wrapper {
                padding: 15px;
                order: 1;
                height: 100px;
                gap: 50px;
                align-items: center;
            }
            #badge_wrapper {
                padding: 15px;
                order: 2;
            }
            #challenges_box {
                order: 3;
            }

            #challenges_box,
            #daily_quiz,
            #rewards,
            #points_wrapper,
            #badge_wrapper {
                width: 100%;
            }

            .right-column {
                width: 100%;
                min-width: unset;
            }

            .challenges-row {
                gap: 15px;
            }

            .challenge-wrapper {
                width: 100%;
                flex-direction: row;
            }

            .donut-wrapper {
                margin-left: auto;
            }

            #points {
                font-size: 45px;
                margin-left: 15px;
            }

            #badges {
                justify-content: center;
                height: 80px;
                margin-top: 10px;
                gap: 30px;
            }

            #badges img {
                width: 80px;
                height: 80px;
            }

            .reward-wrapper {
                margin-left: 4%;
                gap: 30px;
                width: 90%;
            }

            #daily_quiz, #rewards, #points_wrapper, #badge_wrapper {
                width: 100%;
            }
        }

        @media (min-width: 601px) and (max-width: 1000px){
            .dashboard-row,
            .dashboard-row2 {
                flex-direction: column;
                gap: 20px;
            }

            #challenges_box,
            #daily_quiz,
            #rewards,
            #points_wrapper,
            #badge_wrapper {
                width: 100%;
            } 

            .right-column {
                width: 100%;
                flex-direction: row;
                gap: 15px;
            }

            .challenges-row {
                gap: 20px;
                padding-bottom: 15px;
            }

            .challenge-wrapper {
                width: 48%;
                min-width: 300px;
            }

            .donut {
                --size: 100px;
                --thickness: 10px;
            }

            .donut span {
                font-size: 16px;
            }

            #points_wrapper,
            #badge_wrapper {
                flex: 1;
                min-height: 120px;
            }

            #badges img {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>
<body>
    <?php 
        include "challengerTaskbar.php"; 
        include "../eventbanner.php";
    ?>
    
    <br>

    <div class="dashboard-row">
        <div id="challenges_box"> <!-- contains the 3 challenges displayed -->
            <label class="features-label">Challenges</label>
            <a href="trackChallenges.php" id="show_all">Show All</a>
            <br><br>

            <div class="challenges-row"> <!-- to let challenges display in a row -->
            <?php 
                $sql = "SELECT * FROM challenge
                        WHERE ((CURRENT_DATE() > start_date AND end_date IS NULL)
                        OR CURRENT_DATE() BETWEEN start_date AND end_date)
                        AND is_deleted = 0
                        LIMIT 3";
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

                        echo "<div class='challenge-left'>"; // arranges challenge name and buttons to the left
                        echo "<label class='challenge-name'>".$row['name']."</label><br>";

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
                        
                        echo "<button class='btn $status' $disabled onclick='join_challenge(this, \"$challenge_id\")'>$message</button>";
                        echo "</div>";

                        $challenge_progress = number_format(0, 2);
                        if($row_count == 1){
                            $challenge_progress = $row2["current_progress"];
                        }

                        echo "<div class='donut-wrapper'>
                                <div class='donut' style='--percent: $challenge_progress%;'>
                                    <span>$challenge_progress%</span>
                                </div>
                            </div><br>";

                        echo "</div>";

                    }
                }
                
            ?>
            </div>
        </div>
        
        <div class="right-column">
        <div id="points_wrapper">
            <?php 
                $challenger_id = $_SESSION["mySession"];
                $sql = "SELECT * FROM challenger WHERE challenger_id = '$challenger_id'";
                $result = mysqli_query($con, $sql);
                if(!$result){
                    die("Error: " . mysqli_error($con));
                }
                $row = mysqli_fetch_assoc($result);
                $total_points = $row["total_points"];
            ?>
            <label id="points"><?php echo $total_points?></label>

            <div id="points_right">
                <label id="points_label">Eco Points</label>
                <a href="ecoPointsHistory.php">View Points History</a>
            </div>
        </div>

        <div id="badge_wrapper">
            <div id="badge_header">
                <label class="features-label">Badges</label>
                <a href="viewAchievement.php">View More</a>
            </div>
            <div id="badges">
                <?php 
                    $challenger_id = $_SESSION["mySession"];
                    $sql = "SELECT * FROM achievement_history 
                            INNER JOIN achievement
                            ON achievement_history.ach_id = achievement.ach_id
                            WHERE challenger_id = '$challenger_id' 
                            AND achievement.type = 'Badge'
                            AND is_deleted = 0
                            LIMIT 3";
                            
                    $result = mysqli_query($con, $sql);
                    if(!$result){
                        die("Error: " . mysqli_error($con));
                    }

                    if(mysqli_num_rows($result) == 0){
                        echo "<label>No badges found.</label>";
                    }
                    else{
                        while($row = mysqli_fetch_assoc($result)){
                            $file_path = $row["source"];

                            echo "<img src='../$file_path' alt='badge'>";
                        }
                    }
                ?>
            </div>
        </div>
        </div>
    </div>

    <div class="dashboard-row2">
        <div id="daily_quiz">
            <label class="features-label">Daily Quiz</label><br>

            <?php 
                $challenger_id = $_SESSION["mySession"];
                $current_date = date("Y-m-d");
                $sql = "SELECT * FROM quiz WHERE date_active = '$current_date'";

                $result = mysqli_query($con, $sql);
                if(!$result){
                    die("Error: ".mysqli_error($con));
                }

                $row = mysqli_fetch_assoc($result);

                if(mysqli_num_rows($result) == 0 || $row["is_deleted"] == 1){
                    echo "<p>No quiz for today 😊</p>";
                }
                else{
                    $quiz_id = $row["quiz_id"];
                    $sql2 = "SELECT * FROM quiz_attempt WHERE challenger_id = '$challenger_id' AND quiz_id = '$quiz_id'";
                    $result2 = mysqli_query($con, $sql2);
                    if(!$result2){
                        die("Error: " . mysqli_error($con));
                    }

                    if(mysqli_num_rows($result2) == 1){
                        echo "<p>You have completed today's daily quiz ✅</p>";
                    }
                    else {
                        echo'<form id="quiz" method="post" onsubmit="return check_quiz_answer()">
                                <h3>'.$row["title"].'</h3>
                                <label id="question">'.$row["question_text"].'</label><br><br>
                                <label class="options"><input type="radio" name="options" value="Option 1" required>'.$row["option1"].'</label><br>
                                <label class="options"><input type="radio" name="options" value="Option 2" required>'.$row["option2"].'</label><br>
                                <label class="options"><input type="radio" name="options" value="Option 3" required>'.$row["option3"].'</label><br>
                                <label class="options"><input type="radio" name="options" value="Option 4" required>'.$row["option4"].'</label><br>
                                <input type="hidden" id="correct_option" value="'.$row["correct_option"].'">
                                <input type="hidden" id="is_correct" name="is_correct">
                                <input type="hidden" id="quiz_id" name="quiz_id" value="'.$quiz_id.'">
                                <input type="hidden" id="quiz_points" value="'.$row["points"].'">
                                <p id="quiz_result"></p>
                                <p id="points_rewarded"></p><br>
                                <button type="submit" name="submit_btn" id="submit_btn">Submit Answer</button>
                            </form>';
                    }
                }
            ?>
        </div>

        <div id="rewards">
            <label class="features-label">Rewards</label>
            <?php 
                $sql = "SELECT * FROM reward 
                        WHERE is_deleted = 0
                        LIMIT 3";
                $result = mysqli_query($con, $sql);
                if(!$result){
                    die("Error: ".mysqli_error($con));
                }

                while($row = mysqli_fetch_assoc($result)){
                    echo "<div class='reward-wrapper'>";
                    echo "<img src='../".$row['file_path']."' alt='Reward 1'>";
                    echo "<div id='rewards_right'>";
                    echo "<label class='rewards-label'>".$row['name']."</label>";
                    echo "<label class='points-label'>Points Required: ".$row['points']."</label>";
                    echo "</div>";
                    echo "</div>";
                }
            ?> 
            <a href="rewards.php">View More</a>
        </div>
    </div>

    <script>
        function join_challenge(button, challenge_id){
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
            .then(res => res.text())
            .then(data => console.log("Challenge join response:", data))
            .catch(err => console.error(err));
        }

        function check_quiz_answer(){
            const selected = document.querySelector("input[name='options']:checked");
            const correct_answer = document.getElementById("correct_option").value;
            const submit_btn = document.getElementById("submit_btn");
            const quiz_id = document.getElementById("quiz_id").value;
            const points = parseInt(document.getElementById("quiz_points").value);

            const quiz_result = document.getElementById("quiz_result");
            const points_rewarded = document.getElementById("points_rewarded");
            const is_correct_input = document.getElementById("is_correct");

            if(selected.value === correct_answer){
                quiz_result.innerText = "✅ Correct Answer";
                points_rewarded.innerText = points + " points have been rewarded!";
                is_correct_input.value = "Correct";
            }
            else{
                quiz_result.innerText = "❌ Wrong Answer";
                points_rewarded.innerText = "No points awarded.";
                is_correct_input.value = "Incorrect";
            }

            submit_btn.disabled = true; //disable the submit button after submission
            submit_btn.innerText = "Answer Submitted";

            document.querySelectorAll("input[name='options']").forEach(radio => {
                radio.disabled = true;
            }); //disable the radio buttons after submission

            //send result to PHP via fetch
            fetch(window.location.href, {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "submit_btn=1" +
                    "&quiz_id=" + encodeURIComponent(quiz_id) +
                    "&is_correct=" + encodeURIComponent(is_correct_input.value) +
                    "&points=" + encodeURIComponent(points)
                //sending the form data needed to PHP
            })
            .then(response => response.text())
            .then(data => {
                console.log("Server response: ", data);

                // update total points if correct
                if(is_correct_input.value === "Correct"){
                    const total_points = document.getElementById("points");
                    points.innerText = parseInt(points.innerText) + total_points;
                }
            })
            .catch(err => console.error(err));

            return false; //prevents page from reloading
        }
    </script>
</body>
</html>