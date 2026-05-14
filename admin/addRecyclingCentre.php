<?php

    include('../conn.php');
    include('../commonFunctions.php');
    include('../session.php');

    if($_SESSION["role"] !== "Admin"){
        header("Location: ../registerLogin/login.php");
        exit();
    }

    //get next id for centre
    $new_recycling_centre_id = get_next_id($con, 'recycling_centre', 'RC', 'centre_id');



    // check and submit data
    $image_file_name = "";
    $errorMessage = "";
    $success = false;

    if (isset($_POST['existing_image'])) {
        $image_file_name = $_POST['existing_image'];
    }


    if (isset($_POST['submitBtn'])) {
        
            $name    = mysqli_real_escape_string($con, trim($_POST['name']));
            $contact = mysqli_real_escape_string($con, trim($_POST['contact']));
            $address = mysqli_real_escape_string($con, trim($_POST['address']));
            $email   = mysqli_real_escape_string($con, trim($_POST['email']));
            $time    = mysqli_real_escape_string($con, trim($_POST['time']));
            $recycle_admin_id   = mysqli_real_escape_string($con, strtoupper(trim($_POST['admin'])));
        

            $sql_check_rec_admin_duplicate = "SELECT recycle_admin_id FROM recycling_centre WHERE recycle_admin_id = '$recycle_admin_id' AND is_deleted = 0";
            $result_check_duplicate = mysqli_query($con, $sql_check_rec_admin_duplicate);

            $sql_check_rec_admin_exist = "SELECT user_id FROM user WHERE user_id = '$recycle_admin_id'";
            $result_check_exist = mysqli_query($con, $sql_check_rec_admin_exist);

            if ($result_check_duplicate && mysqli_num_rows($result_check_duplicate) > 0) {
                $errorMessage = "Recycling Centre Admin ID '$recycle_admin_id' has already been assigned.";
            } else if ($result_check_exist && mysqli_num_rows($result_check_exist) === 0){
                $errorMessage = "Recycling Centre Admin ID '$recycle_admin_id' does not exist.";
            } else{

                if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
                    $errorMessage = "Image is required.";
                } else if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                    $errorMessage = "Image upload error.";
                }

                if (empty($errorMessage)) {
                    $upload_dir = "../images/recyclingCentrePIC/";
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION) ?: "jpg";

                    $image_file_name = $new_recycling_centre_id . "." . $file_ext;
                    $full_path = $upload_dir . $image_file_name;
                    $web_path = "images/recyclingCentrePIC/" . $image_file_name;

                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $full_path)) {
                        $errorMessage = "Failed to upload image.";
                    }
                }
            
                if (empty($errorMessage)) {
                    $sql_recycling_centre = "INSERT INTO recycling_centre 
                        (centre_id, name, contact_no, address, email, operation_hour, recycle_admin_id, file_path)
                        VALUES 
                        ('$new_recycling_centre_id','$name','$contact','$address','$email','$time','$recycle_admin_id','$web_path')";

                    mysqli_query($con, $sql_recycling_centre);
                    $success = true;
                    $_POST = [];
                    $image_file_name = "";
                }
                
            }
        
    }



    function old($key) {
        return isset($_POST[$key]) ? htmlspecialchars($_POST[$key]) : '';
    }


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Recycling Centre</title>
    <link href="../commonStyle.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <style>
        *{
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        .page{
            max-width:900px;
            margin:40px auto;
            padding:20px;
        }

        h1{
            color:#336A29;
            font-size:40px;
            font-weight:900;
            margin-bottom:5px;
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
            margin-top:18px;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"]{
            width:100%;
            padding:12px;
            border-radius:12px;
            border:1px solid #ccc;
            font-size:14px;
        }

        input:focus{
            outline:none;
            border-color:#498428;
        }

        .image_section{
            margin-top:20px;
        }

        .file_upload{
            display:flex;
            align-items:center;
            gap:12px;
            padding:14px;
            border:2px dashed #eaef9d;
            border-radius:12px;
            background:rgba(234,239,157,.25);
            cursor:pointer;
        }

        .file_upload input{
            display:none;
        }

        .file_upload .material-symbols-outlined{
            color:#336A29;
        }

        .file_upload .text{
            font-size:14px;
            color:#336A29;
        }

        .button_container{
            display:flex;
            gap:12px;
            margin-top:20px;
        }

        .button_container button{
            flex:1;
            padding:16px;
            font-size:16px;
            font-weight:700;
            border:none;
            border-radius:12px;
            cursor:pointer;
            transition:.2s ease;
        }

        button[name="submitBtn"]{
            background:#336A29;
            color:#fff;
        }

        button[name="submitBtn"]:hover{
            background:#498428;
        }

        button[name="resetBtn"]{
            background:none;
            color:#999;
        }

        button[name="resetBtn"]:hover{
            color:red;
        }

        .error_rec_admin{
            background:#fff;
            border:1px solid #eaef9d;
            border-radius:16px;
            padding:20px;
            margin-top:20px;
            color:red;
            box-shadow:0 10px 25px rgba(0,0,0,.05);
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

        button{
            padding:16px;
            font-size:16px;
            font-weight:700;
            border:none;
            border-radius:12px;
            cursor:pointer;
            transition:all .2s ease;
        }

        .centre_bar {
            display: inline-block;
            height: 38px;
            line-height: 38px;
            padding: 0 14px;
            border-radius: 10px;
            border: none;
            background: #498428;
            color: white;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            text-align: center;
            text-decoration: none;
            margin-bottom: 0px;
            min-width: 12%;
        }

        .centre_bar:hover {
            background: #3c6f22;
        }

        #admin {
            text-transform: uppercase;
        }


        @media (max-width: 500px) {
            .file_upload {
                flex-direction: column;
                align-items: flex-start;
            }

            .file_upload .text {
                margin-top: 8px;
            }

            #imagePreview {
                max-width: 100%;
            }
        }

 

    </style>

</head>
<body>

    <?php include "adminTaskbar.php"; ?>
    <div class = "page">
        <a href = "editRecyclingInfo.php" class="centre_bar" style = "text-decoration: none;">
                 Back
            
        </a>
        <h1>Add Recycling Centre</h1>
        <p class = "subtitle">
            Fill in the details below to register a new recycling centre.
        </p>

            <div class = "form" id = "centreForm">
                <form method = "post" enctype = "multipart/form-data">
                    <label for = "name">Recycling Centre Name</label>
                    <input type="text" id="name" name="name" value="<?= old('name') ?>" required>

                    <label for = "address">Recycling Centre Address</label>
                    <input type="text" id="address" name="address" value="<?= old('address') ?>" required>

                    <label for = "time">Recycling Centre Operating Hours</label>
                    <input type="text" id="time" name="time" value="<?= old('time') ?>" required>

                    <label for = "contact">Recycling Centre Contact Number</label>
                    <input type="tel" placeholder = "0123456789" pattern = "[0-9]{10,11}" id="contact" name="contact" value="<?= old('contact') ?>" required>

                    <label for = "email">Recycling Centre Contact Email</label>
                    <input type="email" id="email" name="email" value="<?= old('email') ?>" required>

                    <label for = "admin">Recycling Centre Admin</label>
                    <input type="text" id="admin" name="admin" value="<?= old('admin') ?>" required>


                    <div class="image_section">
                        <label>Recycling Centre Image</label>
                        <label class="file_upload" for="image">
                            <div class="icon">
                                <span class="material-symbols-outlined">
                                    upload
                                </span>
                            </div>
                            <img id="imagePreview" 
                            style="display:none; max-width:200px; margin-top:10px; border-radius:12px;">
                            <div class="text">
                                <span id="file_name_display">Click to upload image</span>
                            </div>
                            

                            <input type="file" name="image" id="image" accept="image/*" onchange="updateFileName(this)">
                            <div id="imageError" style="color:red; margin-top:10px;"></div>
                        </label>
                    </div>

                    <input type="hidden" name="existing_image" value="<?= htmlspecialchars($image_file_name) ?>">

                    <div id="formError" class="error_rec_admin" style="display:<?= !empty($errorMessage) ? 'block' : 'none' ?>;">
                        <?= htmlspecialchars($errorMessage) ?>
                    </div>

                    <div class = "button_container">
                        <button type="submit" name="submitBtn">Submit</button>
                        <button type="button" onclick="window.location.href=window.location.pathname">Reset</button>

                    </div>
                    

                </form>



            </div>
    </div>


    <?php if ($success): ?>
        <div class="overlay">
            <div class="modal">
                <div class="modal_header">
                    <h2 style="font-size: 30px;">Success!</h2>
                </div>
                <div class="modal-body">
                    <p>Recycling centre added successfully.</p>
                    <form method="post">
                        <button onclick="window.location.href=window.location.pathname">OK</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>


    
    <script>
    const MAX_SIZE = 2 * 1024 * 1024; 
    const image = document.getElementById("image");
    const preview = document.getElementById("imagePreview");
    const nameDisplay = document.getElementById("file_name_display");
    const imageError = document.getElementById("imageError");
    const formError = document.getElementById("formError");
    const form = document.getElementById("centreForm");

    image.addEventListener("change", () => {
        const file = image.files[0];
        if (!file) return;

        nameDisplay.textContent = file.name;

        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = "block";
        };
        reader.readAsDataURL(file);

        imageError.textContent = file.size > MAX_SIZE
            ? "Image exceeds 2MB."
            : "";
    });

    // Form validation
    form.addEventListener("submit", function (e) {
        formError.style.display = "none";
        formError.textContent = "";

        const file = image.files[0];
        const existingImage = document.querySelector('input[name="existing_image"]').value;

        if (!file && !existingImage) {
            e.preventDefault();
            showFormError("Image is required.");
            return;
        }

        if (file && file.size > MAX_SIZE) {
            e.preventDefault();
            showFormError("Image exceeds 2MB.");
            return;
        }
    });

    function showFormError(message) {
        formError.textContent = message;
        formError.style.display = "block";
    }

    <?php if (!empty($image_file_name) && file_exists("../images/recyclingCentrePIC/$image_file_name")): ?>
        preview.src = "../images/recyclingCentrePIC/<?= $image_file_name ?>";
        preview.style.display = "block";
        nameDisplay.textContent = "<?= $image_file_name ?>";
    <?php endif; ?>
</script>



</body>
</html>