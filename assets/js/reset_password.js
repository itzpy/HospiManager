document
  .getElementById("resetPasswordForm")
  .addEventListener("submit", async function (event) {
    event.preventDefault(); // Prevent default form submission

    // Input fields and error spans
    const token = document.getElementById("token").value;
    const newPassword = document.getElementById("newPassword").value;
    const confirmPassword = document.getElementById("confirmPassword").value;
    const passwordError = document.getElementById("passwordError");
    const confirmPasswordError = document.getElementById(
      "confirmPasswordError"
    );

    // Clear previous error messages
    passwordError.textContent = "";
    confirmPasswordError.textContent = "";

    // Validation flags
    let valid = true;

    // Password validation
    const passwordPattern = /^(?=.*[A-Z])(?=.*\d{2,})(?=.*[!@#$%^&*]).{8,}$/;
    if (!newPassword.match(passwordPattern)) {
      passwordError.textContent =
        "Password must contain at least 8 characters, 1 uppercase letter, 2 digits, and 1 special character.";
      valid = false;
    }

    // Confirm Password validation
    if (newPassword !== confirmPassword) {
      confirmPasswordError.textContent = "Passwords do not match.";
      valid = false;
    }

    // Proceed if the form is valid
    if (valid) {
      try {
        // Prepare form data
        const formData = new FormData();
        formData.append("token", token);
        formData.append("newPassword", newPassword);
        formData.append("confirmPassword", confirmPassword);

        // Send the form data using fetch API
        const response = await fetch("../actions/reset_password.php", {
          method: "POST",
          body: formData,
        });

        // Handle the JSON response
        const data = await response.json();

        if (data.success) {
          alert("Password has been reset successfully.");
          window.location.href = "./login.php"; // Redirect to login page
        } else {
          alert(data.message || data.errors.general);
        }
      } catch (error) {
        // Log and display fetch errors
        console.log("Error:", error);
        alert(
          "An error occurred while processing your request. Please try again."
        );
      }
    }
  });
