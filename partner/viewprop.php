<?php
require_once '../session.php';
require_once '../conn.php';
if($_SESSION["role"] !== "Partner"){
        header("Location: ../registerLogin/login.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner - View Previous Proposals Status</title>
    <link rel="stylesheet" href="viewpprop.css">    
</head>
<body>
    <?php include "partnerTaskbar.php"; 
    // include("../conn.php");
    $sql_approved = "SELECT COUNT(*) as total FROM proposal WHERE status = 'Approved'";
    $res_approved = mysqli_query($con, $sql_approved);
    $data_approved = mysqli_fetch_assoc($res_approved);
    $count_approved = $data_approved['total'];


    $sql_pending = "SELECT COUNT(*) as total FROM proposal WHERE status = 'Pending'";
    $res_pending = mysqli_query($con, $sql_pending);
    $data_pending = mysqli_fetch_assoc($res_pending);
    $count_pending = $data_pending['total'];
    ?>
    <div id="c1">
        <p>My Proposals</p>
    </div>
    <div id="c2">
        <table style="width:30%;">
            <tr>
                <td><strong>Approved:</strong> <?php echo $count_approved; ?></td>
                <td><strong>Pending:</strong> <?php echo $count_pending; ?></td>
            </tr>
        </table>
    </div>
<div id="c3">
    <p>My Submissions</p>
</div>
<div id="c4">
<?php
$sql = "SELECT * FROM proposal";
$result = mysqli_query($con, $sql);

while ($row = mysqli_fetch_array($result)) {
    echo '<div class="box">';

    echo '<h3 class="proposal-title" 
            style="cursor:pointer; color:#C1D95C; text-decoration:underline;"
            data-id="' . $row['proposal_id'] . '" 
            data-title="' . htmlspecialchars($row['title']) . '" 
            data-date="' . $row['date'] . '" 
            data-desc="' . htmlspecialchars($row['description']) . '" 
            data-status="' . $row['status'] . '">'. $row['title'] . '</h3>';
            
    echo '<p style="color:#C1D95C">' . $row['proposal_id'] . '</p>';
    echo '<p style="color:#C1D95C">' . $row['date'] . '</p>';
    echo '</div>';
}
?>
</div>
<div id="myModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2 id="modalTitle"></h2>
    <hr>
    <p><strong>ID:</strong> <span id="modalId"></span></p>
    <p><strong>Date:</strong> <span id="modalDate"></span></p>
    <p><strong>Status:</strong> <span id="modalStatus"></span></p>
    <p><strong>Description:</strong></p>
    <p id="modalDesc"></p>
  </div>
</div>

<script>
const modal = document.getElementById("myModal");
const span = document.getElementsByClassName("close")[0];


document.querySelectorAll('.proposal-title').forEach(item => {
  item.addEventListener('click', event => {

    const id = item.getAttribute('data-id');
    const title = item.getAttribute('data-title');
    const date = item.getAttribute('data-date');
    const desc = item.getAttribute('data-desc');
    const status = item.getAttribute('data-status');


    document.getElementById('modalTitle').innerText = title;
    document.getElementById('modalId').innerText = id;
    document.getElementById('modalDate').innerText = date;
    document.getElementById('modalStatus').innerText = status;
    document.getElementById('modalDesc').innerText = desc;


    modal.style.display = "block";
  });
});


span.onclick = function() { modal.style.display = "none"; }


window.onclick = function(event) {
  if (event.target == modal) { modal.style.display = "none"; }
}
</script>
</body>
</html>