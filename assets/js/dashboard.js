// Modal Functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// User Management Functions
async function handleAddUser(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    try {
        const response = await fetch('../../actions/add_user.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        
        if (data.success) {
            alert('User added successfully!');
            closeModal('addUserModal');
            location.reload(); // Refresh to show new user
        } else {
            alert(data.message || 'Error adding user');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while adding the user');
    }
}

async function editUser(userId) {
    try {
        const response = await fetch(`../../actions/get_user.php?id=${userId}`);
        const data = await response.json();
        
        if (data.success) {
            // Populate edit form
            document.getElementById('edit_user_id').value = data.user.user_id;
            document.getElementById('edit_first_name').value = data.user.first_name;
            document.getElementById('edit_last_name').value = data.user.last_name;
            document.getElementById('edit_email').value = data.user.email;
            document.getElementById('edit_role').value = data.user.role;
            
            openModal('editUserModal');
        } else {
            alert(data.message || 'Error fetching user data');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while fetching user data');
    }
}

async function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user?')) {
        return;
    }

    try {
        const response = await fetch('../../actions/delete_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ user_id: userId })
        });

        const data = await response.json();
        
        if (data.success) {
            alert('User deleted successfully!');
            location.reload(); // Refresh to update user list
        } else {
            alert(data.message || 'Error deleting user');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while deleting the user');
    }
}

// Category Management Functions
async function handleAddCategory(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    try {
        const response = await fetch('../../actions/add_category.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        
        if (data.success) {
            alert('Category added successfully!');
            closeModal('addCategoryModal');
            location.reload(); // Refresh to show new category
        } else {
            alert(data.message || 'Error adding category');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while adding the category');
    }
}

async function editCategory(categoryId) {
    try {
        const response = await fetch(`../../actions/get_category.php?id=${categoryId}`);
        const data = await response.json();
        
        if (data.success) {
            // Populate edit form
            document.getElementById('edit_category_id').value = data.category.category_id;
            document.getElementById('edit_category_name').value = data.category.name;
            document.getElementById('edit_category_description').value = data.category.description;
            
            openModal('editCategoryModal');
        } else {
            alert(data.message || 'Error fetching category data');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while fetching category data');
    }
}

async function deleteCategory(categoryId) {
    if (!confirm('Are you sure you want to delete this category? All items in this category will be affected.')) {
        return;
    }

    try {
        const response = await fetch('../../actions/delete_category.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ category_id: categoryId })
        });

        const data = await response.json();
        
        if (data.success) {
            alert('Category deleted successfully!');
            location.reload(); // Refresh to update category list
        } else {
            alert(data.message || 'Error deleting category');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while deleting the category');
    }
}

// Add event listeners when document loads
document.addEventListener('DOMContentLoaded', function() {
    // Add user form submission
    const addUserForm = document.getElementById('addUserForm');
    if (addUserForm) {
        addUserForm.addEventListener('submit', handleAddUser);
    }

    // Add category form submission
    const addCategoryForm = document.getElementById('addCategoryForm');
    if (addCategoryForm) {
        addCategoryForm.addEventListener('submit', handleAddCategory);
    }
});
