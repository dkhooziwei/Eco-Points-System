<?php
    include('../conn.php');
    include('../session.php');

    if($_SESSION["role"] !== "Challenger"){
        header("Location: ../registerLogin/login.php");
        exit();
    }

    $search = $_GET['search'] ?? '';
    $search = mysqli_real_escape_string($con, $search);

    $sql_centre = "SELECT * FROM recycling_centre WHERE is_deleted = 0";

    if (!empty($search)) {
        $sql_centre .= " AND name LIKE '%$search%'";
    }

    $sql_centre .= " ORDER BY name";



    $result_centre = mysqli_query($con, $sql_centre);
    if (!$result_centre) 
        die("SQL Error: " . mysqli_error($con));

    if (mysqli_num_rows($result_centre) > 0) {
        while ($row = mysqli_fetch_assoc($result_centre)) {
?>
    <div class="center_list">

        <div class="center_img"
            style="background-image:url('../<?php echo $row['file_path']; ?>')">
        </div>

        <div class="center_details">

            <div class="first">
                <h3><?php echo $row['name']; ?></h3>
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
<?php
    }
} else {
    echo "<p>No results found.</p>";
}
?>
