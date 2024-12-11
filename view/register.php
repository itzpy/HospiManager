<!DOCTYPE html>
<html lang="eng">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <meta name="Author" content  = "Papa Yaw Badu">
    <title>Register - Recipe Sharing Platform</title>
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
        <p>Do you Already have an account? <a href="./login.php">Login</a></p>
        <p><a href="../index.php">Back to Home</a></p>
    </div>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script>
        document.getElementById("signupForm").addEventListener("submit", function (event) {
            event.preventDefault(); // Prevent default form submission

            // Get input values
            const firstName = document.getElementById("first-name").value.trim();
            const lastName = document.getElementById("last-name").value.trim();
            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm-password").value;

            // Get error message elements
            const firstNameError = document.getElementById("firstNameError");
            const lastNameError = document.getElementById("lastNameError");
            const emailError = document.getElementById("emailError");
            const passwordError = document.getElementById("passwordError");
            const confirmPasswordError = document.getElementById("confirmPasswordError");

            // Clear previous error messages
            firstNameError.textContent = "";
            lastNameError.textContent = "";
            emailError.textContent = "";
            passwordError.textContent = "";
            confirmPasswordError.textContent = "";

            let valid = true;

            // First Name validation
            if (firstName === "") {
                firstNameError.textContent = "First name cannot be empty.";
                valid = false;
            }

            // Last Name validation
            if (lastName === "") {
                lastNameError.textContent = "Last name cannot be empty.";
                valid = false;
            }

            // Email validation
            const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (email === "") {
                emailError.textContent = "Email cannot be empty.";
                valid = false;
            } else if (!emailPattern.test(email)) {
                emailError.textContent = "Please enter a valid email address.";
                valid = false;
            }

            // Password validation
            const passwordPattern = /^(?=.*[A-Z])(?=.*\d{3,})(?=.*[!@#$%^&*]).{8,}$/;
            if (!passwordPattern.test(password)) {
                passwordError.textContent = "Password must contain at least 8 characters, 1 uppercase letter, 3 digits, and 1 special character.";
                valid = false;
            }

            // Confirm Password validation
            if (confirmPassword !== password) {
                confirmPasswordError.textContent = "Passwords do not match.";
                valid = false;
            }

            
            // If the form is valid, proceed
            if (valid) {
                // Create a FormData object to send data
                const formData = new FormData();
                formData.append("first-name", firstName);
                formData.append("last-name", lastName);
                formData.append("email", email);
                formData.append("password", password);
                formData.append("confirm-password", confirmPassword);

                // Send the data to the server
                fetch("../actions/register_user.php", {
                    method: "POST",
                    body: formData,
                })
                .then((response) => response.json())
                .then((data) => {
                    console.log(data);
                    if (data.success) {
                        // Registration successful
                        alert("Registration successful! Redirecting to login...");
                        window.location.href = "./login.php"; // Redirect to login page

                    } else {
                        // Display server-side validation errors
                        for (const [key, value] of Object.entries(data.errors)) {
                            const errorElement = document.getElementById(
                                `${key.replace("-", "")}Error`
                            );
                            if (errorElement) {
                                errorElement.textContent = value;
                            }
                        }
                    }
                })
                .catch((error) => {
                    console.log("Error:", error);
                    alert("An error occurred while processing your request. Please try again.");
                });

            }
        });
    </script>
    <!-- <script src="..\assets\js\register.js"></script> -->
</body>
</html>
