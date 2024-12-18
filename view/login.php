<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="Author" content="Papa Yaw Badu" />
    <title>Login - Hospital Management System</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="shortcut icon" href="../assets/images/favicon.ico" type="image/x-icon">
  </head>
  <body>
    <div class="wrapper">
      <nav class="nav animate-fade-right duration-1000 delay-200">
        <div class="nav-logo">
          <p href="./index.php">Hospi Manager</p>
        </div>
        <div class="nav-menu" id="navMenu">
          <ul>
            <li><a href="../index.php" class="link active">Home</a></li>
            <li><a href="../index.php#aboutus" class="link">About Us</a></li>
            <li><a href="../index.php#contactus" class="link">Contact Us</a></li>
          </ul>
        </div>
      </nav>
      <div class="form-box">
        <div class="login-container">
          <header>Login</header>
          <?php
          // Handle error messages
          if (isset($_GET['error'])) {
              $errorMessages = [
                  'unauthorized' => 'You are not authorized to access that page.',
                  'session_expired' => 'Your session has expired. Please log in again.'
              ];
              $errorType = htmlspecialchars($_GET['error']);
              $errorMessage = $errorMessages[$errorType] ?? 'An unknown error occurred.';
              echo "<div class='error-message'>" . $errorMessage . "</div>";
          }
          ?>
          <div id="loginError" class="error-message"></div>
          <form id="loginForm">
            <div class="input-box">
              <ion-icon name="mail-outline"></ion-icon>
              <input
                type="email"
                id="email"
                name="email"
                class="input-field"
                placeholder="Enter your email"
                required
              />
              <span id="emailError" class="error-message"></span>
            </div>

            <div class="input-box">
              <ion-icon name="lock-closed-outline"></ion-icon>
              <input
                type="password"
                id="password"
                name="password"
                class="input-field"
                placeholder="Enter your password"
                required
                minlength="6"
                autocomplete="current-password"
              />
              <span id="passwordError" class="error-message"></span>
            </div>

            <button type="submit" class="submit">Login</button>
          </form>
          <div class="top">
            <span><a href="./forgot_password.php">Forgot Password?</a></span>
          </div>
          <div class="bottom">
            <div class="left">
              <span>Don't have an account? <a href="./register.php" class="signup-link">Sign Up</a></span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="../assets/js/login.js"></script>
  </body>
</html>
