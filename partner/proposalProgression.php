<?php 
require("../session.php");
include("../conn.php");

if($_SESSION["role"] !== "Partner"){
        header("Location: ../registerLogin/login.php");
        exit();
    }
    
$partner_id = $_SESSION['mySession'];

/*Fetch Challenge Progression*/
$progression_query ="SELECT 
    c.challenge_id,
    c.name,
    c.start_date,
    c.end_date,
    COUNT(cp.challenger_id) AS total_participation,
    IFNULL(AVG(cp.current_progress), 0) AS overall_progression,
    SUM(CASE WHEN cp.status = 'Joined' THEN 1 ELSE 0 END) AS ongoing_count,
    SUM(CASE WHEN cp.status = 'Completed' THEN 1 ELSE 0 END) AS completed_count
    FROM challenge c
    LEFT JOIN challenge_participation cp ON c.challenge_id = cp.challenge_id 
    WHERE c.partner_id = '$partner_id' AND c.is_deleted = 0
    GROUP BY c.challenge_id, c.name, c.start_date, c.end_date";

$progressions = mysqli_query($con, $progression_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner - Challenge Progression Analysis</title>
    <link href="../commonStyle.css" rel="stylesheet">
    <style>
        :root {
            --light-lime: #eaef9d;
            --lime: #c1d95c;
            --soft-green: #80b155;
            --mid-green: #498428;
            --dark-green: #336a29;
        }

        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .container{
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;   
        }

        
        /*Section2*/
        .page-title{
            font-size: 28px;
            color: var(--dark-green);
            margin-bottom: 20px;
        }

        .progression-grid{
            display: column;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;

        }

        .progression-card{
            background: white;
            border-radius: 25px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            gap: 30px;  
        }

        .progression-card:hover{
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }

        .progress-details-left{
            display: flex; 
            flex-direction: column;
            flex: 2;
        }

        .progress-details-right{
            flex: 0.8;
            display: flex; 
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-left: 1px dashed #e0e0e0;
            padding-left: 20px;
        }

        .progress-details h3{
            font-size: 1.5em;
            font-weight: 700;
            letter-spacing: 0.3px;
            margin-bottom: 6px;
            color: var(--dark-green);
        }

        .progress-details .date{
            font-size: 0.9em;
            font-weight: 500;
            opacity: 0.7;
            color: var(--mid-green);
            margin-bottom: 15px;
            margin-top: 6px;
            display: inline-flex;
            
        }

        /*Total, Ongoing & Completed Stats*/
        .participation-stats{
            display: flex;
            gap: 20px;
            margin-bottom: 10px;
        }

        .stats-item{
            display: flex;
            flex: 1;
            flex-direction: column;
            justify-content: space-between;
            padding: 22px 18px;
            border-radius: 10px;
            min-height: 100px;
            text-align: center;
            justify-content: center;
            font-size: 0.95em;
            color: var(--dark-green);
            background-image: linear-gradient(to bottom right, var(--light-lime), var(--soft-green));
        }


        .stats-item span:first-child{
            font-size: 0.75rem;
            font-weight: bold;
            color: var(--mid-green);
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .stats-item span:last-child{
            font-size: 1.4rem;
            font-weight: bold;
            color: var(--dark-green);
            margin-top: 10px;
        }

        /*Circular Progress*/
        .circular-progress{
            --size: 150px;
            --thickness: 12px;
            width: var(--size);
            height: var(--size);
            border-radius: 50%;
            background: conic-gradient(var(--soft-green) calc(var(--progress) * 1%), var(--light-lime) 0%);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--dark-green);
            position: relative; 
        }

        .circular-progress::before{
            content: '';
            position: absolute;
            width: calc(var(--size) - var(--thickness) * 2);
            height: calc(var(--size) - var(--thickness) * 2);
            background: white;
            border-radius: 50%;
        }

        .circular-progress span{
            position: relative;
            z-index: 1;
        }

        .circle-label{
            font-size: 1rem;
            color: var(--mid-green);
            font-weight: bold;
            margin-top: 5px;
        }


        /*mobile view*/
        @media (max-width: 600px) {
            .progression-card {
                flex-direction: column; 
                text-align: center;
                padding: 20px;
            }

            .progress-details-left{
                width: 80%;
                height: 80%;
                padding: 8px 5px;
                align-items: center;

            }

            .progress-details-right {
                border-left: none; 
                border-top: 1px dashed #bfbfbfff; 
                padding-left: 0;
                padding-top: 20px;
                width: 100%;
            }

            .participation-stats {
                flex-direction: column; 
                gap: 10px;
                justify-content: space-between;
                display: flex;
                width: 100%;
            }

            .stats-item {
                min-width: 0;
                padding: 8px 4px;
                flex: 1;
            }

            .page-title h2 {
                font-size: 22px;
                text-align: center;
            }

            .circular-progress {
                --size: 180px; 
            }
        }

        /*tablet view */
        @media (max-width: 1200px) {
            .container {
                max-width: 95%;
                padding: 10px;
            }
            
            .progression-card {
                padding: 20px;
                gap: 20px;
            }

            .stats-item {
                padding: 13px 10px; 
            }
        }

    </style>

</head>
<body>

<?php 
include("partnerTaskbar.php")
?>

<main>
    <div class="container">
        <section>
            <div class="page-title">
                <h2>Challenge Progression Analysis</h2>
            </div>
        </section>

        <section>
            <div class="progression-grid">
                <?php 
                while($progression_data = mysqli_fetch_assoc($progressions)):     
                    $avg_progress = round($progression_data['overall_progression']); //Average Progression of all participants
                    $total = $progression_data['total_participation'];

                    if ($total === 0) {
                        $avg_progress = 0;
                        $progress_label = "No Participants";

                    } elseif ($avg_progress == 0) {
                        $progress_label = "No Progress";

                    } elseif ($avg_progress < 50) {
                        $progress_label = "Needs Improvement";

                    } elseif ($avg_progress < 80) {
                        $progress_label = "On Track";

                    } else {
                        $progress_label = "Excellent";
                    }
                ?>                

                <div class="progression-card">
                    <div class="progress-details-left">
                        <div class="progress-details">
                            <h3><?php echo htmlspecialchars($progression_data['name']); ?></h3>
                            <span class="date">📅<?php echo $progression_data['start_date'] ?? 'N/A'; ?> to <?php echo $progression_data['end_date'] ?? 'N/A'; ?></span>
                        </div>
                        
                        <div class="participation-stats">
                            <div class="stats-item">
                                <span>Total Participation Count</span>
                                <span><?php echo htmlspecialchars($progression_data['total_participation']) ?? 0; ?></span>
                            </div>

                            <div class="stats-item">
                                <span>Ongoing Participants</span>
                                <span><?php echo htmlspecialchars($progression_data['ongoing_count']) ?? 0; ?></span>
                            </div>

                            <div class="stats-item">
                                <span>Completed Participants</span>
                                <span><?php echo htmlspecialchars($progression_data['completed_count']) ?? 0; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="progress-details-right">
                        <div class="circular-progress" style="--progress:<?php echo htmlspecialchars($avg_progress); ?>;">
                            <span><?php echo htmlspecialchars($avg_progress); ?>%</span>
                        </div>
                        <p class="circle-label">Overall Progress: <?php echo htmlspecialchars($progress_label); ?></p>
                        
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </section>
    </div>

</main>
</body>
</html>