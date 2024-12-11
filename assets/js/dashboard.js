document.addEventListener("DOMContentLoaded", function () {
  const deleteButtons = document.querySelectorAll(".delete-user");

  deleteButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const userId = this.getAttribute("data-user-id");
      if (confirm("Are you sure you want to delete this user?")) {
        fetch(`../actions/delete_user.php?id=${userId}`, {
          method: "DELETE",
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              alert("User  deleted successfully.");
              location.reload(); // Reload the page to see the changes
            } else {
              alert("Error deleting user: " + data.message);
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            alert("An error occurred while deleting the user.");
          });
      }
    });
  });
});
