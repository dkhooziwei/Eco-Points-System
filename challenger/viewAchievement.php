<?php
require("../session.php");
include("../conn.php");

if($_SESSION["role"] !== "Challenger"){
        header("Location: ../registerLogin/login.php");
        exit();
    }

$challenger_id = $_SESSION['mySession'] ;

$query ="
    SELECT a.ach_id, a.name, a.type, a.source, cp.status, cp.completion_date AS date, cp.current_progress
    FROM achievement a
    LEFT JOIN challenge_participation cp
        ON a.challenge_id = cp.challenge_id
        AND cp.challenger_id = '$challenger_id'
        AND cp.status = 'Completed' 
        AND cp.current_progress = '100.00'
    WHERE a.is_deleted = 0
";

$result = mysqli_query($con, $query);

/*Initialize separate arrays fro sections*/
$badge =['unlocked' => [], 'locked' => []];
$cert =['unlocked' => [], 'locked' => []];

while($achievement = mysqli_fetch_assoc($result)){
    $status =!is_null($achievement['date']) ? 'unlocked' : 'locked';

    if($achievement['type'] === 'E-certificate') {
        $cert[$status][] = $achievement;
    }else{
        $badge[$status][] = $achievement;
    }
}

/*Function to render the achievement grid with desc*/
function renderAchievementGrid($dataArray, $statusType){
    foreach($dataArray as $ach){
        $is_unlocked = ($statusType === 'unlocked');
        $card_class = $is_unlocked ? 'unlocked' : 'locked';
        $img_class = $is_unlocked ? '' : 'locked-icon';

        $box_shape = ($ach['type'] === 'E-certificate') ? 'rectangle-box' : 'circle-box';

        $desc = "Earned by contributing to eco-friendly activities.";

        switch($ach['name']){
            case 'Ocean Saver':
                $desc = "Recycle 10kg of plastic materials to protect our seas.";
                break;

            case 'Glass Guardian':
                $desc = "Recycle 5kg of glass at specific Recycling Centers.";
                break;

            case 'Paper Master':
                $desc = "Master circularity by recycling 5kg of paper.";
                break;

            case 'UNESCO Warrior':
                $desc = "Protect heritage sites through consistent green contributions.";
                break;

            case 'Eco Explorer':
                $desc = "Attend 10 environmental events to expand your impact.";
                break;
        }
        ?>

        <div class="achievement-card <?php echo $card_class; ?>">
            <div class="achievement-icon-box <?php echo $box_shape; ?>">
                <?php 
                    $browser_path = str_replace('\\', '/', $ach['source']);
                    $final_path = "../" . $browser_path;
                ?>

                <img src="<?php echo htmlspecialchars($final_path); ?>"
                class="<?php echo $img_class; ?>"
                alt="Achievement Image">
            </div>

            <h2><?php echo htmlspecialchars($ach['name']); ?></h2>
            <p><?php echo $desc; ?></p>

            <div class="status-label">
                <?php if ($is_unlocked): ?>
                    ✅ Unlocked: <?php echo date("d M Y", strtotime($ach['date'])); ?>
                <?php else: ?>
                    🔒 Not yet unlocked
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Challenger - View Achievement</title>
    <link href="../commonstyle.css" rel="stylesheet">
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

        /*Achievement Layout*/
        .sub-heading {
            font-size: 1.8rem;
            color: var(--dark-green);
            margin: 40px 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 3px solid var(--light-lime);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .achievement-container{
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .header-box{
            background: linear-gradient(135deg, var(--lime), var(--soft-green));
            color: white;
            text-align: center;
            padding: 50px 30px;
            border-radius: 24px;
            margin-bottom: 50px;
            box-shadow: 0 10px 30px rgba(128, 177, 85, 0.2);
        }

        .header-box h1{
            font-size: 3.5rem;
            color: white;
        }

        .achievement-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 35px;
            padding: 20px 0;
            align-items: stretch;
        }

        .achievement-card{
            background-color: white;
            border: 2px solid #eef2e6;
            border-radius: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 35px 25px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            box-shadow: 0 10px 20px rgba(0,0,0,0.02);
            height: 100%;
            min-height: 400px;
        }

        .achievement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.08);
        }

        /*Style for unlocked achievement*/
        .achievement-card.unlocked {
            background: linear-gradient(to right, white , rgba(224, 253, 199, 0.4) );
            border: 2.5px solid var(--soft-green);
        }

        .achievement-card h2{
            font-size: 1.8rem;
            color: var(--dark-green);
            margin-bottom: 5px;
        }

        .achievement-card p{
            color: #555;
            font-weight: bold;
        }

        .achievement-icon-box{
            width: 140px;
            height: 140px;
            background-color: #f0f4f8;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            padding: 20px;
            transition:  0.3s ;
            overflow: hidden;
        }

        .rectangle-box{
            width: 90%;      
            height: 120px;     
            border-radius: 12px;
            padding: 10px;
        }

        .rectangle-box img{
            width: 100%;
            height: 100%;
            object-fit: contain;
            transform: scale(1);
        }

        .circle-box img{
            max-width: 80%;
            max-height: 80%;
            object-fit: contain;
            transform: scale(1.5); 
            display: block;
            transition: transform 0.3s ease;
        }

        .achievement-card:hover .achievement-icon-box {
            transform: rotate(5deg) scale(1.1);
        }

        /*Style for locked achievement*/
        .locked-icon{
            filter: grayscale(1);
            opacity: 0.4;
        }

        .achievement-card.locked::after {
            content: '🔒';
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 1.2rem;
            opacity: 0.3;
        }

       .status-label {
            margin-top: auto; 
            font-weight: 700;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.85rem;
        }

        .unlocked .status-label {
            background-color: var(--light-lime);
            color: var(--dark-green);
        }

        .locked .status-label {
            background-color: #f0f0f0;
            color: #999;
        }

        /* mobile view */
        @media (max-width: 600px) {
            .header-box {
                padding: 30px 20px;
                margin-bottom: 30px;
            }

            .header-box h1 {
                font-size: 2.2rem; 
            }

            .sub-heading {
                font-size: 1.5rem;
            }

            .achievement-card {
                min-height: auto; 
                padding: 25px 20px;
            }
        }

         /* tablet view */
        @media (min-width: 601px) and (max-width: 1200px) {
            .achievement-container {
                max-width: 90%; 
            }
            
            .header-box h1 {
                font-size: 2.8rem; 
            }

            .achievement-grid {
                grid-template-columns: repeat(2, 1fr); 
            }
        }
        
    </style>
</head>
<body>

<?php
include("challengerTaskbar.php")
?>

<main>    
    <div class="achievement-container">
        <div class="header-box">
            <h1>Achievements</h1>
        </div>

        <section class="achievement-section">
            <h2 class="sub-heading">🥇 Badges</h2>
            <div class="achievement-grid">
                <?php
                    renderAchievementGrid($badge['unlocked'], 'unlocked');
                    renderAchievementGrid($badge['locked'], 'locked');
                ?>
            </div>
        </section>

        <section class="achievement-section">
            <h2 class="sub-heading">📃 E-certificates</h2>
            <div class="achievement-grid">
                <?php
                    renderAchievementGrid($cert['unlocked'], 'unlocked');
                    renderAchievementGrid($cert['locked'], 'locked');
                ?>
            </div>
        </section>
    </div>
</div>
</main>
</body>
</html>