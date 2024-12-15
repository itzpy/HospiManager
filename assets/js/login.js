document
  .getElementById("loginForm")
  .addEventListener("submit", async function (event) {
    event.preventDefault();

    // Get form elements
    const form = document.getElementById("loginForm");
    const email = form.querySelector("#email").value.trim();
    const password = form.querySelector("#password").value;
    const emailError = form.querySelector("#emailError");
    const passwordError = form.querySelector("#passwordError");
    const loginError = document.getElementById("loginError");

    // Clear previous error messages
    if (emailError) emailError.textContent = "";
    if (passwordError) passwordError.textContent = "";
    if (loginError) loginError.textContent = "";

    // Validation flags
    let valid = true;

    // Email validation
    if (!email) {
      if (emailError) emailError.textContent = "Email is required";
      valid = false;
    } else if (!email.match(/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/)) {
      if (emailError) emailError.textContent = "Please enter a valid email address";
      valid = false;
    }

    // Password validation
    if (!password) {
      if (passwordError) passwordError.textContent = "Password is required";
      valid = false;
    }

    if (valid) {
      try {
        const formData = new FormData();
        formData.append("email", email);
        formData.append("password", password);

        const response = await fetch("../actions/login_user.php", {
          method: "POST",
          body: formData
        });

        let data;
        const contentType = response.headers.get("content-type");
        
        if (contentType && contentType.includes("application/json")) {
          try {
            data = await response.json();
          } catch (e) {
            console.error("JSON Parse Error:", e);
            throw new Error("Invalid response from server");
          }
        } else {
          throw new Error("Invalid response type from server");
        }

        if (data.success) {
          // Redirect to dashboard or specified URL
          window.location.href = data.redirect || "../view/admin/dashboard.php";
        } else {
          // Display error message
          const errorMessage = data.message || "Login failed. Please try again.";
          if (loginError) {
            loginError.textContent = errorMessage;
          } else {
            alert(errorMessage);
          }
        }
      } catch (error) {
        console.error("Login error:", error);
        const errorMessage = error.message || "An error occurred. Please try again later.";
        if (loginError) {
          loginError.textContent = errorMessage;
        } else {
          alert(errorMessage);
        }
      }
    }
  });
