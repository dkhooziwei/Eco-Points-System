<?php 
require("../session.php");
include("../conn.php");

if($_SESSION["role"] !== "Admin"){
        header("Location: ../registerLogin/login.php");
        exit();
    }

/*Fetch statistics from reward table*/
$reward_query = mysqli_query($con,"SELECT * FROM reward");
$total_reward = mysqli_num_rows($reward_query);

$stock_query = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(stock) as total FROM reward"));
$total_stock = $stock_query['total'] ?? 0;

/*Fetch total redeemptions record from point_history table */
$redeemed_query = "SELECT * FROM point_history WHERE transaction_type = 'Spend' AND source_type = 'Reward'";
$total_redeemed = mysqli_num_rows(mysqli_query($con, $redeemed_query));

$rewards = mysqli_query($con, "SELECT * FROM reward WHERE is_deleted = 0 ORDER BY reward_id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Reward Management</title>
    <link href="../commonStyle.css" rel="stylesheet">
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


        /*Header Style*/
        .header{
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--dark-green);
            padding-top: 30px;
            padding-left: 8%;
        
        }

        /*Stats-card Style*/
        .stats-container{
            display: flex;
            gap: 20px;
            margin: 30px 8%;
            margin-bottom: 30px;
            padding-top: 15px;
            justify-content: center;
        }

        .stats-card{
            background: white;
            flex: 1;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .stats-card h4{
            color: var(--mid-green);
            margin: 0;
            font-size: 1.3rem;
        }

        .stats-card p{
            color: var(--dark-green);
            margin: 10px 0 0 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        /*Action-bar Style*/
        .action-bar{
            display: flex;
            justify-content: flex-end;
            margin: 30px 8%;
        } 

        .btn-add{
            background-color: var(--mid-green);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 650;      
            font-family: "Nunito Sans", sans-serif;
        }

        .btn-add:hover{
            background-color: var(--dark-green);
        }

        /*Reward-grid Style */
        .reward-grid{
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 0 8% 50px 8%;
        }

        .reward-card{
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /*Reward Card Image Style*/
        .img-container{
            width: 100%;
            height: 180px;
            overflow: hidden;
        }

        .img-container img{
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .img-container img:hover{
            transform: scale(1.05);
        }

        /*Reward Card Content Style*/
        .reward-content{
            padding: 15px;
        }

        .reward-content h3{
            margin: 0;
            color: var(--mid-green);
            font-size: 1.2rem;
        }

        .points-badge{
            display: inline-block;
            margin-top: 8px;
            background-color: var(--light-lime);
            color: var(--dark-green);
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .stock-info{
            margin-top: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--mid-green);
            display: flex;
            justify-content: space-between;
        }

        /*Reward Card Action Buttons Style*/
        .card-actions{
            display: flex;
            justify-content: space-between;
            padding: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .btn-edit, .btn-delete{
            padding: 8px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            flex: 1;
            margin: 0 5px;
            font-size: 1rem;
            font-family: 'Nunito Sans', sans-serif;
        }

        .btn-edit{
            background-color: var(--soft-green);
            color: white;
        }

        .btn-edit:hover{
            background-color: var(--mid-green);
        }

        .btn-delete{
            background-color: #f8d7da;
            color: #842029;
        }

        .btn-delete:hover{
            background-color: #f1bfc5;
        }

        /*Modal Style*/
        .modal{
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal-content{
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 400px;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .close{
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover, .close:focus{
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .modal-content h2{
            margin-top: 0;
            color: var(--mid-green);
        }

        .form-group{
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }   

        .form-group label{
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--dark-green);
        }

        .form-group input{
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
        }

        .file-upload {
            height: 150px; 
            width: 100%; 
            display: flex;
            flex-direction: column;
            gap: 15px;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            border: 2px dashed var(--soft-green); 
            background-color: #f9fdf9;
            padding: 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .file-upload:hover {
            background-color: var(--light-lime);
            border-color: var(--mid-green);
        }

        .file-upload .icon svg {
            height: 50px;
            fill: var(--mid-green);
        }

        .file-upload .text span {
            font-weight: 600;
            color: var(--dark-green);
            font-size: 0.9rem;
        }

        .file-upload input {
            display: none;
        }   

        /*Modal Form Action Buttons Style*/
        .form-actions{
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .form-actions button{
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
        }

        #modal-submit-btn{
            background-color: var(--mid-green);
            color: white;
        }

        #modal-submit-btn:hover{
            background-color: var(--dark-green);
        }

        .form-actions button[type="button"]{
            background-color: #ccc;
            color: #333;
        }

        .form-actions button[type="button"]:hover{
            background-color: #bbb;
        }

        /*Mobile View*/
        @media (max-width: 600px){
            .reward-grid {
                grid-template-columns: 1fr; 
                padding: 0 15px;
            }
            .stats-container{
                flex-direction: column;
                margin: 20px 5%;
                padding-top: 10px;
            }

            .stats-card{
                width: 100%;
                text-align: center;
            }

            .modal-content{
                width: 90%;
                margin-top: 30%;
            }
        }

        /*Tablet View*/
        @media (min-width: 601px) and (max-width: 1200px) {
            .stats-container {
                margin: 30px 5%; 
                gap: 15px;
            }
        }

    </style>
</head>
<body>

<?php 
include("adminTaskbar.php");
?>

<main>
    <div class="header">
        <h2>Reward Management</h2>
    </div>

    <div class="stats-container">
        <div class="stats-card">
            <h4>Total Rewards</h4>
            <p><?php echo $total_reward; ?></p>
        </div>

        <div class="stats-card">
            <h4>Total Stock Available</h4>
            <p><?php echo $total_stock; ?></p>
        </div>

        <div class="stats-card">
            <h4>Total Redeemed</h4>
            <p><?php echo $total_redeemed; ?></p>
        </div>
    </div>

     <div class="action-bar">
            <button class="btn-add" onclick="openModal('add')">+ Add Reward</button>
    </div>


    <div class="reward-grid">
    <?php while($reward = mysqli_fetch_assoc($rewards)) :
    // Calculate redemption for this specific card
        $rid = $reward['reward_id'];
        $this_redeemed_query = mysqli_query($con, "SELECT * FROM point_history WHERE reward_id = '$rid' AND transaction_type = 'Spend'");
        $this_redeemed = mysqli_num_rows($this_redeemed_query);
    ?>

        <div class="reward-card">
            <div class="img-container">
                <?php
                    $db_path = !empty($reward['file_path'])
                        ? $reward['file_path']
                        : "../images/rewardsPIC/default.jpg";
                    
                    $final_path = "../" . $db_path;
                ?>

                <img src="<?= htmlspecialchars($final_path) ?>"
                    alt="<?= htmlspecialchars($reward['name']) ?>">

            </div>

            <div class="reward-content">
                <h3><?php echo htmlspecialchars($reward['name']); ?></h3>
                <span class="points-badge">
                    <?php echo $reward['points']; ?>Points
                </span>

                <div class="stock-info">
                    <span>Stock: <strong><?php echo (int)$reward['stock']; ?></strong></span>
                    <span>Redeemed: <strong><?php echo $this_redeemed; ?> </strong></span>
                </div>
            </div>

            <div class="card-actions">
                <button class="btn-edit" 
                        onclick="openEdit(
                        '<?php echo $reward['reward_id']; ?>',
                        '<?php echo addslashes($reward['name']); ?>',
                        '<?php echo $reward['stock']; ?>',
                        '<?php echo $reward['points']; ?>'                      
                    )">
                    Edit
                </button>

                <a class="btn-delete"
                    href="rewardManageProcess.php?delete=<?php echo $reward['reward_id']; ?>"
                    onclick="return confirm('Confirm to delete this reward?')">
                    Delete
                </a>
            </div>
        </div>
    <?php endwhile; ?>
    </div>

    <div id ="reward-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modal-title">Add Reward</h2>
            <form id="add-reward-form" action="rewardManageProcess.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="reward_id" id="reward_id">

                <div class="form-group">
                    <label for="name">Reward Name:</label>
                    <input type="text" name="name" id="name" required>
                </div>

                <div class="form-group">
                    <label for="stock">Stock:</label>
                    <input type="number" name="stock" id="stock" min="0" required>
                </div>

                <div class="form-group">
                    <label for="points">Points Required:</label>
                    <input type="number" name="points" id="points" min="0" required>
                </div>

                <div class="form-group">
                    <label>Reward Image *</label>
                    <label class="file-upload" for="reward_image">
                        <div class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="" viewBox="0 0 24 24">
                                <g stroke-width="0" id="SVGRepo_bgCarrier"></g>
                                <g stroke-linejoin="round" stroke-linecap="round" id="SVGRepo_tracerCarrier"></g>
                                <g id="SVGRepo_iconCarrier"> <path fill="" d="M10 1C9.73478 1 9.48043 1.10536 9.29289 1.29289L3.29289 7.29289C3.10536 7.48043 3 7.73478 3 8V20C3 21.6569 4.34315 23 6 23H7C7.55228 23 8 22.5523 8 22C8 21.4477 7.55228 21 7 21H6C5.44772 21 5 20.5523 5 20V9H10C10.5523 9 11 8.55228 11 8V3H18C18.5523 3 19 3.44772 19 4V9C19 9.55228 19.4477 10 20 10C20.5523 10 21 9.55228 21 9V4C21 2.34315 19.6569 1 18 1H10ZM9 7H6.41421L9 4.41421V7ZM14 15.5C14 14.1193 15.1193 13 16.5 13C17.8807 13 19 14.1193 19 15.5V16V17H20C21.1046 17 22 17.8954 22 19C22 20.1046 21.1046 21 20 21H13C11.8954 21 11 20.1046 11 19C11 17.8954 11.8954 17 13 17H14V16V15.5ZM16.5 11C14.142 11 12.2076 12.8136 12.0156 15.122C10.2825 15.5606 9 17.1305 9 19C9 21.2091 10.7909 23 13 23H20C22.2091 23 24 21.2091 24 19C24 17.1305 22.7175 15.5606 20.9844 15.122C20.7924 12.8136 18.858 11 16.5 11Z" clip-rule="evenodd" fill-rule="evenodd"></path> </g>
                            </svg>
                        </div>
                        <div class="text">
                            <span id="file-name-display">Click to upload image</span>
                        </div>
                        <input type="file" name="reward_image" id="reward_image" accept="image/*" onchange="updateFileName(this)">
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" name="add_reward" id="modal-submit-btn">Add Reward</button>
                    <button type="button" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(type) {
            document.getElementById('reward-modal').style.display = 'flex';
            if(type === 'add') {
                document.getElementById('modal-title').innerText = 'Add Reward';
                document.getElementById('modal-submit-btn').innerText = 'Add Reward';
                document.getElementById('add-reward-form').action = 'rewardManageProcess.php';
                document.getElementById('reward_id').value = '';
                document.getElementById('name').value = '';
                document.getElementById('stock').value = '';
                document.getElementById('points').value = '';
            }
        }

        function closeModal() {
            document.getElementById('reward-modal').style.display = 'none';
        }

        function openEdit(reward_id, name, stock, points) {
            document.getElementById('reward-modal').style.display = 'flex';
            document.getElementById('modal-title').innerText = 'Edit Reward';
            document.getElementById('modal-submit-btn').innerText = 'Update Reward';
            document.getElementById('add-reward-form').action = 'rewardManageProcess.php?edit=' + reward_id;
            document.getElementById('reward_id').value = reward_id;
            document.getElementById('name').value = name;
            document.getElementById('stock').value = stock;
            document.getElementById('points').value = points;
        }

        function updateFileName(input) {
            const fileName = input.files[0] ? input.files[0].name : "Click to upload image";
            document.getElementById('file-name-display').innerText = fileName;
        }

        // Update existing closeModal() to reset text
        function closeModal() {
            document.getElementById('reward-modal').style.display = 'none';
            document.getElementById('file-name-display').innerText = "Click to upload image";
            document.getElementById('add-reward-form').reset();
        }

        // Close modal when clicking outside of the modal content
        window.onclick = function(event) {
            var modal = document.getElementById('reward-modal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</main>
</body>
</html>