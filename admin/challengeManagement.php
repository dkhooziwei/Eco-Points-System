<?php 
    require_once "../session.php";
    require_once "../conn.php";

    if($_SESSION["role"] !== "Admin"){
        header("Location: ../registerLogin/login.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Challenge Management</title>
    <link href="../commonStyle.css" rel="stylesheet">
    <style>
        .wrapper {
            width: 90%;
            display: flex;
            flex-direction: column;
            margin: auto;
            margin-top: 20px;
            padding: 30px;
            background: white;
            border: 2px solid #b2b1b1ff;
            box-shadow: 0 0 8px #b2b1b1ff;
            border-radius: 10px;
        }

        #challenge_management {
            font-size: 28px;
            font-weight: bold;
        }

        .challenge-top {
            width: 100%;
            display: flex;
            gap: 20px;
        }

        .challenge-top input {
            width: 500px;
            height: 40px;
            font-size: 16px;
            font-family: "Nunito Sans", sans-serif;
            padding: 10px;
            border-radius: 10px;
            border: 2px solid #b2b1b1ff;
        }

        .challenge-top input:focus {
            background: #f2f2f2ff;
        }

        .challenge-top button {
            margin-left: auto;
            margin-right: 30px;
            width: 170px;
            height: 40px;
            font-size: 16px;
            font-family: "Nunito Sans", sans-serif;
            font-weight: bold;
            color: #5d8d29ff;
            border-radius: 10px;
            border: 2px solid #5d8d29ff;
            background: #defdbdff;
            cursor: pointer;
        }

        .challenge-top button:hover {
            background: #c0f18bff;
        }

        .table-wrapper {
            width: 97%;
            padding: 15px;
            margin-top: 20px;
            border-radius: 10px;
            box-shadow: 0 0 8px #bfbfbeff;
            overflow-x: auto;
        }

        #challenge_table {
            width: 100%;
        }

        thead th {
            border: 2px solid #57a62aff; 
            font-size: 18px;
            color: white;
            padding: 8px;
            background: linear-gradient(to bottom, #367113ff, #57a62aff);
        }

        thead th:first-child {
            border-top-left-radius: 10px;
        }

        thead th:last-child {
            border-top-right-radius: 10px;
        }

        tbody tr {
            background: white;
            transition: 0.3s ease;
        }

        tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 3px #7db75bff;
        }

        tbody td {
            border-bottom: 2px solid #7db75bff; 
            padding: 10px;
            font-size: 18px;
        }

        tbody td:last-child {
            border-bottom: none;
        }

        tbody td img {
            width: 30px;
        }

        tbody td button {
            border: none;
            background: white;
            cursor: pointer;
            transition: 0.3s ease;
        }

        tbody td button:hover {
            transform: translateY(-3px);
        }

        .buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }

        @media (max-width: 600px){
            .wrapper {
                width: 90%;
                padding: 15px;
            }

            #challenge_management {
                font-size: 22px;
                text-align: center;
            }

            .challenge-top {
                flex-direction: column;
                gap: 10px;
            }

            .challenge-top input {
                width: 100%;
                height: 38px;
                font-size: 14px;
            }

            .challenge-top button {
                width: 100%;
                margin: 0;
                height: 38px;
                font-size: 14px;
            }

            .table-wrapper {
                width: 90%;
                overflow-x: auto;
            }

            #challenge_table {
                min-width: 650px;
            }

            .buttons {
                margin-top: 10px;
                gap: 5px;
            }

            thead th,
            tbody td {
                font-size: 14px;
                padding: 6px;
            }

            tbody td img {
                width: 24px;
            }
        }

        @media (min-width: 601px) and (max-width: 1000px){
            .wrapper {
                width: 95%;
                padding: 20px;
            }

            #challenge_management {
                font-size: 24px;
            }

            .challenge-top input {
                width: 350px;
                height: 40px;
                font-size: 15px;
            }

            .challenge-top button {
                width: 150px;
                height: 40px;
                font-size: 15px;
                margin-right: 0;
            }

            .buttons {
                align-items: center;
                margin-top: 15px;
                gap: 5px;
            }

            thead th,
            tbody td {
                font-size: 16px;
                padding: 8px;
            }

            tbody td img {
                width: 26px;
            }
        }
    </style>
</head>
<body>
    <?php include "adminTaskbar.php"; ?>

    <div class="wrapper">
        <label id="challenge_management">Challenge Management</label>

        <br>
        <div class="challenge-top">
            <input type="search" id="search_bar" placeholder="Search by Challenge Name">
            <button id="add_challenge">+ Add Challenge</button>
        </div>

        <div class="table-wrapper">
            <table id="challenge_table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Points</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Achievement</th>
                        <th>Ach. Type</th>
                        <th></th>
                    </tr>   
                </thead>

                <tbody>
                    <?php 
                        $sql = "SELECT * FROM challenge WHERE is_deleted = 0";
                        $result = mysqli_query($con, $sql);

                        if(!$result){
                            die("Error: ".mysqli_error($con));
                        }

                        if(mysqli_num_rows($result) === 0){
                            echo "<tr>
                                    <td colspan='6'>No challenges found.</td>
                                </tr>";
                        }
                        else{
                            while($row = mysqli_fetch_assoc($result)){
                                $challenge_id = $row["challenge_id"];
                                $title = $row["name"];
                                $type = $row["type"];
                                $points = $row["points"];
                                $start_date = $row["start_date"];
                                $end_date = $row["end_date"];

                                if(!$end_date){
                                    $end_date = "N/A";
                                }

                                $get_ach_sql = "SELECT * FROM achievement WHERE challenge_id = '$challenge_id'";
                                $ach = mysqli_query($con, $get_ach_sql);
                                if(!$ach){
                                    die("Error: ".mysqli_error($con));
                                }

                                $achievement = mysqli_fetch_assoc($ach);
                                $ach_id = $achievement["ach_id"];
                                $ach_name = $achievement["name"];
                                $ach_type = $achievement["type"];

                                echo "<tr data-id='$challenge_id' data-ach_id='$ach_id'>
                                        <td>$title</td>
                                        <td>$type</td>
                                        <td>$points</td>
                                        <td>$start_date</td>
                                        <td>$end_date</td>
                                        <td>$ach_name</td>
                                        <td>$ach_type</td>
                                        <td class='buttons'>
                                            <button class='edit_btn'>
                                                <img src='../images/editRemoveIcons/edit.png' alt='edit'>
                                            </button>
                                            <button class='remove_btn'>
                                                <img src='../images/editRemoveIcons/delete.png' alt='remove'>
                                            </button>
                                        </td>
                                    </tr>";
                            }
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const search_bar = document.getElementById("search_bar");
        const rows = document.querySelectorAll("#challenge_table tbody tr");

        const add_challenge_btn = document.getElementById("add_challenge");
        const edit_btns = document.querySelectorAll(".edit_btn");
        const remove_btns = document.querySelectorAll(".remove_btn");

        search_bar.addEventListener("input", () => {
            const search_input = search_bar.value.toLowerCase();

            rows.forEach(row => {
                const challenge_name = row.cells[0].textContent.toLowerCase();

                if(challenge_name.includes(search_input)){
                    row.style.display = "";
                }
                else{
                    row.style.display = "none";
                }
            });
        });

        rows.forEach((row, index) => {
            const challenge_id = row.dataset.id;
            const ach_id = row.dataset.ach_id;

            edit_btns[index].addEventListener("click", () => {
                window.open("editChallenge.php?id=" + challenge_id + "&id2=" + ach_id, "_blank");
            });

            remove_btns[index].addEventListener("click", () => {
                if(confirm("Are you sure you want to remove this challenge?")){
                    window.location.href="removeChallenge.php?id=" + challenge_id + "&id2=" + ach_id;
                }
            })
        });

        add_challenge_btn.addEventListener("click", () => {
            window.open("addChallenge.php", "_blank");
        });
    </script>
</body>
</html>