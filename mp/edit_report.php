<?php
session_start();
require_once "config.php";

// Access control: Only admin or police
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['admin', 'police'])) {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid report ID.";
    exit;
}

$report_id = intval($_GET['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $age = intval($_POST['age']);
    $gender = $_POST['gender'];
    $last_seen_location = $_POST['last_seen_location'];
    $last_seen_date = $_POST['last_seen_date'];
    $contact_number = $_POST['contact_number'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    // Handle image upload
    $photo = $_POST['existing_photo']; // default
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "uploads/";
        $filename = uniqid() . "_" . basename($_FILES["photo"]["name"]);
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_path)) {
            $photo = $target_path;
        }
    }

    // Update query
    $stmt = $conn->prepare("UPDATE missing_reports SET name=?, age=?, gender=?, last_seen_location=?, last_seen_date=?, contact_number=?, description=?, status=?, photo=? WHERE id=?");
    $stmt->bind_param("sisssssssi", $name, $age, $gender, $last_seen_location, $last_seen_date, $contact_number, $description, $status, $photo, $report_id);
    if ($stmt->execute()) {
        header("Location: view_reports.php?success=1");
        exit;
    } else {
        echo "Failed to update the report.";
    }
}

// Fetch current report
$stmt = $conn->prepare("SELECT * FROM missing_reports WHERE id = ?");
$stmt->bind_param("i", $report_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Report not found.";
    exit;
}

$report = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>Edit Report - ID #<?= $report_id ?></h2>
    <form method="post" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm mt-3">

        <div class="mb-3">
            <label>Name:</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($report['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Age:</label>
            <input type="number" name="age" class="form-control" value="<?= htmlspecialchars($report['age']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Gender:</label>
            <select name="gender" class="form-select" required>
                <option value="Male" <?= $report['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= $report['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                <option value="Other" <?= $report['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Last Seen Location:</label>
            <input type="text" name="last_seen_location" class="form-control" value="<?= htmlspecialchars($report['last_seen_location']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Last Seen Date:</label>
            <input type="date" name="last_seen_date" class="form-control" value="<?= $report['last_seen_date'] ?>" required>
        </div>

        <div class="mb-3">
            <label>Contact Number:</label>
            <input type="text" name="contact_number" class="form-control" value="<?= htmlspecialchars($report['contact_number']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Description:</label>
            <textarea name="description" class="form-control" required><?= htmlspecialchars($report['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label>Status:</label>
            <select name="status" class="form-select" required>
                <option value="Missing" <?= $report['status'] == 'Missing' ? 'selected' : '' ?>>Missing</option>
                <option value="Verified" <?= $report['status'] == 'Verified' ? 'selected' : '' ?>>Verified</option>
                <option value="Closed" <?= $report['status'] == 'Closed' ? 'selected' : '' ?>>Closed</option>
                <option value="Rejected" <?= $report['status'] == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Photo:</label><br>
            <?php if (!empty($report['photo']) && file_exists($report['photo'])): ?>
                <img src="<?= $report['photo'] ?>" alt="Photo" class="img-thumbnail mb-2" width="150"><br>
            <?php endif; ?>
            <input type="file" name="photo" class="form-control">
            <input type="hidden" name="existing_photo" value="<?= $report['photo'] ?>">
        </div>

        <button type="submit" class="btn btn-primary">Update Report</button>
        <a href="view_reports.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
