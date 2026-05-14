<?php 
    require_once "../session.php";
    require_once "../conn.php";
    require_once "../commonFunctions.php";

    if($_SESSION["role"] !== "Admin"){
        header("Location: ../registerLogin/login.php");
        exit();
    }

    if(isset($_POST["submit_btn"])){

        $new_challenge_id = get_next_id($con, "challenge", "CL", "challenge_id");
        $name = $_POST["challenge_name"];
        $type = $_POST["challenge_type"];
        $points = $_POST["points"];
        $start_date = $_POST["start_date"];
        $end_date = empty($_POST["end_date"]) ? "NULL" : "'".$_POST['end_date']."'";
        $partner_id = empty($_POST["partner_id"]) ? "NULL" : "'".$_POST['partner_id']."'";

        if($type === "Event"){
            $no_of_times = $_POST["no_of_times"];

            $sql = "INSERT INTO challenge(
                    challenge_id,
                    name,
                    type,
                    points,
                    start_date,
                    end_date,
                    partner_id,
                    no_of_times)
                    
                    VALUES(
                    '$new_challenge_id',
                    '$name',
                    '$type',
                    '$points',
                    '$start_date',
                    $end_date,
                    $partner_id,
                    '$no_of_times')";
            
            if(!mysqli_query($con, $sql)){
                die("Error: ".mysqli_error($con));
            }
        }
        else{
            $rec_centre_id = empty($_POST["rec_centre_id"]) ? "NULL" : "'".$_POST['rec_centre_id']."'";
            $recyclable_id = empty($_POST["recyclable_id"]) ? "NULL" : "'".$_POST['recyclable_id']."'";
            $weight_kg = $_POST["weight_kg"];

            $sql2 = "INSERT INTO challenge(
                    challenge_id,
                    name,
                    type,
                    points,
                    start_date,
                    end_date,
                    partner_id)
                    
                    VALUES(
                    '$new_challenge_id',
                    '$name',
                    '$type',
                    '$points',
                    '$start_date',
                    $end_date,
                    $partner_id)";
            
            if(!mysqli_query($con, $sql2)){
                die("Error: ".mysqli_error($con));
            }

            $sql3 = "INSERT INTO recycling_challenge(
                    challenge_id,
                    rec_centre_id,
                    recyclable_type,
                    weight_kg)
                    
                    VALUES(
                    '$new_challenge_id',
                    $rec_centre_id,
                    $recyclable_id,
                    '$weight_kg')";
            
            if(!mysqli_query($con, $sql3)){
                die("Error: ".mysqli_error($con));
            }
        }

        if(!isset($_FILES["ach_source"])) {
            echo "<script>alert('No file uploaded.')
            window.location.href='addChallenge.php';</script>";
            exit;
        }

        $error = $_FILES["ach_source"]["error"];

        if($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE){
            echo "<script>alert('File is too large. Max 2MB allowed.');
            window.location.href='addChallenge.php';</script>";
            exit;
        }

        if($error !== UPLOAD_ERR_OK){
            echo "<script>alert('Upload failed.');
            window.location.href='addChallenge.php';</script>";
            exit;
        }

        $file = $_FILES["ach_source"];

        $filename = $file["name"]; // original file name
        $file_tmp = $file["tmp_name"]; // temporary file on server
        $file_size = $file["size"]; // file size
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION)); //get extension

        $allowed = ["png", "jpg"];
        if(in_array($file_ext, $allowed)){
            if($file_size <= 2 * 1024 * 1024){ //check file size (max 2MB)
                $new_ach_id = get_next_id($con, "achievement", "ACH", "ach_id");
                $ach_name = $_POST["ach_name"];
                $ach_type = $_POST["ach_type"];
                
                $file_path = "";
                $destination = "";
                if($ach_type === "Badge"){
                    $destination = "../uploads/achievements/badges/" . $new_ach_id . "." . $file_ext;
                    $file_path = "uploads/achievements/badges/". $new_ach_id . "." . $file_ext;
                }
                else{
                    $destination = "../uploads/achievements/e-certificates/" . $new_ach_id . "." . $file_ext;
                    $file_path = "uploads/achievements/e-certificates/". $new_ach_id . "." . $file_ext;
                }

                if(!move_uploaded_file($file_tmp, $destination)){
                    echo "<script>alert('File upload failed. Please try again.');</script>";
                    exit();
                }

                $sql = "INSERT INTO achievement(
                ach_id,
                name,
                type,
                source,
                challenge_id)
        
                VALUES(
                '$new_ach_id',
                '$ach_name',
                '$ach_type',
                '$file_path',
                '$new_challenge_id')";

                if(!mysqli_query($con, $sql)){
                    die("Error: ".mysqli_error($con));
                }
            }
            else{
                echo "<script>alert('File is too large. Max 2MB allowed.');
                window.location.href='addChallenge.php';</script>";
            }
        }
        else{
            echo "<script>alert('Invalid file type. Only PNG, JPG allowed.');
            window.location.href='addChallenge.php';</script>";
        }

        echo "<script>alert('New challenge added!');
        window.location.href='addChallenge.php';</script>";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Add New Challenge</title>
    <link href="../commonStyle.css" rel="stylesheet">
    <style>
        input, button, select {
            font-family: "Nunito Sans", sans-serif;
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

        #add_challenge_form {
            display: flex;
            flex-direction: column;
            width: 87%;
            padding: 25px 35px;
            border: 2px solid rgba(167, 190, 128, 1);
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(167, 190, 128, 1);
            gap: 5px;
        }

        #add_challenge_form label {
            font-size: 18px;
            font-weight: bold;
            color: rgba(40, 89, 22, 1);
        }

        #add_challenge_form input {
            box-sizing: border-box;
            width: 100%;
            height: 35px;
            border-radius: 10px;
            padding: 0 5px;
            border: 1px solid rgba(107, 107, 107, 1);
        }

        #add_challenge_form select {
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
            margin-top: 15px;
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

            #add_challenge_form {
                width: 87%;
                padding: 20px;
                gap: 8px;
            }

            #add_challenge_form label {
                font-size: 15px;
            }

            #add_challenge_form input, 
            #add_challenge_form select {
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

            #add_challenge_form {
                width: 90%;
                padding: 25px;
            }

            #add_challenge_form label {
                font-size: 16px;
            }

            #add_challenge_form input,
            #add_challenge_form select {
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
        <h2>Add New Challenge</h2>

        <form method="post" enctype="multipart/form-data" id="add_challenge_form" onsubmit="return confirm_submit();">
            <label for="challenge_name">Challenge Name:</label>
            <input type="text" id="challenge_name" name="challenge_name" placeholder="Enter challenge name" required>
            <br>
            <label for="challenge_type">Challenge Type:</label>
            <select id="challenge_type" name ="challenge_type" required>
                <option value="" disabled selected>Please select a type</option>
                <option value="Event">Event</option>
                <option value="Recycling">Recycling</option>
            </select><br>

            <label for="points">Points Rewarded for Completing This Challenge:</label>
            <input type="number" id="points" name="points" min="1" step="1" placeholder="Enter number of points" required>
            <br>
            <label for="start_date">Challenge Start Date:</label>
            <input type="date" id="start_date" name="start_date" min="<?= date("Y-m-d") ?>" required>
            <br>
            <label for="end_date">Challenge End Date (optional):</label>
            <input type="date" id="end_date" name="end_date" min="<?= date("Y-m-d") ?>">
            <br>
            <label for="partner_id">Partner (optional):</label>
            <select id="partner_id" name="partner_id">
                <option value="" disabled selected>Please select a partner</option>
                <option value="">None</option>

                <!-- get partner ids and names -->
                <?php 
                    $get_partner_sql = "SELECT user_id, name FROM user WHERE role = 'Partner'";
                    $result = mysqli_query($con, $get_partner_sql);

                    if(!$result){
                        die("Error: ".mysqli_error($con));
                    }

                    if(mysqli_num_rows($result) == 0){
                        echo "<option value='' disabled>No partners found</option>";
                    }
                    else {
                        while($row = mysqli_fetch_assoc($result)){
                            $partner_id = $row["user_id"];
                            $partner_name = $row["name"];

                            echo "<option value='$partner_id'>$partner_id - $partner_name</option>";
                        }
                    }
                ?>
            </select>
            <br>

            <div id="event_challenge_details" style="display: none;">
                <h3>Event Challenge Details</h3>
                <label for="no_of_times">Events Needed to Complete the Challenge:</label>
                <p id="helper_text">*The number of times a user must complete the event to finish this challenge</p>
                <input type="number" id="no_of_times" name="no_of_times" min="1" step="1" placeholder="Enter a number (min: 1)" required> 
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
                        $result = mysqli_query($con, $get_rec_centre_sql);

                        if(!$result){
                            die("Error: ".mysqli_error($con));
                        }

                        if(mysqli_num_rows($result) == 0){
                            echo "<option value='' disabled>No recycling centres found</option>";
                        }
                        else{
                            while($row = mysqli_fetch_assoc($result)){
                                $centre_id = $row["centre_id"];
                                $centre_name = $row["name"];

                                echo "<option value='$centre_id'>$centre_id - $centre_name</option>";
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
                        $result = mysqli_query($con, $get_recyclable_sql);

                        if(!$result){
                            die("Error: ".mysqli_error($con));
                        }

                        if(mysqli_num_rows($result) == 0){
                            echo "<option value='' disabled>No recyclables found</option>";
                        }
                        else{
                            while($row = mysqli_fetch_assoc($result)){
                                $recyclable_id = $row["recyclable_id"];
                                $recyclable_type = $row["type"];

                                echo "<option value='$recyclable_id'>$recyclable_type</option>";
                            }
                        }
                    ?>
                </select>
                <br><br>
                <label for="weight_kg">Weight of Recyclables Needed to Complete the Challenge:</label>
                <input type="number" step="0.01" id="weight_kg" name="weight_kg" placeholder="Enter weight (kg)"required>
            </div>
            
            <h3 id="ach_details">Achievement Details</h3>
            <label for="ach_name">Achievement Name:</label>
            <input type="text" id="ach_name" name="ach_name" placeholder="Enter achievement name" required>
            <br>
            <label for="ach_type">Achievement Type:</label>
            <select id="ach_type" name ="ach_type" required>
                <option value="" disabled selected>Please select a type</option>
                <option value="Badge">Badge</option>
                <option value="E-certificate">E-certificate</option>
            </select><br>
            <label for="ach_source">Achievement Badge/E-certificate:</label>
            <div id="upload_box">
                <p id="upload_box_text">Drag and drop your file here or click to select</p>
                <span id="file_name" style="display:none;"></span> <!-- to display file name-->
                <button type="button" id="remove_btn" style="display:none;">✕</button> <!-- button to remove file -->
                <input type="file" id="ach_source" name="ach_source" accept=".png,.jpg" required>
            </div>
            <div id="file_message">
                <p>Accepted File Types: PNG, JPG</p>
                <p>Max. File Size: 2MB</p>
            </div>

            <br><br>
            <button type="submit" id="submit_btn" name="submit_btn">+ Add Challenge</button>
        </form>
    </div>

    <script>
        const challenge_type = document.getElementById("challenge_type");

        const event_challenge_details = document.getElementById("event_challenge_details");
        const no_of_times = document.getElementById("no_of_times");

        const recycling_challenge_details = document.getElementById("recycling_challenge_details");
        const weight_kg = document.getElementById("weight_kg");

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

        // upload achievement file
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

            const file = e.dataTransfer.files[0];

            if(file.size > 2 * 1024 * 1024){
                alert("File too large. Max 2MB allowed.");
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
            return confirm("Are you sure you want to add this challenge?");
        }
    </script>
</body>
</html>