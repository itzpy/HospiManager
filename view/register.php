<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="Author" content="Papa Yaw Badu" />
    <title>Register - Hospital Management System</title>
    <link rel="stylesheet" href="../assets/css/login.css">
  </head>
  <body>
    <div class="wrapper">
      <nav class="nav animate-fade-right duration-1000 delay-200">
        <div class="nav-logo">
          <p href="./index.php">Hospi Manager</p>
        </div>
        <div class="nav-menu" id="navMenu">
          <ul>
            <li><a href="../index.php#home" class="link active">Home</a></li>
            <li><a href="../index.php#about" class="link">About Us</a></li>
            <li><a href="../index.php#contact" class="link">Contact Us</a></li>
          </ul>
        </div>
      </nav>
      <div class="form-box">
        <div class="signup-container">
          <header>Register</header>
          <div id="registrationError" class="error-message"></div>
          <form id="signupForm">
            <div class="two-forms">
              <div class="input-box">
                <ion-icon name="person-outline"></ion-icon>
                <input
                  type="text"
                  id="first-name"
                  name="first-name"
                  class="input-field"
                  placeholder="First Name"
                  required
                />
                <span id="firstNameError" class="error-message"></span>
              </div>
              <div class="input-box">
                <ion-icon name="person-outline"></ion-icon>
                <input
                  type="text"
                  id="last-name"
                  name="last-name"
                  class="input-field"
                  placeholder="Last Name"
                  required
                />
                <span id="lastNameError" class="error-message"></span>
              </div>
            </div>

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
                placeholder="Create a password"
                required
                minlength="8"
              />
              <span id="passwordError" class="error-message"></span>
            </div>

            <div class="input-box">
              <ion-icon name="lock-closed-outline"></ion-icon>
              <input
                type="password"
                id="confirm-password"
                name="confirm-password"
                class="input-field"
                placeholder="Confirm password"
                required
                minlength="8"
              />
              <span id="confirmPasswordError" class="error-message"></span>
            </div>

            <button type="submit" class="submit">Sign Up</button>
          </form>
          <div class="top">
            <span>Already have an account? <a href="./login.php">Login</a></span>
          </div>
        </div>
      </div>
    </div>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="../assets/js/register.js"></script>
    <script>
      document.getElementById('signupForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Collect form data
        const formData = new FormData(this);
        
        // Add default role as 'staff'
        formData.append('role', 'staff');
        
        // Send registration request
        fetch('../controller/register_user.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Redirect to login or dashboard
            window.location.href = './login.php?registered=true';
          } else {
            // Show error message
            document.getElementById('registrationError').textContent = data.message;
          }
        })
        .catch(error => {
          console.error('Registration error:', error);
          document.getElementById('registrationError').textContent = 'An unexpected error occurred.';
        });
      });
    </script>
  </body>
</html>
