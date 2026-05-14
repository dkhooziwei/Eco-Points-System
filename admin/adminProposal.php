<?php
require_once "../session.php";
require_once "../conn.php";

if($_SESSION["role"] !=="Admin"){
    header("Location: ../registerLogin/login.php");
    exit();
}


// Handle form status update
if (isset($_POST['update_status'])) {
    $proposal_id = mysqli_real_escape_string($con, $_POST['proposal_id']);
    $new_status = mysqli_real_escape_string($con, $_POST['new_status']);

    $sql = "UPDATE proposal SET status = '$new_status' WHERE proposal_id = '$proposal_id'";
    mysqli_query($con, $sql);

    // Redirect to prevent form resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


// Fetch proposals
$sql = "SELECT p.proposal_id, p.file_path, p.date, p.status, 
        u.name as partner_name 
        FROM proposal p
        JOIN user u ON p.partner_id = u.user_id
        ORDER BY p.date DESC";

$result = mysqli_query($con, $sql);
if (!$result) die("Query Failed: " . mysqli_error($con));

// Helper function for status class
function getStatusClass($status) {
    $classes = [
        'Approved' => 'status-approved',
        'Rejected' => 'status-rejected',
        'Pending' => 'status-pending'
    ];
    return $classes[$status] ?? 'status-pending';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposal Management</title>
    <link href="../commonStyle.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Base Styles - Green Theme */
        .container-proposal {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.15);
            margin: 30px 20px;
            border: 1px solid #e8f5e9;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 15px;
            flex-wrap: wrap;
        }

        .section-header h3 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            color: #2e7d32;
        }

        .category-filter {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .category-filter label {
            color: #666;
            font-size: 14px;
        }

        .category-filter select {
            padding: 10px 35px 10px 15px;
            border: 2px solid #c8e6c9;
            border-radius: 25px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
            color: #333;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%234CAF50' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
        }

        .category-filter select:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.15);
        }

        /* Table */
        .proposal-table {
            width: 100%;
            border-collapse: collapse;
        }

        .proposal-table th {
            background: #f1f8e9;
            padding: 16px 15px;
            text-align: left;
            border-bottom: 2px solid #c8e6c9;
            font-weight: 600;
            color: #2e7d32;
            font-size: 14px;
        }

        .proposal-table td {
            padding: 16px 15px;
            border-bottom: 1px solid #e8f5e9;
            color: #555;
            font-size: 14px;
        }

        .proposal-table tr:hover {
            background: #f9fdf9;
        }

        /* Proposal ID */
        .proposal-table td:first-child {
            color: #7b7b7b;
            font-weight: 500;
        }

        /* File link */
        .proposal-table a {
            color: #5c6bc0;
            text-decoration: none;
            font-weight: 500;
        }

        .proposal-table a:hover {
            text-decoration: underline;
            color: #3949ab;
        }

        /* Status Dropdown - Clean style */
        .status-select {
            padding: 8px 32px 8px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            min-width: 110px;
            appearance: none;
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 10px;
            transition: all 0.2s;
        }

        .status-select:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
        }

        /* Status colors - Applied to select element only */
        .status-pending { 
            background-color: #fff8e1; 
            color: #f57c00; 
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 12 12'%3E%3Cpath fill='%23f57c00' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        }

        .status-approved { 
            background-color: #e8f5e9; 
            color: #388e3c; 
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 12 12'%3E%3Cpath fill='%23388e3c' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        }

        .status-rejected { 
            background-color: #ffebee; 
            color: #d32f2f; 
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 12 12'%3E%3Cpath fill='%23d32f2f' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        }

        /* Dropdown options - Clean white background */
        .status-select option {
            background: white;
            color: #333;
            font-weight: normal;
            padding: 10px;
        }

        /* No data row */
        .no-data {
            text-align: center;
            color: #999;
            padding: 40px 15px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container-proposal { 
                margin: 15px; 
                padding: 15px; 
                border-radius: 15px;
            }
            .section-header { 
                flex-direction: column; 
                align-items: flex-start; 
            }
            .section-header h3 {
                font-size: 20px;
            }
            .category-filter { 
                width: 100%; 
            }
            .category-filter select {
                flex: 1;
            }
            .proposal-table { 
                display: block; 
                overflow-x: auto; 
            }
            .proposal-table th,
            .proposal-table td {
                padding: 12px 10px;
                font-size: 13px;
            }
        }

        @media (min-width: 1200px) {
            .container-proposal { 
                max-width: 1200px; 
                margin: 40px auto; 
                padding: 30px; 
            }
        }
    </style>
</head>

<body>
    <?php include "adminTaskbar.php"; ?>

    <div class="container-proposal">
        <div class="section-header">
            <h3>Proposal Management</h3>
            <div class="category-filter">
                <label for="statusFilter">Status →</label>
                <select id="statusFilter">
                    <option value="">All</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
        </div>

        <table class="proposal-table">
            <thead>
                <tr>
                    <th>Proposal ID</th>
                    <th>Proposal File</th>
                    <th>Partner Name</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr data-status="<?= htmlspecialchars($row['status']) ?>">
                            <td><?= htmlspecialchars($row['proposal_id']) ?></td>
                            <td>
                                <?php if (!empty($row['file_path'])): ?>
                                    <a href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank">
                                        <?= htmlspecialchars(basename($row['file_path'])) ?>
                                    </a>
                                <?php else: ?>
                                    No file
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['partner_name']) ?></td>
                            <td><?= htmlspecialchars($row['date']) ?></td>
                            <td>
                                <form method="POST" style="margin:0;"> 
                                    <input type="hidden" name="update_status" value="1">
                                    <input type="hidden" name="proposal_id" value="<?= htmlspecialchars($row['proposal_id']) ?>">
                                    <select name="new_status" class="status-select <?= getStatusClass($row['status']) ?>"
                                            onchange="updateStatusStyle(this); this.form.submit();">
                                        <option value="Pending" <?= $row['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="Approved" <?= $row['status'] == 'Approved' ? 'selected' : '' ?>>Approved</option>
                                        <option value="Rejected" <?= $row['status'] == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="no-data">No proposals found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Update status dropdown style when changed
        function updateStatusStyle(selectElement) {
            selectElement.classList.remove('status-pending', 'status-approved', 'status-rejected');
            selectElement.classList.add('status-' + selectElement.value.toLowerCase());
        }

        // Filter proposals by status
        const statusFilter = document.getElementById('statusFilter');
        const tableRows = document.querySelectorAll('.proposal-table tbody tr');

        statusFilter.addEventListener('change', (e) => {
            const filterValue = e.target.value;
            
            tableRows.forEach(row => {
                const rowStatus = row.dataset.status;
                row.style.display = (!filterValue || rowStatus === filterValue) ? '' : 'none';
            });
        });
    </script>
</body>
</html>