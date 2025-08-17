<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$password = "";
$dbname = "student_services_db";

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch key metrics
$scholarships_sql = "SELECT COUNT(*) AS total_scholarships FROM scholarships";
$scholarships_result = $conn->query($scholarships_sql);
$total_scholarships = $scholarships_result->fetch_assoc()['total_scholarships'];

$applications_sql = "SELECT COUNT(*) AS total_applications FROM scholarship_applications WHERE status = 'pending'";
$applications_result = $conn->query($applications_sql);
$total_applications = $applications_result->fetch_assoc()['total_applications'];

$approved_scholars_sql = "SELECT COUNT(*) AS total_approved FROM scholarship_applications WHERE status = 'approved'";
$approved_scholars_result = $conn->query($approved_scholars_sql);
$total_approved = $approved_scholars_result->fetch_assoc()['total_approved'];

$pending_applications_sql = "SELECT COUNT(*) AS total_pending FROM scholarship_applications WHERE status = 'pending'";
$pending_applications_result = $conn->query($pending_applications_sql);
$total_pending = $pending_applications_result->fetch_assoc()['total_pending'];

$rejected_applications_sql = "SELECT COUNT(*) AS total_rejected FROM scholarship_applications WHERE status = 'rejected'";
$rejected_applications_result = $conn->query($rejected_applications_sql);
$total_rejected = $rejected_applications_result->fetch_assoc()['total_rejected'];

// Fetch data for the graph
$applications_over_time_sql = "
    SELECT 
        DATE_FORMAT(application_date, '%Y-%m') AS month,
        COUNT(*) AS applications
    FROM 
        scholarship_applications
    GROUP BY 
        DATE_FORMAT(application_date, '%Y-%m')
    ORDER BY 
        DATE_FORMAT(application_date, '%Y-%m')
";
$applications_over_time_result = $conn->query($applications_over_time_sql);
$graph_data = [];
while ($row = $applications_over_time_result->fetch_assoc()) {
    $graph_data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
        }
        .sidebar {
            width: 260px;
            height: 100vh;
            background-color: #003366;
            color: white;
            position: fixed;
            padding-top: 20px;
            transition: all 0.3s ease;
        }
        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-color: #002855;
            font-size: 1.5rem;
            font-weight: 700;
            text-transform: uppercase;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            color: #FFD700;
        }
        .sidebar-header i {
            margin-right: 10px;
            color: #FFD700;
        }
        .sidebar-menu {
            margin-top: 20px;
        }
        .menu-item {
            text-decoration: none;
            color: white;
            display: block;
            padding: 15px 20px;
            font-size: 1.1rem;
            margin: 8px 15px;
            background-color: #004080;
            transition: all 0.3s ease;
            border-radius: 8px;
        }
        .menu-item:hover,
        .menu-item.active {
            background-color: #FFD700;
            color: #003366;
            transform: scale(1.05);
            font-weight: bold;
        }
        .logout-btn {
            text-decoration: none;
            color: white;
            display: block;
            padding: 15px;
            font-size: 1.1rem;
            background-color: #d9534f;
            margin: 30px 15px;
            border-radius: 8px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .logout-btn:hover {
            background-color: #c9302c;
            transform: scale(1.05);
        }
        .main-content {
            margin-left: 260px;
            padding: 20px;
            width: calc(100% - 260px);
            transition: all 0.3s ease;
        }
        .header {
            background: linear-gradient(90deg, #00509E, #002855);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: scale(1.05);
        }
        .card-header {
            background: #004080;
            color: white;
            padding: 10px;
            text-align: center;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        .card-body {
            padding: 20px;
            text-align: center;
        }
        .chart-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            padding: 20px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            .sidebar {
                height: auto;
                position: static;
                width: 100%;
            }
        }
    </style>
</head>
<body>

<?php include 'admin_scholarship_header.php'; ?>

<div class="main-content">
    <div class="header">
        <h2>Admin Dashboard</h2>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card" onclick="window.location.href='admin_manage_scholarships.php'">
                <div class="card-header">
                    Total Scholarships
                </div>
                <div class="card-body">
                    <h3><?= $total_scholarships ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card" onclick="window.location.href='manage_applications.php'">
                <div class="card-header">
                    Total Applications
                </div>
                <div class="card-body">
                    <h3><?= $total_applications ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card" onclick="window.location.href='approved_scholars.php'">
                <div class="card-header">
                    Approved Scholars
                </div>
                <div class="card-body">
                    <h3><?= $total_approved ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card" onclick="window.location.href='manage_applications.php'">
                <div class="card-header">
                    Pending Applications
                </div>
                <div class="card-body">
                    <h3><?= $total_pending ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card" onclick="window.location.href='manage_applications.php'">
                <div class="card-header">
                    Rejected Applications
                </div>
                <div class="card-body">
                    <h3><?= $total_rejected ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="chart-container">
        <h4>Applications Over Time</h4>
        <div id="applicationsChart"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    var graphData = <?= json_encode($graph_data) ?>;
    var labels = graphData.map(data => data.month);
    var data = graphData.map(data => data.applications);

    var options = {
        series: [{
            name: 'Applications',
            data: data
        }],
        chart: {
            type: 'line',
            height: 350,
            zoom: {
                enabled: true
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth'
        },
        title: {
            text: 'Applications Over Time',
            align: 'left'
        },
        grid: {
            row: {
                colors: ['#f3f3f3', 'transparent'],
                opacity: 0.5
            }
        },
        xaxis: {
            categories: labels,
        },
        tooltip: {
            enabled: true,
            shared: false,
            intersect: true,
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'dark',
                type: 'horizontal',
                shadeIntensity: 0.5,
                gradientToColors: ['#ABE5A1'],
                inverseColors: true,
                opacityFrom: 1,
                opacityTo: 1,
                stops: [0, 100]
            }
        },
        colors: ['#77B6EA', '#545454'],
    };

    var chart = new ApexCharts(document.querySelector("#applicationsChart"), options);
    chart.render();
</script>
</body>
</html>