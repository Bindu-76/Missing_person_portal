<?php
session_start();
require_once "config.php";

// Access control
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['admin', 'police'])) {
    header("Location: admin_login.php");
    exit;
}

$adminName = $_SESSION['user_name'] ?? 'Admin';

// Summary stats
$stats = [
    'total' => 0,
    'verified' => 0,
    'missing' => 0,
    'closed' => 0
];

$query = "SELECT 
    COUNT(*) AS total,
    COUNT(CASE WHEN status = 'Verified' THEN 1 END) AS verified,
    COUNT(CASE WHEN status = 'Missing' THEN 1 END) AS missing,
    COUNT(CASE WHEN status = 'Closed' THEN 1 END) AS closed
FROM missing_reports";

$result = $conn->query($query);
if ($result) {
    $stats = $result->fetch_assoc();
}

// Latest reports
$recentReports = [];
$recentQuery = "SELECT name AS missing_name, status, submitted_at AS created_at FROM missing_reports ORDER BY submitted_at DESC LIMIT 5";
$res = $conn->query($recentQuery);
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $recentReports[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Missing Person Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { display: flex; margin: 0; font-family: 'Segoe UI', sans-serif; }
        .sidebar {
            width: 240px;
            background-color: #1e3a8a;
            color: white;
            padding: 20px;
            height: 100vh;
        }
        .sidebar h2 { font-size: 22px; margin-bottom: 30px; }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            margin: 15px 0;
            padding: 8px 10px;
            border-radius: 6px;
        }
        .sidebar a:hover { background-color: #374bb3; }
        .main-content {
            flex: 1;
            background-color: #f4f6f9;
            padding: 30px;
            overflow-y: auto;
        }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .card:hover { transform: translateY(-5px); }
        .card h3 { margin: 0; font-size: 28px; color: #1e3a8a; }
        .card p { margin: 10px 0 0; color: #555; font-size: 15px; }
        .topbar {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logout {
            text-decoration: none;
            color: white;
            background: #dc3545;
            padding: 8px 16px;
            border-radius: 6px;
        }
        .logout:hover { background: #bb2d3b; }
        .chart-container, .recent-reports {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="index_admin.php">Dashboard</a>
    <a href="view_reports.php">Manage Reports</a>
    <a href="manage_notices.php">Manage Notices</a>
    <a href="manage_police.php">Police Stations</a>
    <a href="view_messages.php">View Messages</a>
    <a href="logout_admin.php" class="logout">Logout</a>
</div>

<div class="main-content">
    <div class="topbar">
        <h1>Welcome, <?= htmlspecialchars($adminName) ?></h1>
    </div>

    <div class="summary-cards">
        <div class="card" onclick="window.location.href='view_reports.php'">
            <h3><?= $stats['total'] ?></h3>
            <p>Total Reports</p>
        </div>
        <div class="card" onclick="window.location.href='view_reports.php?status=Verified'">
            <h3><?= $stats['verified'] ?></h3>
            <p>Verified</p>
        </div>
        <div class="card" onclick="window.location.href='view_reports.php?status=Missing'">
            <h3><?= $stats['missing'] ?></h3>
            <p>Missing</p>
        </div>
        <div class="card" onclick="window.location.href='view_reports.php?status=Closed'">
            <h3><?= $stats['closed'] ?></h3>
            <p>Closed</p>
        </div>
    </div>

    <div class="mb-4" style="font-size: 16px; color: #333;">
        <strong>Total Reports:</strong> <?= $stats['total'] ?> &nbsp; | &nbsp;
        <strong>Verified:</strong> <?= $stats['verified'] ?> &nbsp; | &nbsp;
        <strong>Missing:</strong> <?= $stats['missing'] ?> &nbsp; | &nbsp;
        <strong>Closed:</strong> <?= $stats['closed'] ?>
    </div>

    <div class="row">
        <div class="col-md-6 chart-container">
            <h5 class="text-center mb-3">Report Status - Pie</h5>
            <canvas id="pieChart"></canvas>
        </div>
        <div class="col-md-6 chart-container">
            <h5 class="text-center mb-3">Report Status - Bar</h5>
            <canvas id="barChart"></canvas>
        </div>
    </div>

    <div class="recent-reports">
        <h5 class="mb-3">Latest 5 Reports</h5>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Reported At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recentReports)): ?>
                    <?php foreach ($recentReports as $report): ?>
                        <tr>
                            <td><?= htmlspecialchars($report['missing_name']) ?></td>
                            <td><?= htmlspecialchars($report['status']) ?></td>
                            <td><?= date("d M Y, h:i A", strtotime($report['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="text-center">No recent reports found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const pieData = [<?= $stats['verified'] ?>, <?= $stats['missing'] ?>, <?= $stats['closed'] ?>];
const barLabels = ["Verified", "Missing", "Closed"];
const barColors = ["#198754", "#ffc107", "#dc3545"];

new Chart(document.getElementById("pieChart"), {
    type: 'pie',
    data: {
        labels: barLabels,
        datasets: [{ data: pieData, backgroundColor: barColors }]
    },
    options: { plugins: { legend: { position: 'bottom' } } }
});

new Chart(document.getElementById("barChart"), {
    type: 'bar',
    data: {
        labels: barLabels,
        datasets: [{
            label: 'Reports',
            data: pieData,
            backgroundColor: barColors
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});
</script>
</body>
</html>
