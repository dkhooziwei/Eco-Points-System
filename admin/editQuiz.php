<?php 
    require_once "../session.php";
    require_once "../conn.php";
    require_once "../commonFunctions.php";

    if($_SESSION["role"] !== "Admin"){
        header("Location: ../registerLogin/login.php");
        exit();
    }

    if(!isset($_GET["id"])){
        echo "<script>alert('Please select a quiz to edit');
        window.location.href='quizManagement.php'; </script>";
        exit();
    }

    $quiz_id = $_GET["id"];
    $result = mysqli_query($con, "SELECT * FROM quiz WHERE quiz_id = '$quiz_id'");
    $row = mysqli_fetch_assoc($result);

    if(isset($_POST["submit_btn"])){
        $title = $_POST["title"];
        $active_date = $_POST["active_date"];
        $points = $_POST["points"];
        $question = $_POST["question"];
        $option1 = $_POST["option1"];
        $option2 = $_POST["option2"];
        $option3 = $_POST["option3"];
        $option4 = $_POST["option4"];
        $correct_option = $_POST["correct_option"];

        $sql = "UPDATE quiz
                SET title = '$title',
                date_active = '$active_date',
                points = '$points',
                question_text = '$question',
                option1 = '$option1',
                option2 = '$option2',
                option3 = '$option3',
                option4 = '$option4',
                correct_option = '$correct_option'
                WHERE quiz_id = '$quiz_id'";

        if(!mysqli_query($con, $sql)){
            die("Error: ".mysqli_error($con));
        }

        echo "<script>alert('Quiz details saved!');
        window.location.href='editQuiz.php?id=$quiz_id';</script>";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Add New Quiz</title>
    <link href="../commonStyle.css" rel="stylesheet">
    <style>
        input, button, select, textarea {
            font-family: "Nunito Sans", sans-serif;
        }

        .form-wrapper {
            width: 50%;
            padding: 30px;
            display: flex;
            flex-direction: column;
            margin: auto;
            margin-top: 20px;
            gap: 20px;

            background: white;
            border: 2px solid rgba(174, 174, 174, 1);
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(174, 174, 174, 1);
        }

        .form-wrapper h2 {
            margin-top: 0;
            margin-bottom: 0;
        }

        #edit_quiz_form {
            display: flex;
            flex-direction: column;
            width: 87%;
            padding: 25px 35px;
            border: 2px solid rgba(167, 190, 128, 1);
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(167, 190, 128, 1);
            gap: 5px;
        }

        #edit_quiz_form label {
            font-size: 18px;
            font-weight: bold;
            color: rgba(40, 89, 22, 1);
        }

        #edit_quiz_form input {
            box-sizing: border-box;
            width: 100%;
            height: 35px;
            border-radius: 10px;
            padding: 0 5px;
            border: 1px solid rgba(107, 107, 107, 1);
        }

        #edit_quiz_form select {
            box-sizing: border-box;
            width: 100%;
            height: 35px;
            border-radius: 10px;
            padding: 0 5px;
        }

        textarea {
            padding: 5px 5px;
            border-radius: 10px;
        }

        #submit_btn {
            width: 100%;
            height: 40px;
            font-size: 16px;
            font-weight: bold;
            color: rgba(44, 105, 22, 1);
            border-radius: 10px;
            border: 2px solid rgba(44, 105, 22, 1);
            background: rgba(184, 216, 173, 1);
            cursor: pointer;
        }

        #submit_btn:hover {
            background: rgba(152, 214, 132, 1);
        }

        @media (max-width: 600px){
            .form-wrapper {
                width: 90%;
                padding: 15px;
                margin-top: 10px;
            }

            .form-wrapper h2 {
                font-size: 22px;
                text-align: center;
            }

            #edit_quiz_form {
                width: 87%;
                padding: 20px;
                gap: 8px;
            }

            #edit_quiz_form label {
                font-size: 15px;
            }

            #edit_quiz_form input, 
            #edit_quiz_form select {
                width: 95%;
                height: 40px;
                font-size: 14px;
            }

            #submit_btn {
                height: 45px;
                font-size: 15px;
            }
        }

        @media (min-width: 601px) and (max-width: 1000px){
            .form-wrapper {
                width: 70%;
                padding: 25px;
            }

            #edit_quiz_form {
                width: 90%;
                padding: 25px;
            }

            #edit_quiz_form label {
                font-size: 16px;
            }

            #edit_quiz_form input,
            #edit_quiz_form select {
                width: 100%;
                height: 38px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include "adminTaskbar.php"; ?>

    <div class="form-wrapper">
        <h2>Edit Quiz Details</h2>

        <form method="post" id="edit_quiz_form" onsubmit="return confirm_submit();">
            <label for="title">Quiz Title:</label>
            <input type="text" id="title" name="title" placeholder="Enter quiz title" value="<?= $row["title"] ?>" required>
            <br>
            <label for="active_date">Quiz Active Date:</label>
            <input type="date" id="active_date" name="active_date" value="<?= $row["date_active"] ?>" required>
            <br>
            <label for="points">Points Rewarded for Completing This Quiz:</label>
            <input type="number" id="points" name="points" min="1" step="1" placeholder="Enter number of points" value="<?= $row["points"] ?>" required>
            <br>
            <label for="question">Question:</label>
            <textarea id="question" name="question" rows="6" cols="50" placeholder="Enter your question" required><?= $row["question_text"] ?></textarea>
            <br>
            <label for="title">Option 1:</label>
            <input type="text" id="option1" name="option1" placeholder="Enter the first option" value="<?= $row["option1"] ?>" required>
            <br>
            <label for="title">Option 2:</label>
            <input type="text" id="option2" name="option2" placeholder="Enter the second option" value="<?= $row["option2"] ?>" required>
            <br>
            <label for="title">Option 3:</label>
            <input type="text" id="option3" name="option3" placeholder="Enter the third option" value="<?= $row["option3"] ?>" required>
            <br>
            <label for="title">Option 4:</label>
            <input type="text" id="option4" name="option4" placeholder="Enter the fourth option" value="<?= $row["option4"] ?>" required>
            <br>
            <label for="correct_option">Correct Option:</label>
            <select id="correct_option" name ="correct_option" required>
                <option value="" disabled selected>Please select a option</option>
                <option value="Option 1"
                <?php 
                    if($row["correct_option"] === "Option 1"){
                        echo "selected";
                    }
                ?>>Option 1</option>
                <option value="Option 2"
                <?php 
                    if($row["correct_option"] === "Option 2"){
                        echo "selected";
                    }
                ?>>Option 2</option>
                <option value="Option 3"
                <?php 
                    if($row["correct_option"] === "Option 3"){
                        echo "selected";
                    }
                ?>>Option 3</option>
                <option value="Option 4"
                <?php 
                    if($row["correct_option"] === "Option 4"){
                        echo "selected";
                    }
                ?>>Option 4</option>
            </select><br>

            <br><br>
            <button type="submit" id="submit_btn" name="submit_btn">Save</button>
        </form>
    </div>

    <script>
        function confirm_submit(){
            return confirm("Are you sure you want to save these details?");
        }
    </script>
</body>
</html>