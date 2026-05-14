<?php

    include('../conn.php');
    include('../commonFunctions.php');
    include('../session.php');

    if($_SESSION["role"] !== "Admin"){
        header("Location: ../registerLogin/login.php");
        exit();
    }

    if (!isset($_GET['centre_id']) || trim($_GET['centre_id']) === '') {
        die("Invalid recycling centre ID.");
    }

    $id = mysqli_real_escape_string($con, $_GET['centre_id']);


    $sql = "SELECT * FROM recycling_centre WHERE centre_id = '$id' LIMIT 1";
    $result = mysqli_query($con, $sql);

    if (!$result || mysqli_num_rows($result) === 0) {
        die("Recycling centre not found.");
    }

    $row = mysqli_fetch_assoc($result);



    // check and submit data
    $image_file_name = $row['file_path'] ?? "";
    $errorMessage = "";
    $success = false;

    if (isset($_POST['existing_image']) && (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE)) {
        $image_file_name = $_POST['existing_image'];
    }


    if (isset($_POST['submitBtn'])) {

        $image_uploaded = false;

        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

            if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {

                $upload_dir = "../images/recyclingCentrePIC/";
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                $file_temp = $_FILES['image']['tmp_name'];
                $file_ext  = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                if ($file_ext == "") $file_ext = "jpg";

                $image_file_name = $id . "." . $file_ext;
                $full_path = $upload_dir . $image_file_name;
                $web_path = "images/recyclingCentrePIC/" . $image_file_name;

                if (!empty($row['file_path']) && file_exists("../" . $row['file_path']) && ("../" . $row['file_path'] != $full_path)) {
                    unlink("../" . $row['file_path']);
                }

                if (!move_uploaded_file($file_temp, $full_path)) {
                    $errorMessage = "Failed to upload image.";
                } else {
                    $image_uploaded = true;
                }

            } else {
                $errorMessage = "Image upload error.";
            }

        } else {
            if (!empty($_POST['existing_image'])) {
                $web_path = $_POST['existing_image'];
            } else {
                $errorMessage = "Image is required.";
            }
        }

        if (empty($errorMessage)) {
            $name    = mysqli_real_escape_string($con, trim($_POST['name']));
            $contact = mysqli_real_escape_string($con, trim($_POST['contact']));
            $address = mysqli_real_escape_string($con, trim($_POST['address']));
            $email   = mysqli_real_escape_string($con, trim($_POST['email']));
            $time    = mysqli_real_escape_string($con, trim($_POST['time']));
            $admin   = mysqli_real_escape_string($con, strtoupper(trim($_POST['admin'])));
            $id      = mysqli_real_escape_string($con, $id);


            $sql_check_rec_admin_duplicate = "SELECT 1 
                FROM recycling_centre 
                WHERE recycle_admin_id = '$admin'
                AND centre_id != '$id'
                AND is_deleted = 0
                LIMIT 1
            ";
            $result_check_duplicate = mysqli_query($con, $sql_check_rec_admin_duplicate);

            $sql_check_rec_admin_exist = "SELECT user_id FROM user WHERE user_id = '$admin'";
            $result_check_exist = mysqli_query($con, $sql_check_rec_admin_exist);

            if ($result_check_duplicate && mysqli_num_rows($result_check_duplicate) > 0) {
                $errorMessage = "Recycling Centre Admin ID '$admin' has already been assigned.";
            } else if ($result_check_exist && mysqli_num_rows($result_check_exist) === 0){
                $errorMessage = "Recycling Centre Admin ID '$admin' does not exist.";
            } else {
                $sql_recycling_centre = "UPDATE recycling_centre SET 
                    name = '$name', 
                    contact_no = '$contact', 
                    address = '$address', 
                    email = '$email', 
                    operation_hour = '$time', 
                    recycle_admin_id = '$admin',
                    file_path = '$web_path' 
                    WHERE centre_id = '$id'";        

                mysqli_query($con, $sql_recycling_centre);
                $success = true;
                $_POST = [];
            }
        }
    }




    function old($key, $default = '') {
        if (isset($_POST[$key])) {
            return htmlspecialchars($_POST[$key]);
        }
        return htmlspecialchars($default);
    }



?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recycling Centre</title>
    <link href="../commonStyle.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <style>
        *{
            box-sizing:border-box;
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
        <a href = "editRecyclingInfo.php" style = "text-decoration: none;">
            <button class="centre_bar">
                 Back
            </button>
        </a>
        <h1>Edit Recycling Centre</h1>
        <p class = "subtitle">
            Edit in the details below to update the details of the recycling centre.
        </p>

            <div class = "form" id = "centreForm">
                <form method = "post" enctype = "multipart/form-data" id="editCentreForm">
                    <label for = "name">Recycling Centre Name</label>
                    <input type="text" id="name" name="name" value="<?= old('name', $row['name']) ?>" required>

                    <label for = "address">Recycling Centre Address</label>
                    <input type="text" id="address" name="address" value="<?= old('address', $row['address']) ?>" required>

                    <label for = "time">Recycling Centre Operating Hours</label>
                    <input type="text" id="time" name="time" value="<?= old('time', $row['operation_hour']) ?>" required>

                    <label for = "contact">Recycling Centre Contact Number</label>
                    <input type="tel" placeholder = "0123456789" pattern = "[0-9]{10,11}" id="contact" name="contact" value="<?= old('contact', $row['contact_no']) ?>" required>

                    <label for = "email">Recycling Centre Contact Email</label>
                    <input type="email" id="email" name="email" value="<?= old('email', $row['email']) ?>" required>

                    <label for = "admin">Recycling Centre Admin</label>
                    <input type="text" id="admin" name="admin" value="<?= old('admin', $row['recycle_admin_id']) ?>" required>


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
                            

                            <input type="file" name="image" id="image" accept="image/*" >
                            <div id="imageError" style="color:red; margin-top:10px;"></div>
                        </label>
                    </div>

                    <input type="hidden" name="existing_image" value="<?= htmlspecialchars($image_file_name) ?>">

                    <div id="formError" class="error_rec_admin" style="display:<?= !empty($errorMessage) ? 'block' : 'none' ?>;">
                        <?= htmlspecialchars($errorMessage) ?>
                    </div>

                    <div class = "button_container">
                        <button type="submit" name="submitBtn">Submit</button>
                        <button type="button"
                            onclick="window.location.href='<?= $_SERVER['PHP_SELF'] ?>?centre_id=<?= urlencode($id) ?>'">
                            Reset
                        </button>


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
                    <p>Recycling centre updated successfully.</p>
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
    const form = document.getElementById("editCentreForm");


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

    document.addEventListener("DOMContentLoaded", () => {
        const existingImage = document.querySelector('input[name="existing_image"]').value;
        if (existingImage && existingImage !== '') {
            preview.src = '<?= dirname($_SERVER["PHP_SELF"]) ?>/../' + existingImage;
            preview.style.display = "block";
            nameDisplay.textContent = existingImage.split('/').pop();
        }
    });






</script>



</body>
</html>