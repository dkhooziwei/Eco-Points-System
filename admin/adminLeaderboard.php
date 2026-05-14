<?php 
    require_once "../session.php"; 
    require_once ("../conn.php"); 

    if($_SESSION["role"]!== "Admin"){
        header("Location: ../registerLogin/login.php");
        exit();
    }

    // Get the selected tab from the URL
    // If no tab is selected then default to Student
    if (isset($_GET['group'])) {
        $selected_group = $_GET['group'];
    } else {
        $selected_group = 'Student';
    }

    $sql = "SELECT u.name, c.total_points
            FROM challenger c
            JOIN user u ON c.challenger_id = u.user_id";

    // Check if the selected tab is NOT Community
    // Community should show all users, so no filtering is applied
    if ($selected_group != 'Community') {

    // Sanitize input to prevent SQL Injection
    $safe_group = mysqli_real_escape_string($con, $selected_group);

        // Add WHERE condition for Student or Staff
        $sql .= " WHERE c.group = '$safe_group'";
    }

    //  Sort the results by total points (highest first)
    $sql .= " ORDER BY c.total_points DESC";

    // Execute the SQL query
    $result = mysqli_query($con, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Leaderboard</title>
    <link href="../commonStyle.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* CSS STYLES - Green Eco Theme */        
        .content-area {
            flex: 1;
            padding: 20px;
            padding-top: 15px;
        }

        .page-title {
            font-size: 26px;
            font-weight: bold;
            color: #2e7d32;
            margin-bottom: 20px;
            text-align: left;
        }

        /* Tabs Styling */
        .tabs-container {
            display: flex;
            background-color: white;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.15);
            overflow: hidden;
            border: 1px solid #e8f5e9;
        }

        /* The link fills the whole tab area */
        .tab {
            flex: 1;
            background-color: #fafafa;
            border-right: 1px solid #e8f5e9;
            cursor: pointer;
            transition: all 0.3s;
        }

        .tab a {
            display: block;
            padding: 15px 10px;
            text-align: center;
            text-decoration: none;
            color: #666;
            font-weight: 500;
            font-size: 14px;
            width: 100%;
            height: 100%;
            transition: all 0.3s;
        }

        .tab:hover {
            background-color: #f1f8e9;
        }

        .tab:hover a {
            color: #4caf50;
        }

        /* Active State */
        .tab.active {
            background-color: white;
            border-bottom: 3px solid #4caf50;
        }

        .tab.active a {
            color: #2e7d32;
            font-weight: bold;
        }

        /* Table Container */
        .table-container {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.15);
            margin-top: 10px;
            border: 1px solid #e8f5e9;
        }

        .table-title {
            font-size: 18px;
            font-weight: bold;
            color: #2e7d32;
            margin-bottom: 15px;
            text-align: left;
            padding-bottom: 12px;
            border-bottom: 2px solid #e8f5e9;
        }

        /* Leaderboard Table */
        .leaderboard-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .leaderboard-table th {
            background-color: #f1f8e9;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            color: #2e7d32;
            border-bottom: 2px solid #c8e6c9;
            font-size: 14px;
        }

        .leaderboard-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #e8f5e9;
            color: #555;
            font-size: 14px;
        }

        /* Rank column styling */
        .leaderboard-table td:first-child {
            font-weight: 600;
            color: #7b7b7b;
        }

        /* Points column styling */
        .leaderboard-table td:last-child {
            font-weight: 600;
            color: #4caf50;
        }

        .leaderboard-table tr:hover {
            background-color: #f9fdf9;
        }

        /* Top 3 ranks special styling */
        .leaderboard-table tbody tr:nth-child(1) td:first-child {
            color: #ffd700;
        }
        .leaderboard-table tbody tr:nth-child(2) td:first-child {
            color: #c0c0c0;
        }
        .leaderboard-table tbody tr:nth-child(3) td:first-child {
            color: #cd7f32;
        }

        /* No data message */
        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .content-area {
                padding: 15px;
            }

            .page-title {
                font-size: 22px;
            }

            .tabs-container {
                border-radius: 10px;
            }

            .tab a {
                padding: 12px 8px;
                font-size: 13px;
            }

            .table-container {
                padding: 15px;
                border-radius: 12px;
            }

            .leaderboard-table th,
            .leaderboard-table td {
                padding: 12px 8px;
                font-size: 13px;
            }
        }

        @media (min-width: 1200px) {
            .content-area {
                max-width: 1200px;
                margin: 0 auto;
                padding: 30px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        
        <?php include "adminTaskbar.php"; ?>

        <div class="content-area">
            <div class="page-title">Leaderboard</div>

            <div class="tabs-container">
                <div class="tab <?php if($selected_group == 'Student') echo 'active'; ?>">
                    <a href="?group=Student">Students</a>
                </div>
                <div class="tab <?php if($selected_group == 'Community') echo 'active'; ?>">
                    <a href="?group=Community">Community</a>
                </div>
                <div class="tab <?php if($selected_group == 'Staff') echo 'active'; ?>">
                    <a href="?group=Staff">Staff</a>
                </div>
            </div>

            <div class="table-container">
                <div class="table-title">Category: <?php echo htmlspecialchars($selected_group); ?></div>
                <table class="leaderboard-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Name</th>
                            <th>Points</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php 
                        // DISPLAY DATA FROM DATABASE
                        if ($result && mysqli_num_rows($result) > 0) {
                            $rank = 1;
                            // Loop through every row found
                            while($row = mysqli_fetch_assoc($result)) {
                        ?>
                            <tr>
                                <td>#<?php echo $rank; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['total_points']); ?></td>
                            </tr>
                        <?php 
                                $rank++; // Increase rank number
                            }
                        } else {
                            echo "<tr><td colspan='3' class='no-data'>No data found for this category.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>