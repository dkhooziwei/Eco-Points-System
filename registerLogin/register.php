<?php 
    $message1 = $message2 = $message3 = $message4 = $message5 = "";

    if(isset($_POST["register_btn"])) {
        include "../conn.php";
        $group = $_POST["group"];
        $name = $_POST["name"];
        $username = $_POST["username"];
        $password = $_POST["password"];
        $email = $_POST["email"];
        $phone_no = $_POST["phone_no"];

        $valid_info = true;

        for($i = 0; $i < strlen($name); $i++) {
            if(ctype_alpha($name[$i]) || $name[$i] == " "){
                continue;
            }
            else {
                $valid_info = false;
                $message1 = "<br>Please insert a valid name.<br>";
                break;
            }
        }

        $sql = "SELECT * FROM user WHERE username = '$username'";
        $result = mysqli_query($con, $sql);
        if(!$result){
            die("Error: ".mysqli_error($con));
        }

        $row_count = mysqli_num_rows($result);
        if($row_count == 1){
            $message2 = "<br>Username has been taken.<br>";
            $valid_info = false;
        }

        if(strlen($password) < 8 || 
        !preg_match("/[A-Z]/", $password) || 
        !preg_match("/[a-z]/", $password) || 
        !preg_match("/[0-9]/", $password) || 
        !preg_match("/[\W]/", $password)){
            $valid_info = false;
            $message3 = "<br>Please ensure your password meet these requirements:<br>
            - At least 8 characters<br>
            - Contains at least one uppercase letter<br>
            - Contains at least one lowercase letter<br>
            - Contains at least one special character<br>";
        }

        $sql = "SELECT * FROM user WHERE email = '$email'";
        $result = mysqli_query($con, $sql);
        if(!$result){
            die("Error: ".mysqli_error($con));
        }

        $row_count = mysqli_num_rows($result);
        if($row_count == 1){
            $message4 = "<br>This email has been registered.<br>";
            $valid_info = false;
        }

        $sql = "SELECT * FROM user WHERE contact_no = '$phone_no'";
        $result = mysqli_query($con, $sql);
        if(!$result){
            die("Error: ".mysqli_error($con));
        }
        
        if(strlen($phone_no) < 10 || strlen($phone_no) > 11){
            $message5 = "<br>Please insert a valid phone number.";
            $valid_info = false;
        }
        else{
            $row_count = mysqli_num_rows($result);
            if($row_count == 1){
                $message5 = "<br>This phone number has been registered.";
                $valid_info = false;
            }
        }

        if($valid_info){
            include "../commonFunctions.php";
            $user_id = get_next_id($con, "user", "C", "user_id");
            $system_role = "Challenger";
            $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

            $sql = "INSERT INTO user (
            user_id,
            role,
            name, 
            username,
            password,
            email,
            contact_no)
            
            VALUES (
            '$user_id',
            '$system_role',
            '$name',
            '$username',
            '$password',
            '$email',
            '$phone_no')";

            if(mysqli_query($con, $sql)){
                $sql2 = "INSERT INTO challenger (
                challenger_id,
                `group`,
                total_points)
            
                VALUES (
                '$user_id',
                '$group',
                0)
                ";

                if(mysqli_query($con, $sql2)){
                    echo "<script>alert('Successfully Registered!');
                    window.location.href='login.php'; </script>";
                }
                else {
                    die("Error: ". mysqli_error($con));
                }
            }
            else {
                    die("Error: ". mysqli_error($con));
            }
        }

        mysqli_close($con);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Points - Register as Challenger</title>
    <link href="../commonStyle.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito+Sans&display=swap');

        .image-container {
            text-align: center;
        }
        .image-container img {
            width: 300px;
            height: 250px;
        }

        label {
            font-size: 18px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"],
        input[type="tel"] {
            width: 450px;
            padding: 10px;
            border: 2px solid rgba(183, 183, 183, 1);
            border-radius: 10px;
            outline: none;
            box-sizing: border-box;
        }

        input:focus {
            border-color: rgba(35, 99, 37, 1);
            box-shadow: 0 0 5px rgba(62, 60, 60, 1);
        }

        input::placeholder {
            font-size: 16px;
            font-family: "Nunito Sans", sans-serif;
            color: rgba(110, 110, 110, 1)
        }

        button {
            width: 450px;
            height: auto;
            padding: 8px;
            font-size: 18px;
            font-family: "Nunito Sans", sans-serif;
            font-weight: bold;
            background-color: #80B155;
            border: 2px solid rgba(39, 39, 39, 1);
            border-radius: 10px;
            outline: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #C1D95C;
            box-shadow: 0 0 8px rgb(1,1,1);
        }

        #wrapper {
            width: 500px;
            margin: 0 auto;
            padding: 25px;
            border: 2px solid #498428;
            border-radius: 20px;
            box-shadow: 0 0 14px rgba(99, 143, 105, 1);
            background-color: #fcffd1ff;
        }

        a {
            text-decoration: none;
            font-weight: 900;
            color: #316727ff;
            text-shadow: 1px 1px 4px #76cd66ff;
        }

        a:hover {
            color: #49973bff;
        }

        @media (max-width: 600px){
            .image-container img {
                width: 100%;
                max-width: 250px;
                height: auto;
                display: block;
                margin: 0 auto;
            }   
            #wrapper {
                width: 90%;
                max-width: 400px;
                margin: 0 auto;
            }
            input[type="text"],
            input[type="password"],
            input[type="tel"],
            input[type="email"],
            button {
                width: 100%;
                box-sizing: border-box;
            }
        }

        * {
            box-sizing: border-box;
        }

    </style>
</head>
<body>
    <div id="wrapper">
        <div class="image-container">
            <img src="../images/Eco Points Logo.png" alt="Logo" class="center=img">
        </div>

        <br><br>

        <form method="post" id="register">
            
            <label name="group">Group</label><br>
            <label name="student">
                <input type="radio" name="group" value="Student" required>
                Student
            </label>
            <label name="staff">
                <input type="radio" name="group" value="Staff" required>
                Staff
            </label>

            <br><br>
            <label name="name">Full Name (As per IC)</label><br>
            <input type="text" name="name" placeholder="Enter your name" required>

            <br><br>
            <label name="username">Username</label><br>
            <input type="text" name="username" placeholder="Enter your username" required>

            <br><br>
            <label name="password">Password</label><br>
            <input type="password" name="password" placeholder="Enter your password" required>

            <br><br>
            <label name="email">Email</label><br>
            <input type="email" name="email" placeholder="Enter your email" required>

            <br><br>
            <label name="phone_no">Contact Number</label><br>
            <input type="tel" name="phone_no" pattern="[0-9]{10,11}" placeholder="Enter your phone number" required>

            <br><br><br>
            <button type="submit" name="register_btn">Register</button>
            
            <div id="error_message" style="color:red; font-family:'Nunito Sans', sans-serif;">
                <?php 
                    echo $message1;
                    echo $message2;
                    echo $message3;
                    echo $message4;
                    echo $message5;
                ?>
            </div>
            <br><hr>
            <p>Already have an account?   
                <a href="login.php">Log In</a>
            </p>

        </form>
    </div>
</body>
</html>