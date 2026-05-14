<?php
require_once "../conn.php";


if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
   
    $stmt = $con->prepare("SELECT * FROM user WHERE user_id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        echo "<script>alert('User not found!'); window.location.href='../admin/admin User Management.php';</script>";
        exit();
    }
} else if (!isset($_POST['updateBtn'])) {
    header("Location: ../admin/admin User Management.php");
    exit();
}


if (isset($_POST['updateBtn'])) {
    $id       = $_POST['user_id'];
    $role     = $_POST['role'];
    $name     = $_POST['contact_name'];
    $contact  = $_POST['contact_phone'];
    $email    = $_POST['contact_email'];
    $username = $_POST['username'];
    $new_pass = $_POST['password'];

   
    if (!empty($new_pass)) {
      
        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
        $sql = "UPDATE user SET role=?, name=?, username=?, email=?, contact_no=?, password=? WHERE user_id=?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("sssssss", $role, $name, $username, $email, $contact, $hashed_pass, $id);
    } else {
        
        $sql = "UPDATE user SET role=?, name=?, username=?, email=?, contact_no=? WHERE user_id=?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ssssss", $role, $name, $username, $email, $contact, $id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('User Updated Successfully!'); window.location.href='admin User Management.php';</script>";
    } else {
        echo "<script>alert('Update Failed: " . $stmt->error . "');</script>";
    }
}
?>

<!DOCTYPE html> 
<html lang='en'> 
<head> 
    <meta charset='UTF-8'> 
    <meta name='viewport' content='width=device-width, initial-scale=1.0'> 
    <title>Edit User</title>
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
        
       
        .readonly-field {
             background-color: #ccc;
              cursor: not-allowed;
               color: #666; 
            }
            
    </style>
</head>
<body>

    <?php include 'adminTaskbar.php'; ?>

    <main>
        <div class="content-wrapper">
            <h1>Edit User</h1>

            <div class="registration-form-container">
                <form method="post" action="">
                    
                    <div class="form-grid">
                        <div class="column-left">
                            
                            <div class="input-group">
                                <label class="input-label">User ID</label>
                                <input type="text" class="input-field readonly-field" name="user_id" value="<?php echo $row['user_id']; ?>" readonly>
                            </div>

                            <div class="input-group">
                                <label class="input-label">Role</label>
                                <select class="input-field" name="role" required>
                                    <option value="Challenger" <?php if($row['role']=="Challenger") echo "selected"; ?>>Challenger</option>
                                    <option value="Admin" <?php if($row['role']=="Admin") echo "selected"; ?>>Admin</option>
                                    <option value="Partner" <?php if($row['role']=="Partner") echo "selected"; ?>>Partner</option>
                                    <option value="Recycling Centre Admin" <?php if($row['role']=="Recycling Centre Admin") echo "selected"; ?>>Recycling Centre Admin</option>
                                </select>
                            </div>

                            <div class="input-group">
                                <label class="input-label">Name</label>
                                <input type="text" class="input-field" name="contact_name" value="<?php echo $row['name']; ?>" required>
                            </div>
                        </div>

                        <div class="column-right">
                            <div class="input-group">
                                <label class="input-label">Phone Number</label>
                                <input type="tel" class="input-field" name="contact_phone" value="<?php echo $row['contact_no']; ?>" required>
                            </div>
                            
                            <div class="input-group">
                                <label class="input-label">Username</label>
                                <input type="text" class="input-field" name="username" value="<?php echo $row['username']; ?>" required>
                            </div>

                            <div class="input-group">
                                <label class="input-label">Email Address</label>
                                <input type="email" class="input-field" name="contact_email" value="<?php echo $row['email']; ?>" required>
                            </div>

                            <div class="input-group">
                                <label class="input-label">New Password (Leave blank to keep current)</label>
                                <input type="password" class="input-field" name="password" placeholder="Type only if changing password">
                            </div>
                        </div>
                    </div>

                    <div class="button-row">
                        <button type="submit" name="updateBtn" class="btn">Update User</button>
                        <a href="../admin User Management.php" class="btn btn-cancel">Cancel</a>
                    </div>

                </form>
            </div>
        </div>
    </main>
</body>
</html>