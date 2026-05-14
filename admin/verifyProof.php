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
    <title>Admin - Challenge Proofs</title>
    <link href="../commonStyle.css" rel="stylesheet">
    <style>        
        .proof-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
        }

        #challenge_proofs {
            font-size: 30px;
            font-weight: bold;
        }

        .proof-right {
            margin-left: auto;
            margin-right: 3%;
        }

        #filter_status {
            width: 150px;
            height: 35px;
            font-size: 16px;
            font-weight: bold;
            font-family: "Nunito Sans", sans-serif;
            border: 2px solid #5b943aff;
            border-radius: 8px;
            padding: 5px;
        }

        #filter_status:hover {
            background: #d3e9c7ff;
        }

        #proofs_table {
            margin: auto;
            padding: 30px;
            width: 95%;
            border: 1px solid #b6b6b6ff; 
            border-radius: 10px;
            box-shadow: 0 0 10px #a1a1a1ff;
            background: white;
            border-collapse: separate;
            border-spacing: 1;
            overflow: hidden;
        }

        thead th {
            border: 2px solid #57a62aff; 
            font-size: 20px;
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
            cursor: pointer;
            transition: 0.3s ease;
            height: 70px;
        }

        tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 3px #7db75bff;
        }

        .status {
            width: 80px;
            padding: 5px 12px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 16px;
            display: inline-block;
            text-align: center;
        }

        .status.pending {
            background: #f5f77aff;
            box-shadow: 0 0 3px #f5f77aff;
        }

        .status.approved {
            background: #a3e38cff;
            box-shadow: 0 0 3px #a3e38cff;
        }

        .status.rejected {
            background: #f6ababff;
            box-shadow: 0 0 3px #f6ababff;
        }

        #no_records {
            cursor: default;
        }

        #no_records:hover {
            background: linear-gradient(to bottom, #d6e5cdff, #ffffffff);
        }

        tbody td {
            border-bottom: 2px solid #7db75bff; 
            padding: 10px;
            font-size: 18px;
        }

        @media (max-width: 600px){
            .proof-top {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            #challenge_proofs{
                font-size: 22px;
            }

            .proof-right{
                margin-left: 0;
                margin-right: 0;
                width: 100%;
            }

            #filter_status {
                font-size: 14px;
                width: 100%;
            }

            .table-wrapper {
                width: 100%;
                overflow-x: auto;
            }

            #proofs_table {
                width: 100%;
                padding: 10px;
            }

            #proofs_table th:nth-child(4),
            #proofs_table td:nth-child(4) {
                display: none;
            }

            thead th {
                font-size: 14px;
                padding: 5px;
            }

            tbody td {
                font-size: 12px;
                padding: 8px;
                line-height: 1.4;
            }

            .status {
                width: 60px;
                font-size: 12px;
            }
        }

        @media (min-width: 601px) and (max-width: 1000px){
            #proofs_table {
                padding: 15px;
            }

            #filter_status {
                width: 200px;
            }

            thead th {
                font-size: 18px;
            }

            tbody td {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <?php 
        include "adminTaskbar.php";
    ?>

    <div class="proof-top">
        <label id="challenge_proofs">Challenge Proofs</label>
        <div class="proof-right">
            <select id="filter_status">
                <option value="All">All</option>
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
                <option value="Rejected">Rejected</option>
            </select>
        </div>
    </div>

    <?php 
        $sql = "SELECT * FROM proof ORDER BY proof_id DESC";
        $result = mysqli_query($con, $sql);
        if(!$result){
            die("Error: ".mysqli_error($con));
        }

        if(mysqli_num_rows($result) == 0){
            echo "<p>No records found.</p>";
        }
        else{
            echo "<div class='table-wrapper'>";
            echo "<table id='proofs_table'>
                    <thead>
                        <tr>
                            <th>Proof ID</th>
                            <th>Challenger ID</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>";
                        while($row = mysqli_fetch_assoc($result)){
                        $proof_id = $row["proof_id"];
                        $challenger_id = $row["challenger_id"];
                        $date = $row["date"];
                        $time = $row["time"];
                        $status = $row["status"];

                        echo "<tr data-status=$status data-id=$proof_id>
                                <td>$proof_id</td>
                                <td>$challenger_id</td>
                                <td>$date</td>
                                <td>$time</td>
                                <td>
                                    <span class='status ".strtolower($status)."'>$status</span>
                                </td>
                            </tr>";
                        }
                        echo "<tr id='no_records' style='display:none;'>
                                <td colspan='5'>No records found.</td>
                            </tr>";
                echo "</tbody>
                </table>";
            echo "</div>";
        }
    ?>
    
    <script>
        const filter_status = document.getElementById("filter_status");
        const rows = document.querySelectorAll("#proofs_table tbody tr");
        const no_records = document.getElementById("no_records");

        filter_status.addEventListener("change", () => {
            const status = filter_status.value;
            let row_found = false;

            rows.forEach(row => {
                if(status === "All" || row.dataset.status === status){
                    row_found = true;
                    row.style.display = "";
                }
                else{
                    row.style.display = "none";
                }
            });

            if(row_found){
                no_records.style.display = "none"; //reset the header and clear the message if there are records
            }
            else {
                no_records.style.display = ""; //hide the header and show message if no records found
            }
        });

        rows.forEach(row => {
            if(row.id === "no_records"){
                return;
            }

            row.addEventListener("click", () => {
                const proof_id = row.dataset.id;
                window.open("viewProof.php?id=" + proof_id, "_blank");
            });        
        })
    </script>
</body>
</html>