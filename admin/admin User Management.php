<?php 

require_once "../session.php";
require_once "../conn.php";

if($_SESSION["role"] !== "Admin"){
        header("Location: ../registerLogin/login.php");
        exit();
    }


if (isset($con) && !isset($con)) {
    $con = $con; 
} elseif (isset($con) && !isset($con)) {
    $con = $con; 
}


if (!isset($con) || !$con) {
    die("CRITICAL ERROR: Database connection failed. Please check your 'conn.php' file.");
}
?>

<!DOCTYPE html> 
<html lang='en'> 
<head> 
    <meta charset='UTF-8'> 
    <meta name='viewport' content='width=device-width, initial-scale=1.0'> 
    <title>Admin-User Management</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link href="commonStyle.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
           padding: 20px 20px; 
        }
    h1 { 
        font-weight: 600; 
        color: #333;
         font-size: 1.8rem;
         }
    .controls-container {
         display: flex; 
         width: 100%; 
         gap: 15px; 
         align-items: center;
          margin-bottom: 25px; 
        }
    .btn { 
        border: none; 
        padding: 10px 25px; 
        border-radius: 8px; 
        font-weight: 700; 
        cursor: pointer; 
        font-size: 0.9rem; 
        transition: 0.2s; 
        white-space: nowrap;
     }
    .btn-register {
         display: flex; 
         align-items: center;
          gap: 5px; 
          text-decoration: none; 
          color: inherit; 
        } 
    .search-wrapper { 
        flex-grow: 1; 
        background-color: #e0e0e0; 
        border-radius: 20px; 
        padding: 0 15px; 
        display: flex; 
        align-items: center; 
        height: 40px;
     }
    .search-input { 
        idth: 100%;
         border: none;
          background: transparent;
           outline: none; 
           font-weight: 600; 
           color: #555; 
           font-size:0.9rem;
         }
    .btn-filter{ 
        margin-left:0;
     }
    .user-list-container { 
        background-color: white;
         border-radius: 10px;
          box-shadow: 0 2px 5px rgba(0,0,0,0.05);
           overflow: hidden;
         }
    .list-row { 
        display: grid; 
        grid-template-columns: 30% 1fr auto; 
        padding: 15px 15px; gap: 10px;
         align-items: center; 
        }
    .list-header { 
        background-color: white; 
        font-weight: 700; 
        color: #333; 
        border-bottom: 2px solid #eee;
     }
    .list-item:hover { 
        background-color: #fafafa;
     }
    .cell-text { 
        font-size: 0.9rem;
         overflow: hidden; 
         text-overflow: ellipsis;
         }
    .actions-cell{ 
        display:flex;
         gap:15px; 
         justify-content:flex-end;
          align-items:center; 
        }
    .action-btn{ 
        background: none;
         border: none; 
         cursor:pointer;
          font-size: 1.1 rem; 
          color: #777; 
          transition: 0.2s;
         }
    .action-icon:hover {
         color: #333; 
        }
    .icon-delete:hover { 
        color: #d9534f; 
    }
    .mobile-info-stack {
         display: flex; 
         flex-direction: column;
          gap: 6px; 
          justify-content: center;
           border-left: 2px solid #eee; 
           padding-left: 10px; 
        }
    .info-text { 
        font-size: 0.8rem; 
        color: #666; 
        font-weight: 600;
         word-break: break-all;
          line-height: 1.2; 
        }

    @media (min-width:769px){
         main { 
            max-width: 1400px; 
            padding: 40px; 
            margin: 0 auto; 
        }
         h1 { 
            font-size: 3rem; 
            margin-bottom:30px; 
        }
         .search-wrapper { 
            max-width: 500px; 
        }
         .mobile-only{ 
            display:none;
         }
         .list-row { 
            grid-template-columns: 25% 25% 35% auto; 
        }
    }

    @media (max-width:768px){
         body { 
            padding-bottom: 80px; 
        } 
         .controls-container {
             justify-content: space-between; 
            }
         .search-wrapper { 
            display: none;
         } 
         .btn-register, .btn-filter {
             width: 48%; 
             justify-content: center;
             }
         .list-row { 
            grid-template-columns: 1fr 1.5fr 40px;
             padding: 15px 15px; 
             gap: 10px; 
            }
         .desktop-col { 
            display: none; 
        }
    }
</style>
</head>
<body>
    
    <?php include 'adminTaskbar.php'; ?>

    <main> 
        <div class="top-row">
            <h1>User Management</h1>
        </div>
       <div class="controls-container">
            <a href="register_user.php" class="btn btn-register" style="text-decoration:none; color:inherit;">
                <i class="fa-solid fa-plus"></i> Register
            </a>
            
            <div class="search-wrapper">
        <i class="fa-solid fa-magnifying-glass" style="color: #777; margin-right: 10px;"></i>
        
        <input type="text" id="searchInput" class="search-input" placeholder="Search by name..." onkeyup="searchUsers()">
    </div>
</div>

        <div class="user-list-container">
            
            <div class="list-row list-header">
                <div class="cell-text">Name</div>
                <div class="cell-text desktop-col">Contact Number</div>
                <div class="cell-text desktop-col">Email</div>
                <div class="cell-text mobile-only info-header">Contact / Email</div> 
                <div></div> 
            </div>

            <?php
           
            $sql = "SELECT user_id, name, contact_no, email FROM user WHERE is_deleted = 0";

           
            if(isset($con)){
                $result = $con->query($sql);

                if ($result && $result->num_rows > 0){
                    while($row = $result->fetch_assoc()) {
            ?>

            <div class="list-row list-item">
                <div class="cell-text"><?php echo htmlspecialchars($row['name']); ?></div>
                
                <div class="cell-text desktop-col"><?php echo htmlspecialchars($row['contact_no']); ?></div>
                <div class="cell-text desktop-col"><?php echo htmlspecialchars($row['email']); ?></div>
                
                <div class="mobile-info-stack mobile-only">
                    <span class="info-text"><?php echo htmlspecialchars($row['contact_no']); ?></span>
                    <span class="info-text"><?php echo htmlspecialchars($row['email']); ?></span>
                </div>

                <div class="actions-cell">
                    <a href="edit_user.php?id=<?php echo $row['user_id']; ?>" class="action-btn" title="Edit">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                    
                    <a href="delete_user.php?id=<?php echo $row['user_id']; ?>" class="action-btn btn-delete-icon" title="Delete" onclick="return confirm('Are you sure you want to delete this user?');">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                </div>
            </div>

            <?php 
                    } 
                } else {
                    echo "<div style='padding:20px; text-align:center;'>No users found in database.</div>";
                }
            
            } else {
                echo "<div style='color:red; padding:20px;'>Database connection variable (\$con) is missing. Check conn.php</div>";
            }
            ?>
        </div>
        <script>
    function searchUsers() {
      
        let input = document.getElementById("searchInput");
        let filter = input.value.toUpperCase();
        let rows = document.getElementsByClassName("list-item");
        for (let i = 0; i < rows.length; i++) {
            let rowText = rows[i].textContent || rows[i].innerText;
            if (rowText.toUpperCase().indexOf(filter) > -1) {
                rows[i].style.display = ""; 
            } else {
                rows[i].style.display = "none"; 
            }
        }
    }
</script>
    </main>
</body>
</html>