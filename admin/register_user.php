<?php
require_once "../session.php";
require_once "../conn.php";
require_once "../commonFunctions.php";


if (isset($con) && !isset($con)) {
    $con = $con; 
} elseif (isset($con) && !isset($con)) {
    $con = $con; 
}

if (!isset($con) || !$con) {
    die("CRITICAL ERROR: Database connection failed. Please check your 'conn.php' file.");
}

if(isset($_POST['submitBtn'])){

   
    $role     = $_POST['role'];
    $name     = $_POST['contact_name'];
    $contact  = $_POST['contact_phone'];
    $email    = $_POST['contact_email'];
    $raw_pass = $_POST['password'];

 
    $prefix = "U"; 
    if ($role == "Challenger") $prefix = "C";
    elseif ($role == "Admin") $prefix = "A";
    elseif ($role == "Partner") $prefix = "P";
    elseif ($role == "Recycling Centre Admin") $prefix = "RA";

  
    if (function_exists('get_next_id')) {
        $id = get_next_id($con, 'user', $prefix, 'user_id');
    } else {
        die("ERROR: The function 'get_next_id' was not found. Check if 'commonFunctions.php' is in the same folder.");
    }

  
    $parts = explode('@', $email);
    $username = $parts[0];

   
    $hashed_pass = password_hash($raw_pass, PASSWORD_DEFAULT);

   
    $sql = "INSERT INTO user (user_id, role, name, username, password, email, contact_no) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $con->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssssss", $id, $role, $name, $username, $hashed_pass, $email, $contact);

        if($stmt->execute()){
            if ($role === "Recycling Centre Admin") {
                echo "<script>
                    alert('Recycling Centre Admin Registered Successfully! New ID: $id');
                    window.location.href = 'addRecyclingCentre.php';
                </script>";
            } else {
                echo "<script>
                    alert('User Registered Successfully! New ID: $id');
                    window.location.href = 'admin User Management.php';
                </script>";
            }  
                
        } else {
            echo "<script>alert('Database Error: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('SQL Prepare Error: " . $con->error . "');</script>";
    }
}
?>

<!DOCTYPE html> 
<html lang='en'> 
<head> 
    <meta charset='UTF-8'> 
    <meta name='viewport' content='width=device-width, initial-scale=1.0'> 
    <title>Admin - User Registration</title>
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
             display: flex;
              flex-direction: column; 
              flex-grow: 1; 
              align-items: center; 
              justify-content: center; 
              padding: 40px; 
            }

        .content-wrapper { 
            width: 100%; 
            max-width: 1600px; 
            display: flex; 
            flex-direction: column;
             gap: 20px; 
            }
        h1 { 
            font-weight: 700; 
            color: #333; 
            font-size: 2rem;
             align-self: flex-start; 
            }
        .registration-form-container {
             background-color: white; 
             border-radius: 20px; 
             box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
             padding: 50px; 
             width: 100%; 
             display: flex;
              flex-direction: column; 
              gap: 40px; 
            }

        .form-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr;
             column-gap: 80px; 
             row-gap: 30px;
             }

        .input-group { 
            display: flex; 
            flex-direction: column; 
            gap: 10px; 
        }
        .input-label {
             font-weight: 700; 
             color: #333; 
             font-size: 1rem; 
            }
        .input-field { 
            width: 100%;
             padding: 15px 20px; 
             border: 2px solid #e0e0e0; 
             border-radius: 8px; 
             background-color: #e0e0e0; 
             font-size: 1.1rem; 
             outline: none; 
             transition: 0.3s; 
            }

        .input-field:focus { 
            background-color: white; 
            border-color: #80B155;
         }

        .input-field.valid {
             background-color: #7EB77F; 
             border-color: #7EB77F; 
             color: white; 
            }

        .input-field.invalid {
             background-color: #ED7884; 
             border-color: #ED7884;
              color: white;
             }

        .button-row { 
            display: flex;
            justify-content: flex-start;
            gap: 15px;
            margin-top: 30px; 
            }

        .btn { 
            padding: 15px 40px;
             border-radius: 5px;
              font-weight: 700; 
              cursor: pointer;
               border: none; 
               font-size: 16px;
                background-color: #80B155; 
                color: white; 
                transition: background-color 0.3s ease; 
            }

        .btn:hover { 
            background-color: #b3b3b3; 
        }
        .btn-cancel { 
            background-color: #666; 
            text-decoration: none; 
            display: inline-block; 
            text-align: center;
         }

       @media (max-width: 768px) {
             body { 
                padding-bottom: 80px;
             } 
             main {
                 padding: 20px; 
                 justify-content: flex-start;
                 } 
            .registration-form-container { 
                padding: 20px; 
                gap: 25px; 
            } 
            .form-grid { 
                grid-template-columns: 1fr; 
                gap: 25px; 
            } 
            
            .button-row { 
                flex-direction: column; 
                align-items: stretch; 
            }
        }


    </style>
</head>
<body>

    <?php include 'adminTaskbar.php'; ?>

    <main>
        <div class="content-wrapper">
            <h1>Register New User</h1>

            <div class="registration-form-container">
                <form method="post" action="" id="registrationForm" onsubmit="return validateForm(event)">
                    
                    <div class="form-grid">
                        <div class="column-left">
                            <div class="input-group">
                                <label class="input-label">Role</label>
                                <select class="input-field" name="role" required onchange="validateInput(this)">
                                    <option value="" disabled selected>Select Role</option>
                                    <option value="Challenger">Challenger</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Partner">Partner</option>
                                    <option value="Recycling Centre Admin">Recycling Centre Admin</option>
                                </select>
                            </div>
                            <div class="input-group">
                                <label class="input-label">Name</label>
                                <input type="text" class="input-field" name="contact_name" oninput="validateInput(this)" required>
                            </div>
                            <div class="input-group">
                                <label class="input-label">Phone Number</label>
                                <input type="tel" class="input-field" name="contact_phone" pattern="[0-9]{10,11}" oninput="validateInput(this)" required>
                            </div>
                        </div>

                        <div class="column-right">
                            <div class="input-group">
                                <label class="input-label">Email Address</label>
                                <input type="email" class="input-field" name="contact_email" oninput="validateInput(this)" required>
                            </div>
                            <div class="input-group">
                                <label class="input-label">Password</label>
                                <input type="password" class="input-field" name="password" oninput="validateInput(this)" required>
                            </div>
                        </div>
                    </div>

                    <div class="button-row">
                        <button type="submit" name="submitBtn" class="btn">Submit</button>
                        <button type="reset" class="btn" onclick="resetValidation()" style="background-color: #d9534f;">Reset</button>
                        <a href="admin User Management.php" class="btn btn-cancel">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        function validateInput(input) {
            if (input.value.trim() !== "") {
                input.classList.remove('invalid');
                input.classList.add('valid');
            } else {
                input.classList.remove('valid');
                input.classList.add('invalid');
            }
        }
        function validateForm(event) {
            const inputs = document.querySelectorAll('.input-field');
            let isAllValid = true;
            inputs.forEach(input => {
                if (input.value.trim() === "") {
                    input.classList.remove('valid');
                    input.classList.add('invalid');
                    isAllValid = false;
                } else {
                    input.classList.remove('invalid');
                    input.classList.add('valid');
                }
            });
            if (!isAllValid) {
                alert("Please fill in all fields.");
                return false; 
            }
            return true;
        }
        function resetValidation() {
            const inputs = document.querySelectorAll('.input-field');
            inputs.forEach(input => {
                input.classList.remove('valid');
                input.classList.remove('invalid');
            });
        }
    </script>
</body>
</html>