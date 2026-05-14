<?php
    require_once "../session.php";
    require_once "../conn.php";

    // Get selected month/year or default to current
    $selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
    $selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

    // Get total weight for selected month/year
    $totalQuery = "SELECT COALESCE(SUM(weight_kg), 0) as total
                    FROM recycling_history
                    WHERE MONTH(date) = ? AND YEAR(date) = ?";

    $stmt = mysqli_prepare($con, $totalQuery);
    mysqli_stmt_bind_param($stmt, "ii", $selectedMonth, $selectedYear);
    mysqli_stmt_execute($stmt);
    $totalResult = mysqli_stmt_get_result($stmt);
    $totalRow = mysqli_fetch_assoc($totalResult);
    $totalWeight = number_format((float)$totalRow['total'], 1);

    // Get weight per recyclable type for chart
    $chartQuery = "SELECT r.type, COALESCE(SUM(rh.weight_kg), 0) as total_weight
                    FROM recyclable r
                    LEFT JOIN recycling_history rh ON r.recyclable_id = rh.recyclable_id
                        AND MONTH(rh.date) = ? AND YEAR(rh.date) = ?
                    GROUP BY r.recyclable_id, r.type
                    ORDER BY r.type";

    $stmt = mysqli_prepare($con, $chartQuery);
    mysqli_stmt_bind_param($stmt, "ii", $selectedMonth, $selectedYear);
    mysqli_stmt_execute($stmt);
    $chartResult = mysqli_stmt_get_result($stmt);

    // Build arrays for chart
    $labels = [];
    $data = [];
    while ($row = mysqli_fetch_assoc($chartResult)) {
        $labels[] = $row['type'];
        $data[] = (float)$row['total_weight'];
    }

    // Generate colors dynamically based on number of types
    $colors = [
        ['rgba(255, 99, 132, 0.6)', 'rgba(255, 99, 132, 1)'],
        ['rgba(54, 162, 235, 0.6)', 'rgba(54, 162, 235, 1)'],
        ['rgba(255, 206, 86, 0.6)', 'rgba(255, 206, 86, 1)'],
        ['rgba(75, 192, 192, 0.6)', 'rgba(75, 192, 192, 1)'],
        ['rgba(153, 102, 255, 0.6)', 'rgba(153, 102, 255, 1)'],
        ['rgba(255, 159, 64, 0.6)', 'rgba(255, 159, 64, 1)'],
    ];
    $bgColors = [];
    $borderColors = [];
    for ($i = 0; $i < count($labels); $i++) {
        $colorIndex = $i % count($colors);
        $bgColors[] = $colors[$colorIndex][0];
        $borderColors[] = $colors[$colorIndex][1];
    }

    // Get available years from data (for year dropdown)
    $yearsQuery = "SELECT DISTINCT YEAR(date) as year FROM recycling_history ORDER BY year DESC";
    $yearsResult = mysqli_query($con, $yearsQuery);
    $availableYears = [];
    while ($row = mysqli_fetch_assoc($yearsResult)) {
        $availableYears[] = $row['year'];
    }
    // Add current year if not in list
    if (!in_array(date('Y'), $availableYears)) {
        array_unshift($availableYears, date('Y'));
    }

    $months = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
    ];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recycling Collection Report</title>
    <link href="../commonStyle.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<style>
    /* Page Title */
    .title {
        padding: 20px;
    }

    .title h2 {
        color: #2e7d32;
        font-size: 26px;
        font-weight: 600;
        margin: 0;
    }

    /* Data Range and Total Container */
    .data-total-container {
        display: flex;
        flex-direction: row;
        gap: 20px;
        margin: 0 20px 30px 20px;
        width: calc(100% - 40px);
    }

    .data, .total {
        background: linear-gradient(135deg, #c8e6c9 0%, #a5d6a7 100%);
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.25);
        border: 1px solid #a5d6a7;
        transition: all 0.3s ease;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .data:hover, .total:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(76, 175, 80, 0.35);
    }

    .data h3, .total h3 {
        color: #2e7d32;
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .data h3 i, .total h3 i {
        color: #388e3c;
        font-size: 20px;
    }

    /* Filter Dropdowns */
    .filter-row {
        display: flex;
        gap: 12px;
    }

    .filter-row select {
        flex: 1;
        padding: 14px 40px 14px 16px;
        border: 2px solid #81c784;
        border-radius: 12px;
        background-color: white;
        font-size: 15px;
        font-weight: 500;
        color: #2e7d32;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%232e7d32' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        transition: all 0.3s;
    }

    .filter-row select:focus {
        outline: none;
        border-color: #4caf50;
        box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
    }

    .filter-row select:hover {
        border-color: #4caf50;
    }

    /* Dropdown options  */
    .filter-row select option {
        background: white;
        color: #333;
        padding: 10px;
    }

    /* Total Weight Display */
    .total h2 {
        color: #1b5e20;
        font-size: 36px;
        font-weight: 700;
        margin-top: 10px;
    }

    /* Chart Wrapper */
    .chart-wrapper {
        width: calc(100% - 40px);
        max-width: 1200px;
        height: 400px;
        margin: 0 auto 30px auto;
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.15);
        border: 1px solid #e8f5e9;
    }

    #myChart {
        width: 100% !important;
        height: 100% !important;
    }

    /* Responsive - Mobile */
    @media (max-width: 768px) {
        .title {
            padding: 15px;
        }

        .title h2 {
            font-size: 22px;
        }

        .data-total-container {
            flex-direction: column;
            gap: 15px;
            margin: 0 15px 20px 15px;
            width: calc(100% - 30px);
        }

        .data, .total {
            padding: 20px;
        }

        .filter-row {
            flex-direction: column;
            gap: 10px;
        }

        .total h2 {
            font-size: 28px;
        }

        .chart-wrapper {
            width: calc(100% - 30px);
            height: 300px;
            padding: 15px;
            border-radius: 15px;
        }
    }

    /* Responsive - Tablet */
    @media (min-width: 769px) and (max-width: 1024px) {
        .data-total-container {
            flex-direction: column;
            gap: 15px;
        }

        .filter-row {
            max-width: 400px;
        }
    }

    /* Responsive - Desktop */
    @media (min-width: 1200px) {
        .data-total-container {
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            width: 100%;
            padding: 0 20px;
        }

        .title {
            max-width: 1200px;
            margin: 0 auto;
        }
    }
</style>

<body>
    <?php include "recAdminTaskbar.php"; ?>

    <!-- Title of Report -->
    <div class="title">
        <h2>Recycling Collection Report</h2>
    </div>

    <!-- Data Range and Total Collected -->
    <div class="data-total-container">
        <div class="data">
            <h3><i class="fas fa-calendar-alt"></i> Date Range</h3>
            <form method="GET" id="filterForm">
                <div class="filter-row">
                    <select id="month-select" name="month" onchange="document.getElementById('filterForm').submit()">
                        <?php foreach ($months as $num => $name): ?>
                            <option value="<?php echo $num; ?>" <?php echo ($selectedMonth == $num) ? 'selected' : ''; ?>>
                                <?php echo $name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select id="year-select" name="year" onchange="document.getElementById('filterForm').submit()">
                        <?php foreach ($availableYears as $year): ?>
                            <option value="<?php echo $year; ?>" <?php echo ($selectedYear == $year) ? 'selected' : ''; ?>>
                                <?php echo $year; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <div class="total">
            <h3><i class="fas fa-weight-hanging"></i> Total Collected</h3>
            <h2><?php echo $totalWeight; ?> kg</h2>
        </div>
    </div>

    <!-- Bar Chart -->
    <div class="chart-wrapper">
        <canvas id="myChart"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('myChart');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Collection by Material Type (kg)',
                    data: <?php echo json_encode($data); ?>,
                    backgroundColor: <?php echo json_encode($bgColors); ?>,
                    borderColor: <?php echo json_encode($borderColors); ?>,
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            color: '#2e7d32',
                            font: {
                                size: 14,
                                weight: '500'
                            },
                            padding: 20
                        }
                    },
                    title: {
                        display: true,
                        text: 'Waste Collection Data - <?php echo $months[$selectedMonth] . ' ' . $selectedYear; ?>',
                        color: '#2e7d32',
                        font: {
                            size: 18,
                            weight: '600'
                        },
                        padding: {
                            bottom: 20
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Quantity (kg)',
                            color: '#388e3c',
                            font: {
                                size: 14,
                                weight: '500'
                            }
                        },
                        grid: {
                            display: true,
                            color: 'rgba(76, 175, 80, 0.1)'
                        },
                        ticks: {
                            color: '#555'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#555'
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>