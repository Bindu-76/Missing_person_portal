<?php
session_start();
require_once "config.php";

// Restrict access to users only
if (!isset($_SESSION["user_email"])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["user_id"];
$userName = $_SESSION["user_name"];

$success = $error = "";
$isEditing = false;
$editData = [];

// DELETE
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM missing_reports WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $deleteId, $userId);
    $stmt->execute();
    $success = "Report deleted successfully.";
}

// EDIT LOAD
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM missing_reports WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $editId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows == 1) {
        $editData = $result->fetch_assoc();
        if ($editData['edit_count'] >= 2) {
            $error = "You can't edit this report more than 2 times.";
        } else {
            $isEditing = true;
        }
    }
}

// SUBMIT NEW or UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"] ?? "");
    $age = intval($_POST["age"] ?? 0);
    $gender = $_POST["gender"] ?? "";
    $lastSeenDate = $_POST["last_seen_date"] ?? "";
    $lastSeenLocation = trim($_POST["last_seen_location"] ?? "");
    $contactNumber = trim($_POST["contact_number"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $photoPath = "";

    // Validation (optional but recommended)
    if (empty($name) || empty($age) || empty($gender) || empty($lastSeenDate) || empty($lastSeenLocation) || empty($contactNumber)) {
        $error = "Please fill all required fields.";
    } else {
        // Handle Photo Upload
        if (!empty($_FILES["photo"]["name"])) {
            $targetDir = "uploads/";
            if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
            $fileName = basename($_FILES["photo"]["name"]);
            $photoPath = $targetDir . time() . "_" . $fileName;
            move_uploaded_file($_FILES["photo"]["tmp_name"], $photoPath);
        }

        if (isset($_POST["edit_id"])) {
            // UPDATE
            $editId = intval($_POST["edit_id"]);
            $stmt = $conn->prepare("SELECT edit_count FROM missing_reports WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $editId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows == 1) {
                $row = $result->fetch_assoc();
                if ($row['edit_count'] < 2) {
                    $newCount = $row['edit_count'] + 1;
                    $query = "UPDATE missing_reports SET name=?, age=?, gender=?, last_seen_date=?, last_seen_location=?, contact_number=?, description=?, edit_count=?";
                    if ($photoPath) {
                        $query .= ", photo=?";
                    }
                    $query .= " WHERE id=? AND user_id=?";
                    $stmt = $conn->prepare($query);
                    if ($photoPath) {
                        $stmt->bind_param("sisssssisii", $name, $age, $gender, $lastSeenDate, $lastSeenLocation, $contactNumber, $description, $newCount, $photoPath, $editId, $userId);
                    } else {
                        $stmt->bind_param("sisssssi", $name, $age, $gender, $lastSeenDate, $lastSeenLocation, $contactNumber, $description, $newCount, $editId, $userId);
                    }
                    if ($stmt->execute()) {
                        $success = "Report updated successfully.";
                    } else {
                        $error = "Failed to update.";
                    }
                } else {
                    $error = "Edit limit reached.";
                }
            }
        } else {
            // INSERT
            $stmt = $conn->prepare("INSERT INTO missing_reports (user_id, name, age, gender, last_seen_date, last_seen_location, contact_number, description, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isissssss", $userId, $name, $age, $gender, $lastSeenDate, $lastSeenLocation, $contactNumber, $description, $photoPath);
            if ($stmt->execute()) {
                $success = "Report submitted successfully!";
            } else {
                $error = "Error submitting report.";
            }
        }
    }
}

// Load user's reports
$reports = [];
$stmt = $conn->prepare("SELECT * FROM missing_reports WHERE user_id = ? ORDER BY submitted_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result) $reports = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Missing Person</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f6fa; padding: 30px; }
        .container { max-width: 800px; background: white; padding: 30px; border-radius: 10px; }
        .form-section { margin-bottom: 40px; }
        img.thumb { width: 80px; height: auto; border-radius: 6px; }
    </style>
</head>
<body>

<div class="container">
    <a href="dashboard.php" class="btn btn-secondary mb-3">← Back to Dashboard</a>

    <h2 class="mb-4"><?= $isEditing ? "Edit" : "Submit" ?> Missing Person Report</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="form-section">
        <?php if ($isEditing): ?>
            <input type="hidden" name="edit_id" value="<?= $editData['id'] ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label>Full Name</label>
            <input type="text" name="name" value="<?= $isEditing ? htmlspecialchars($editData['name']) : '' ?>" class="form-control" required>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label>Age</label>
                <input type="number" name="age" value="<?= $isEditing ? $editData['age'] : '' ?>" class="form-control" required>
            </div>
            <div class="col">
                <label>Gender</label>
                <select name="gender" class="form-select" required>
                    <option disabled <?= !$isEditing ? "selected" : "" ?>>Select</option>
                    <option <?= ($isEditing && $editData['gender'] == 'Male') ? "selected" : "" ?>>Male</option>
                    <option <?= ($isEditing && $editData['gender'] == 'Female') ? "selected" : "" ?>>Female</option>
                    <option <?= ($isEditing && $editData['gender'] == 'Other') ? "selected" : "" ?>>Other</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label>Last Seen Date</label>
            <input type="date" name="last_seen_date" value="<?= $isEditing ? $editData['last_seen_date'] : '' ?>" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Last Seen Location</label>
            <input type="text" name="last_seen_location" value="<?= $isEditing ? htmlspecialchars($editData['last_seen_location']) : '' ?>" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Contact Number</label>
            <input type="text" name="contact_number" value="<?= $isEditing ? htmlspecialchars($editData['contact_number']) : '' ?>" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control"><?= $isEditing ? htmlspecialchars($editData['description']) : '' ?></textarea>
        </div>

        <div class="mb-3">
            <label>Photo (optional)</label>
            <input type="file" name="photo" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary"><?= $isEditing ? "Update" : "Submit" ?> Report</button>
        <?php if ($isEditing): ?>
            <a href="report_missing.php" class="btn btn-secondary">Cancel Edit</a>
        <?php endif; ?>
    </form>

    <h4 class="mb-3">Your Reports</h4>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Name</th><th>Age</th><th>Gender</th><th>Status</th><th>Photo</th><th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($reports as $report): ?>
            <tr>
                <td><?= htmlspecialchars($report['name']) ?></td>
                <td><?= $report['age'] ?></td>
                <td><?= $report['gender'] ?></td>
                <td><?= htmlspecialchars($report['status']) ?></td>
                <td>
                    <?php if ($report['photo']): ?>
                        <img src="<?= $report['photo'] ?>" class="thumb">
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($report['edit_count'] < 2): ?>
                        <a href="?edit=<?= $report['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <?php endif; ?>
                    <a href="?delete=<?= $report['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this report?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
