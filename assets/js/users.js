document.addEventListener("DOMContentLoaded", function () {
  //   const editUserForm = document.getElementById("editUser Form");
  //   const editUserModal = document.getElementById("editUser Modal");
  //   let currentRow; // Keep track of the row being edited

  // Get all delete buttons
  const deleteButtons = document.querySelectorAll(".delete");
  deleteButtons.forEach((button) => {
    button.addEventListener("click", function () {
      console.log("Delete button clicked");
      const row = this.closest("tr"); // Get the parent row of the clicked button
      const userId = this.getAttribute("data-user-id");
      // Get the user ID from the button

      // Confirm deletion
      const confirmDeletion = confirm(
        `Are you sure you want to delete this user?`
      );
      if (confirmDeletion) {
        // Send DELETE request to the server
        console.log("Sending DELETE request for user ID:", userId);
        fetch("../actions/user_actions.php", {
          method: "DELETE",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `id=${userId}`,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              alert(data.message);
              row.remove(); // Remove the row from the table
            } else {
              alert(data.message);
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            alert("An error occurred while trying to delete the user.");
          });
      }
    });
  });

  // Get all view buttons
  const viewButtons = document.querySelectorAll(".view");
  viewButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const row = this.closest("tr"); // Get the parent row
      const userName = row.cells[1].textContent; // Get the name from the row
      const userEmail = row.cells[2].textContent; // Get the email from the row

      // Show user details in an alert (or you can implement a modal for better UI)
      alert(`User  Details:\n\nName: ${userName}\nEmail: ${userEmail}`);
    });
  });
});
