document.addEventListener("DOMContentLoaded", function () {
  const deleteButtons = document.querySelectorAll(".delete");
  const editButtons = document.querySelectorAll(".edit");

  deleteButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const id =
        this.getAttribute("data-category-id") ||
        this.getAttribute("data-item-id");
      const type = this.getAttribute("data-category-id") ? "category" : "item";
      if (confirm(`Are you sure you want to delete this ${type}?`)) {
        fetch(`../actions/delete_${type}.php?id=${id}`, {
          method: "DELETE",
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              alert(
                `${
                  type.charAt(0).toUpperCase() + type.slice(1)
                } deleted successfully.`
              );
              location.reload(); // Reload the page to see the changes
            } else {
              alert(`Error deleting ${type}: ` + data.message);
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            alert(`An error occurred while deleting the ${type}.`);
          });
      }
    });
  });

  editButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const id =
        this.getAttribute("data-category-id") ||
        this.getAttribute("data-item-id");
      const type = this.getAttribute("data-category-id") ? "category" : "item";
      const newName = prompt(`Enter new name for the ${type}:`);
      if (newName) {
        fetch(`../actions/edit_${type}.php`, {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `id=${id}&name=${newName}`,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              alert(
                `${
                  type.charAt(0).toUpperCase() + type.slice(1)
                } updated successfully.`
              );
              location.reload(); // Reload the page to see the changes
            } else {
              alert(`Error updating ${type}: ` + data.message);
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            alert(`An error occurred while updating the ${type}.`);
          });
      }
    });
  });
});
