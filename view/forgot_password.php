<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Hospital Management System</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="shortcut icon" href="../assets/images/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="wrapper">
        <div class="form-box">
            <div class="login-container">
                <header>Forgot Password</header>
                <div id="forgotMessage"></div>
                <form id="forgotPasswordForm">
                    <div class="input-box">
                        <ion-icon name="mail-outline"></ion-icon>
                        <input type="email" id="email" name="email" class="input-field" placeholder="Enter your email" required />
                    </div>
                    <span id="emailError" class="error-message"></span>
                    <button type="submit" class="submit">Send Reset Link</button>
                </form>
                <div class="top">
                    <span><a href="./login.php">Back to Login</a></span>
                </div>
            </div>
        </div>
    </div>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="../assets/js/forgot_password.js"></script>
</body>
</html>
