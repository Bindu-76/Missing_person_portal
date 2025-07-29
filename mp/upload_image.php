<?php
require_once "config.php";

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $photoName = $_FILES['photo']['name'];
    $photoTmp = $_FILES['photo']['tmp_name'];

    if (!empty($name) && !empty($photoName)) {
        $uploadDir = "uploads/";
        $ext = pathinfo($photoName, PATHINFO_EXTENSION);
        $safeName = strtolower(preg_replace("/[^a-zA-Z0-9]/", "_", $name));
        $newFileName = $safeName . "_" . time() . "." . $ext;
        $uploadPath = $uploadDir . $newFileName;

        if (move_uploaded_file($photoTmp, $uploadPath)) {
            // Optional: update an existing record or create a new one
            $stmt = $conn->prepare("INSERT INTO missing_reports (name, photo, age, gender, last_seen_date, last_seen_location, contact_number, description, user_id) VALUES (?, ?, 0, 'Other', NOW(), '', '', '', 1)");
            $stmt->bind_param("ss", $name, $newFileName);
            $stmt->execute();

            $message = "Photo uploaded and record created successfully.";
        } else {
            $message = "Failed to upload photo.";
        }
    } else {
        $message = "Please enter a name and choose a photo.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Missing Person Photo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2>Upload Missing Person Photo</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="bg-white p-4 rounded shadow">
        <div class="mb-3">
            <label for="name" class="form-label">Person's Name</label>
            <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Aarav Kumar" required>
        </div>
        <div class="mb-3">
            <label for="photo" class="form-label">Choose Photo</label>
            <input type="file" name="photo" id="photo" class="form-control" accept="image/*" required>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
        <a href="index.php" class="btn btn-secondary">Back to Home</a>
    </form>
</div>

</body>
</html>
