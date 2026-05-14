<?php

    include('../conn.php');
    include('../session.php');

    if($_SESSION["role"] !== "Challenger"){
        header("Location: ../registerLogin/login.php");
        exit();
    }

    $challenger_id = $_SESSION['mySession'];

    //total points
    $sql_points = "SELECT total_points FROM challenger
    WHERE challenger_id = '$challenger_id'";

    $result_points = mysqli_query($con, $sql_points);
    if (!$result_points) {
        die("SQL Error: " . mysqli_error($con));
    }

    $row_points = mysqli_fetch_assoc($result_points);

    $total_points = (int)$row_points['total_points'];

    //history
    $history_records = [];

    $sql = "SELECT * FROM point_history 
    WHERE challenger_id = '$challenger_id'
    ORDER BY date DESC, time DESC";
    $result = mysqli_query($con, $sql);

    if (!$result) {
        die("SQL Error: " . mysqli_error($con));
    }

    while ($row = mysqli_fetch_assoc($result)) {
        
        $history_records[] = $row;
    }


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Points History</title>
    <link href="../commonStyle.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <style>
        :root {
            --soft-yellow: #eaef9d;
            --bg-color: #FFFDF1;
            --text-dark: #1f2937;
            --text-gray: #6b7280;
            --text-light: #9ca3af;
            --border-color: #e5e7eb;
            --spend: #ef4444;
            --earn-bg: #dcfce7;
            --earn-text: #166534;
            --quiz-bg: #dbeafe;
            --challenge-bg: #fef9c3;
            --challenge-text: #854d0e;
            --reward-bg: #ffedd5;
            --reward-text: #9a3412;
            --light-lime: #eaef9d;
            --soft-green: #80b155;
            --dark-green: #336a29;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        .navbar{
            background-color: lightblue;
            height: 50px;
        }

        main {
            flex-grow: 1;
            max-width: 1024px;
            margin: 0 auto;
            width: 100%;
            padding: 32px 20px;
        }

        .page_header{
            margin-bottom: 32px;
        }

        .page_title{
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--dark-green);
        }
        
        .page_subtitle {
            margin-top: 8px;
            color: var(--text-gray);
        }

        .points-dashboard {
            background: linear-gradient(135deg, var(--dark-green), var(--soft-green));
            color: white;
            border-radius: 24px;
            margin: 40px auto;
            padding: 40px;
            text-align: center;
            max-width: 800px;
            box-shadow: 0 20px 40px rgba(51, 106, 41, 0.2);
            position: relative;
            overflow: hidden;
            
        }

        .points-dashboard::after {
            content: '♻️';
            position: absolute;
            right: -20px;
            bottom: -20px;
            font-size: 150px;
            opacity: 0.1;
            
        }

        .point-label { 
            font-size: 1rem; 
            text-transform: uppercase; 
            letter-spacing: 2px; 
            opacity: 0.9; 
        }
        
        .point-display {
            font-size: 4rem;
            font-weight: 800;
            margin: 10px 0;
            text-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .btn-history {
            background: white;
            color: var(--dark-green);
            border: none;
            padding: 12px 35px;
            border-radius: 50px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            display: inline-block;
            text-decoration: none;
        }

        .btn-history:hover {
            background: var(--white);
            transform: scale(1.05);
        }

        .history_header{
            margin-bottom: 24px;
        }

        .header_title{
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-dark-green)
        }

        .history_records{
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .history_list{
            list-style: none;
        }

        .history_item{
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background-color 0.2s;
        }

        .history_item:hover{
            background-color: #f9fafb;
        }

        .history_item:last-child {
            border-bottom: none;
        }

        .item-left {
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .icon-box {
            padding: 8px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 4px;
        }

        .icon-recycle{
            background-color: var(--earn-bg); 
            color: var(--earn-text); 
        }

        .icon-quiz{
            background-color: var(--quiz-bg); 
            color: var(--quiz-text); 
        }

        .icon-challenge{
            background-color: var(--challenge-bg); 
            color: var(--challenge-text); 
        }

        .icon-reward{
            background-color: var(--reward-bg); 
            color: var(--reward-text); 
        }

        .icon-default { 
            background-color: #f3f4f6; 
            color: var(--text-gray); 
        }

        .item-details h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 2px;
        }

        .item-subtext {
            font-size: 0.875rem;
            color: var(--text-gray);
            margin-bottom: 4px;
        }

        .item-time {
            font-size: 0.75rem;
            color: var(--text-light);
        }

        .item-right {
            text-align: right;
        }

        .amount-text {
            font-size: 1.125rem;
            font-weight: 700;
            display: block;
        }

        .text-Earn { 
            color: var(--dark-green); 
        }

        .text-Spend { 
            color: var(--spend); 
        }

        .type-badge {
            font-size: 0.75rem;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .empty-state {
            padding: 32px;
            text-align: center;
            color: var(--text-gray);
        }

        @media (max-width: 630px) {
            .points-dashboard::after {
                content: none;
            }
        }


    </style>

</head>
<body>
    <?php include "challengerTaskbar.php"; ?>
    

    <main>

        <div class="page_header">
            <h1 class="page_title">My Eco Points</h1>
            <p class="page_subtitle">Track your contribution to a greener planet.</p>
        </div>

        <section class="points-dashboard">
            <p class="point-label">Current Point Balance</p>
            <div class="point-display">
                <?php echo number_format($total_points); ?> <small style="font-size: 1.5rem">PTS</small>
            </div>
            <a href="redeemReward.php" class="btn-history">Redeem Rewards</a>
        </section>


        
        <div class = "history_header">
            <h2 class = "header_title">
                Points History
            </h2>


        </div>

        <div class = "history_records">
            <ul class = "history_list">
                <?php if (empty($history_records)): ?>
                    <li class="empty-state">
                        No transactions found yet. Start recycling!
                    </li>
                <?php else: ?>

                <?php foreach ($history_records as $row): 
                    
                    $isEarn = ($row['transaction_type'] === 'Earn');
                    
                    
                    $amountClass = $isEarn ? 'text-Earn' : 'text-Spend';
                    $amountSign  = $isEarn ? '+' : '-';
                    
                    
                    $iconName = 'history';
                    $iconClass = 'icon-default';
                    $details = '';

                    
                    if (stripos($row['source_type'], 'Recycling') !== false) {
                        $iconName = 'recycling';
                        $iconClass = 'icon-recycle';
                        if (!empty($row['recycle_history_id'])) $details = 'Recycle ID: #' . $row['recycle_history_id'];
                    }
                    
                    elseif (stripos($row['source_type'], 'Daily Quiz') !== false) {
                        $iconName = 'quiz';
                        $iconClass = 'icon-quiz';
                        if (!empty($row['quiz_id'])) $details = 'Quiz ID: #' . $row['quiz_id'];
                    }
                    
                    elseif (stripos($row['source_type'], 'Challenge') !== false) {
                        $iconName = 'trophy';
                        $iconClass = 'icon-challenge';
                        if (!empty($row['challenge_id'])) $details = 'Challenge ID: #' . $row['challenge_id'];
                    }
                    
                    elseif ($row['source_type'] === 'Reward') {
                        $iconName = 'redeem';
                        $iconClass = 'icon-reward';
                        if (!empty($row['reward_id'])) $details = 'Reward ID: #' . $row['reward_id'];
                    }

                
                    $phpDate = strtotime($row['date'] . ' ' . $row['time']);
                    $displayDate = date('d M Y', $phpDate);
                    $displayTime = date('g:i A', $phpDate);
                ?>

                    <li class="history_item">
                        <div class="item-left">
                            <div class="icon-box <?php echo $iconClass; ?>">
                                <span class="material-symbols-outlined"><?php echo $iconName; ?></span>
                            </div>
                            
                            <div class="item-details">
                                <h4><?php echo ucfirst($row['source_type']); ?></h4>
                                <?php if($details): ?>
                                    <p class="item-subtext"><?php echo $details; ?></p>
                                <?php endif; ?>
                                <p class="item-time">
                                    <?php echo $displayDate . ' at ' . $displayTime; ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="item-right">
                            <span class="amount-text <?php echo $amountClass; ?>">
                                <?php echo $amountSign . $row['points']; ?>
                            </span>
                            <span class="type-badge">
                                <?php echo $row['transaction_type']; ?>
                            </span>
                        </div>
                    </li>
                <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

    </main>
</body>
</html>