<?php
session_start();
require_once "config.php";

// Access control: only admin/police
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['admin', 'police'])) {
    header("Location: admin_login.php");
    exit;
}

// Handle deletion if 'id' is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $reportId = $_GET['id'];

    // Prepare and execute delete query
    $stmtDelete = $conn->prepare("DELETE FROM missing_reports WHERE id = ?");
    $stmtDelete->bind_param("i", $reportId);
    $stmtDelete->execute();

    // Redirect to avoid resubmission on refresh
    header("Location: delete_report.php");
    exit;
}

// Status filter
$statusFilter = "";
$allowedStatuses = ['Missing', 'Verified', 'Closed', 'Rejected'];

if (isset($_GET['status']) && in_array($_GET['status'], $allowedStatuses)) {
    $statusFilter = $_GET['status'];
}

// Build query
if ($statusFilter !== "") {
    $stmt = $conn->prepare("SELECT mr.*, u.name AS submitted_by FROM missing_reports mr JOIN users u ON mr.user_id = u.id WHERE mr.status = ? ORDER BY mr.submitted_at DESC");
    $stmt->bind_param("s", $statusFilter);
} else {
    $stmt = $conn->prepare("SELECT mr.*, u.name AS submitted_by FROM missing_reports mr JOIN users u ON mr.user_id = u.id ORDER BY mr.submitted_at DESC");
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">
        <?= $statusFilter ? htmlspecialchars($statusFilter) . " Reports" : "All Reports" ?>
    </h2>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Status</th>
                <th>Last Seen Location</th>
                <th>Reported Date</th>
                <th>Submitted By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td><?= htmlspecialchars($row['last_seen_location']) ?></td>
                        <td><?= date("d M Y", strtotime($row['submitted_at'])) ?></td>
                        <td><?= htmlspecialchars($row['submitted_by']) ?></td>
                        <td>
                            <a href="edit_report.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="delete_report.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this report?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">No reports found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Back Button -->
    <div class="text-center mt-4">
        <a href="index_admin.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>
</div>
</body>
</html>
