<?php
session_start();
require_once "config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $role = "user"; // Force role to user

    if (!empty($email) && !empty($password)) {
        $sql = "SELECT id, name, email, password, role FROM users WHERE email = ? AND role = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION["user_id"] = $user['id'];
                $_SESSION["user_email"] = $user['email'];
                $_SESSION["user_name"] = $user['name'];
                $_SESSION["user_type"] = $user['role'];

                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No user account found with that email.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Login - Missing Person Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
        }

        .bg-cover {
            background: url('images/background.jpeg') no-repeat center center fixed;
            background-size: cover;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            max-width: 450px;
            width: 100%;
            padding: 30px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

<div class="bg-cover">
    <div class="login-box">
        <h2 class="text-center mb-4">User Login</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="mb-3">
                <label>Email address</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">Show</button>
                </div>
            </div>

            <!-- Hidden field to force role as user -->
            <input type="hidden" name="role" value="user">

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>

            <div class="mt-3 text-center">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>

            <div class="mt-2 text-center">
                <span>Don't have an account? </span><a href="signup_user.php">User Signup</a>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassword() {
    const passInput = document.getElementById("password");
    passInput.type = passInput.type === "password" ? "text" : "password";
}
</script>

</body>
</html>
