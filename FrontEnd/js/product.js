// Initialize the page when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Fetch categories first
    fetchCategories();
    
    // Add event listeners for filters
    document.getElementById('categoryFilter').addEventListener('change', fetchProducts);
    document.getElementById('searchBar').addEventListener('input', debounce(fetchProducts, 500));
    
    // Fetch products on page load
    fetchProducts();
});

// Function to fetch categories from the API
function fetchCategories() {
    const categoryFilter = document.getElementById('categoryFilter');
    
    // Keep the "All Categories" option
    categoryFilter.innerHTML = '<option value="all">All Categories</option>';
    
    fetch('http://localhost/Ecommerce/BackEnd/api/categories/getCategories.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(categories => {
            // Add each category to the dropdown
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id; // Use category ID instead of name
                option.textContent = category.name;
                categoryFilter.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error fetching categories:', error);
            // If there's an error, keep the default categories
            const defaultCategories = [
                { id: 1, name: 'Electronics' },
                { id: 2, name: 'Clothing' },
                { id: 3, name: 'Home & Kitchen' },
                { id: 4, name: 'Accessories' }
            ];
            
            defaultCategories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                categoryFilter.appendChild(option);
            });
        });
}

// Debounce function to limit API calls during typing
function debounce(func, delay) {
    let timeout;
    return function() {
        const context = this;
        const args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), delay);
    };
}

// Function to fetch products from the API
function fetchProducts() {
    const productsContainer = document.getElementById('products');
    const categoryId = document.getElementById('categoryFilter').value;
    const searchTerm = document.getElementById('searchBar').value.toLowerCase();
    
    // Show loading state
    productsContainer.innerHTML = `
        <div class="loading">
            <i class="bx bx-loader-alt bx-spin"></i>
            <p>Loading products...</p>
        </div>
    `;

    // Build the API URL with query parameters
    let apiUrl = 'http://localhost/Ecommerce/BackEnd/api/products/getProducts.php';
    
    // Add query parameters if they have values
    const params = new URLSearchParams();
    if (categoryId !== 'all') {
        params.append('category', categoryId);
    }
    if (searchTerm) {
        params.append('search', searchTerm);
    }
    
    // Append query string if there are parameters
    if (params.toString()) {
        apiUrl += '?' + params.toString();
    }

    console.log('Fetching products with URL:', apiUrl);

    // Fetch products from the API
    fetch(apiUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            displayProducts(data);
        })
        .catch(error => {
            console.error('Error fetching products:', error);
            productsContainer.innerHTML = `
                <div class="error-message">
                    <i class="bx bx-error-circle"></i>
                    <p>There was an error loading the products. Please try again later.</p>
                    <p class="error-details">${error.message}</p>
                </div>
            `;
        });
}

// Function to display products in the container
function displayProducts(products) {
    const productsContainer = document.getElementById('products');
    productsContainer.innerHTML = '';  
    
    if (!products || products.length === 0) {
        productsContainer.innerHTML = `
            <div class="no-products">
                <i class="bx bx-package"></i>
                <p>No products found matching your criteria.</p>
                <a href="product.html" class="btn">View All Products</a>
            </div>
        `;
        return;
    }
    
    products.forEach(product => { 
        const productElement = document.createElement('div');
        productElement.classList.add('product');
        productElement.setAttribute('data-category', product.category_id);
    
        productElement.innerHTML = `
            <div class="product-image">
                <img src="${product.image_products}" alt="${product.name}">
            </div>
            <div class="product-info">
                <h3>${product.name}</h3>
                <p>${product.description || 'No description available'}</p>
                <div class="price">$${product.price}</div>
                <div class="quantity-selector">
                    <label for="quantity-${product.id}">Quantity:</label>
                    <input type="number" id="quantity-${product.id}" value="1" min="1" max="10">
                </div>
                <button onclick="addToCart(${product.id})">
                    <i class="bx bx-cart-add"></i> Add to Cart
                </button>
            </div>
        `;
    
        productsContainer.appendChild(productElement);
    });
}

// Function to add a product to the cart
function addToCart(productId) {
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    const quantityInput = document.getElementById(`quantity-${productId}`);
    const quantity = parseInt(quantityInput.value) || 1;
    
    // Get user data from localStorage
    const userData = JSON.parse(localStorage.getItem('user') || '{}');
    
    console.log('Adding to cart - User data:', userData);
    
    // Check if user is logged in
    if (!userData.id) {
        alert('Please login to add items to your cart');
        window.location.href = 'login.html';
        return;
    }
    
    // Show loading state
    button.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Adding...';
    button.disabled = true;
    
    const data = { 
        product_id: productId,
        quantity: quantity,
        user_id: userData.id
    };

    console.log('Add to cart request data:', data);

    // Use the full URL to avoid any path issues
    fetch('http://localhost/Ecommerce/BackEnd/api/cart/addToCart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data),
        credentials: 'include', // Include cookies for session support
        mode: 'cors' // Explicitly set CORS mode
    })
    .then(response => {
        console.log('Add to cart response status:', response.status);
        return response.json().then(data => {
            if (!response.ok) {
                throw new Error(data.error || `HTTP error! Status: ${response.status}`);
            }
            return data;
        });
    })
    .then(data => {
        console.log('Add to cart success response:', data);
        if (data.success) {
            // Show success message
            button.innerHTML = '<i class="bx bx-check"></i> Added!';
            button.classList.add('success');
            
            // Reset button after 2 seconds
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('success');
                button.disabled = false;
            }, 2000);
        } else {
            throw new Error(data.error || 'Failed to add item to cart');
        }
    })
    .catch(error => {
        console.error('Add to cart error:', error);
        
        // Show error state
        button.innerHTML = '<i class="bx bx-x"></i> Error';
        button.classList.add('error');
        
        // Reset button after 2 seconds
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('error');
            button.disabled = false;
        }, 2000);
        
        // Show error message to user
        alert(error.message || 'Failed to add item to cart. Please try again.');
    });
}
