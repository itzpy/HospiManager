document
  .getElementById("forgotPasswordForm")
  .addEventListener("submit", async function (event) {
    event.preventDefault(); // Prevent default form submission

    // Input fields and error spans
    const email = document.getElementById("email").value.trim();
    const emailError = document.getElementById("emailError");

    // Clear previous error messages
    emailError.textContent = "";

    // Validation flags
    let valid = true;

    // Email validation
    const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!email) {
      emailError.textContent = "Email cannot be empty.";
      valid = false;
    } else if (!email.match(emailPattern)) {
      emailError.textContent = "Please enter a valid email address.";
      valid = false;
    }

    // Proceed if the form is valid
    if (valid) {
      try {
        // Prepare form data
        const formData = new FormData();
        formData.append("email", email);

        // Send the form data using fetch API
        const response = await fetch("../actions/forgot_password.php", {
          method: "POST",
          body: formData,
        });

        // Handle the JSON response
        const data = await response.json();

        if (data.success) {
          alert("Password reset link has been sent to your email.");
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
