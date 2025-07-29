<?php
session_start();
require_once "config.php";

// Only for admin or police roles
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['admin', 'police'])) {
    header("Location: admin_login.php");
    exit;
}

// Add Police Station
$success = $error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['address'], $_POST['contact'])) {
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $contact = trim($_POST['contact']);
    $map = isset($_POST['location']) ? trim($_POST['location']) : null;

    if ($name && $address && $contact) {
        $stmt = $conn->prepare("INSERT INTO police_stations (name, address, location_link, contact_number) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $address, $map, $contact);
        if ($stmt->execute()) {
            $success = "Police station added successfully.";
        } else {
            $error = "Error adding police station.";
        }
    } else {
        $error = "Please fill all required fields.";
    }
}

// Delete Station
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM police_stations WHERE id = $id");
    header("Location: manage_police.php");
    exit;
}

// Fetch all stations
$stations = $conn->query("SELECT * FROM police_stations ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Police Stations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 900px; margin-top: 40px; }
        .station-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .station-card h5 { margin-bottom: 10px; }
    </style>
</head>
<body class="bg-light">

<div class="container">
    <h2 class="mb-4 text-center">Manage Police Stations</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <!-- Add Station Form -->
    <div class="card p-4 mb-4">
        <h5>Add Police Station</h5>
        <form method="POST">
            <div class="mb-3">
                <label>Station Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Address</label>
                <textarea name="address" rows="2" class="form-control" required></textarea>
            </div>
            <div class="mb-3">
                <label>Google Maps Link (optional)</label>
                <input type="url" name="location" class="form-control">
            </div>
            <div class="mb-3">
                <label>Contact Number</label>
                <input type="text" name="contact" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Station</button>
        </form>
    </div>

    <!-- Display All Stations -->
    <?php while ($row = $stations->fetch_assoc()): ?>
        <div class="station-card">
            <h5><?= htmlspecialchars($row['name']) ?></h5>
            <p><strong>Address:</strong> <?= nl2br(htmlspecialchars($row['address'])) ?></p>
            <p><strong>Contact:</strong> <?= htmlspecialchars($row['contact_number']) ?></p>
            <?php if (!empty($row['location_link'])): ?>
                <p><a href="<?= htmlspecialchars($row['location_link']) ?>" target="_blank">View on Google Maps</a></p>
            <?php endif; ?>
            <div class="text-end">
                <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this police station?')">Delete</a>
            </div>
        </div>
    <?php endwhile; ?>

    <!-- Back Button -->
    <div class="text-center mt-4">
        <a href="index_admin.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>
