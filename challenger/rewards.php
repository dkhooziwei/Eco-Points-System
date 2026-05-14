<?php
require("../session.php");
include("../conn.php");

if($_SESSION["role"] !== "Challenger"){
        header("Location: ../registerLogin/login.php");
        exit();
    }

$challenger_id = $_SESSION['mySession'];

if(!$con){
    die("Database connection failed: " . mysqli_connect_error());
}

/* Point calculation */
$point_query = "
SELECT total_points AS total
FROM challenger 
WHERE challenger_id = '$challenger_id'
";

$point_result = mysqli_query($con, $point_query);
$current_points = ($point_result) ? (mysqli_fetch_assoc($point_result)['total'] ?? 0) : 0;

/*Fetch rewards*/
$reward_query = "SELECT * FROM reward WHERE is_deleted = 0 ORDER BY reward_id";
$reward_result = mysqli_query($con, $reward_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Challenger - Rewards</title>
    <link href="../commonstyle.css" rel="stylesheet">
    <style>
        :root {
            --light-lime: #eaef9d;
            --lime: #c1d95c;
            --soft-green: #80b155;
            --mid-green: #498428;
            --dark-green: #336a29;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Points Section  */
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

        .point-label { font-size: 1rem; text-transform: uppercase; letter-spacing: 2px; opacity: 0.9; }
        
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
        }

        .btn-history:hover {
            transform: scale(1.05);
        }

        /* Rewards Grid */
        .reward-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            padding: 20px 8% 80px;
            max-width: 1300px;
            margin: 0 auto;
        }

        .reward-card {
            background: var(--white);
            border-radius: 20px;
            padding: 20px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 3px solid var(--soft-green);
            text-align: center;
            display: flex;
            flex-direction: column;
        }

        .reward-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
        }

        .img-container img{
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
        }

        .reward-card .img-container {
            background: white;
            border-radius: 15px;
            margin-bottom: 15px;
            overflow: hidden;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .reward-card img {
            max-width: 100%;
            max-height: 180px;
            transition: 0.5s;
        }

        .reward-card:hover img { 
            transform: scale(1.1); 
        }

        .reward-card h4 {
            font-size: 1.2rem;
            color: var(--dark-green);
            margin-bottom: 8px;
        }

        .reward-info{
            text-align: center;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        .reward-points {
            color: var(--mid-green);
            font-weight: 800;
            font-size: 1.3rem;
            margin-bottom: 5px;
        }

        .stock {
            display: inline-block;
            background: #f0f0f0;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .redeem-btn {
            background: var(--soft-green);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            cursor: pointer;
            width: 100%;
            font-weight: 700;
            margin-top: auto;
            transition: 0.3s;
            font-size: 1rem;
            font-weight: 600;
        }

        .redeem-btn:hover:not(:disabled) {
            background: var(--dark-green);
            box-shadow: 0 5px 15px rgba(73, 132, 40, 0.3);
        }

        .redeem-btn:disabled {
            background: #e0e0e0;
            color: #999;
            cursor: not-allowed;
        }

        /* Modal Overlay */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
            display: flex;
            justify-content: center;
            align-items: center;
            visibility: hidden;
            opacity: 0;
            transition: 0.3s ease;
            z-index: 2000;
        }

        .modal-overlay.active {
            visibility: visible;
            opacity: 1;
        }

        /* Modal Box */
        .modal-content {
            background: var(--lime);
            color: var(--dark-green);
            line-height: 1.5;
            padding: 40px;
            border-radius: 24px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            transform: scale(0.8);
            transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .modal-overlay.active .modal-content {
            transform: scale(1);
        }

        .modal-icon { 
            font-size: 50px; margin-bottom: 15px; 
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-cancel {
            flex: 1;
            background: #daf8d7ff;
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-confirm {
            flex: 1;
            background: var(--dark-green);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-confirm:hover { background: var(--mid-green); }

        /* mobile view */
        @media (max-width: 600px) {
            .points-dashboard {
                margin: 20px 15px;
                padding: 25px 15px;
                border-radius: 18px;
            }

            .point-display {
                font-size: 2.8rem; 
            }

            .reward-grid {
                grid-template-columns: 1fr; 
                padding: 10px 20px 50px;
                gap: 20px;
            }

            .reward-card {
                padding: 15px; 
            }

            .modal-content {
                width: 95%; 
                padding: 25px 15px;
            }

        }

        /* tablet view */
        @media (min-width: 601px) and (max-width: 1200px) {
           .reward-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: 20px;
                padding: 20px 5% 60px;
            }
            
            .points-dashboard {
                max-width: 90%;
                padding: 30px;
            }
        }
        
    </style>
</head>
<body>

<?php 
include("challengerTaskbar.php");
?>

<main>
    <section class="points-dashboard">
        <p class="point-label">Current Point Balance</p>
        <div class="point-display">
            <?php echo number_format($current_points); ?> <small style="font-size: 1.5rem">PTS</small>
        </div>
        <a href ="ecoPointsHistory.php"><button class="btn-history">View Points History</button></a>
    </section>

    <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="color: var(--dark-green);">Redeem Your Points</h2>
        <p style="color: #666;">Choose from our eco-friendly rewards</p>
    </div>

    <section class="reward-grid">
        <?php while($reward = mysqli_fetch_assoc($reward_result)): ?>
            <div class="reward-card">
                <div class="img-container">
                    <?php
                        $db_path = !empty($reward['file_path'])
                        ? $reward['file_path']
                        : "images\\rewardsPIC\\default.jpg";

                    // Convert Windows backslashes to browser safe slashes
                    $browser_path = str_replace('\\', '/', $db_path);

                    
                    $final_path = "../" . $browser_path;
                ?>

                <img src="<?= htmlspecialchars($final_path) ?>"
                    alt="<?= htmlspecialchars($reward['name']) ?>">
                </div>

                <div class="reward-info">
                    <h4><?php echo htmlspecialchars($reward['name']); ?></h4>
                    <div class="reward-points"><?php echo number_format($reward['points']); ?> pts</div>
                    <div><span class="stock">📦 <?php echo $reward['stock']; ?> items left</span></div>
                </div>

                <button class="redeem-btn"
                    <?php 
                        if($current_points < $reward['points'] || $reward['stock'] <= 0) {
                            echo 'disabled'; 
                        } else{
                            echo 'onclick="openModal(\'' . $reward['reward_id'] . '\', \'' . addslashes($reward['name']). '\', \'' .$reward['points']. '\')"';
                        }
                    ?>>

                    <?php 
                        if($reward['stock'] <= 0) echo 'Out of Stock';
                        elseif($current_points < $reward['points']) echo 'Insufficient Points';
                        else echo 'Redeem Now';
                    ?>
                </button>
            </div>
        <?php endwhile; ?>
    </section>

    <div id="redeemModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-icon">🎁</div>
        <h2 id="modalTitle">Confirm Redemption</h2>
        <p id="modalDescription">Are you sure you want to spend <strong id="modalPoints">0</strong> pts for <strong id="modalItem">this item</strong>?</p>
        
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal()">Cancel</button>
            <form action="redeemReward.php" method="POST" style="display: inline;">
                <input type="hidden" name="reward_id" id="formRewardId">
                <button type="submit" class="btn-confirm">Yes, Redeem</button>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal(id, name, points) {
        document.getElementById('modalItem').innerText = name;
        document.getElementById('modalPoints').innerText = points;
        document.getElementById('formRewardId').value = id;
        document.getElementById('redeemModal').classList.add('active');
    }

    function closeModal() {
        document.getElementById('redeemModal').classList.remove('active');
    }

    // Close modal if user clicks outside of the box
    window.onclick = function(event) {
        let modal = document.getElementById('redeemModal');
        if (event.target == modal) closeModal();
    }
</script>
</main>

</body>
</html>