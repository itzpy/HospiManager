<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Hospital Management System</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <div class="wrapper">
        <div class="form-box">
            <div class="login-container">
                <header>Reset Password</header>
                <form id="resetPasswordForm">
                    <input type="hidden" id="token" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
                    <div class="input-box">
                        <ion-icon name="lock-closed-outline"></ion-icon>
                        <input
                            type="password"
                            id="newPassword"
                            name="newPassword"
                            class="input-field"
                            placeholder="Enter your new password"
                            required
                        />
                    </div>
                    <span id="passwordError" class="error-message"></span>
                    <div class="input-box">
                        <ion-icon name="lock-closed-outline"></ion-icon>
                        <input
                            type="password"
                            id="confirmPassword"
                            name="confirmPassword"
                            class="input-field"
                            placeholder="Confirm your new password"
                            required
                        />
                    </div>
                    <span id="confirmPasswordError" class="error-message"></span>
                    <button type="submit" class="submit">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="../assets/js/reset_password.js"></script>
</body>
</html>