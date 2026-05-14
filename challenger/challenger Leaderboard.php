<?php
require_once "../session.php";
require_once "../conn.php";

if($_SESSION["role"] !== "Challenger"){
        header("Location: ../registerLogin/login.php");
        exit();
    }

if (isset($con) && !isset($con)) { $con = $con; } 
elseif (isset($con) && !isset($con)) { $con = $con; }

function getTopChallengers($con, $groupName) {
    
    $sql = "SELECT u.name, SUM(ph.points) as total_score 
            FROM challenger c
            JOIN user u ON c.challenger_id = u.user_id
            JOIN point_history ph ON c.challenger_id = ph.challenger_id
            WHERE c.group = ? 
            GROUP BY c.challenger_id 
            ORDER BY total_score DESC 
            LIMIT 5";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $groupName);
    $stmt->execute();
    return $stmt->get_result();
}

$topStudents = getTopChallengers($con, 'Student');


$topCommunity = getTopChallengers($con, 'Staff');
?>

<!DOCTYPE html> 
<html lang='en'> 
<head> 
    <meta charset='UTF-8'> 
    <meta name='viewport' content='width=device-width, initial-scale=1.0'> 
    <title>Challenger-Leaderboard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    
    <link href="commonStyle.css" rel="stylesheet">

<style>

    * {
        margin:0;
        padding:0;
        box-sizing:border-box;
        font-family:Arial,sans-serif;
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

    .banner-container {
        width:100%;
        max-width:800px;
        background-color:#C1D95C; 
        border-radius:15px;
        padding: 30px 20px; 
        text-align:center; 
        margin-bottom:20px;
    }

    h1 { 
        font-weight: 600; 
        color: #333; 
        font-size: 1.8rem; 
    }

    
    .tabs-container {
        display:flex;
        justify-content:center;
        gap:10px;
        width:100%;
        max-width:600px; 
        margin-bottom:20px;
    }

    .tab-btn {
        flex:1; padding:10px; 
        border-radius:15px; 
        border:0;
        font-size:1rem; 
        font-weight:700; 
        cursor:pointer; 
        text-align: center;
        transition: all 0.2s ease;
    }

 
    .tab-active {
        background-color:#80B155; 
        color:black;
        border: 2px solid #999; 
    }

    .tab-inactive {
        background-color:#498428; 
        color:white;
        border: 2px solid transparent;
    }

    .ranking-container {
        background-color: #EAEF9D; 
        width:100%;
         max-width:800px; 
         border-radius:20px; 
         padding:20px;
        display:flex; 
        flex-direction:column;
         gap:15px;
    }

    .list-labels {
        display:flex; 
        justify-content:space-between; 
        padding: 0 20px;
        font-weight:700; 
        color:#333; 
        margin-bottom:5px;
    }

    .leaderboard-list {
        display: flex; 
        flex-direction: column;
         gap: 15px; 
         width: 100%;
    }

    .rank-card {
        background-color:white; 
        border-radius:30px; 
        padding:15px 30px;
        display:flex; 
        justify-content:center;
         align-items:center;
        font-weight:800; 
        font-size:1.1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .d-none {
        display: none !important;
    }


   /*Dekstop View */
    @media (min-width: 769px) {

        .mobile-footer {
             display: none; 
            }

        main{ 
            padding: 40px; 
            align-items: center;
         }
        h1 { 
            font-size: 3rem; 
        }

        .banner-container,.tabs-container,.ranking-container{
            width:100%;
             max-width:1000px; 
             margin-left:auto;
              margin-right:auto;
        }
    }

    /* Mobile View */
    @media (max-width: 768px) {

        .desktop-only { 
            display: none !important; 
        } 
        h1{ 
            font-size: 1.5rem; 
        }
    }


</style>
</head>

<body>
    <?php include 'challengerTaskbar.php'; ?>
    <main> 
        <div class="banner-container">
            <h1>Leaderboards</h1>
        </div>
        
       <div class="tabs-container">
            <button id="btn-student" type="button" class="tab-btn tab-active" onclick="showLeaderboard('student')">Student</button>
            <button id="btn-community" type="button" class="tab-btn tab-inactive" onclick="showLeaderboard('community')">Community</button>
        </div>

        <div class="ranking-container">
            <div class="list-labels">
                <span>Name</span>
                <span>Rank</span>
            </div>

            <div id="list-student" class="leaderboard-list">
                <?php 
                if ($topStudents->num_rows > 0) {
                    $rank = 1;
                    while($row = $topStudents->fetch_assoc()) {
                ?>
                    <div class="rank-card">
                        <span><?php echo htmlspecialchars($row['name']); ?></span>
                        <div>
                            <span class="rank-points"><?php echo $row['total_score']; ?> pts</span>
                            <span>#<?php echo $rank++; ?></span>
                        </div>
                    </div>
                <?php 
                    }
                } else {
                    echo "<div style='text-align:center; padding:20px;'>No students found yet.</div>";
                }
                ?>
            </div>

            <div id="list-community" class="leaderboard-list d-none">
                <?php 
                if ($topCommunity->num_rows > 0) {
                    $rank = 1;
                    while($row = $topCommunity->fetch_assoc()) {
                ?>
                    <div class="rank-card">
                        <span><?php echo htmlspecialchars($row['name']); ?></span>
                        <div>
                            <span class="rank-points"><?php echo $row['total_score']; ?> pts</span>
                            <span>#<?php echo $rank++; ?></span>
                        </div>
                    </div>
                <?php 
                    }
                } else {
                    echo "<div style='text-align:center; padding:20px;'>No community members found yet.</div>";
                }
                ?>
            </div>
        </div>
    </main> 

    <footer class="mobile-footer">
        <a href="#" class="footer-item"><div class="footer-icon"></div></a>
        <a href="#" class="footer-item"><div class="footer-icon"></div></a>
        <a href="#" class="footer-item"><div class="footer-icon"></div></a>
    </footer>

    <script>
        function showLeaderboard(type) {
            const listStudent = document.getElementById('list-student');
            const listCommunity = document.getElementById('list-community');
            const btnStudent = document.getElementById('btn-student');
            const btnCommunity = document.getElementById('btn-community');

            if (type === 'student') {
                listStudent.classList.remove('d-none');
                listCommunity.classList.add('d-none');
                btnStudent.className = 'tab-btn tab-active';
                btnCommunity.className = 'tab-btn tab-inactive';

            } else if (type === 'community') {
                listCommunity.classList.remove('d-none');
                listStudent.classList.add('d-none');
                btnCommunity.className = 'tab-btn tab-active';
                btnStudent.className = 'tab-btn tab-inactive';
            }
        }
    </script>

</body>
</html>