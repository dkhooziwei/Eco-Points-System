<?php
    require_once "../session.php";
    require_once "../conn.php";

    // get the partner id from session
    $partner_id = $_SESSION['mySession'] ?? null;

    // Get challenges with participant stats, only where start_date and end_date are not null
    $query = "SELECT 
                c.challenge_id,
                c.name,
                c.partner_id,
                c.start_date,
                c.end_date,
                COUNT(cp.participation_id) as total_participants,
                SUM(CASE WHEN cp.status = 'Completed' THEN 1 ELSE 0 END) as completed_count
                FROM challenge c
                LEFT JOIN challenge_participation cp ON c.challenge_id = cp.challenge_id
                WHERE c.start_date IS NOT NULL 
                AND c.end_date IS NOT NULL 
                AND c.partner_id = '$partner_id'
                GROUP BY c.challenge_id, c.name, c.partner_id, c.start_date, c.end_date
                ORDER BY c.start_date DESC LIMIT 2";

    $result = mysqli_query($con, $query);

    $challenges = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $total = (int)$row['total_participants'];
        $completed = (int)$row['completed_count'];

        // Calculate completion percentage (avoid division by zero)
        $percentage = ($total > 0) ? round(($completed / $total) * 100) : 0;

        $challenges[] = [
            'id' => $row['challenge_id'],
            'name' => $row['name'],
            'partner_id' => $row['partner_id'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date'],
            'participants' => $total,
            'percentage' => $percentage
        ];
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner - HomePage</title>
    <link href="../commonStyle.css" rel="stylesheet">
    <style>
    h2 {
        color: #2e7d32;
        margin-bottom: 30px;
        font-size: 28px;
    }

    .challenges-and-showBtn {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 20px;
    }

    /* Show More button - green theme */
    .show-more-btn {
        padding: 12px 40px;
        background: transparent;
        color: #4CAF50;
        border: 2px solid #4CAF50;
        border-radius: 25px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .show-more-btn:hover {
        background: #4CAF50;
        color: white;
    }

    /* Charts Container - handles 1 or 2 charts */
    .charts-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 30px;
        margin: 20px;
        padding: 30px;
        background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
        border-radius: 20px;
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.2);
    }

    /* Challenge Card */
    .challenge-card {
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid #c8e6c9;
        transition: 0.3s ease;
        width: 100%;
        max-width: 450px;
        flex: 1 1 400px;
    }

    .challenge-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(76, 175, 80, 0.25);
    }

    /* If only 1 card, don't let it stretch too wide */
    .challenge-card:only-child {
        flex: 0 1 450px;
    }

    .challenge-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e8f5e9;
    }

    .challenge-title {
        font-size: 20px;
        font-weight: 600;
        color: #2e7d32;
    }

    .participant-count {
        background: #e8f5e9;
        color: #388e3c;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 14px;
    }

    .chart-wrapper {
        position: relative;
        height: 220px;
        margin: 20px 0;
    }

    canvas {
        width: 100% !important;
        height: 100% !important;
    }

    .progress-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #e8f5e9;
    }

    .progress-text {
        font-size: 16px;
        font-weight: 600;
        color: #555;
    }

    .progress-percentage {
        font-size: 28px;
        font-weight: 700;
        color: #4CAF50;
    }

    /* Buttons */
    .buttons-container {
        display: flex;
        gap: 20px;
        margin: 30px 20px;
        justify-content: center;
    }

    .cta-button {
        padding: 14px 45px;
        background: linear-gradient(135deg, #4CAF50 0%, #388e3c 100%);
        color: white;
        border: none;
        border-radius: 25px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s;
        text-decoration: none;
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
    }

    .cta-button:hover {
        background: linear-gradient(135deg, #388e3c 0%, #2e7d32 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
    }

    .no-challenges {
        text-align: center;
        padding: 50px;
        color: #666;
        font-size: 18px;
        width: 100%;
    }

    /* Mobile */
    @media (max-width: 600px) {
        .challenges-and-showBtn {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }

        h2 {
            margin-bottom: 10px;
            font-size: 24px;
        }

        .charts-container {
            margin: 10px;
            padding: 15px;
        }

        .challenge-card {
            padding: 20px;
            max-width: 100%;
            flex: 1 1 100%;
        }

        .challenge-header {
            flex-direction: column;
            gap: 10px;
            text-align: center;
        }

        .challenge-title {
            font-size: 18px;
        }

        .chart-wrapper {
            height: 200px;
        }

        .progress-info {
            flex-direction: column;
            gap: 10px;
            text-align: center;
        }

        .buttons-container {
            flex-direction: column;
            align-items: center;
            padding: 0 20px;
        }

        .cta-button {
            width: 100%;
            text-align: center;
        }
    }

    /* Tablet */
    @media (min-width: 601px) and (max-width: 1199px) {
        .challenge-card {
            max-width: 500px;
            flex: 1 1 100%;
        }
    }

    /* Desktop - Side by side for 2 cards */
    @media (min-width: 1200px) {
        .challenge-card {
            flex: 1 1 calc(50% - 30px);
            max-width: calc(50% - 15px);
        }

        .challenge-card:only-child {
            flex: 0 1 450px;
            max-width: 450px;
        }
    }
</style>
</head>
<body>
    <?php include "partnerTaskbar.php"; ?>
    <div class="challenges-and-showBtn">
            <h2>Challenge Progression</h2>
            <div class="show-more">
                <a href="proposalProgression.php"><button class="show-more-btn">Show More</button></a>
            </div>
    </div>

        <div class="charts-container">
            <?php if (empty($challenges)): ?>
                <div class="no-challenges">No challenges available.</div>
            <?php else: ?>
                <?php foreach ($challenges as $index => $challenge): ?>
                    <div class="challenge-card">
                        <div class="challenge-header">
                            <div class="challenge-title"><?php echo htmlspecialchars($challenge['name']); ?></div>
                            <div class="participant-count"><?php echo $challenge['participants']; ?> Participants</div>
                        </div>

                        <div class="chart-wrapper">
                            <canvas id="chart<?php echo $index; ?>"></canvas>
                        </div>

                        <div class="progress-info">
                            <div class="progress-text">Challenge Progression</div>
                            <div class="progress-percentage"><?php echo $challenge['percentage']; ?>%</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Buttons Section -->
        <div class="buttons-container">
            <a href="submitProposal.php"><button class="cta-button">Submit Proposal</button></a>
            <a href="viewprop.php"><button class="cta-button" href="viewprop.php">My Proposals</button></a>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Function to create a doughnut chart
    function createDoughnutChart(canvasId, progressPercentage, label) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        const remainingPercentage = 100 - progressPercentage;

        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Remaining'],
                datasets: [{
                    data: [progressPercentage, remainingPercentage],
                    backgroundColor: [
                        '#4CAF50',
                        '#E0E0E0'
                    ],
                    borderColor: [
                        '#388E3C',
                        '#BDBDBD'
                    ],
                    borderWidth: 2,
                    borderRadius: 10,
                    spacing: 5,
                    cutout: '75%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                size: 14
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.parsed}%`;
                            }
                        }
                    }
                }
            },
            plugins: [{
                id: 'doughnutlabel',
                beforeDraw: function(chart) {
                    const { ctx, chartArea } = chart;
                    
                    if (!chartArea) return;
                    
                    // Use chartArea to get the actual chart center (excludes legend)
                    const centerX = (chartArea.left + chartArea.right) / 2;
                    const centerY = (chartArea.top + chartArea.bottom) / 2;

                    ctx.save();
                    
                    // Draw percentage
                    ctx.font = "bold 28px 'Arial', sans-serif";
                    ctx.fillStyle = "#2e7d32";
                    ctx.textAlign = "center";
                    ctx.textBaseline = "middle";
                    ctx.fillText(progressPercentage + '%', centerX, centerY - 10);

                    // Draw label
                    ctx.font = "14px 'Arial', sans-serif";
                    ctx.fillStyle = "#666";
                    ctx.fillText(label, centerX, centerY + 15);

                    ctx.restore();
                }
            }]
        });
    }

    // Create charts dynamically from PHP data
    const challengeData = <?php echo json_encode($challenges); ?>;

    challengeData.forEach((challenge, index) => {
        createDoughnutChart('chart' + index, challenge.percentage, 'Completed');
    });
</script>

</body>
</html>