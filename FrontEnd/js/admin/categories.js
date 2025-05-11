document.addEventListener('DOMContentLoaded', function() {
    // Check if user is logged in and is an admin
    const userData = JSON.parse(localStorage.getItem('user') || '{}');
    if (!userData.id || userData.role !== 'admin') {
        window.location.href = '../login.html';
        return;
    }

    // Set admin name
    document.getElementById('adminName').textContent = userData.name || 'Admin';

    // Initialize variables
    let categories = [];
    const categoriesTable = document.getElementById('categoriesTable');
    const categoryModal = document.getElementById('categoryModal');
    const categoryForm = document.getElementById('categoryForm');
    const searchInput = document.getElementById('searchInput');

    // Event Listeners
    document.getElementById('addCategoryBtn').addEventListener('click', () => openModal());
    document.getElementById('closeModalBtn').addEventListener('click', closeModal);
    document.getElementById('cancelBtn').addEventListener('click', closeModal);
    categoryForm.addEventListener('submit', handleCategorySubmit);
    searchInput.addEventListener('input', handleSearch);
    document.getElementById('logoutBtn').addEventListener('click', handleLogout);

    // Initial load
    fetchCategories();

    // Functions
    async function fetchCategories() {
        try {
            const response = await fetch('http://localhost/Ecommerce/BackEnd/api/admin/categories.php', {
                method: 'GET',
                credentials: 'include'
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            categories = data.categories || [];
            displayCategories(categories);
        } catch (error) {
            console.error('Error fetching categories:', error);
            showError('Failed to load categories');
        }
    }

    function displayCategories(categoriesToShow) {
        const tbody = categoriesTable.querySelector('tbody');
        tbody.innerHTML = '';

        if (categoriesToShow.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center">No categories found</td>
                </tr>
            `;
            return;
        }

        categoriesToShow.forEach(category => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${category.id}</td>
                <td>${category.name}</td>
                <td>${category.description || ''}</td>
                <td>${category.product_count || 0}</td>
                <td>
                    <button class="edit-btn" onclick="editCategory(${category.id})">
                        <i class="bx bx-edit"></i>
                    </button>
                    <button class="delete-btn" onclick="deleteCategory(${category.id})">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function handleSearch(e) {
        const searchTerm = e.target.value.toLowerCase();
        const filteredCategories = categories.filter(category => 
            category.name.toLowerCase().includes(searchTerm) ||
            (category.description && category.description.toLowerCase().includes(searchTerm))
        );
        displayCategories(filteredCategories);
    }

    function openModal(category = null) {
        const modalTitle = categoryModal.querySelector('.modal-title');
        const submitBtn = categoryForm.querySelector('button[type="submit"]');
        
        if (category) {
            modalTitle.textContent = 'Edit Category';
            submitBtn.textContent = 'Update Category';
            categoryForm.dataset.mode = 'edit';
            categoryForm.dataset.id = category.id;
            
            document.getElementById('categoryName').value = category.name;
            document.getElementById('categoryDescription').value = category.description || '';
        } else {
            modalTitle.textContent = 'Add New Category';
            submitBtn.textContent = 'Add Category';
            categoryForm.dataset.mode = 'add';
            categoryForm.reset();
        }
        
        categoryModal.style.display = 'block';
    }

    function closeModal() {
        categoryModal.style.display = 'none';
        categoryForm.reset();
    }

    async function handleCategorySubmit(e) {
        e.preventDefault();
        
        const formData = {
            name: document.getElementById('categoryName').value,
            description: document.getElementById('categoryDescription').value
        };

        if (categoryForm.dataset.mode === 'edit') {
            formData.id = categoryForm.dataset.id;
        }

        try {
            const response = await fetch('http://localhost/Ecommerce/BackEnd/api/admin/categories.php', {
                method: categoryForm.dataset.mode === 'add' ? 'POST' : 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData),
                credentials: 'include'
            });

            const data = await response.json();

            if (response.ok) {
                closeModal();
                fetchCategories();
                showSuccess(data.message);
            } else {
                showError(data.error || 'Failed to save category');
            }
        } catch (error) {
            console.error('Error saving category:', error);
            showError('An error occurred while saving the category');
        }
    }

    async function deleteCategory(id) {
        if (!confirm('Are you sure you want to delete this category?')) {
            return;
        }

        try {
            const response = await fetch('http://localhost/Ecommerce/BackEnd/api/admin/categories.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id }),
                credentials: 'include'
            });

            const data = await response.json();

            if (response.ok) {
                fetchCategories();
                showSuccess(data.message);
            } else {
                showError(data.error || 'Failed to delete category');
            }
        } catch (error) {
            console.error('Error deleting category:', error);
            showError('An error occurred while deleting the category');
        }
    }

    function showError(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert error';
        alertDiv.textContent = message;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 3000);
    }

    function showSuccess(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert success';
        alertDiv.textContent = message;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 3000);
    }

    async function handleLogout(e) {
        e.preventDefault();
        try {
            const response = await fetch('http://localhost/Ecommerce/BackEnd/api/auth/logout.php', {
                method: 'POST',
                credentials: 'include'
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            localStorage.removeItem('user');
            window.location.href = '../login.html';
        } catch (error) {
            console.error('Error logging out:', error);
            showError('Failed to logout');
        }
    }

    // Make functions available globally
    window.editCategory = function(id) {
        const category = categories.find(c => c.id === id);
        if (category) {
            openModal(category);
        }
    };

    window.deleteCategory = deleteCategory;
}); 