<?php 
    session_start();
    include("../conn.php");

    if(isset($_POST["sign_in_btn"])){
        $username = mysqli_real_escape_string($con, $_POST["username"]);
        $password = mysqli_real_escape_string($con, $_POST["password"]);

        $sql = "SELECT * FROM user WHERE username = '$username'";
        $result = mysqli_query($con, $sql);
        if(!$result){
            die("Error: ".mysqli_error($con));
        }
        
        $row = mysqli_fetch_assoc($result);
        $row_count = mysqli_num_rows($result);

        if($row_count == 1){
            if($row["is_deleted"] == 1){
                echo "<script>alert('Invalid account.');
                window.location.href='login.php';</script>";
                exit();
            }
            else if(password_verify($password, $row["password"])){
                $_SESSION["mySession"] = $row["user_id"];
                $_SESSION["role"] = $row["role"];

                if(isset($_POST["remember_me"])){
                    setcookie("username", $username, time() + (30 * 24 * 60 * 60), "/");
                //stores data for 30 days
                }
                else {
                    setcookie("username", "", time() - 3600, "/");
                }

                $redirect = "";
                if($_SESSION["role"] === "Admin"){
                    $redirect = "../admin/admin.php";
                }
                else if($_SESSION["role"] === "Challenger"){
                    $redirect = "../challenger/challengerHomePage.php";
                }
                else if($_SESSION["role"] === "Recycling Centre Admin"){
                    $redirect = "../recyclingCentreAdmin/recAdminHomePage.php";
                }
                else{
                    $redirect = "../partner/partnerHomePage.php";
                }

                header("Location: $redirect");
                exit();
            }
        } 
        echo "<script>alert('Invalid Username or Password. Please Try Again.');</script>";
    }

    mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Points - Login</title>
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
            font-size: 20px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"] {
            width: 450px;
            padding: 12px;
            border: 2px solid rgba(183, 183, 183, 1);
            border-radius: 10px;
            outline: none;
            box-sizing: border-box;
        }

        input[type="checkbox"] {
            width: 16px;
            height: 16px;
        }

        input:focus {
            border-color: rgba(35, 99, 37, 1);
            box-shadow: 0 0 5px rgba(62, 60, 60, 1);
        }

        input::placeholder {
            font-size: 17px;
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

        <form method="post">
            <label for="username">Username</label><br>
            <input type="text" id="username" name="username" placeholder="Enter your username" 
            value="<?php echo isset($_COOKIE['username']) ? $_COOKIE['username'] : ''; ?>" required>

            <br><br>
            <label for="password">Password</label><br>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
            <br><br>
            <a href="forgotPassword.php">Forgot Password?</a>

            <br><br><br>
            <label name="remember_me"><input type="checkbox" name="remember_me" <?php echo isset($_COOKIE['username']) ? 'checked' : ''; ?>>Remember Me</label>

            <br><br><br>
            <button type="submit" name="sign_in_btn">Sign In</button>

        </form>
        <br>
        <hr>

        <p>Does not have an account?
        <a href="register.php"> Click here to register as Challenger</a>
        </p>

        <p>Recycling Centre admins and partners, please email the
            <a href="mailto:aidenloh@gmail.com">Administrator</a>
            to request an account.
        </p>
    </div> 
</body>
</html>