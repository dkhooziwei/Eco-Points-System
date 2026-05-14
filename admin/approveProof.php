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
        echo "<script>alert('Please select a proof to approve');
        window.location.href='viewProof.php?=$proof_id'; </script>";
        exit();
    }

    $sql = "SELECT proof.challenger_id, proof.challenge_id, 
            challenge_participation.status AS participation_status, 
            challenge_participation.current_progress,
            challenge.no_of_times
            FROM proof INNER JOIN challenge_participation
            ON proof.challenger_id = challenge_participation.challenger_id
            AND proof.challenge_id = challenge_participation.challenge_id
            INNER JOIN challenge
            ON proof.challenge_id = challenge.challenge_id
            WHERE proof.proof_id = '$proof_id'";
    $result = mysqli_query($con, $sql);

    if(!$result){
        die("Error: ".mysqli_error($con));
    }

    if(mysqli_num_rows($result) === 0){
        die("No proof found with id $proof_id.");
    }

    $row = mysqli_fetch_assoc($result);
    $challenger_id = $row["challenger_id"];
    $challenge_id = $row["challenge_id"];
    $participation_status = $row["participation_status"];
    $current_progress = $row["current_progress"];
    $no_of_times = $row["no_of_times"];

    if($participation_status === "Completed"){
        echo "<script>alert('This challenge has been completed. Proof cannot be approved.');
        window.location.href='viewProof.php?id=$proof_id';</script>";
    }
    else{
        //enable mysqli exceptions
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try{
            mysqli_begin_transaction($con);

            // 1. update proof status
            $update_status_sql = "UPDATE proof SET status = 'Approved' WHERE proof_id = '$proof_id'";
            mysqli_query($con, $update_status_sql);

            // 2. calculate progress
            if($no_of_times === 0){
                die("Invalid challenge: number of times is zero.");
            }
            $current_no_of_times = (int) round($current_progress / 100 * $no_of_times);
            $new_progress = round(($current_no_of_times + 1) / $no_of_times * 100, 2);
            if($new_progress > 100.00){
                $new_progress = 100.00;
            }

            // 3. update challenge progress
            $update_progress_sql = "UPDATE challenge_participation
                    SET current_progress = $new_progress
                    WHERE challenger_id = '$challenger_id'
                    AND challenge_id = '$challenge_id'";
            mysqli_query($con, $update_progress_sql);

            // 4. If challenge completed:
            if($new_progress >= 100){

                $get_ach_sql = "SELECT achievement.ach_id, challenge.points
                        FROM achievement INNER JOIN challenge
                        ON achievement.challenge_id = challenge.challenge_id
                        WHERE challenge.challenge_id = '$challenge_id'";
            
                $result = mysqli_query($con, $get_ach_sql);
                $row = mysqli_fetch_assoc($result);
                $ach_id = $row["ach_id"];
                $points = $row["points"];

                $get_total_sql = "SELECT total_points FROM challenger WHERE challenger_id = '$challenger_id'";
                $result = mysqli_query($con, $get_total_sql);
                $row = mysqli_fetch_assoc($result);

                // 5. Calculate current total points
                $total_points = $row["total_points"];
                $current_points = $total_points + $points;
            
                $new_status = "Completed";
                $date = date("Y-m-d");
                $time = date("H:i:s");
                $new_ach_history_id = get_next_id($con, "achievement_history", "AH", "ach_history_id");
                $new_pt_history_id = get_next_id($con, "point_history", "PH", "pt_history_id");

                // 6. update challenge participation status
                $update_participation_sql = "UPDATE challenge_participation
                        SET status = 'Completed',
                        completion_date = '$date'
                        WHERE challenger_id = '$challenger_id'
                        AND challenge_id = '$challenge_id'";

                // 7. reward a new achievement to challenger
                $add_ach_sql = "INSERT INTO achievement_history(
                        ach_history_id, 
                        challenger_id, 
                        date, time, 
                        ach_id)
                        
                        VALUES(
                        '$new_ach_history_id',
                        '$challenger_id',
                        '$date','$time',
                        '$ach_id')";
                
                // 8. reward points to challenger 
                $add_reward_sql = "INSERT INTO point_history(
                        pt_history_id,
                        challenger_id,
                        date, time,
                        points, transaction_type,
                        source_type,
                        challenge_id)
                        
                        VALUES(
                        '$new_pt_history_id',
                        '$challenger_id',
                        '$date', '$time',
                        '$points',
                        'Earn', 'Challenge',
                        '$challenge_id')";

                // 9. update challenger's total points
                $update_points_sql = "UPDATE challenger
                        SET total_points = '$current_points'
                        WHERE challenger_id = '$challenger_id'";

                mysqli_query($con, $update_participation_sql);
                mysqli_query($con, $add_ach_sql);
                mysqli_query($con, $add_reward_sql);
                mysqli_query($con, $update_points_sql);
            }

            // save all database changes
            mysqli_commit($con);

            echo "<script>alert('Submission approved!');
                window.location.href='viewProof.php?id=$proof_id';</script>";
        }
        catch (mysqli_sql_exception $e){
            mysqli_rollback($con);
            die("Approval failed: ". $e -> getMessage());
        }
    }
?>