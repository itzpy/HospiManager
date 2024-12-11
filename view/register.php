<!DOCTYPE html>
<html lang="eng">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <meta name="Author" content="Papa Yaw Badu">
    <title>Register - Hospital Management System</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="form-container">
        <h2>Register</h2>
        <form id="signupForm">
            <label for="first-name">First Name</label>
            <div class="input-icon">
                <ion-icon name="person-outline"></ion-icon>
                <input type="text" id="first-name" name="first-name" placeholder="Enter your first name" required>
            </div>
            <span id="firstNameError" class="error"></span> 

            <label for="last-name">Last Name</label>
            <div class="input-icon">
                <ion-icon name="person-outline"></ion-icon>
                <input type="text" id="last-name" name="last-name" placeholder="Enter your last name" required>
            </div>
            <span id="lastNameError" class="error"></span> 

            <label for="email">Email</label>
            <div class="input-icon">
                <ion-icon name="mail-outline"></ion-icon>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <span id="emailError" class="error"></span>

            <label for="password">Password</label>
            <div class="input-icon">
                <ion-icon name="lock-closed-outline"></ion-icon>
                <input type="password" id="password" name="password" placeholder="Create a password" required>
            </div>
            <span id="passwordError" class="error"></span>

            <label for="confirm-password">Confirm Password</label>
            <div class="input-icon">
                <ion-icon name="lock-closed-outline"></ion-icon>
                <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm your password" required>
            </div>
            <span id="confirmPasswordError" class="error"></span>

            <button type="submit">Sign Up</button>
        </form>
        <p>Do you already have an account? <a href="./login.php">Login</a></p>
        <p><a href="../index.php">Back to Home</a></p>
    </div>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="../assets/js/register.js"></script>
</body>
</html>
