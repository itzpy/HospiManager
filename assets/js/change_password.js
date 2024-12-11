document
  .getElementById("changePasswordForm")
  .addEventListener("submit", function (event) {
    event.preventDefault(); // Prevent default form submission

    // Get input values
    const currentPassword = document.getElementById("currentPassword").value;
    const newPassword = document.getElementById("newPassword").value;
    const confirmPassword = document.getElementById("confirmPassword").value;

    // Create a FormData object to send data
    const formData = new FormData();
    formData.append("currentPassword", currentPassword);
    formData.append("newPassword", newPassword);
    formData.append("confirmPassword", confirmPassword);

    // Send the data to the server
    fetch("../actions/change_password.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert(data.message);
          window.location.href = "../view/admin/dashboard.php"; // Redirect to dashboard
        } else {
          alert(data.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert(
          "An error occurred while processing your request. Please try again."
        );
      });
  });
