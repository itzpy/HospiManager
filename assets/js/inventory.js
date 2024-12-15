// DOM Elements
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
const stockFilter = document.getElementById('stockFilter');
const inventoryTable = document.getElementById('inventoryTable');

// Event Listeners
searchInput.addEventListener('input', filterItems);
categoryFilter.addEventListener('change', filterItems);
stockFilter.addEventListener('change', filterItems);

// Filter items based on search and filters
function filterItems() {
    const searchTerm = searchInput.value.toLowerCase();
    const categoryValue = categoryFilter.value;
    const stockValue = stockFilter.value;
    
    const rows = inventoryTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let row of rows) {
        const itemName = row.cells[0].textContent.toLowerCase();
        const category = row.dataset.category;
        const stockStatus = row.dataset.stock;
        
        const matchesSearch = itemName.includes(searchTerm);
        const matchesCategory = !categoryValue || category === categoryValue;
        const matchesStock = !stockValue || stockStatus === stockValue;
        
        row.style.display = matchesSearch && matchesCategory && matchesStock ? '' : 'none';
    }
}

// Modal Functions
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// Form Submissions
document.getElementById('addItemForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Detailed form data logging
    console.group('Add Item Form Submission');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }
    console.groupEnd();
    
    try {
        const response = await fetch('../../actions/add_item.php', {
            method: 'POST',
            body: formData
        });
        
        // Log full response
        const responseText = await response.text();
        console.log('Raw Response:', responseText);
        
        // Try to parse JSON
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Failed to parse JSON response:', parseError);
            console.log('Raw Response Content:', responseText);
            alert('Error: Unexpected server response');
            return;
        }
        
        if (data.success) {
            alert('Item added successfully');
            location.reload();
        } else {
            console.error('Add Item Error:', data);
            alert(data.message || 'Failed to add item');
        }
    } catch (error) {
        console.error('Add Item Network Error:', error);
        alert('An error occurred. Please check the console for details.');
    }
});

document.getElementById('editItemForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Detailed form data logging
    console.group('Edit Item Form Submission');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }
    console.groupEnd();
    
    try {
        const response = await fetch('../../actions/edit_item.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Item updated successfully');
            location.reload();
        } else {
            console.error('Edit Item Error:', data);
            alert(data.message || 'Failed to update item');
        }
    } catch (error) {
        console.error('Edit Item Network Error:', error);
        alert('An error occurred. Please check the console for details.');
    }
});

document.getElementById('adjustStockForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('../../actions/remove_inventory.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Stock updated successfully');
            location.reload();
        } else {
            console.error('Adjust Stock Error:', data);
            alert(data.message || 'Failed to update stock');
        }
    } catch (error) {
        console.error('Adjust Stock Network Error:', error);
        alert('An error occurred. Please check the console for details.');
    }
});

// Item Actions
function editItem(itemId) {
    // Fetch item details and populate edit modal
    fetch(`../../actions/get_item.php?id=${itemId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = data.item;
                document.getElementById('edit_item_id').value = item.item_id;
                document.getElementById('edit_item_name').value = item.name;
                document.getElementById('edit_category_id').value = item.category_id;
                document.getElementById('edit_unit').value = item.unit;
                document.getElementById('edit_description').value = item.description;
                document.getElementById('edit_quantity').value = item.quantity;
                
                openModal('editItemModal');
            } else {
                alert(data.message || 'Failed to fetch item details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while fetching item details');
        });
}

function adjustStock(itemId) {
    // Fetch item details and populate adjust stock modal
    fetch(`../../actions/get_item.php?id=${itemId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = data.item;
                document.getElementById('adjust_item_id').value = item.item_id;
                document.getElementById('adjust_item_name').textContent = item.name;
                document.getElementById('current_quantity').textContent = item.quantity;
                
                openModal('adjustStockModal');
            } else {
                alert(data.message || 'Failed to fetch item details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while fetching item details');
        });
}

function deleteItem(itemId) {
    if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
        return;
    }

    const formData = new FormData();
    formData.append('item_id', itemId);

    fetch('../../actions/delete_item.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Check if response is OK and content type is JSON
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Response is not JSON');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Item deleted successfully');
            location.reload();
        } else {
            // Use a more descriptive error message
            const errorMessage = data.message || 'Failed to delete item';
            console.error('Delete Item Error:', errorMessage);
            alert(errorMessage);
        }
    })
    .catch(error => {
        console.error('Delete Item Error:', error);
        alert('An unexpected error occurred while deleting the item. Please try again.');
    });
}

// Add CSS class for active navigation item
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-menu a');
    
    navLinks.forEach(link => {
        if (currentPath.includes(link.getAttribute('href'))) {
            link.parentElement.classList.add('active');
        }
    });
    
    const searchInput = document.getElementById('searchInput');
    
    // Debounce function to prevent excessive filtering
    function debounce(func, delay) {
        let timeoutId;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                func.apply(context, args);
            }, delay);
        };
    }

    // Function to update search filter
    function updateSearchFilter() {
        const searchValue = searchInput.value.trim();
        const urlParams = new URLSearchParams(window.location.search);
        
        if (searchValue) {
            urlParams.set('search', searchValue);
        } else {
            urlParams.delete('search');
        }
        
        window.location.search = urlParams.toString();
    }

    // Add event listener with debounce
    searchInput.addEventListener('input', debounce(updateSearchFilter, 500));
});
