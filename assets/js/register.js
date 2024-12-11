document
  .getElementById("signupForm")
  .addEventListener("submit", async function (event) {
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
    const passwordPattern = /^(?=.*[A-Z])(?=.*\d{2,})(?=.*[!@#$%^&*]).{8,}$/;
    if (!passwordPattern.test(password)) {
      passwordError.textContent =
        "Password must contain at least 8 characters, 1 uppercase letter, 2 digits, and 1 special character.";
      valid = false;
    }

    // Confirm Password validation
    if (confirmPassword !== password) {
      confirmPasswordError.textContent = "Passwords do not match.";
      valid = false;
    }

    // If the form is valid, proceed
    if (valid) {
      try {
        // Create a FormData object to send data
        const formData = new FormData();
        formData.append("first-name", firstName);
        formData.append("last-name", lastName);
        formData.append("email", email);
        formData.append("password", password);
        formData.append("confirm-password", confirmPassword);

        // Send the data to the server
        const response = await fetch("../actions/register_user.php", {
          method: "POST",
          body: formData,
        });

        // Handle the JSON response
        const data = await response.json();

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
      } catch (error) {
        console.log("Error:", error);
        alert("An error occurred while processing your request. Please try again.");
      }
    }
  });
