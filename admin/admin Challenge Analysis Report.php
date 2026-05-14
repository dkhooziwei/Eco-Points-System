<?php
require_once "../session.php";
require_once "../conn.php";

if($_SESSION["role"] !== "Admin"){
        header("Location: ../registerLogin/login.php");
        exit();
    }
 

$sql_participants = "SELECT COUNT(*) FROM challenger";
$res_participants = $con->query($sql_participants);
$totalParticipants = $res_participants->fetch_row()[0];


$sql_challenges = "SELECT COUNT(*) FROM challenge"; 
$res_challenges = $con->query($sql_challenges);
$activeChallenges = $res_challenges->fetch_row()[0];


$sql_total_part = "SELECT COUNT(*) FROM challenge_participation";
$sql_comp_part  = "SELECT COUNT(*) FROM challenge_participation WHERE status = 'Completed'";

$totalPart = $con->query($sql_total_part)->fetch_row()[0];
$compPart  = $con->query($sql_comp_part)->fetch_row()[0];

if ($totalPart > 0) {
    $completionRate = round(($compPart / $totalPart) * 100, 1);
} else {
    $completionRate = 0;
}


$sql_points = "SELECT SUM(points) FROM point_history WHERE transaction_type = 'Earn'";
$res_points = $con->query($sql_points);
$row_points = $res_points->fetch_row();

$pointsAwarded = $row_points[0] ? number_format($row_points[0]) : "0"; 



$labels_bar = [];
$data_student = [];
$data_staff = [];

$sql_bar = "SELECT c.name, ch.group, COUNT(*) as count 
            FROM challenge_participation cp
            JOIN challenge c ON cp.challenge_id = c.challenge_id
            JOIN challenger ch ON cp.challenger_id = ch.challenger_id
            GROUP BY c.name, ch.group
            ORDER BY c.name";

$result_bar = $con->query($sql_bar);


$tempData = [];
while ($row = $result_bar->fetch_assoc()) {
    $challengeName = $row['name'];
    $group = $row['group']; 
    $count = $row['count'];

    if (!isset($tempData[$challengeName])) {
        $tempData[$challengeName] = ['Student' => 0, 'Staff' => 0];
    }
    $tempData[$challengeName][$group] = $count;
}

foreach ($tempData as $name => $counts) {
    $labels_bar[] = $name; 
    $data_student[] = $counts['Student'];
    $data_staff[] = $counts['Staff'];
}



$labels_top = [];
$data_top = [];

$sql_top = "SELECT c.name, COUNT(*) as count 
            FROM challenge_participation cp
            JOIN challenge c ON cp.challenge_id = c.challenge_id
            GROUP BY c.name
            ORDER BY count DESC
            LIMIT 5";

$result_top = $con->query($sql_top);
while($row = $result_top->fetch_assoc()){
    $labels_top[] = $row['name'];
    $data_top[] = $row['count'];
}



$data_status = ['Completed' => 0, 'Joined' => 0]; 

$sql_status = "SELECT status, COUNT(*) as count FROM challenge_participation GROUP BY status";
$result_status = $con->query($sql_status);
while($row = $result_status->fetch_assoc()){
    $status = $row['status'];
    if($status == 'Completed') $data_status['Completed'] = $row['count'];
    if($status == 'Joined')    $data_status['Joined'] = $row['count'];
}
?>

<!DOCTYPE html> 
<html lang='en'> 
<head> 
    <meta charset='UTF-8'> 
    <meta name='viewport' content='width=device-width, initial-scale=1.0'> 
    <title>Admin-Challenge Analysis Report</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
            max-width: 100%;
            display: flex; 
            flex-direction: column; 
            flex-grow: 1;
            padding: 30px;
        }

        h1 { 
            font-weight: 700;
            color: #333;
            font-size: 2rem;
            margin-bottom: 10px;
            }

        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px; 
            width: 100%;
        } 


        .stats-grid {
            display: grid;
            gap: 20px;
             grid-template-columns: 1fr 1fr;
        }

        .stat-card {
            background-color: white;
             padding: 25px; 
             border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            display: flex; 
            flex-direction: column;
             justify-content: space-between;
            height: 120px;
        }

        .stat-title { 
            font-size: 0.9rem; 
            font-weight: 700; 
            color: #666; 
        }

        .stat-value { 
            font-size: 1.8rem; 
            font-weight: 800; 
            color: #333; 
            margin-top: 10px; 
        }

        .stat-graph-placeholder { 
             height: 12px; 
             width: 100%; 
             background-color: #e0e0e0;
              border-radius: 6px;
               margin-top: 15px;
        }


        .dashboard-container {
            display: flex;
             gap: 40px; 
             flex-direction: column; 
             margin-top: 40px; 
        }


        .card-box {
            background-color: white; 
            border-radius: 20px; 
            padding: 30px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.05); 
            width: 100%; 
            min-width: 0;
        }

        .card-header {
            background-color: #a6e58fff;
             padding: 10px 20px; 
             border-radius: 8px;
            display: inline-block; 
            font-weight: 700; 
            font-size: 1rem;
            margin-bottom: 30px; 
            color: #333;
        }

        .chart-wrapper {
            position: relative; 
            height: 350px;
             width: 100%;
        }

        .pie-chart-wrapper {
            display: flex; 
            justify-content: center; 
            height: 350px; 
            margin-top: 50px; 
        }
            

        /*DESKTOP VIEW  */
        @media (min-width: 769px) {

            .dekstop-only { 
                display: block;
             }
            
            main { 
                max-width: 1400px; 
                margin: 0 auto; 
                padding: 50px 40px; 
            }

            h1 { 
                font-size: 3rem; 
                margin-bottom: 20px; 
            }
           
            .header-right {
                 gap: 20px;
                 }

            .stats-grid { 
                gap: 40px;
                 grid-template-columns: repeat(4, 1fr);
                 }
            
            .stat-card {
                 height: 160px; 
                 padding: 30px;
                 }
            
            .stat-value { 
                font-size: 2.5rem; 
            }

            .dashboard-container {
                 flex-direction: row; 
                 align-items: flex-start;
                  gap: 40px; 
                }

            .participation-card {
                 flex: 2; 
                }

            .top-challenges-card { 
               flex: 2; 
               display: flex;
               flex-direction: column;
            }

             .pie-chart-wrapper { 
                display: flex; 
                margin-top: 20px;
                height: 300px;
        }
}

        /* MOBILE VIEW  */

        @media (max-width: 768px) {

             body { 
                padding-bottom: 80px;
             } 

             .desktop-only { 
                display: none !important; 
            } 

            .stat-value{
                font-size:1rem;
            }
        }

    </style>
</head>
<body>
         <?php include 'adminTaskbar.php'; ?>

    <main> 
        <div class="top-row">
            <h1>Challenge Analytics</h1>
        </div>

      <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title"> Total Participants</div>
                <div class="stat-value"><?php echo $totalParticipants; ?></div>
                <div class="stat-graph-placeholder" style="width: 70%; background-color: #80B155;"></div>
            </div>
            <div class="stat-card">
                <div class="stat-title"> Active Challenges</div>
                <div class="stat-value"><?php echo $activeChallenges; ?></div>
                <div class="stat-graph-placeholder" style="width: 50%; background-color: #2e8836ff;"></div>
            </div>
            <div class="stat-card">
                <div class="stat-title"> Completion Rate</div>
                <div class="stat-value"><?php echo $completionRate; ?>%</div>
                <div class="stat-graph-placeholder" style="width: <?php echo $completionRate; ?>%; background-color: #20a744ff;"></div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Points Awarded</div>
                <div class="stat-value"><?php echo $pointsAwarded; ?></div>
                <div class="stat-graph-placeholder" style="width: 90%; background-color: #E76F51;"></div>
            </div>
        </div>

         <div class="dashboard-container">
            
            <div class="card-box participation-card">
                <div class="card-header">Student vs Staff Participation</div>
                <div class="chart-wrapper">
                    <canvas id="participationChart"></canvas>
                </div>
            </div>

            <div class="card-box top-challenges-card">
                <div class="card-header">Top Performing Challenges</div>
                
                <div class="chart-wrapper">
                    <canvas id="topChallengesChart"></canvas>
                </div>

                <div class="pie-chart-wrapper">
                    <canvas id="mobilePieChart"></canvas>
                </div>
            </div>

        </div>
    </main>

    <script>
     
        const labelsBar = <?php echo json_encode($labels_bar); ?>;
        const dataStudent = <?php echo json_encode($data_student); ?>;
        const dataStaff = <?php echo json_encode($data_staff); ?>;

        const ctxPart = document.getElementById('participationChart').getContext('2d');
        new Chart(ctxPart,{
            type: 'bar', 
            data: {
                labels: labelsBar,
                datasets: [
                    { label: 'Students', data: dataStudent, backgroundColor: '#80B155', barPercentage: 0.5 },
                    { label: 'Staff', data: dataStaff, backgroundColor: '#264653', barPercentage: 0.5 }
                ]
            },
            options: {
                indexAxis: 'y', 
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top', align: 'start' } },
                scales: { 
                    x: { grid: { display: false }, ticks: { stepSize: 1 } },
                    y: { grid: { display: false } } 
                }
            }
        });

       
        const labelsTop = <?php echo json_encode($labels_top); ?>;
        const dataTop = <?php echo json_encode($data_top); ?>;

        const ctxTop = document.getElementById('topChallengesChart').getContext('2d');
        new Chart(ctxTop, {
            type: 'bar',
            data: {
                labels: labelsTop,
                datasets: [{ 
                    label: 'Participants', 
                    data: dataTop, 
                    backgroundColor: '#94ee5cff', 
                    borderRadius: 5, 
                    barThickness: 20  
                }]
            },
            options: {
                indexAxis: 'y', 
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { 
                    x: { display: false, ticks: { stepSize: 1 } }, 
                    y: { 
                        ticks: {
                            font: {
                                size: 10, 
                                family: "'Nunito Sans', sans-serif" 
                            },
                            autoSkip: false, 
                            maxRotation: 0
                        },
                        grid: { 
                            display: true, 
                            drawBorder: true 
                        } 
                    } 
                }
            }
        });
  
        const dataPie = [<?php echo $data_status['Completed']; ?>, <?php echo $data_status['Joined']; ?>];
        
        const ctxPie = document.getElementById('mobilePieChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: ['Completed', 'Active (Joined)'],
                datasets: [{ 
                    data: dataPie, 
                    backgroundColor: ['#39eb5fff', '#4dc118ff'], 
                    borderWidth: 0 
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { position: 'bottom' } 
                }
            }
        });
    </script>
</body>
</html>