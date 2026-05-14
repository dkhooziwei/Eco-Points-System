<?php

    include('../conn.php');
    include('../commonFunctions.php');
    include('../session.php');

    if($_SESSION["role"] !== "Admin"){
        header("Location: ../registerLogin/login.php");
        exit();
    }


    //recycling center
    $sql_centre = "SELECT * FROM recycling_centre
    ORDER BY name";

    $result_centre = mysqli_query($con, $sql_centre);


    if (!$result_centre) {
        die("SQL Error: " . mysqli_error($con));
    }

    while ($row = mysqli_fetch_assoc($result_centre)) {
        $centre_record[] = $row;
    }


    // points convertion chart
    $sql_recyclables = "SELECT * FROM recyclable";

    $result_recyclables = mysqli_query($con, $sql_recyclables);


    if (!$result_recyclables) {
        die("SQL Error: " . mysqli_error($con));
    }

    while ($row = mysqli_fetch_assoc($result_recyclables)) {       
        $recyclable_record[] = $row;
    }

    //point calculator
    $type = '';
    $weight = 0;
    $totalPoints = 0;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $type = $_POST['type'];

        if (isset($_POST['weight'])) {
            $weight = floatval($_POST['weight']);

        } else {
            $weight = 0;
        }


        if ($type && $weight > 0) {
            $totalPoints = point_calculation($weight, $type);
        }
    }

    //get recyclable name
    $recyclable_name = '';

    $sql_name = "SELECT type FROM recyclable WHERE recyclable_id = '$type'";
    $result_name = mysqli_query($con, $sql_name);

    $row_name = mysqli_fetch_assoc($result_name);
    if ($row_name !== null) {
        $recyclable_name = $row_name['type'];
    }

    //delete
    if (isset($_POST['confirmDelete']) && !empty($_POST['delete_id'])) {

    $delete_id = mysqli_real_escape_string($con, $_POST['delete_id']);

    $sql_img = "SELECT file_path FROM recycling_centre WHERE centre_id = '$delete_id'";
    $res_img = mysqli_query($con, $sql_img);
    $img = mysqli_fetch_assoc($res_img);

    if (!empty($img['file_path']) && file_exists("../" . $img['file_path'])) {
        unlink("../" . $img['file_path']);
    }

    mysqli_query($con, "UPDATE recycling_centre SET is_deleted = 1
                    WHERE centre_id = '$delete_id'");

    header("Location: " . $_SERVER['PHP_SELF'] . "?deleted=1");
    exit;

    }


?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recycling Info</title>
    <link href="../commonStyle.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

    <style>
        :root{
            --dark-green:#335a29;
            --mid-green:#498428;
            --text:#2E3A2E;
            --grey:#6F7F6F;
            --white: #ffffff;
            --mid-red: #c0392b;
            --dark-red: #a93226;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        .content{
            max-width: 1200px;
            margin: 0 auto;
            padding: 16px;
        }


        .title h1{
            color:var(--dark-green);
            margin-bottom:5px;
        }

        .title p{
            color:var(--grey);
            margin-bottom:30px;
        }

        .section{
            display: flex;
            gap: 25px;
        }

        .recycling_center{
            width: 65%;
        }

        .points{
            width: 35%;
            position: sticky;
            top: 20px;
        }

        .center_list{
            display:flex;
            gap:16px;
            align-items:center;
            background:var(--white);
            padding:16px;
            border-radius:16px;
            margin-bottom:20px;
            box-shadow:0 6px 18px rgba(0,0,0,.06);
            position: relative
        }

        .center_img{
            width:150px;
            height:130px;
            border-radius:12px;
            background:#ddd;
            background-size:cover;
            background-position:center;
            flex-shrink:0;
        }

        .center_details{
            flex:1;
        }

        .first {
            color: var(--green);
            word-break: break-word;
            margin-bottom: 10px;
            margin-right: 120px;

        }


        .row{
            display:grid;
            grid-template-columns: 1fr 1fr;
            gap:20px;
            margin-bottom:6px;
        }

        .info{
            font-size:14px;
            color:var(--grey);
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        .points-row{
            display:flex;
            justify-content:space-between;
            padding:10px 0;
            border-bottom:1px dashed #ddd;
        }

        .points-row:last-child{
            border:none;
        }

        .calc-btn{
            width:100%;
            margin-top:12px;
            padding:12px;
            border:none;
            border-radius:12px;
            background:var(--mid-green);
            color:white;
            font-size:15px;
            font-weight:600;
            cursor:pointer;
        }

        .calc-btn:hover{
            background:#3c6f22;
        }

        .points_calculator {
            background: var(--white);
            border-radius: 16px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 6px 18px rgba(0,0,0,.06);
        }

        .points_calculator h3 {
            margin-bottom: 16px;
            color: var(--dark-green);
        }

        .type_select_section,
        .weight_section {
            margin-bottom: 14px;
        }

        .type_select_section label,
        .weight_section label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--grey);
        }

        .type_select_section select,
        .weight_section input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #ddd;
            font-size: 14px;
        }

        .type_select_section select:focus,
        .weight_section input:focus {
            outline: none;
            border-color: var(--mid-green);
        }

        .points_result {
            margin-top: 16px;
            padding: 14px;
            border-radius: 12px;
            background: #f1f7ee;
            color: var(--dark-green);
            font-size: 16px;
            font-weight: 550;
            text-align: left;
        }
        

        .search_section{ 
            margin-bottom: 20px; 
            width: 65%;
        }

        .search_section input{ 
            width:100%; 
            padding:10px; 
            border-radius:10px; 
            border:1px solid #ddd; 
            margin-top:6px; 
        }

        .material-symbols-outlined {
            font-size: 20px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark-green);
        }


        .bar{
            display: none;
        }

        .name_buttons{
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            margin-bottom: 8px;
            gap: 15px;

        }

        .action_button {
            position: absolute;
            top: 16px;
            right: 16px;
        }

        .edit button {
            background: var(--mid-green);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
        }


        .delete button {
            background: var(--mid-red);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
        }

        .edit button:hover {
            background: #3c6f22;
        }

        .delete button:hover {
            background: var(--dark-red);
        }

        .edit_points{
            display: flex;
            align-items: center; 
            justify-content: center;  
            gap: 8px;            
            height: 40px;
            padding: 0 16px;
            border-radius: 10px;
            border: none;
            background: var(--mid-green);
            color: white;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            text-decoration: none;
        }

        .edit_points:hover{
            background: var(--dark-green);
        }

        .add{
            margin-bottom: 20px;
            width: 65%;
        }

        .add_centre{
            display: flex;
            align-items: center; 
            justify-content: center;  
            gap: 8px;            
            height: 40px;
            padding: 0 16px;
            border-radius: 10px;
            border: none;
            background: var(--mid-green);
            color: white;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }

        .add_centre:hover{
            background: var(--dark-green);
        }

        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .modal {
            background: white;
            width: 400px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,.2);
        }

        .modal_header {
            padding: 14px 20px;
            color: white;
        }

        .modal-body {
            padding: 20px;
            text-align: center;
        }

        .button_container {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }

        .button_container button {
            flex: 1;
            padding: 10px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }
        .points_conversion_chart {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 6px 18px rgba(0,0,0,.06);
            margin-bottom: 20px;
        }

        .centre_bar{
            display: none;
        }



        @media(max-width:900px){

            .title,.search_container,.recycling_center, .add{
                display: none;
            }

            .points{
                margin: 0 auto;
                width: 90%;
            }
            .centre_bar{
                display: block;
                height: 38px;
                padding: 0 14px;
                border-radius: 10px;
                border: none;
                background: var(--mid-green);
                color: white;
                font-weight: 600;
                cursor: pointer;
                white-space: nowrap;
                margin-bottom: 20px;
            }
            

        }

    
        
        


    </style>



</head>


<body>
    <?php include "adminTaskbar.php"; ?>
    <div class = "content">

        <div class = "title">
            <h1>Recycling Information</h1>
            <p>Find the nearest recycling centre and calculate your Eco Points!</p>
        </div>

        
        <div class = "search_container">
            <div class="search_section">
                <label for="searchInput" style="font-weight:600; color:var(--dark-green);">
                    Search Recycling Centre:
                </label>
                <input type="text" id="searchInput" placeholder="Enter centre name...">
            </div>

            <a href = "editPoints.php" style = "text-decoration: none;">
                <button class="bar">
                        Points Chart
                </button>
            </a>

        </div>

        <div class = "add">
            <a href= "addRecyclingCentre.php" class="add_centre" style = "text-decoration: none;">
                        <span class="material-symbols-outlined">add</span>Add Recycling Centre
            </a>

        </div>


        <div class = "section">

            <div class = "recycling_center" id = "centersContainer">

                <?php foreach ($centre_record as $row): ?>
                    <?php if ((int)$row['is_deleted'] === 1) continue; ?>

                    <div class="center_list">

                        <div class="center_img"
                            style="background-image:url('../<?php echo $row['file_path']; ?>')">
                        </div>

                        <div class="center_details">

                           

                            <div class = "name_buttons">
                                <div class = "first">
                                    <h3 class = "center_name"><?php echo $row['name']; ?></h3>
                                </div>

                                <div class = "action_button">
                                    <a class="edit" href="editRecyclingCentre.php?centre_id=<?php echo $row['centre_id']; ?>" style = "text-decoration: none;">
                                        <button>Edit</button>
                                    </a>


                                    <a class="delete"
                                        data-id="<?php echo $row['centre_id']; ?>"
                                        data-name="<?php echo htmlspecialchars($row['name']); ?>">
                                            <button type="button">Delete</button>
                                        </a>



                                </div>
                            </div>
                            
                            <div class="row">

                                <div class="info">
                                    <span class="material-symbols-outlined">
                                        location_on
                                    </span>
                                    <?php echo $row['address']; ?>
                                </div>
                                
                                <div class="info">
                                    <span class="material-symbols-outlined">
                                        alarm
                                    </span>
                                    <?php echo $row['operation_hour']; ?>
                                </div>
                                
                            </div>

                            <div class="row">

                                <div class="info">
                                    <span class="material-symbols-outlined">
                                        call
                                    </span>    
                                    <?php echo $row['contact_no']; ?>
                                </div>

                                <div class="info">
                                    <span class="material-symbols-outlined">
                                        mail
                                    </span>    
                                    <?php echo $row['email']; ?>
                                </div>

                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <div class="points">
           <a href = "editRecyclingInfo.php" style = "text-decoration: none;">
                <button class="centre_bar">
                        Search Recycling Centre
                </button>
            </a>

            <div class = "points_conversion_chart">
                <h3>Points Conversion Chart</h3>

                <div class = "points-row">

                    <span style = "font-weight: 700;">Recyclables</span>
                    <strong>Points per KG</strong>

                </div>

                <?php foreach ($recyclable_record as $row): ?>
                    <?php if ((int)$row['is_deleted'] === 1) continue; ?>

                    <div class="points-row">

                        <span><?php echo $row['type']; ?></span>
                        <strong><?php echo $row['points_per_kg']; ?></strong>
    
                    </div>

                <?php endforeach; ?>

                <a href = "editPointsConvertionChart.php" class ="edit_points">
                    Edit
                </a>

            </div>

            <div class="points_calculator">
                <h3>Eco Points Calculator</h3>

                <form id="pointsForm" method="POST">

                    <div class="type_select_section">

                        <label for="type">TYPE OF RECYCLABLES</label>
                        <select id="type" name="type" required>

                            <option value="">Please Select</option>

                            <?php foreach ($recyclable_record as $row): ?>
                                <?php if ((int)$row['is_deleted'] === 1) continue; ?>

                                <option value="<?php echo htmlspecialchars($row['recyclable_id']); ?>">
                                    <?php echo htmlspecialchars($row['type']); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="weight_section">

                        <label for="weight">WEIGHT (KG)</label>
                        <input type="number" id="weight" name="weight" step="0.01" min="0" required>

                    </div>

                    <button type="submit" class="calc-btn">Calculate</button>

                </form>


                <div id="pointsResult" class="points_result" style="display:none;">

                    Estimated Eco Points: <span id="pointsValue" style = "font-weight: 800; font-size: 20px;">0</span><br>
                    Type Of Recyclable  : <span id="recyclableValue">-</span><br>
                    Weight (KG)         : <span id="weightValue">-</span>

                </div>

            </div>
            
        </div>

    </div>


    <div class="overlay" id="deleteOverlay" style="display:none;">
        <div class="modal">
            <div class="modal_header" style="background:#c0392b;">
                <h2>Confirm Delete</h2>
            </div>

            <div class="modal-body">
                <p>Are you sure you want to delete this recycling centre?</p>
                <p id="deleteCentreName" style="font-weight:600; color:#c0392b;"></p>


                <form method="post">
                    <input type="hidden" name="delete_id" id="delete_id">

                    <div class="button_container">
                        <button type="submit" name="confirmDelete" style="background:#c0392b; color:white;">
                            Delete
                        </button>
                        <button type="button" id="cancelDelete">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>






    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const resultBox = document.getElementById("pointsResult");
            const points = document.getElementById("pointsValue");
            const type = document.getElementById("recyclableValue");
            const weight = document.getElementById("weightValue");

            <?php if ($totalPoints !== null): ?>
                points.textContent = "<?php echo $totalPoints; ?>";
                type.textContent = "<?php echo htmlspecialchars($recyclable_name); ?>";
                weight.textContent = "<?php echo $weight; ?>";
                resultBox.style.display = "block";

                
            <?php endif; ?>

            


            const searchInput = document.getElementById("searchInput");
            const centersContainer = document.getElementById("centersContainer");

            searchInput.addEventListener("input", function() {
                const query = this.value;

                const xhr = new XMLHttpRequest();
                xhr.open("GET", "search_centers.php?search=" + encodeURIComponent(query), true);
                xhr.onload = function() {
                    if (this.status === 200) {
                        centersContainer.innerHTML = this.responseText;
                    }
                };
                xhr.send();
            });
        });


        const deleteOverlay = document.getElementById("deleteOverlay");
        const deleteIdInput = document.getElementById("delete_id");
        const deleteNameText = document.getElementById("deleteCentreName");
        const cancelDelete = document.getElementById("cancelDelete");

        document.addEventListener("click", function (e) {
            const deleteBtn = e.target.closest(".delete");
            if (!deleteBtn) return;

            e.preventDefault();

            deleteIdInput.value = deleteBtn.dataset.id;
            deleteNameText.textContent = deleteBtn.dataset.name;

            deleteOverlay.style.display = "flex";
        });

        cancelDelete.addEventListener("click", function () {
            deleteOverlay.style.display = "none";
        });



    </script>


</body>

</html>