<?php
session_start();
require_once "config.php";

// Ensure only logged-in users can access
if (!isset($_SESSION["user_email"]) || $_SESSION["user_type"] !== "user") {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["user_id"];

// Handle Delete
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM missing_reports WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $deleteId, $userId);
    $stmt->execute();
    header("Location: my_reports.php");
    exit;
}

// Fetch user's reports
$stmt = $conn->prepare("SELECT * FROM missing_reports WHERE user_id = ? ORDER BY submitted_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$results = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .report-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .report-img {
            max-width: 100%;
            max-height: 300px;
            width: auto;
            height: auto;
            border-radius: 8px;
            object-fit: contain;
            background-color: #f8f9fa;
        }
        .action-btn {
            margin-right: 10px;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <!-- ✅ Back Button -->
    <a href="dashboard.php" class="btn btn-secondary mb-3">← Back to Dashboard</a>

    <h2 class="text-center mb-4">My Submitted Reports</h2>

    <?php if ($results->num_rows === 0): ?>
        <div class="alert alert-info text-center">You haven't submitted any reports yet.</div>
    <?php endif; ?>

    <?php while ($row = $results->fetch_assoc()): ?>
        <div class="report-card row">
            <div class="col-md-4">
                <?php if (!empty($row['photo']) && file_exists($row['photo'])): ?>
                    <img src="<?= $row['photo'] ?>" class="report-img" alt="Missing Person">
                <?php else: ?>
                    <img src="images/default_user.jpg" class="report-img" alt="No Image">
                <?php endif; ?>
            </div>
            <div class="col-md-8">
                <h5><?= htmlspecialchars($row['name']) ?> (<?= $row['age'] ?> yrs)</h5>
                <p><strong>Gender:</strong> <?= $row['gender'] ?></p>
                <p><strong>Last Seen:</strong> <?= $row['last_seen_location'] ?> on <?= $row['last_seen_date'] ?></p>
                <p><strong>Status:</strong> <span class="badge bg-info"><?= $row['status'] ?></span></p>
                <p><strong>Submitted:</strong> <?= $row['submitted_at'] ?></p>
                <p><strong>Edit Count:</strong> <?= $row['edit_count'] ?> / 2</p>

                <div class="mt-3">
                    <?php if ($row['edit_count'] < 2): ?>
                        <a href="report_missing.php?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning action-btn">Edit</a>
                    <?php else: ?>
                        <button class="btn btn-sm btn-secondary action-btn" disabled>Edit Limit Reached</button>
                    <?php endif; ?>
                    <a href="my_reports.php?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')" class="btn btn-sm btn-danger">Delete</a>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>
