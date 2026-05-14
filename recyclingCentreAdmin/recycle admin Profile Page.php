<?php
require_once "../session.php";
require_once "../conn.php";

if($_SESSION["role"] !== "Recycling Centre Admin"){
        header("Location: ../registerLogin/login.php");
        exit();
    }

    $name = "Unknown";
    $RecycleCenterAdminID = "N/A";
    $phone = "N/A";
    $email = "N/A";
    $username = "N/A";

    if (isset($_SESSION['mySession']) && isset($con)) {
        $userID = $_SESSION['mySession']; 

    
        $sql = "SELECT * FROM user WHERE user_id = ?"; 
        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $userID); 
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
        
            $name = isset($row['name']) ? $row['name'] : $name;
            $phone = isset($row['contact_no']) ? $row['contact_no'] : $phone;
            $email = isset($row['email']) ? $row['email'] : $email;
            $username = isset($row['username']) ? $row['username'] : $username;
            $RecycleCenterAdminID = isset($row['user_id']) ? $row['user_id'] : $RecycleCenterAdminID;
        }
    }
?>

<!DOCTYPE html> 
<html lang='en'> 
<head> 
    <meta charset='UTF-8'> 
    <meta name='viewport' content='width=device-width, initial-scale=1.0'> 
    <title>Recycle Admin-Profile</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="commonStyle.css" rel="stylesheet">

<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Nunito Sans', sans-serif;
        }

        body {
            background-color: #FFFDF1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
   
        main {
            width: 100%;
            max-width:100%;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            padding: 30px 40px;
        }

        h1 {
            font-weight: 600;
            color: #333;
            font-size: 2.2rem;
        }

        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            width:100%;
        } 

        .btn-edit {
            background-color: #EAEF9D; 
            color: #333; 
            border: 0px;
            padding: 10px 35px;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            text-transform: uppercase;
            margin:0;
            border-radius: 5px;
        }

        .profile-pic-container {
            display: flex; 
            justify-content: center;
            align-items: center;
            width: 100%;
            margin-bottom: 30px;
        }

       
        .profile-pic-large {
            width: 220px; 
            height: 220px; 
            object-fit: contain; 
            border-radius: 10px; 
            background-color: #e0e0e0; 
        }
       
        .info-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            width: 100%;
        }

        .gray-box {
            background-color: #8ab961ff; 
            padding: 15px 20px;
            text-align: center;
            font-size: 1.1rem;
            font-weight: 600;
            width: 100%;
            color: #333;
            border-radius: 0;
        }

        .row-top {
            width: 100%;
            max-width: 900px;
        }

        .row-middle {
            display: flex;
            justify-content: center;
            gap: 20px;
            width: 100%;
            max-width: 900px;
        }

        .row-middle .gray-box {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .row-bottom {
            width: auto;
            min-width: 400px;
        }

        .logout-container {
            display: flex;
            justify-content: flex-end;
            margin-top: auto;
            padding-top: 50px;
            width: 100%;
        }

        .btn-logout {
            background-color: #336A29; 
            color: white; 
            border: 0px;
            padding: 15px 45px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            text-transform: uppercase;
            border-radius:5px;
        }

        /* MOBILE VIEW */
        @media (max-width: 768px) {
            main { 
                padding: 20px;
            }
            h1 { 
                font-size: 1.8rem; 
            }
            
            .row-middle {
                gap: 10px; 
                flex-direction: column; 
            }
            .gray-box { 
                font-size: 0.9rem; 
                padding: 12px; 
            }
            .profile-pic-large { 
               
                width: 160px; 
                height: 160px; 
            }
            
            .btn-edit {
                padding: 8px 20px; 
            }
            .btn-logout { 
                padding: 10px 30px; 
                font-size: 1rem; 
            }
        }

    </style>
</head>
<body>
    <?php include 'recAdminTaskbar.php'; ?>

    <main> 
        <div class="top-row">
            <h1>Recycle Admin Profile</h1>
            <button class="btn-edit" onclick="window.location.href='../Recycle Admin assignment/recAdminEditProfile.php'">EDIT</button>
        </div>
        
        <div class="profile-pic-container">
            <img src="../images/Profile Picture.jpg" alt="Profile Picture" class="profile-pic-large"> 
        </div>
 <div class="info-section">
            <div class="gray-box row-top">
                <?php echo $name; ?> | Recycle Center Admin ID: <?php echo $RecycleCenterAdminID; ?> | RECYCLE CENTER ADMIN
            </div>

            <div class="row-middle">
                <div class="gray-box">
                    Phone Number: <?php echo $phone; ?>
                </div>
                <div class="gray-box">
                    Email: <?php echo $email; ?>
                </div>
            </div>
            
            <div class="gray-box row-bottom">
                Username: <?php echo $username; ?>
            </div>
        </div>

          <div class="logout-container" style="display: flex; gap: 15px; align-items: center;">
        <form action="../registerLogin/logout.php" method="POST">
        <button type="submit" name="logoutBtn" class="btn-logout">LOG OUT</button>
        </form>
    </div>
    </main>
</body>
</html>