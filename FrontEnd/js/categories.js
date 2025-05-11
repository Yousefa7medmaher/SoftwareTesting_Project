// Categories Management Functions
let categories = [];

// Fetch all categories
async function fetchCategories() {
    try {
        const response = await fetch('../api/categories/getCategories.php');
        const data = await response.json();
        categories = data;
        displayCategories();
    } catch (error) {
        console.error('Error fetching categories:', error);
        showAlert('Error loading categories', 'danger');
    }
}

// Display categories in the table
function displayCategories() {
    const tableBody = document.getElementById('categoriesTableBody');
    tableBody.innerHTML = '';

    categories.forEach(category => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${category.id}</td>
            <td>${category.name}</td>
            <td>${category.description}</td>
            <td>${category.products_count || 0}</td>
            <td>
                <button class="btn btn-primary" onclick="editCategory(${category.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger" onclick="deleteCategory(${category.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Open add category modal
function openAddCategoryModal() {
    document.getElementById('modalTitle').textContent = 'Add New Category';
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryModal').style.display = 'block';
}

// Open edit category modal
function editCategory(categoryId) {
    const category = categories.find(c => c.id === categoryId);
    if (!category) return;

    document.getElementById('modalTitle').textContent = 'Edit Category';
    document.getElementById('categoryId').value = category.id;
    document.getElementById('categoryName').value = category.name;
    document.getElementById('categoryDescription').value = category.description;

    document.getElementById('categoryModal').style.display = 'block';
}

// Close category modal
function closeCategoryModal() {
    document.getElementById('categoryModal').style.display = 'none';
}

// Handle category form submission
async function handleCategorySubmit(event) {
    event.preventDefault();

    const categoryId = document.getElementById('categoryId').value;
    const formData = {
        name: document.getElementById('categoryName').value,
        description: document.getElementById('categoryDescription').value
    };

    try {
        const url = categoryId 
            ? `../api/categories/updateCategory.php?id=${categoryId}`
            : '../api/categories/addCategory.php';
        
        const response = await fetch(url, {
            method: categoryId ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();
        
        if (data.success) {
            showAlert(categoryId ? 'Category updated successfully' : 'Category added successfully', 'success');
            closeCategoryModal();
            fetchCategories();
        } else {
            showAlert(data.message || 'Error saving category', 'danger');
        }
    } catch (error) {
        console.error('Error saving category:', error);
        showAlert('Error saving category', 'danger');
    }
}

// Delete category
async function deleteCategory(categoryId) {
    if (!confirm('Are you sure you want to delete this category? This will also remove it from all products.')) return;

    try {
        const response = await fetch(`../api/categories/deleteCategory.php?id=${categoryId}`, {
            method: 'DELETE'
        });

        const data = await response.json();
        
        if (data.success) {
            showAlert('Category deleted successfully', 'success');
            fetchCategories();
        } else {
            showAlert(data.message || 'Error deleting category', 'danger');
        }
    } catch (error) {
        console.error('Error deleting category:', error);
        showAlert('Error deleting category', 'danger');
    }
}

// Show alert message
function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;

    const contentHeader = document.querySelector('.content-header');
    contentHeader.insertAdjacentElement('afterend', alertDiv);

    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    checkAdminAccess();
    fetchCategories();
}); 