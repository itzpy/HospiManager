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

        const response = await fetch("../controller/login_user.php", {
          method: "POST",
          body: formData
        });

        console.log("Response status:", response.status);
        console.log("Response headers:", response.headers);

        let data;
        const contentType = response.headers.get("content-type");
        console.log("Content type:", contentType);
        
        try {
          const text = await response.text();
          console.log("Raw response:", text);
          
          try {
            data = JSON.parse(text);
          } catch (parseError) {
            console.error("JSON Parse Error:", parseError);
            throw new Error("Invalid JSON response: " + text);
          }
        } catch (e) {
          console.error("Response read error:", e);
          throw new Error("Could not read server response");
        }

        console.log("Parsed data:", data);

        if (data.success) {
          // Redirect based on user role
          switch(data.role) {
            case 'admin':
            case 'superadmin':
              window.location.href = './admin/admin_dashboard.php';
              break;
            case 'staff':
              window.location.href = './staff/staff_dashboard.php';
              break;
            default:
              // Fallback redirect
              window.location.href = '../index.php';
          }
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
