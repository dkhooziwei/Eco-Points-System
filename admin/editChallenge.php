<?php 
    require_once "../session.php";
    require_once "../conn.php";
    require_once "../commonFunctions.php";

    if($_SESSION["role"] !== "Admin"){
        header("Location: ../registerLogin/login.php");
        exit();
    }

    if(!isset($_GET["id"]) || !isset($_GET["id2"])){
        echo "<script>alert('Please select a challenge to edit');
        window.location.href='challengeManagement.php'; </script>";
        exit();
    }

    $challenge_id = $_GET["id"];
    $result = mysqli_query($con, "SELECT * FROM challenge WHERE challenge_id = '$challenge_id'");
    $row = mysqli_fetch_assoc($result);

    $row2 = [];
    if($row && $row["type"] === "Recycling"){
        $result2 = mysqli_query($con, "SELECT * FROM recycling_challenge WHERE challenge_id = '$challenge_id'");
        if(mysqli_num_rows($result2) > 0){
            $row2 = mysqli_fetch_assoc($result2);
        }
    }

    $ach_id = $_GET["id2"];
    $ach_result = mysqli_query($con, "SELECT * FROM achievement WHERE ach_id = '$ach_id'");
    $ach_row = mysqli_fetch_assoc($ach_result);

    if(isset($_POST["submit_btn"])){
        $name = $_POST["challenge_name"];
        $type = $_POST["challenge_type"];
        $previous_type = $_POST["previous_type"];
        $points = $_POST["points"];
        $start_date = $_POST["start_date"];
        $end_date = empty($_POST["end_date"]) ? "NULL" : "'".$_POST['end_date']."'";
        $partner_id = empty($_POST["partner_id"]) ? "NULL" : "'".$_POST['partner_id']."'";

        if($type === "Event"){
            if($previous_type === "Recycling"){
                if(!mysqli_query($con, "DELETE FROM recycling_challenge WHERE challenge_id = '$challenge_id'")){
                    die("Error: ".mysqli_error($con));
                }
            }

            $no_of_times = $_POST["no_of_times"];

            $sql = "UPDATE challenge
                    SET name = '$name',
                    type = '$type',
                    points = '$points',
                    start_date = '$start_date',
                    end_date = $end_date,
                    partner_id = $partner_id,
                    no_of_times = '$no_of_times'
                    WHERE challenge_id = '$challenge_id'";
            
            if(!mysqli_query($con, $sql)){
                die("Error: ".mysqli_error($con));
            }
        }
        else{
            $rec_centre_id = empty($_POST["rec_centre_id"]) ? "NULL" : "'".$_POST['rec_centre_id']."'";
            $recyclable_id = empty($_POST["recyclable_id"]) ? "NULL" : "'".$_POST['recyclable_id']."'";
            $weight_kg = $_POST["weight_kg"];

            $sql2 = "UPDATE challenge
                    SET name = '$name',
                    type = '$type',
                    points = '$points',
                    start_date = '$start_date',
                    end_date = $end_date,
                    partner_id = $partner_id,
                    no_of_times = NULL
                    WHERE challenge_id = '$challenge_id'";
            
            if(!mysqli_query($con, $sql2)){
                die("Error: ".mysqli_error($con));
            }

            $sql3 = "";
            if($previous_type === "Event"){
                $sql3 = "INSERT INTO recycling_challenge(
                        challenge_id,
                        rec_centre_id,
                        recyclable_type,
                        weight_kg)
                        
                        VALUES(
                        '$challenge_id',
                        $rec_centre_id,
                        $recyclable_id,
                        '$weight_kg')";
            }
            else{
                $sql3 = "UPDATE recycling_challenge
                        SET rec_centre_id = $rec_centre_id,
                        recyclable_type = $recyclable_id,
                        weight_kg = '$weight_kg'
                        WHERE challenge_id = '$challenge_id'";
            }
            
            if(!mysqli_query($con, $sql3)){
                die("Error: ".mysqli_error($con));
            }
        }

        $ach_name = $_POST["ach_name"];
        $ach_type = $_POST["ach_type"];
        $ach_file_path = $ach_row["source"];
        $previous_ach_type = $ach_row["type"];
        $old_file = "../" . $ach_row["source"];
        $file_ext = pathinfo($old_file, PATHINFO_EXTENSION);

        // if admin uploaded a new file
        if(isset($_FILES["ach_source"]) && $_FILES["ach_source"]["error"] === UPLOAD_ERR_OK){
            
            $file = $_FILES["ach_source"];

            $filename = $file["name"]; // original file name
            $file_tmp = $file["tmp_name"]; // temporary file on server
            $file_size = $file["size"]; // file size
            $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION)); //get extension

            $allowed = ["png", "pdf"];
            if(!in_array($file_ext, $allowed)){
                echo "<script>alert('Invalid file type. Only PNG, JPG allowed.');
                window.location.href='editChallenge.php?id=$challenge_id&id2=$ach_id';</script>";
            }

            if($file_size > 2 * 1024 * 1024){
                echo "<script>alert('File is too large. Max 2MB allowed.');
                window.location.href='editChallenge.php?id=$challenge_id&id2=$ach_id';</script>";
            }

            if(file_exists($old_file)){
                unlink($old_file); // deletes old file from server
            }

            $destination = "";
            if($ach_type === "Badge"){
                $destination = "../uploads/achievements/badges/" . $ach_id . "." . $file_ext;
                $ach_file_path = "uploads/achievements/badges/" . $ach_id . "." . $file_ext;
            }
            else{
                $destination = "../uploads/achievements/e-certificates/" . $ach_id . "." . $file_ext;
                $ach_file_path = "uploads/achievements/e-certificates/" . $ach_id . "." . $file_ext;
            }
            if(!move_uploaded_file($file_tmp, $destination)){
                die("Error uploading the file.");
            }
        }
        else{
            if($ach_type != $previous_ach_type){
                if($ach_type == "Badge"){
                    $ach_file_path = "uploads/achievements/badges/" . $ach_id . "." . $file_ext;
                    $new_destination = "../uploads/achievements/badges/" . $ach_id . "." . $file_ext;
                    if(!file_exists($old_file)){
                        die("Old file does not exist." . $old_file);
                    }

                    if(!rename($old_file, $new_destination)){
                        echo "<script>alert('Error moving the file');
                        window.location.href='editChallenge.php?id=$challenge_id&id2=$ach_id';</script>";
                    }
                }
                else{
                    $ach_file_path = "uploads/achievements/e-certificates/" . $ach_id . "." . $file_ext;
                    $new_destination = "../uploads/achievements/e-certificates/" . $ach_id . "." . $file_ext;
                    if(!file_exists($old_file)){
                        die("Old file does not exist." . $old_file);
                    }
                    if(!rename($old_file, $new_destination)){
                        echo "<script>alert('Error moving the file');
                        window.location.href='editChallenge.php?id=$challenge_id&id2=$ach_id';</script>";
                    }
                }
            }
        }

        $update_ach_sql = "UPDATE achievement
                            SET name = '$ach_name',
                            type = '$ach_type',
                            source = '$ach_file_path'
                            WHERE ach_id = '$ach_id'";
        if(!mysqli_query($con, $update_ach_sql)){
            die("Error: ".mysqli_error($con));
        }

        echo "<script>alert('Challenge details saved!');
        window.location.href='editChallenge.php?id=$challenge_id&id2=$ach_id';</script>";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Edit Challenge</title>
    <link href="../commonStyle.css" rel="stylesheet">
    <style>
        input, button, select {
            font-family: "Nunito Sans", sans-serif;
        }

        body {
            overflow-y: auto;
        }

        .form-wrapper {
            width: 50%;
            padding: 30px;
            display: flex;
            flex-direction: column;
            margin: auto;
            margin-top: 20px;
            gap: 20px;

            background: white;
            border: 2px solid rgba(174, 174, 174, 1);
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(174, 174, 174, 1);
        }

        .form-wrapper h2 {
            margin-top: 0;
            margin-bottom: 0;
        }

        #edit_challenge_form {
            display: flex;
            flex-direction: column;
            width: 87%;
            padding: 25px 35px;
            border: 2px solid rgba(167, 190, 128, 1);
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(167, 190, 128, 1);
            gap: 5px;
        }

        #edit_challenge_form label {
            font-size: 18px;
            font-weight: bold;
            color: rgba(40, 89, 22, 1);
        }

        #edit_challenge_form input {
            box-sizing: border-box;
            width: 100%;
            height: 35px;
            border-radius: 10px;
            padding: 0 5px;
            border: 1px solid rgba(107, 107, 107, 1);
        }

        #edit_challenge_form select {
            box-sizing: border-box;
            width: 100%;
            height: 35px;
            border-radius: 10px;
            padding: 0px 5px;
        }

        #event_challenge_details,
        #recycling_challenge_details {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }

        h3 {
            text-align: center;
            padding: 5px 0;
            border-radius: 10px;
            background: linear-gradient(to right, rgba(48, 100, 28, 1), rgba(112, 172, 88, 1));
            color: white;
        }

        #recycling_challenge_details input, 
        #recycling_challenge_details select {
            margin-top: 5px;
        }

        #helper_text {
            font-size: 14px;
            color: rgba(71, 150, 42, 1);
            margin-top: 0;
            margin-bottom: 10px;
            font-style: italic;
        }

        #ach_details {
            margin-top: 40px;
        }

        #upload_box {
            position: relative;
            width: 100%;
            height: 200px;
            border: 3px dashed #498428;
            border-radius: 10px;
            background: #d4e7acff;

            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            margin: 20px 0;
            cursor: pointer;
            margin-bottom: 0;

            font-weight: normal;
        }

        #upload_box:hover {
            background: #d4f09aff;
        }

        #upload_box input[type="file"] {
            opacity: 0;
            width: 100%;
            max-width: 500px;
            height: 200px;
            position: absolute;
            cursor: pointer;
            z-index: 1;
        }

        #file_name {
            font-weight: bold;
            margin-top: 10px;
        }

        #remove_btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #d96767ff;
            border-radius: 10px;
            width: 28px;
            height: 28px;
            cursor: pointer;
            font-weight: bold;
            z-index: 2; /* placed above file input */
        }

        #file_message {
            font-style: italic;
            font-size: 14px;
        }

        #submit_btn {
            width: 100%;
            height: 40px;
            font-size: 16px;
            font-weight: bold;
            color: rgba(44, 105, 22, 1);
            border-radius: 10px;
            border: 2px solid rgba(44, 105, 22, 1);
            background: rgba(184, 216, 173, 1);
            cursor: pointer;
        }

        #submit_btn:hover {
            background: rgba(152, 214, 132, 1);
        }

        @media (max-width: 600px){
            .form-wrapper {
                width: 90%;
                padding: 15px;
                margin-top: 10px;
            }

            .form-wrapper h2 {
                font-size: 22px;
                text-align: center;
            }

            #edit_challenge_form {
                width: 87%;
                padding: 20px;
                gap: 8px;
            }

            #edit_challenge_form label {
                font-size: 15px;
            }

            #edit_challenge_form input, 
            #edit_challenge_form select {
                width: 95%;
                height: 40px;
                font-size: 14px;
            }

            #event_challenge_details,
            #recycling_challenge_details {
                margin-top: 15px;
                gap: 8px;
            }

            h3 {
                font-size: 16px;
                padding: 8px 0;
            }

            #helper_text {
                font-size: 13px;
            }

            #upload_box {
                width: 100%;
                height: 160px;
            }

            #file_message {
                font-size: 14px;
            }

            #submit_btn {
                height: 45px;
                font-size: 15px;
            }
        }

        @media (min-width: 601px) and (max-width: 1000px){
            .form-wrapper {
                width: 70%;
                padding: 25px;
            }

            #edit_challenge_form {
                width: 90%;
                padding: 25px;
            }

            #edit_challenge_form label {
                font-size: 16px;
            }

            #edit_challenge_form input,
            #edit_challenge_form select {
                width: 100%;
                height: 38px;
                font-size: 15px;
            }

            h3 {
                font-size: 17px;
            }
        }
    </style>
</head>
<body>
    <?php include "adminTaskbar.php"; ?>

    <div class="form-wrapper">
        <h2>Edit Challenge Details</h2>

        <form method="post" enctype="multipart/form-data" id="edit_challenge_form" onsubmit="return confirm_submit();">
            <label for="challenge_name">Challenge Name:</label>
            <input type="text" id="challenge_name" name="challenge_name" placeholder="Enter challenge name" 
            value="<?= $row['name'] ?>" required>
            <br>
            <label for="challenge_type">Challenge Type:</label>
            <select id="challenge_type" name ="challenge_type" required>
                <option value="" disabled selected>Please select a type</option>
                <option value="Event"
                <?php 
                    if($row["type"] === "Event"){
                        echo "selected";
                    }
                ?>>
                Event</option>
                <option value="Recycling" <?php 
                    if($row["type"] === "Recycling"){
                        echo "selected";
                    }
                ?>>Recycling</option>
            </select><br>
            <input type="hidden" name="previous_type" value="<?= $row["type"] ?>">

            <label for="points">Points Rewarded for Completing This Challenge:</label>
            <input type="number" id="points" name="points" min="1" step="1" placeholder="Enter number of points" value="<?= $row['points'] ?>" required>
            <br>
            <label for="start_date">Challenge Start Date:</label>
            <input type="date" id="start_date" name="start_date" value="<?= $row['start_date'] ?>" required>
            <br>
            <label for="end_date">Challenge End Date (optional):</label>
            <input type="date" id="end_date" name="end_date" value="<?= $row['end_date'] ?>">
            <br>
            <label for="partner_id">Partner (optional):</label>
            <select id="partner_id" name="partner_id">
                <option value="" disabled selected>Please select a partner</option>
                <option value="">None</option>

                <!-- get partner ids and names -->
                <?php 
                    $get_partner_sql = "SELECT user_id, name FROM user WHERE role = 'Partner'";
                    $partner_result = mysqli_query($con, $get_partner_sql);

                    if(!$partner_result){
                        die("Error: ".mysqli_error($con));
                    }

                    if(mysqli_num_rows($partner_result) == 0){
                        echo "<option value='' disabled>No partners found</option>";
                    }
                    else {
                        while($partner = mysqli_fetch_assoc($partner_result)){
                            $partner_id = $partner["user_id"];
                            $partner_name = $partner["name"];

                            echo "<option value='$partner_id'";
                            if($row["partner_id"] === "$partner_id"){
                                echo "selected";
                            }
                            echo ">$partner_id - $partner_name</option>";
                        }
                    }
                ?>
            </select>

            <div id="event_challenge_details" style="display: none;">
                <h3>Event Challenge Details</h3>
                <label for="no_of_times">Events Needed to Complete the Challenge:</label>
                <p id="helper_text">*The number of times a user must complete the event to finish this challenge</p>
                <input type="number" id="no_of_times" name="no_of_times" min="1" step="1" placeholder="Enter a number (min: 1)" value="<?= $row['no_of_times'] ?>"> 
            </div>

            <div id="recycling_challenge_details" style="display: none;">
                <h3>Recycling Challenge Details</h3>
                <label for="rec_centre_id">Recycling Centre (optional):</label>
                <select id="rec_centre_id" name="rec_centre_id">
                    <option value="" disabled selected>Please select a recycling centre</option>
                    <option value="">None</option>

                    <!-- get the recycling centre ids and names -->
                    <?php 
                        $get_rec_centre_sql = "SELECT centre_id, name FROM recycling_centre";
                        $rec_centre_result = mysqli_query($con, $get_rec_centre_sql);

                        if(!$rec_centre_result){
                            die("Error: ".mysqli_error($con));
                        }

                        if(mysqli_num_rows($rec_centre_result) == 0){
                            echo "<option value='' disabled>No recycling centres found</option>";
                        }
                        else{
                            while($rec_centre = mysqli_fetch_assoc($rec_centre_result)){
                                $centre_id = $rec_centre["centre_id"];
                                $centre_name = $rec_centre["name"];

                                echo "<option value='$centre_id'";
                                if(!empty($row2) && $row2["rec_centre_id"] === "$centre_id"){
                                    echo "selected";
                                }
                                echo ">$centre_id - $centre_name</option>";
                            }
                        }
                    ?>
                </select>
                <br><br>
                <label for="recyclable_id">Recyclable Type (optional):</label>
                <select id="recyclable_id" name="recyclable_id">
                    <option value="" disabled selected>Please select a recyclable type</option>
                    <option value="">None</option>

                    <!-- get the recyclable names -->
                    <?php 
                        $get_recyclable_sql = "SELECT recyclable_id, type FROM recyclable";
                        $recyclable_result = mysqli_query($con, $get_recyclable_sql);

                        if(!$recyclable_result){
                            die("Error: ".mysqli_error($con));
                        }

                        if(mysqli_num_rows($recyclable_result) == 0){
                            echo "<option value='' disabled>No recyclables found</option>";
                        }
                        else{
                            while($recyclable = mysqli_fetch_assoc($recyclable_result)){
                                $recyclable_id = $recyclable["recyclable_id"];
                                $recyclable_type = $recyclable["type"];

                                echo "<option value='$recyclable_id'";
                                if(!empty($row2) && $row2["recyclable_type"] === "$recyclable_id"){
                                    echo "selected";
                                }
                                echo">$recyclable_type</option>";
                            }
                        }
                    ?>
                </select>
                <br><br>
                <label for="weight_kg">Weight of Recyclables Needed to Complete the Challenge:</label>
                <input type="number" step="0.01" id="weight_kg" name="weight_kg" placeholder="Enter weight (kg)" 
                value="<?= empty($row2) ? '' : $row2['weight_kg'] ?>">
            </div>

            <h3 id="ach_details">Achievement Details</h3>
            <label for="ach_name">Achievement Name:</label>
            <input type="text" id="ach_name" name="ach_name" placeholder="Enter achievement name" value="<?= $ach_row['name']?>" required>
            <br>
            <label for="ach_type">Achievement Type:</label>
            <select id="ach_type" name ="ach_type" required>
                <option value="" disabled selected>Please select a type</option>
                <option value="Badge"
                <?php 
                    if($ach_row["type"] === "Badge"){
                        echo "selected";
                    }
                ?>>Badge</option>
                <option value="E-certificate"
                <?php 
                    if($ach_row["type"] === "E-certificate"){
                        echo "selected";
                    }
                ?>>E-certificate</option>
            </select><br>
            <label for="ach_source">Achievement Badge/E-certificate:</label>
            <div id="upload_box">
                <p id="upload_box_text" style="display:none;">Drag and drop your file here or click to select</p>
                <span id="file_name"><?= basename($ach_row["source"]) ?></span> <!-- to display file name-->
                <button type="button" id="remove_btn">✕</button> <!-- button to remove file -->
                <input type="file" id="ach_source" name="ach_source" accept=".png,.jpg">
            </div>
            <div id="file_message">
                <p>Accepted File Types: PNG, JPG</p>
                <p>Max. File Size: 2MB</p>
            </div>

            <br><br>
            
            <button type="submit" id="submit_btn" name="submit_btn">Save</button>
        </form>
    </div>

    <script>
        const challenge_type = document.getElementById("challenge_type");

        const event_challenge_details = document.getElementById("event_challenge_details");
        const no_of_times = document.getElementById("no_of_times");

        const recycling_challenge_details = document.getElementById("recycling_challenge_details");
        const weight_kg = document.getElementById("weight_kg");

        if(challenge_type.value === "Event"){
            event_challenge_details.style.display = "block";
            no_of_times.required = true;
            no_of_times.disabled = false;

            recycling_challenge_details.style.display = "none";
            weight_kg.required = false;
            weight_kg.disabled = true;
        } 
        else{
            recycling_challenge_details.style.display = "block";
            weight_kg.required = true;
            weight_kg.disabled = false;                

            event_challenge_details.style.display = "none";
            no_of_times.required = false;
            no_of_times.disabled = true;
        }

        challenge_type.addEventListener("change", () => {
            if(challenge_type.value === "Event"){
                event_challenge_details.style.display = "block";
                no_of_times.required = true;
                no_of_times.disabled = false;

                recycling_challenge_details.style.display = "none";
                weight_kg.required = false;
                weight_kg.disabled = true;
            } 
            else{
                recycling_challenge_details.style.display = "block";
                weight_kg.required = true;
                weight_kg.disabled = false;                

                event_challenge_details.style.display = "none";
                no_of_times.required = false;
                no_of_times.disabled = true;
            }
        });

        const upload_box = document.getElementById("upload_box");
        const file_input = document.getElementById("ach_source");
        const upload_box_text = document.getElementById("upload_box_text");
        const file_name = document.getElementById("file_name");
        const remove_btn = document.getElementById("remove_btn");

        upload_box.addEventListener("dragover", (e) => {
            e.preventDefault();
            upload_box.style.background = "#e0f0c0";
        });

        upload_box.addEventListener("dragleave", (e) => {
            e.preventDefault();
            upload_box.style.background = "";
        });

        upload_box.addEventListener("drop", (e) => {
            e.preventDefault();
            upload_box.style.background = "";

            if(e.dataTransfer.files.length > 1){
                alert("Only one file ia allowed.");
                return;
            }

            file_input.files = e.dataTransfer.files;
            show_file_name(); // run the function after user drops a file
        });

        file_input.addEventListener("change", show_file_name); //run the function when user selects a file

        function show_file_name(){
            if(file_input.files.length > 0){
                upload_box_text.style.display = "none"; //remove upload text
                file_name.style.display = "block"; //display file name
                remove_btn.style.display = "block"; //display remove button
                file_name.textContent = file_input.files[0].name;
            }
        }

        remove_btn.addEventListener("click", () => {
            file_input.value = "";
            file_name.style.display = "none";
            remove_btn.style.display = "none";
            upload_box_text.style.display = "block";
        });

        document.getElementById("ach_source").addEventListener("change", function(){
            if(this.files[0].size > 2 * 1024 * 1024) { //prevent user from submitting a file exceeding max size
                alert("File too large. Max 2MB allowed.");
                this.value = "";
                file_name.style.display = "none";
                remove_btn.style.display = "none";
                upload_box_text.style.display = "block";
            }
        });

        function confirm_submit(){
            return confirm("Are you sure you want to save these details?");
        }
    </script>
</body>
</html>