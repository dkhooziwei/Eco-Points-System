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
    <title>Admin - Daily Quiz Management</title>
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

        #quiz_management {
            font-size: 28px;
            font-weight: bold;
        }

        .quiz-top {
            width: 100%;
            display: flex;
            gap: 20px;
        }

        .quiz-top input {
            width: 500px;
            height: 40px;
            font-size: 16px;
            font-family: "Nunito Sans", sans-serif;
            padding: 10px;
            border-radius: 10px;
            border: 2px solid #b2b1b1ff;
        }

        .quiz-top input:focus {
            background: #f2f2f2ff;
        }

        .quiz-top button {
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

        .quiz-top button:hover {
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

        #quiz_table {
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
            height: 100%;
        }

        tbody td:last-child {
            border-bottom: none;
        }

        tbody td img {
            width: 30px;
            height: auto;
        }

        tbody td button {
            border: none;
            background: white;
            cursor: pointer;
            transition: 0.3s ease;
            height: auto;
        }

        tbody td button:hover {
            transform: translateY(-3px);
        }

        .buttons {
            display: flex;
            gap: 20px;
            align-items: stretch;
            justify-content: center;
            height: auto;
        }

        @media (max-width: 600px){
            .wrapper {
                width: 90%;
                padding: 15px;
            }

            #quiz_management {
                font-size: 22px;
                text-align: center;
            }

            .quiz-top {
                flex-direction: column;
                gap: 10px;
            }

            .quiz-top input {
                width: 100%;
                height: 38px;
                font-size: 14px;
            }

            .quiz-top button {
                width: 100%;
                margin: 0;
                height: 38px;
                font-size: 14px;
            }

            .table-wrapper {
                width: 90%;
                overflow-x: auto;
            }

            #quiz_table {
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

            #quiz_management {
                font-size: 24px;
            }

            .quiz-top input {
                width: 350px;
                height: 40px;
                font-size: 15px;
            }

            .quiz-top button {
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
        <label id="quiz_management">Daily Quiz Management</label>

        <br>
        <div class="quiz-top">
            <input type="search" id="search_bar" placeholder="Search by Quiz Name">
            <button id="add_quiz_btn">+ Add Quiz</button>
        </div>

        <div class="table-wrapper">
            <table id="quiz_table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Active Date</th>
                        <th>Points</th>
                        <th>Question</th>
                        <th>Option 1</th>
                        <th>Option 2</th>
                        <th>Option 3</th>
                        <th>Option 4</th>
                        <th>Correct Option</th>
                        <th></th>
                    </tr>   
                </thead>

                <tbody>
                    <?php 
                        $sql = "SELECT * FROM quiz WHERE is_deleted = 0";
                        $result = mysqli_query($con, $sql);

                        if(!$result){
                            die("Error: ".mysqli_error($con));
                        }

                        if(mysqli_num_rows($result) === 0){
                            echo "<tr>
                                    <td colspan='10'>No quizzes found.</td>
                                </tr>";
                        }
                        else{
                            while($row = mysqli_fetch_assoc($result)){
                                $quiz_id = $row["quiz_id"];
                                $title = $row["title"];
                                $active_date = $row["date_active"];
                                $points = $row["points"];
                                $question = $row["question_text"];
                                $option1 = $row["option1"];
                                $option2 = $row["option2"];
                                $option3 = $row["option3"];
                                $option4 = $row["option4"];
                                $correct_option = $row["correct_option"];

                                echo "<tr data-id='$quiz_id'>
                                        <td>$title</td>
                                        <td>$active_date</td>
                                        <td>$points</td>
                                        <td>$question</td>
                                        <td>$option1</td>
                                        <td>$option2</td>
                                        <td>$option3</td>
                                        <td>$option4</td>
                                        <td>$correct_option</td>
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
        const rows = document.querySelectorAll("#quiz_table tbody tr");

        const add_quiz_btn = document.getElementById("add_quiz_btn");
        const edit_btns = document.querySelectorAll(".edit_btn");
        const remove_btns = document.querySelectorAll(".remove_btn");

        search_bar.addEventListener("input", () => {
            const search_input = search_bar.value.toLowerCase();

            rows.forEach(row => {
                const quiz_title = row.cells[0].textContent.toLowerCase();

                if(quiz_title.includes(search_input)){
                    row.style.display = "";
                }
                else{
                    row.style.display = "none";
                }
            });
        });

        rows.forEach((row, index) => {
            const quiz_id = row.dataset.id;

            edit_btns[index].addEventListener("click", () => {
                window.open("editQuiz.php?id=" + quiz_id, "_blank");
            });

            remove_btns[index].addEventListener("click", () => {
                if(confirm("Are you sure you want to remove this quiz?")){
                    window.location.href="removeQuiz.php?id=" + quiz_id;
                }
            })
        });

        add_quiz_btn.addEventListener("click", () => {
            window.open("addQuiz.php", "_blank");
        });
    </script>
</body>
</html>