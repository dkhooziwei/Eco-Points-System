<?php
require("../session.php");
include("../conn.php");
include("../commonFunctions.php");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$challenger_id = $_SESSION['mySession'];


if($_SERVER["REQUEST_METHOD"] == "POST" && isset ($_POST['reward_id'])){
    $reward_id = mysqli_real_escape_string($con,$_POST['reward_id']);

    /*Fetch reward details (points needed and current stock)*/
    $query = "
    SELECT name, points, stock FROM reward 
    WHERE reward_id = '$reward_id' AND is_deleted = 0
    ";
    $result = mysqli_query($con,$query);
    $reward = mysqli_fetch_assoc($result);

    if(!$reward){
        die("Invalid Reward ID.");
    }

    $points_needed = $reward['points'];
    $current_stock = $reward['stock'];

    /*Verify if challenger has enough points */
    $point_query = "
    SELECT c.total_points AS total
    FROM challenger c
    WHERE challenger_id = '$challenger_id'
    ";


    $point_result = mysqli_query($con,$point_query);
    $point_data = mysqli_fetch_assoc($point_result);
    $challenger_point = (int)($point_data['total'] ?? 0);
    

    /*Logic check*/
    if($current_stock <= 0){
        echo "<script>alert('Error: This item is out of stock!'); window.location.href='rewards.php';</script>";
        exit();
    }

    if($challenger_point < $points_needed){
        echo "<script>alert('Error: You do not have enough points!(Balance: $challenger_point, Required: $points_needed)'); window.location.href='rewards.php';</script>";
        exit();
    }

    /*Database Updates*/
    mysqli_begin_transaction($con);

    try{

        $new_history_id = get_next_id($con, 'point_history', 'PH', 'pt_history_id');
        /*-Deduct stock from reward table */
        $update_stock = "
            UPDATE reward
            SET stock = stock - 1 
            WHERE reward_id = '$reward_id' AND stock > 0 AND is_deleted = 0
        ";
        mysqli_query($con,$update_stock);

        if(mysqli_affected_rows($con) < 1){
            throw new Exception("Stock deduction failed. Item may be out of stock.");
        }

        /*Deduct challenger's points by adding a 'Spend' record in point_history table*/
        $insert_point_history = "
            INSERT INTO point_history (pt_history_id, challenger_id, points, transaction_type, `date`, `time`, source_type, reward_id)
            VALUES ('$new_history_id', '$challenger_id', $points_needed, 'Spend', CURDATE(), CURTIME(), 'Reward', '$reward_id')";

        mysqli_query($con,$insert_point_history);

        if(mysqli_affected_rows($con)!==1){
            throw new Exception("Point history record did not insert successfully.");
        }

        $update_challenger_points = "
            UPDATE challenger
            SET total_points = total_points -" . (int)$points_needed . "
            WHERE challenger_id = '$challenger_id'
        ";
        mysqli_query($con, $update_challenger_points);

        if(mysqli_affected_rows($con) !== 1){
            throw new Exception("Failed to update challenger total points.");
        }

        /*Commit both changes to the database */
        mysqli_commit($con);
        $rewardName = addslashes($reward['name']);


        echo"<script>alert('Success! " . $rewardName . " redeemed.\\n' +
        '" . $points_needed . " points deducted. \\n\\n' +
        'Redemption Code: " . $reward_id . "\\n\\n' +
        'Please present this screen or the reference code at the counter to collect your reward.\\n' +
        '(Please screenshot this page)'
        ); 
        window.location.href='rewards.php';
        </script>";

    } catch(Exception $e){
        mysqli_rollback($con);
        $error_msg = addslashes($e->getMessage());
        echo "<script>alert('System error: $error_msg'); window.location.href='rewards.php';</script>";
    }
    
}else{
    header("Location: rewards.php");
}
?>
