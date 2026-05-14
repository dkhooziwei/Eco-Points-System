<?php
require_once '../session.php';
require_once '../conn.php';
if($_SESSION["role"] !== "Challenger"){
        header("Location: ../registerLogin/login.php");
        exit();
    }
// Rest of your page code
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Challenger - Announcements</title>
    <link rel="stylesheet" href="userA.css">
    <style>
    .mark {
        background-color: blue;
        color: black;
        border-radius: 2px;
    }
    #c1 {
    text-align: center;
    font-size: 32px;
    padding: 30px 15px;
  }
    .box {
        width: 92%;
        height: auto;
        background-color: #C1D95C;
        margin:10px auto;
        padding:15px;
        box-sizing:border-box;
        border-radius:10px;
        border:2px solid #336A29;
        display: block;
        font-family: "Josefin Sans", sans-serif;
        transition: opacity 0.3s ease;
    }
.announcement-img img {
        width: 100%;
        max-width: 400px;
        height: auto;
        border-radius: 8px;
        margin: 15px 0;
        display: block;
        border: 1px solid #ddd;
    }
    @media (max-width: 600px){
      .searchForm {
        margin: 5px;
      }
.announcement-img img {
            max-width: 100%;
            margin: 10px auto;
        }
}
    </style>
</head>
<body>
    <?php include "challengerTaskbar.php"; ?>
    <div id="c1">
        <p>Notifications</p>
    </div>
    <form id="searchForm" onsubmit="event.preventDefault(); filterResults();">
        <input type="search" id="searchInput" placeholder="Search..">
        <button type="button" onclick="filterResults()">Search</button>
    </form>
<?php
// include("../conn.php");

$search_key = "";
if(isset($_POST['searchBtn'])){
    $search_key = $_POST['search_key'];
}

$sql = "SELECT * FROM announcement ORDER BY created_at DESC";
$result = mysqli_query($con, $sql);

while ($row = mysqli_fetch_array($result)) {
    echo '<div class="box">';
    echo '<h3>' . $row['ann_id'] . '</h3>';
    echo '<p class="title-text">' . htmlspecialchars($row['title']) . '</p>';
    echo '<p>' . $row['content'] . '</p>';

    if (!empty($row['file_path'])) {
        echo '<div class="announcement-img">';
        // Added "../" before the file path variable
        echo '<img src="../' . htmlspecialchars($row['file_path']) . '" alt="Announcement Image" style="height:auto;">';
        echo '</div>';
    }

    echo '<p>' . $row['created_at'] . '</p>';
    echo '</div>';
}
?>

<script>
function filterResults() {
    const inputField = document.getElementById('searchInput');
    const filter = inputField.value.toLowerCase();
    const boxes = document.querySelectorAll('.box');

    boxes.forEach(box => {
        const titleElement = box.querySelector('.title-text');
        if (!titleElement) return;

        if (!box.dataset.originalText) {
            box.dataset.originalText = titleElement.textContent;
        }
        
        const originalText = box.dataset.originalText;

        if (originalText.toLowerCase().includes(filter)) {
            box.style.display = ""; 
            
            if (filter !== "") {
                const escapedInput = filter.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                const regex = new RegExp(`(${escapedInput})`, 'gi');
                titleElement.innerHTML = originalText.replace(regex, '<mark>$1</mark>');
            } else {
                titleElement.innerHTML = originalText;
            }
        } else {
            box.style.display = "none"; 
        }
    });
}
</script>
</body>
</html>