<?php 
    require_once "../session.php";
    require_once "../conn.php";
    require_once "../commonFunctions.php";

    if($_SESSION["role"] !== "Challenger"){
        header("Location: ../registerLogin/login.php");
        exit();
    }

    if(!isset($_GET["id"])){
        die("Invalid request.");
    }

    $challenge_id = $_GET["id"];

    if(isset($_POST["submit_btn"])){

        if(!isset($_FILES["proof"])) {
            echo "<script>alert('No file uploaded.');</script>";
            exit;
        }

        $error = $_FILES["proof"]["error"];

        if($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE){
            echo "<script>alert('File is too large. Max 2MB allowed.');</script>";
            exit;
        }

        if($error !== UPLOAD_ERR_OK){
            echo "<script>alert('Upload failed.');</script>";
            exit;
        }

        $file = $_FILES["proof"];

        $filename = $file["name"]; // original file name
        $file_tmp = $file["tmp_name"]; // temporary file on server
        $file_size = $file["size"]; // file size
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION)); //get extension

        $allowed = ["doc", "docx", "pdf"];
        if(in_array($file_ext, $allowed)){
            if($file_size <= 2 * 1024 * 1024){ //check file size (max 2MB)
                $proof_id = get_next_id($con, "proof", "PR", "proof_id");
                $destination = "../uploads/proofs/" . $proof_id . "." . $file_ext;

                if(!move_uploaded_file($file_tmp, $destination)){
                    echo "<script>alert('File upload failed. Please try again.');</script>";
                    exit();
                }

                $challenger_id = $_SESSION["mySession"];
                $challenge_id = $_POST["challenge_id"];
                $date = date("Y-m-d");
                $time = date("H:i:s");
                $status = "Pending";
                $file_path = "uploads/proofs/". $proof_id . "." . $file_ext;

                $sql = "INSERT INTO proof(
                proof_id,
                challenger_id,
                challenge_id,
                date,
                time,
                status,
                file_path)
        
                VALUES(
                '$proof_id',
                '$challenger_id',
                '$challenge_id',
                '$date',
                '$time',
                '$status',
                '$file_path')";

                if(mysqli_query($con, $sql)){
                    echo "<script>alert('Proof submitted!');
                    window.location.href='uploadProof.php?id=$challenge_id';</script>";
                }
                else{
                    die("Error: ".mysqli_error($con));
                }
            }
            else{
                echo "<script>alert('File is too large. Max 2MB allowed.');</script>";
            }
        }
        else{
            echo "<script>alert('Invalid file type. Only DOC, DOCX, PDF allowed.');</script>";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Challenger - Upload Proof</title>
    <link href="../commonStyle.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito+Sans&display=swap');

        body {
            font-size: 18px;
            font-weight: bold;
        }

        .upload-wrapper {
            width: 100%;
            max-width: 530px;
            height: auto;
            display: flex;
            margin: 0 auto;
            margin-top: 30px;
            justify-content: center;
            align-items: center;
            padding: 25px;

            background: linear-gradient(to top, #d5e8c9ff, white);
            border: 2px solid #979797ff;
            box-shadow: 0 0 10px #979797ff;
            border-radius: 40px;
        }

        #proof_form {
            width: 100%;
            margin-left: 2%;
        }

        #upload_box {
            position: relative;
            width: 100%;
            max-width: 500px;
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
            font-size: 16px;
            margin-top: 10px;
            font-style: italic;
            font-weight: normal;
        }

        #submit_btn {
            width: 140px;
            height: 40px;
            font-size: 18px;
            font-family: "Nunito Sans", sans-serif;
            font-weight: bold;
            border-radius: 10px;
            cursor: pointer;
            color: #407222ff;
            background-color: #C1D95C;
            border: 3px solid #498428;
            margin-top: 30px;
        }

        #submit_btn:hover {
            background-color: #ccde8dff;
        }

        @media (max-width: 600px){
            body {
                font-size: 14px;
            }

            a {
                font-size: 16px;
            }

            .upload-wrapper {
                width: 90%;
                padding: 20px;
            }

            #challenges {
                margin-left: 0;
                width: 100%;
                height: 30px;
            }

            #upload_box {
                width: 100%;
                height: 160px;
            }

            #file_message {
                font-size: 14px;
            }

            #submit_btn {
                width: 100%;
                height: 40px;
                font-size: 16px;
                margin-left: 0;
                margin-top: 5%;
            }
        }
    </style>
</head>
<body>
    <?php 
        include "challengerTaskbar.php";
    ?>

    <div class="upload-wrapper">
        <form method="post" id="proof_form" enctype="multipart/form-data">
            <?php 
                $sql = "SELECT * FROM challenge WHERE challenge_id = '$challenge_id'";
                $result = mysqli_query($con, $sql);

                if(!$result){
                    die("Error: ".mysqli_error($con));
                }

                $row = mysqli_fetch_assoc($result);
                $challenge_name = $row["name"];
            ?>
            <label>Challenge: <?= $challenge_name ?></label>
            <input type="hidden" name="challenge_id" value="<?= $challenge_id?>">
            </select>
            <br><br>

            <label for="proof">Upload your proof:</label>

            <div id="upload_box">
                <p id="upload_box_text">Drag and drop your file here or click to select</p>
                <span id="file_name" style="display:none;"></span> <!-- to display file name-->
                <button type="button" id="remove_btn" style="display:none;">✕</button> <!-- button to remove file -->
                <input type="file" id="proof" name="proof" accept=".doc,.docx,.pdf" required>
            </div>

            <div id="file_message">
                <p>Accepted File Types: DOC, DOCX, PDF</p>
                <p>Max. File Size: 2MB</p>
            </div>

            <button type="submit" name="submit_btn" id="submit_btn">Submit Proof</button>
        </form>
    </div>

    <script>
        const upload_box = document.getElementById("upload_box");
        const file_input = document.getElementById("proof");
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

        document.getElementById("proof").addEventListener("change", function(){
            if(this.files[0].size > 2 * 1024 * 1024) { //prevent user from submitting a file exceeding max size
                alert("File too large. Max 2MB allowed.");
                this.value = "";
                file_name.style.display = "none";
                remove_btn.style.display = "none";
                upload_box_text.style.display = "block";
            }
        });
    </script>
</body>
</html>