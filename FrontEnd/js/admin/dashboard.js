document.addEventListener('DOMContentLoaded', function() {
    // Check if user is logged in and is an admin
    const userData = JSON.parse(localStorage.getItem('user') || '{}');
    if (!userData.id || !userData.isAdmin) {
        window.location.href = '../login.html';
        return;
    }

    // Set admin name
    document.getElementById('adminName').textContent = userData.name || 'Admin';

    // Initialize variables
    let products = [];
    let categories = [];
    let orders = [];
    let users = [];

    // Fetch initial data
    fetchProducts();
    fetchCategories();
    fetchOrders();
    fetchUsers();

    // Event Listeners
    document.getElementById('logoutBtn').addEventListener('click', handleLogout);

    // Functions
    async function fetchProducts() {
        try {
            const response = await fetch('http://localhost/Ecommerce/BackEnd/api/products/getProducts.php', {
                method: 'GET',
                credentials: 'include'
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            products = data;
            updateProductStats();
        } catch (error) {
            console.error('Error fetching products:', error);
            showError('Failed to load products');
        }
    }

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
            updateCategoryStats();
        } catch (error) {
            console.error('Error fetching categories:', error);
            showError('Failed to load categories');
        }
    }

    async function fetchOrders() {
        try {
            const response = await fetch('http://localhost/Ecommerce/BackEnd/api/admin/orders.php', {
                method: 'GET',
                credentials: 'include'
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            orders = data.orders || [];
            updateOrderStats();
        } catch (error) {
            console.error('Error fetching orders:', error);
            showError('Failed to load orders');
        }
    }

    async function fetchUsers() {
        try {
            const response = await fetch('http://localhost/Ecommerce/BackEnd/api/admin/users.php', {
                method: 'GET',
                credentials: 'include'
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            users = data.users || [];
            updateUserStats();
        } catch (error) {
            console.error('Error fetching users:', error);
            showError('Failed to load users');
        }
    }

    function updateProductStats() {
        const totalProducts = products.length;
        document.getElementById('totalProducts').textContent = totalProducts;
    }

    function updateCategoryStats() {
        const totalCategories = categories.length;
        document.getElementById('totalCategories').textContent = totalCategories;
    }

    function updateOrderStats() {
        const totalOrders = orders.length;
        document.getElementById('totalOrders').textContent = totalOrders;
    }

    function updateUserStats() {
        const totalUsers = users.length;
        document.getElementById('totalUsers').textContent = totalUsers;
    }

    function showError(message) {
        const activityList = document.getElementById('activityList');
        activityList.innerHTML = `
            <div class="activity-item error">
                <i class="bx bx-error-circle"></i>
                <div class="activity-details">
                    <div class="activity-title">Error</div>
                    <div class="activity-time">${message}</div>
                </div>
            </div>
        `;
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
}); 