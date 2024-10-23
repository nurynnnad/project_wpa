<?php
require_once "config/db.php"; // Include DB connection
$error = ""; // Initialize error message

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password']; 
    $name = $_POST['name'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if the username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username already exists!";
        } else {
            // Insert the new staff user into the database (role is set to 'staff')
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $conn->prepare("INSERT INTO users (username, password, role, name) VALUES (?, ?, 'staff', ?)");
            $insertStmt->bind_param("sss", $username, $hashedPassword, $name);

            if ($insertStmt->execute()) {
                header("Location: login.php"); // Redirect to login after successful signup
                exit;
            } else {
                $error = "Error occurred during registration!";
            }
            $insertStmt->close();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Registration</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #FFDEE9, #B5FFFC);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #333;
        }

        .signup-container {
            background-color: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .signup-container h2 {
            color: #2c3e50;
            font-size: 26px;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .signup-container input {
            width: 100%;
            padding: 12px;
            margin: 15px 0;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        .signup-container input:focus {
            border-color: #3498db;
            outline: none;
        }

        button {
            background-color: #3498db;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #2980b9;
        }

        .error-message {
            color: red;
            margin-top: 20px;
            font-size: 14px;
        }
    </style>
    <script>
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

<div class="signup-container">
    <h2>Staff Registration</h2>

    <form method="POST" action="" onsubmit="return validateForm();">
        <input type="text" name="name" placeholder="Full Name" required aria-label="Full Name">
        <input type="text" name="username" placeholder="Username" required aria-label="Username">
        <input type="password" id="password" name="password" placeholder="Password" required aria-label="Password">
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required aria-label="Confirm Password">
        <button type="submit">Register</button>
    </form>

    <?php if (!empty($error)): ?>
        <p class="error-message"><?php echo $error; ?></p>
    <?php endif; ?>
</div>

</body>
</html>
