<?php
    include('../session.php');
    include("../conn.php");
    include("../commonFunctions.php");




    if($_SESSION["role"] !== "Recycling Centre Admin"){
        header("Location: ../registerLogin/login.php");
        exit();
    }


    $recycle_admin_id = $_SESSION['mySession'];
    


    //load types of recyclables
    $types = [];

    $sql_types = "SELECT recyclable_id, type FROM recyclable WHERE is_deleted = 0";
    $result_types = mysqli_query($con, $sql_types);

    if ($result_types) {
        while ($row = mysqli_fetch_assoc($result_types)) {
            $types[] = $row;
        }
    }

    // the confirmation page do not show dulu
    $showConfirm = false;
    $showSuccess = false;
    
    $inputData = [
        'challenger_id' => '',
        'type' => '',
        'weight' => ''
    ];

    if (isset($_POST['resetBtn'])) {
        $showConfirm = false;
        $inputData = [
            'challenger_id' => '',
            'type' => '',
            'weight' => ''
        ];
    }

    if (isset($_POST['submitBtn'])) {
    
        $weight = $_POST['weight'] ?? '0';

        $type = isset($_POST['type']) ? $_POST['type'] : '';
        $challenger_id = strtoupper(trim($_POST['challenger_id'] ?? ''));

        $sql_check_challenger = "SELECT challenger_id FROM challenger WHERE challenger_id = '$challenger_id'";
        $result_check = mysqli_query($con, $sql_check_challenger);

        if ($result_check && mysqli_num_rows($result_check) === 1) {

        

        
        //count point and prep data for confirm
        $point = point_calculation($weight, $type);

        $inputData = [
            'challenger_id' => $_POST['challenger_id'],
            'type' => $_POST['type'],
            'weight' => $_POST['weight']
        ];

        $showConfirm = true;
    }
    else{
        $showConfirm = false;
        $inputData = [
            'challenger_id' => $challenger_id,
            'type' => $type,
            'weight' => $_POST['weight']
        ];

        $errorMessage = "Challenger ID '$challenger_id' does not exist. Please enter a valid ID.";
   

    }
    }

    if (isset($_POST['cancelBtn'])) {
        $showConfirm = false;
        $inputData = [
            'challenger_id' => $_POST['challenger_id'],
            'type' => $_POST['type'],
            'weight' => $_POST['weight']
        ];
    }



    


    if (isset($_POST['confirmBtn'])) {
        

        date_default_timezone_set("Asia/Kuala_Lumpur");

        $currentDate = date("Y-m-d");
        $currentTime = date("H:i:s");
        $challenger_id = strtoupper(trim($_POST['challenger_id'] ?? ''));

        
        $weight = floatval($_POST['weight']);
        $type   = $_POST['type'];

        $point = point_calculation($weight, $type);


        // find recycling center id
        $sql_get_recycling_center_id = "SELECT centre_id
        FROM recycling_centre
        WHERE recycle_admin_id = '$recycle_admin_id'";

        $result_recycling_center_id = mysqli_query($con, $sql_get_recycling_center_id);

        $row_recycling_center_id = mysqli_fetch_assoc($result_recycling_center_id);
        $recycling_center_id = $row_recycling_center_id['centre_id'];


        // recycling history enter data part

        $new_recycling_history_id = get_next_id($con, 'recycling_history', 'RH', 'recycle_history_id');

        $sql_recycling_history="INSERT INTO recycling_history (recycle_history_id, date,
                time, recyclable_id, weight_kg, centre_id, challenger_id)
                VALUES
                ('$new_recycling_history_id','$currentDate','$currentTime',
                '$_POST[type]','$weight','$recycling_center_id', '$challenger_id')";

        // insert into point history

        $new_pt_history_id = get_next_id($con, 'point_history', 'PH', 'pt_history_id');


        $sql_point_history="INSERT INTO point_history (pt_history_id, challenger_id, 
                date, time, points, transaction_type, source_type, challenge_id, reward_id, recycle_history_id, quiz_id)
                VALUES
                ('$new_pt_history_id','$challenger_id','$currentDate','$currentTime',
                '$point','Earn','Recycling',NULL,NULL, '$new_recycling_history_id', NULL)";



        // find total point of challenger in challenger table
        $sql_get_total_point = "SELECT total_points
        FROM challenger
        WHERE challenger_id = '$challenger_id'";

        $result_total_point = mysqli_query($con, $sql_get_total_point);

        $row_total_point = mysqli_fetch_assoc($result_total_point);
        $current_total_point = $row_total_point['total_points'];

        // calculate total points for challenger in challenger table
        $new_total_point = $current_total_point + $point;

        $sql_update_total_point = "UPDATE challenger
        SET total_points = '$new_total_point'
        WHERE challenger_id = '$challenger_id'";

        

        mysqli_query($con, $sql_recycling_history);
        mysqli_query($con, $sql_point_history);
        mysqli_query($con, $sql_update_total_point);


        //check valid challange aprticipation


        $sql_valid_challenge = "SELECT 
        cp.challenge_id,
        cp.joined_date,
        c.end_date,
        c.points,
        rc.rec_centre_id,
        rc.recyclable_type,
        rc.weight_kg,
        a.ach_id

        FROM  challenge_participation cp 
        INNER JOIN challenge c ON cp.challenge_id = c.challenge_id
        INNER JOIN recycling_challenge rc ON c.challenge_id = rc.challenge_id
        LEFT JOIN achievement a ON c.challenge_id = a.challenge_id
        WHERE cp.challenger_id = '$challenger_id'
        AND cp.status = 'Joined'
        AND c.type = 'Recycling'
        AND '$currentDate' >= cp.joined_date
        AND (c.end_date IS NULL OR '$currentDate' <= c.end_date)
        ";



        $result = mysqli_query($con, $sql_valid_challenge);


        $validChallenges = [];


        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $validChallenges[] = $row;
            }
        }



        
        //loop challenge history
        foreach($validChallenges as $challenge) {

            $total_weight = 0;
            $endDate = $challenge['end_date'];
            $recCentreId = $challenge['rec_centre_id'];
            $recyclable_type = $challenge['recyclable_type'];

            $sql_conditions = [];
            $sql_conditions[] = "challenger_id = '$challenger_id'";
            $sql_conditions[] = "date >= '{$challenge['joined_date']}'";

            if (!is_null($recCentreId)) {
                $sql_conditions[] = "centre_id = '$recCentreId'";
            }

            if (!is_null($recyclable_type)) {
                $sql_conditions[] = "recyclable_id = '$recyclable_type'";
            }



            $sql_history = "SELECT * FROM recycling_history WHERE " . implode(" AND ", $sql_conditions);

            $result_history = mysqli_query($con, $sql_history);


            if ($result_history && mysqli_num_rows($result_history) > 0) {
                while ($row = mysqli_fetch_assoc($result_history)) {

                    $typeMatch = true;
                    $centreMatch = true;

                    if (!is_null($challenge['recyclable_type'])) {
                        $typeMatch = ($challenge['recyclable_type'] === $row['recyclable_id']);
                    }

                    if (!is_null($challenge['rec_centre_id'])) {
                        $centreMatch = ($challenge['rec_centre_id'] === $row['centre_id']);
                    }

                    if ($typeMatch && $centreMatch) {
                        $total_weight += floatval($row['weight_kg']);
                    }

                }
            }

            $progress_percent = min(100, ($total_weight / $challenge['weight_kg']) * 100);

            if ($progress_percent >= 100){
        
                $sql_challenge_participation = "UPDATE challenge_participation
                SET 
                    status = 'Completed',
                    completion_date = '$currentDate',
                    current_progress = '$progress_percent'
                WHERE challenge_id = '{$challenge['challenge_id']}'
                AND challenger_id = '$challenger_id'
                ";


                mysqli_query($con, $sql_challenge_participation);

                //insert point history
                $new_pt_history_id = get_next_id($con, 'point_history', 'PH', 'pt_history_id');
        
                $sql_point_history="INSERT INTO point_history (pt_history_id, challenger_id, 
                date, time, points, transaction_type, source_type, challenge_id, reward_id, recycle_history_id, quiz_id)
                VALUES
                ('$new_pt_history_id','$challenger_id','$currentDate','$currentTime',
                '{$challenge['points']}','Earn','Challenge','{$challenge['challenge_id']}',NULL,NULL,NULL)";

                mysqli_query($con, $sql_point_history);

                //challenger total point
                $sql_update_total_point = "UPDATE challenger
                SET total_points = total_points + {$challenge['points']}
                WHERE challenger_id = '$challenger_id'
                ";
                mysqli_query($con, $sql_update_total_point);


                //insert achievement history
                $new_achievement_history_id = get_next_id($con, 'achievement_history', 'AH', 'ach_history_id');
        
                $sql_achievement_history="INSERT INTO achievement_history (ach_history_id, challenger_id, 
                date, time, ach_id)
                VALUES
                ('$new_achievement_history_id','$challenger_id','$currentDate','$currentTime',
                '{$challenge['ach_id']}')";

                mysqli_query($con, $sql_achievement_history);


            }
            else{
                $sql_challenge_participation = "UPDATE challenge_participation
                SET current_progress = '$progress_percent'
                WHERE challenge_id = '{$challenge['challenge_id']}'
                AND challenger_id = '$challenger_id'
                ";

                mysqli_query($con, $sql_challenge_participation);
            }



        }




        mysqli_close($con);
        $showSuccess = true;
        
        
        $inputData = [
            'challenger_id' => '',
            'type' => '',
            'weight' => ''
        ];
        }

        if (isset($_POST['okBtn'])) {
            $showSuccess = false;
        }



    $value_challenger = isset($inputData['challenger_id']) ? $inputData['challenger_id'] : '';
    $value_weight = isset($inputData['weight']) ? $inputData['weight'] : '';
    $selected_type  = isset($inputData['type']) ? $inputData['type'] : '';

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link href="../commonStyle.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        .main_form{
            max-width:900px;
            margin:40px auto;
            padding:20px;
        }
        h1{
            color:#336A29;
            font-size:40px;
            margin-bottom:5px;
            font-weight: 900;
        }

        .subtitle{
            color:#498428;
            margin-bottom:30px;
        }
        
        .form{
            background:#fff;
            border:1px solid #eaef9d;
            border-radius:16px;
            padding:24px;
            box-shadow:0 10px 25px rgba(0,0,0,.05);

        }
        label{
            font-size:14px;
            font-weight:600;
            color:#336A29;
            display:block;
            margin-bottom:5px;
        }

        input, select{
            width:100%;
            padding:12px;
            border-radius:12px;
            border:1px solid rgba(204, 204, 204, 1);
            margin-bottom:20px;
            font-size:14px;
        }

        input:focus{
            outline:none;
            border-color:#498428;
        }


        .row{
            display:flex;
            gap:20px;
        }

        .row label {
            min-height: 40px;
            display: flex;
            align-items: flex-end;
        }        

        .row > div{
            flex:1;
            min-width: 0;
        }

        #challenger_id {
            text-transform: uppercase;
        }

        button{
            padding:16px;
            font-size:16px;
            font-weight:700;
            border:none;
            border-radius:12px;
            cursor:pointer;
            transition:all .2s ease;
        }

        button[name="submitBtn"],
        button[name="confirmBtn"]{
            background:#336A29;
            color:#fff;
        }

        button[name="submitBtn"]:hover,
        button[name="confirmBtn"]:hover{
            background:#498428;
        }


        button[name="resetBtn"]{
            background:none;
            color:#999;
        }

        button[name="resetBtn"]:hover{
            color:red;
        }

        button[name="cancelBtn"]{
            background:none;
            color:#999;
        }

        button[name="cancelBtn"]:hover{
            color:red;
        }

        .button_container{
            display:flex;
            gap:12px;
            margin-top:10px;
        }

        .button_container button{
            flex:1;
        }

        .overlay .modal-body form{
            display:flex;
            flex-direction:column;
            gap:12px;
            margin-top:10px;
        }

        .overlay .modal-body form button{
            width:100%;
        }

        .overlay{
            position:fixed;
            inset:0;
            background:rgba(0,0,0,.5);
            display:flex;
            align-items:center;
            justify-content:center;
            z-index:100;
            overflow-y: auto;
            padding: 20px;
        }

        .modal{
            width:380px;
            max-width:90%;
            background:white;
            border-radius:16px;
            overflow:hidden;
            box-shadow:0 20px 40px rgba(0,0,0,.25);
        }

        .modal_header{
            background:#498428;
            color:white;
            padding:20px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .modal-body{
            padding:20px;
        }

        .modal-row{
            display:flex;
            justify-content:space-between;
            margin-bottom:10px;
        }

        .points{
            background:rgba(234,239,157,.5);
            padding:16px;
            border-radius:12px;
            text-align:center;
            margin:15px 0;
        }

        .points-value{
            font-size:36px;
            font-weight:800;
            color:#336A29;
        }
        .error_challenger{
            background:#fff;
            border:1px solid #eaef9d;
            border-radius:16px;
            padding:24px;
            box-shadow:0 10px 25px rgba(0,0,0,.05);
            color: red;
            max-width:900px;
            margin:20px auto;
            

        }

        @media (max-width: 480px) {
            .modal{
                width:90%;
            }

            .modal_header h2 {
                font-size: 18px;
            }

            .modal_header p {
                font-size: 14px;
            }

            .points-value {
                font-size: 28px;
            }

            .modal-body {
                padding: 15px;
            }

            .modal-row span,
            .modal-row strong {
                font-size: 14px;
            }

            .overlay .modal-body form button {
                font-size: 14px;
                padding: 12px;
            }
        }



    </style>

</head>

<body>
    <?php include "recAdminTaskbar.php"; ?>

    <div class = "main_form">
        <div class = "title">
            <h1>Eco Points Entry</h1>
            <p class = "subtitle">Record recycling data for challengers</p>

        </div>


        <div class = "form">
            <form method = "post">

                <div class = "challenger_id_section">
                    <label for = "challenger_id">CHALLENGER ID</label>
                    <input type = "text" id = "challenger_id" name = "challenger_id" value="<?php echo htmlspecialchars($value_challenger); ?>" required>
                </div>

                <div class = "row">
                    <div class = "type_select_section">
                        <label for= "type">TYPE OF RECYCLABLES</label>
                            <select id="type" name="type" required>
                                <option value="">Please Select</option>

                                <?php foreach ($types as $t): ?>
                                    <?php if ((int)$t['is_deleted'] === 1) continue; ?>
                                    <option 
                                        value="<?php echo htmlspecialchars($t['recyclable_id']); ?>"
                                        <?php if ($selected_type === $t['recyclable_id']) echo 'selected'; ?>>

                                        <?php echo htmlspecialchars($t['type']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>


                    </div>
                    <div class = "weight_section">
                        <label for = "weight">WEIGHT (KG)</label>
                        <input type = "number"id = "weight" name = "weight" step = "0.01" min = "0" value="<?php echo htmlspecialchars($value_weight); ?>" required>
                    </div>
                </div>

                    
                    <div class = "button_container">
                        <button type="submit" name="submitBtn">Submit</button>
                        <button type="submit" name="resetBtn" formnovalidate>Reset</button>
                    </div>
                    
            
            </form>
            <?php if (!empty($errorMessage)): ?>
                <div class = "error_challenger">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    

    

    <?php if ($showConfirm): ?>
        <div class = "overlay">
            <div class = "modal">

                <div class = "modal_header">

                    <h2>Entry Verification</h2>
                    <p>Review before submitting</p>

                </div>

                <div class="modal-body">
                    <div class="modal-row">
                        <span>CHALLENGER ID</span>
                        <strong><?php echo htmlspecialchars($inputData['challenger_id']); ?></strong>
                    </div>
                    <div class="modal-row">
                        <span>TYPE OF RECYCLABLES</span>
                        <?php
                            $recyclable_name = '';

                            $sql_name = "SELECT type FROM recyclable WHERE recyclable_id = '{$inputData['type']}'";
                            $result_name = mysqli_query($con, $sql_name);

                            if ($result_name && mysqli_num_rows($result_name) === 1) {
                                $row_name = mysqli_fetch_assoc($result_name);
                                $recyclable_name = $row_name['type'];
                            }
                        ?>
                        <strong><?php echo htmlspecialchars($recyclable_name); ?></strong>
                    </div>
                    <div class="modal-row">
                        <span>WEIGHT</span>
                        <strong><?php echo htmlspecialchars($inputData['weight']); ?> KG</strong>
                    </div>

                    <div class="points">
                        <div>ESTIMATED ECO POINTS EARNED</div>
                        <div class="points-value"><?php echo $point; ?> PTS</div>
                    </div>

                    <form method="post">
                        <input type="hidden" name="challenger_id" value="<?php echo htmlspecialchars($inputData['challenger_id']); ?>">
                        <input type="hidden" name="type" value="<?php echo htmlspecialchars($inputData['type']); ?>">
                        <input type="hidden" name="weight" value="<?php echo htmlspecialchars($inputData['weight']); ?>">

                        <button type="submit" name="confirmBtn">Confirm</button>
                        <button type="submit" name="cancelBtn">Cancel</button>
                    </form>

                </div>


            </div>

        </div>

    
    
    <?php endif; ?>

    <?php if ($showSuccess): ?>
        <div class="overlay">
            <div class="modal">
                <div class="modal_header">
                    <h2 style="font-size: 30px;">Success!</h2>
                </div>
                <div class="modal-body">
                    <p>Recycling entry recorded successfully.</p>
                    <form method="post">
                        <button type="submit" name="okBtn">OK</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="overlay" id="deleteOverlay" style="display:none;">
    <div class="modal">
        <div class="modal_header" style="background:#c0392b;">
            <h2>Confirm Delete</h2>
        </div>

        <div class="modal-body">
            <p>Are you sure you want to delete this recyclable type?</p>
            <p id="deleteCentreName" style="font-weight:600; color:#c0392b;"></p>


            <form method="post">
                <input type="hidden" name="delete_id" id="delete_id">

                <div class="button_container">
                    <button type="submit" name="confirmDelete" style="background:#c0392b; color:white;">
                        Delete
                    </button>
                    <button type="button" id="cancelDelete">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const deleteOverlay = document.getElementById("deleteOverlay");
        const deleteIdInput = document.getElementById("delete_id");
        const deleteNameText = document.getElementById("deleteCentreName");
        const cancelDelete = document.getElementById("cancelDelete");

        document.addEventListener("click", function (e) {
            const deleteBtn = e.target.closest(".delete");
            if (!deleteBtn) return;

            e.preventDefault();

            deleteIdInput.value = deleteBtn.dataset.id;
            deleteNameText.textContent = deleteBtn.dataset.name;

            deleteOverlay.style.display = "flex";
            });

            cancelDelete.addEventListener("click", function () {
                deleteOverlay.style.display = "none";
            });

    </script>
    
</body>
</html>