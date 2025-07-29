<?php
require_once "config.php";

$name = $email = $password = $confirm_password = $profile_pic = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // File upload handling
    if (!empty($_FILES["profile_pic"]["name"])) {
        $target_dir = "uploads/";
        $file_name = basename($_FILES["profile_pic"]["name"]);
        $target_file = $target_dir . time() . "_" . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $valid_extensions = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($imageFileType, $valid_extensions)) {
            $error = "Only JPG, JPEG, PNG & GIF files are allowed.";
        } elseif ($_FILES["profile_pic"]["size"] > 2 * 1024 * 1024) {
            $error = "Profile picture must be under 2MB.";
        } else {
            move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file);
            $profile_pic = $target_file;
        }
    }

    if (empty($error)) {
        if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Check if email already exists
            $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = "Email is already registered.";
            } else {
                // Hash password and insert user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, profile_pic) VALUES (?, ?, ?, 'user', ?)");
                $stmt->bind_param("ssss", $name, $email, $hashed_password, $profile_pic);

                if ($stmt->execute()) {
                    header("Location: login.php?signup=success");
                    exit;
                } else {
                    $error = "Registration failed. Try again.";
                }
                $stmt->close();
            }
            $check->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Signup - Missing Person Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('images/signup-bg.jpeg') no-repeat center center fixed;
            background-size: cover;
        }
        .signup-box {
            max-width: 500px;
            margin: auto;
            margin-top: 80px;
            padding: 30px;
            background: rgba(255,255,255,0.95);
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }
    </style>
    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            input.type = input.type === "password" ? "text" : "password";
        }
    </script>
</head>
<body>

<div class="signup-box">
    <h2 class="text-center mb-4">User Signup</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="post" action="signup_user.php" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Full Name</label>
            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($name) ?>">
        </div>

        <div class="mb-3">
            <label>Email Address</label>
            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($email) ?>">
        </div>

        <div class="mb-3">
            <label>Password</label>
            <div class="input-group">
                <input type="password" name="password" id="password" class="form-control" required>
                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">Show</button>
            </div>
        </div>

        <div class="mb-3">
            <label>Confirm Password</label>
            <div class="input-group">
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">Show</button>
            </div>
        </div>

        <div class="mb-3">
            <label>Profile Picture (Optional)</label>
            <input type="file" name="profile_pic" class="form-control" accept=".jpg,.jpeg,.png,.gif">
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Create Account</button>
        </div>

        <div class="mt-3 text-center">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </form>
</div>

</body>
</html>
