<?php
    include('../conn.php');
    include('../commonFunctions.php');
    include('../session.php');

    if($_SESSION["role"] !== "Admin"){
        header("Location: ../registerLogin/login.php");
        exit();
    }





    $data = [];
    $result = mysqli_query($con, "SELECT * FROM recyclable ORDER BY type ASC");
    while ($row = mysqli_fetch_assoc($result)) {
        $data[$row['recyclable_id']] = $row;
    }

    //save data edit eh
    if (isset($_POST['save'])){
        foreach($_POST['rows'] as $key => $row){
            $type = mysqli_real_escape_string($con, $row['type']);
            $points = intval($row['points']);

            $sql_save = "UPDATE recyclable SET type = '$type', points_per_kg = $points
                        WHERE recyclable_id = '$key'";

            mysqli_query($con, $sql_save);
        }

        header("Location: editPointsConvertionChart.php");
        exit;

    }

    //delete
    if(isset($_POST['confirmDelete'])){
        $id = mysqli_real_escape_string($con, $_POST['delete_id']);

        $sql_delete = "UPDATE recyclable SET is_deleted = 1
                        WHERE recyclable_id = '$id'";

        mysqli_query($con, $sql_delete);

        header("Location: editPointsConvertionChart.php");
        exit;
    }

    // add new
    if(isset($_POST['add'])){
        $type = mysqli_real_escape_string($con, $_POST['type']);
        $points = intval($_POST['points']);
        $new_id = get_next_id($con, 'recyclable', 'R', 'recyclable_id');

        $sql_add = "INSERT INTO recyclable(recyclable_id, type, points_per_kg)
        VALUES ('$new_id', '$type', $points)";

        mysqli_query($con, $sql_add);

        header("Location: editPointsConvertionChart.php");
        exit;
    }




?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Points Convertion Chart</title>
    <link href="../commonStyle.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <style>

        *{
            box-sizing:border-box;
            margin: 0;
            padding: 0;
        }

        .page {
            max-width: 900px;
            margin:40px auto;
            background: #ffffff;
            border-radius: 14px;
            padding: 30px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        }
        h1{
            color: #335a29;
        }

        .table {
            margin-top: 20px;
        }

        .table .content {
            padding: 8px 0;
        }

        .table .row {
            display: grid;
            grid-template-columns: 2.0fr 2.0fr auto;
            gap: 16px;
            align-items: center;
        }


        .table .col {
            padding: 0 20px;
        }

        .table .header {
            padding-bottom: 10px;
            margin-bottom: 12px;
            border-bottom: 2px solid #e5e7eb;
            text-align: left;
        }

        .table .label {
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .table input[type="text"],
        .table input[type="number"],
        .add_row input[type="text"],
        .add_row input[type="number"] {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .table input:focus {
            outline: none;
            border-color: rgba(51, 90, 41, 1);
        }

        button.delete {
            background: #c0392b;
            color: #ffffff;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
        }

        button.delete:hover {
            background: #a93226;
        }

        .save_cancel {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 20px;
        }

        .button._save {
            background: #498428;
            color: #ffffff;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }

        .button._save:hover {
            background: #335a29;
        }

        .button_cancel {
            background: #f3f4f6;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
        }

        .button_cancel:hover{
            background: #d6d6d6ff;
        }

        .add_section {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .add_title {
            font-weight: 600;
            margin-bottom: 12px;
            color: #374151;
        }





        .button_add {
            background: #498428;
            color: #ffffff;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }

        .button_add:hover {
            background: #335a29;
        }

        .centre_bar {
            display: inline-block;
            height: 38px;
            line-height: 38px;
            padding: 0 14px;
            border-radius: 10px;
            border: none;
            background: #498428;
            color: white;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            text-align: center;
            text-decoration: none;
            margin-bottom: 0px;
            min-width: 12%;
        }

        .centre_bar:hover{
            background: #335a29;
        }

        .add_row {
            display: flex; 
            gap: 20px;               
            align-items: center;
        }

        .add_row input {
            flex: 1; 
        }

        .add_row button {
            flex-shrink: 0;  
        }

        @media (max-width: 650px) {

     
            .table .header {
                display: none;
            }

            .table .row {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .table .col {
                padding: 0;
                display: grid;
                grid-template-columns: 140px 1fr;
                align-items: center;
                gap: 10px;
            }

            .table .col:nth-child(1)::before {
                content: "Type";
                font-size: 13px;
                font-weight: 600;
                color: #6b7280;
                text-transform: uppercase;
            }

            .table .col:nth-child(2)::before {
                content: "Points per KG";
                font-size: 13px;
                font-weight: 600;
                color: #6b7280;
                text-transform: uppercase;
            }

            .table .col:nth-child(3) {
                grid-template-columns: 1fr;
            }

            .table .col:nth-child(3)::before {
                content: "";
            }

            button.delete {
                width: 100%;
            }

            .save_cancel {
                flex-direction: row;
            }

            .save_cancel button {
                flex: 1;
            }

            .add_row {
                flex-direction: column;
                gap: 12px;
            }

            .add_row input,
            .add_row button {
                width: 100%;
            }

            .table input[type="text"],
            .table input[type="number"] {
                padding-right: 20px; 
                margin-right: 16px;  
                box-sizing: border-box;
            }

            .table .content {
                padding-bottom: 24px;
                margin-bottom: 24px;
                border-bottom: 1px solid #e5e7eb;
            }

            .table .content:last-of-type {
                border-bottom: none;
                margin-bottom: 0;
                padding-bottom: 0;
            }

            .add_section {
                    border-top: 2px solid #374151;
                }

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



    </style>
</head>
<body>

    <?php include "adminTaskbar.php"; ?>
    
    <div class = "page">
        <a href = "editRecyclingInfo.php" class="centre_bar" style = "text-decoration: none;">
            Back        
        </a>

        <h1>Edit Points Convertion Chart</h1>

        <form method = "post">
            <div class = "table">
                <div class="content header">
                    <div class="row">
                        <div class="col label">Type</div>
                        <div class="col label">Points per KG</div>
                        <div class="col label"></div>
                    </div>
                </div>

                <?php
                    foreach($data as $key => $value): 
                ?>
                <?php if ((int)$value['is_deleted'] === 1) continue; ?>
                <div class = "content">
                    <div class = "row">
                        <div class="col">
                            <input type="text"
                                name="rows[<?= $key ?>][type]"
                                value="<?= htmlspecialchars($value['type']) ?>">
                        </div>

                        <div class="col">
                            <input type="number"
                                name="rows[<?= $key ?>][points]"
                                value="<?= $value['points_per_kg'] ?>">
                        </div>

                        <div class="col">
                            <button type="button"
                                    class="delete"
                                    data-id="<?= $key ?>"
                                    data-name="<?= htmlspecialchars($value['type']) ?>">
                                Delete
                            </button>

                        </div>
                    </div>

                </div>

                <?php endforeach; ?>
            </div>

            <div class = "save_cancel">
                <button class = "button _save" name = "save"> Save All</button>
                <button type = "reset" class = "button_cancel">Cancel Changes</button>
            </div>
        </form>

        <form method = "post">
            <div class = "add_section">
                <div class = "add_title">
                    Add New Recyclable Type
                </div>

                <div class = "add_row">
                    <input type = "text" name = "type" placeholder = "Type" required>
                    <input type = "number" name = "points" placeholder = "Points per KG" required>
                    <button class = "button_add" name = "add">Add New</button>

                </div>


            </div>

        </form>
        <div class="overlay" id="deleteOverlay" style="display:none;">
            <div class="modal">
                <div class="modal_header" style="background:#c0392b;">
                    <h2>Confirm Delete</h2>
                </div>

                <div class="modal-body">
                    <p>Are you sure you want to delete this recyclable type?</p>
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
    </div>
<script>
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