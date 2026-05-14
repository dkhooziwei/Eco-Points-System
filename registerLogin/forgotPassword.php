<?php 
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    function generate_temp_password() {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%";
        return substr(str_shuffle($chars), 0, 10);
    }

    if(isset($_POST["send_email_btn"])){
        include "../conn.php";
        $email = $_POST["email"];
        $sql = "SELECT * FROM user WHERE email = '$email'";
        $result = mysqli_query($con, $sql);
        if(!$result){
            die("Error: ".mysqli_error($con));
        }

        $rowcount = mysqli_num_rows($result);
        if($rowcount == 1){
            $temp_password = generate_temp_password();
            $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);

            $sql2 = "UPDATE user 
                    SET password = '$hashed_password'
                    WHERE email = '$email'";
            if(!mysqli_query($con, $sql2)){
                die("Error: ".mysqli_error($con));
            }
            else{
                require "PHPMailer/Exception.php";
                require "PHPMailer/PHPMailer.php";
                require "PHPMailer/SMTP.php";

                $mail = new PHPMailer(true);

                try {
                    $mail -> isSMTP();
                    $mail -> Host = "smtp.gmail.com";
                    $mail -> SMTPAuth = true;
                    $mail -> Username = "ecopoints6@gmail.com";
                    $mail -> Password = "tmvg wuuh rhrz fxym";
                    $mail -> SMTPSecure = "tls";
                    $mail -> Port = 587;

                    $mail -> setFrom("ecopoints6@gmail.com", "Eco Points");
                    $mail -> addAddress($email);

                    $mail -> Subject = "Eco Points - Password Reset";
                    $mail -> Body = "Your temporary password is:\n$temp_password\n\nPlease change your password later in Profile.";

                    $mail -> send();
                    echo "<script>alert('A temporary password has been sent to your email.');</script>";
                }
                catch (Exception $e){
                    echo "Mailler Error: ".$mail -> ErrorInfo;
                }
            }
        }
        else{
            echo "<script>alert('Email not found.');</script>";
        }

        mysqli_close($con);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="../commonStyle.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito+Sans&display=swap');

        label {
            font-size: 18px;
            font-weight: bold;
        }

        input[type="email"]{
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
            height: 400px;
            padding: 25px;
            border: 2px solid #498428;
            border-radius: 20px;
            box-shadow: 0 0 14px rgba(99, 143, 105, 1);
            background-color: #fcffd1ff;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
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
            #wrapper {
                width: 90%;
                max-width: 400px;
                margin: 0 auto;
            }
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
    <div id="wrapper" class="box">
        <form method="post" id="forgot_password">

            <a href="login.php">< Back to Login</a>
            <br><br>

            <h2>Forgot Password</h2>

            <label name="email">Email</label><br>
            <input type="email" name="email" placeholder="Enter your email" required>

            <br><br><br><br>
            <button type="submit" name="send_email_btn">Send Email</button>
    
        </form>
    </div>
</body>
</html>