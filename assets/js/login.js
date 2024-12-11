document
  .getElementById("loginForm")
  .addEventListener("submit", async function (event) {
    event.preventDefault(); // Prevent default form submission

    // Input fields and error spans
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;

    const emailError = document.getElementById("emailError");
    const passwordError = document.getElementById("passwordError");

    // Clear previous error messages
    emailError.textContent = "";
    passwordError.textContent = "";

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

    // Password validation
    const passwordPattern = /^(?=.*[A-Z])(?=.*\d{3,})(?=.*[!@#$%^&*]).{8,}$/;
    if (!password.match(passwordPattern)) {
      passwordError.textContent =
        "Password must contain at least 8 characters, 1 uppercase letter, 3 digits, and 1 special character.";
      valid = false;
    }

    // Proceed if the form is valid
    if (valid) {
      try {
        // Prepare form data
        const formData = new FormData();
        formData.append("email", email);
        formData.append("password", password);

        // Send the form data using fetch API
        const response = await fetch("../actions/login_user.php", {
          method: "POST",
          body: formData,
        });

        // Handle the JSON response
        const data = await response.json();

        if (data.status === "success") {
          // Redirect to the appropriate dashboard
          window.location.href = "../view/admin/dashboard.php";
        } else {
          // Display error from the server
          alert(data.message);
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
