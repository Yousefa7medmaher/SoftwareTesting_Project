document.addEventListener('DOMContentLoaded', function() {
    // Check if user is logged in and is an admin
    const userData = JSON.parse(localStorage.getItem('user') || '{}');
    
    if (!userData.id || userData.role !== 'admin') {
        customAlert.error('Access denied', 'You must be logged in as an admin to access this page');
        setTimeout(() => {
            window.location.href = '../login.html';
        }, 2000);
        return;
    }
    
    // Set admin name
    document.getElementById('adminName').textContent = userData.name || 'Admin';
    
    // Initialize variables
    const productsTableBody = document.getElementById('productsTableBody');
    const searchInput = document.getElementById('searchInput');
    const productModal = document.getElementById('productModal');
    const productForm = document.getElementById('productForm');
    const modalTitle = document.getElementById('modalTitle');
    const productIdInput = document.getElementById('productId');
    const addProductBtn = document.getElementById('addProductBtn');
    const closeModalBtn = document.getElementById('closeModal');
    const cancelBtn = document.getElementById('cancelBtn');
    
    let products = [];
    let categories = [];
    
    // Fetch products and categories
    fetchProducts();
    fetchCategories();
    
    // Event Listeners
    addProductBtn.addEventListener('click', () => openModal());
    closeModalBtn.addEventListener('click', () => closeModal());
    cancelBtn.addEventListener('click', () => closeModal());
    
    productForm.addEventListener('submit', (e) => {
        e.preventDefault();
        saveProduct();
    });
    
    searchInput.addEventListener('input', () => {
        const searchTerm = searchInput.value.toLowerCase();
        const filteredProducts = products.filter(product => 
            product.name.toLowerCase().includes(searchTerm) ||
            product.category_name.toLowerCase().includes(searchTerm)
        );
        displayProducts(filteredProducts);
    });
    
    // Functions
    function fetchProducts() {
        fetch('http://localhost/Ecommerce/BackEnd/api/admin/products.php', {
            method: 'GET',
            credentials: 'include'
        })
        .then(response => {
            console.log('Products fetch response status:', response.status);
            if (!response.ok) {
                return response.json().then(data => {
                    console.error('Products fetch error response:', data);
                    throw new Error(data.error || `HTTP error! Status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Products received:', data);
            products = data.products || [];
            displayProducts(products);
            customAlert.success('Products loaded successfully');
        })
        .catch(error => {
            console.error('Error fetching products:', error);
            productsTableBody.innerHTML = `
                <tr>
                    <td colspan="6">
                        <div class="error-message">
                            <i class="bx bx-error-circle"></i>
                            <p>Error loading products: ${error.message}</p>
                            <button onclick="fetchProducts()" class="btn">Retry</button>
                        </div>
                    </td>
                </tr>
            `;
            customAlert.error('Failed to load products', error.message);
        });
    }
    
    function fetchCategories() {
        fetch('http://localhost/Ecommerce/BackEnd/api/admin/categories.php', {
            method: 'GET',
            credentials: 'include'
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.error || `HTTP error! Status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            categories = data.categories || [];
            updateCategorySelect();
        })
        .catch(error => {
            console.error('Error fetching categories:', error);
            customAlert.error('Failed to load categories', error.message);
        });
    }
    
    function displayProducts(productsToDisplay) {
        if (!productsToDisplay || productsToDisplay.length === 0) {
            productsTableBody.innerHTML = `
                <tr>
                    <td colspan="6">
                        <div class="no-data">
                            <i class="bx bx-package"></i>
                            <p>No products found</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }
        
        let html = '';
        
        productsToDisplay.forEach(product => {
            html += `
                <tr>
                    <td>
                        <img src="${product.image_products}" alt="${product.name}" class="product-img">
                    </td>
                    <td>${product.name}</td>
                    <td>${product.category_name}</td>
                    <td>$${parseFloat(product.price).toFixed(2)}</td>
                    <td>${product.stock}</td>
                    <td>
                        <button class="action-btn edit" onclick="editProduct(${product.id})">
                            <i class="bx bx-edit"></i>
                        </button>
                        <button class="action-btn delete" onclick="deleteProduct(${product.id})">
                            <i class="bx bx-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        productsTableBody.innerHTML = html;
    }
    
    function updateCategorySelect() {
        const categorySelect = document.getElementById('productCategory');
        let options = '<option value="">Select a category</option>';
        
        categories.forEach(category => {
            options += `<option value="${category.id}">${category.name}</option>`;
        });
        
        categorySelect.innerHTML = options;
    }
    
    function openModal(productId = null) {
        if (productId) {
            // Edit mode
            const product = products.find(p => p.id === productId);
            if (product) {
                modalTitle.textContent = 'Edit Product';
                productIdInput.value = product.id;
                document.getElementById('productName').value = product.name;
                document.getElementById('productCategory').value = product.category_id;
                document.getElementById('productPrice').value = product.price;
                document.getElementById('productStock').value = product.stock;
                document.getElementById('productDescription').value = product.description;
                document.getElementById('productImage').value = product.image_products;
            }
        } else {
            // Add mode
            modalTitle.textContent = 'Add New Product';
            productForm.reset();
            productIdInput.value = '';
        }
        
        productModal.style.display = 'block';
    }
    
    function closeModal() {
        productModal.style.display = 'none';
        productForm.reset();
    }
    
    function saveProduct() {
        const productId = productIdInput.value;
        const formData = {
            name: document.getElementById('productName').value,
            category_id: document.getElementById('productCategory').value,
            price: document.getElementById('productPrice').value,
            stock: document.getElementById('productStock').value,
            description: document.getElementById('productDescription').value,
            image_products: document.getElementById('productImage').value
        };
        
        const url = 'http://localhost/Ecommerce/BackEnd/api/admin/products.php';
        const method = productId ? 'PUT' : 'POST';
        
        if (productId) {
            formData.id = productId;
        }
        
        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.error || `HTTP error! Status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            closeModal();
            fetchProducts();
            customAlert.success(productId ? 'Product updated successfully' : 'Product added successfully');
        })
        .catch(error => {
            console.error('Error saving product:', error);
            customAlert.error('Failed to save product', error.message);
        });
    }
    
    // Make functions available globally
    window.editProduct = function(productId) {
        openModal(productId);
    };
    
    window.deleteProduct = function(productId) {
        if (confirm('Are you sure you want to delete this product?')) {
            fetch('http://localhost/Ecommerce/BackEnd/api/admin/products.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({ id: productId })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.error || `HTTP error! Status: ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                fetchProducts();
                customAlert.success('Product deleted successfully');
            })
            .catch(error => {
                console.error('Error deleting product:', error);
                customAlert.error('Failed to delete product', error.message);
            });
        }
    };
    
    // Logout functionality
    document.getElementById('logoutBtn').addEventListener('click', function(e) {
        e.preventDefault();
        
        fetch('http://localhost/Ecommerce/BackEnd/api/auth/logout.php', {
            method: 'POST',
            credentials: 'include'
        })
        .then(response => response.json())
        .then(data => {
            localStorage.removeItem('user');
            localStorage.removeItem('userToken');
            customAlert.success('Logged out successfully');
            setTimeout(() => {
                window.location.href = '../login.html';
            }, 1500);
        })
        .catch(error => {
            console.error('Error logging out:', error);
            customAlert.error('Error logging out', error.message);
        });
    });
}); 