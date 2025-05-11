// Product Management Functions
let products = [];
let categories = [];

// Fetch all products
async function fetchProducts() {
    try {
        const response = await fetch('../api/products/getProducts.php');
        const data = await response.json();
        products = data;
        displayProducts();
    } catch (error) {
        console.error('Error fetching products:', error);
        showAlert('Error loading products', 'danger');
    }
}

// Fetch all categories
async function fetchCategories() {
    try {
        const response = await fetch('../api/categories/getCategories.php');
        const data = await response.json();
        categories = data;
        populateCategorySelect();
    } catch (error) {
        console.error('Error fetching categories:', error);
        showAlert('Error loading categories', 'danger');
    }
}

// Display products in the table
function displayProducts() {
    const tableBody = document.getElementById('productsTableBody');
    tableBody.innerHTML = '';

    products.forEach(product => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${product.id}</td>
            <td>
                <img src="${product.image_url}" alt="${product.name}" style="width: 50px; height: 50px; object-fit: cover;">
            </td>
            <td>${product.name}</td>
            <td>${getCategoryName(product.category_id)}</td>
            <td>$${product.price}</td>
            <td>${product.stock}</td>
            <td>
                <button class="btn btn-primary" onclick="editProduct(${product.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger" onclick="deleteProduct(${product.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Get category name by ID
function getCategoryName(categoryId) {
    const category = categories.find(c => c.id === categoryId);
    return category ? category.name : 'Unknown';
}

// Populate category select in the form
function populateCategorySelect() {
    const select = document.getElementById('productCategory');
    select.innerHTML = '<option value="">Select a category</option>';
    
    categories.forEach(category => {
        const option = document.createElement('option');
        option.value = category.id;
        option.textContent = category.name;
        select.appendChild(option);
    });
}

// Open add product modal
function openAddProductModal() {
    document.getElementById('modalTitle').textContent = 'Add New Product';
    document.getElementById('productId').value = '';
    document.getElementById('productForm').reset();
    document.getElementById('productModal').style.display = 'block';
}

// Open edit product modal
function editProduct(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;

    document.getElementById('modalTitle').textContent = 'Edit Product';
    document.getElementById('productId').value = product.id;
    document.getElementById('productName').value = product.name;
    document.getElementById('productCategory').value = product.category_id;
    document.getElementById('productPrice').value = product.price;
    document.getElementById('productStock').value = product.stock;
    document.getElementById('productDescription').value = product.description;

    document.getElementById('productModal').style.display = 'block';
}

// Close product modal
function closeProductModal() {
    document.getElementById('productModal').style.display = 'none';
}

// Handle product form submission
async function handleProductSubmit(event) {
    event.preventDefault();

    const productId = document.getElementById('productId').value;
    const formData = new FormData();
    
    formData.append('name', document.getElementById('productName').value);
    formData.append('category_id', document.getElementById('productCategory').value);
    formData.append('price', document.getElementById('productPrice').value);
    formData.append('stock', document.getElementById('productStock').value);
    formData.append('description', document.getElementById('productDescription').value);
    
    const imageFile = document.getElementById('productImage').files[0];
    if (imageFile) {
        formData.append('image', imageFile);
    }

    try {
        const url = productId 
            ? `../api/products/updateProduct.php?id=${productId}`
            : '../api/products/addProduct.php';
        
        const response = await fetch(url, {
            method: productId ? 'PUT' : 'POST',
            body: formData
        });

        const data = await response.json();
        
        if (data.success) {
            showAlert(productId ? 'Product updated successfully' : 'Product added successfully', 'success');
            closeProductModal();
            fetchProducts();
        } else {
            showAlert(data.message || 'Error saving product', 'danger');
        }
    } catch (error) {
        console.error('Error saving product:', error);
        showAlert('Error saving product', 'danger');
    }
}

// Delete product
async function deleteProduct(productId) {
    if (!confirm('Are you sure you want to delete this product?')) return;

    try {
        const response = await fetch(`../api/products/deleteProduct.php?id=${productId}`, {
            method: 'DELETE'
        });

        const data = await response.json();
        
        if (data.success) {
            showAlert('Product deleted successfully', 'success');
            fetchProducts();
        } else {
            showAlert(data.message || 'Error deleting product', 'danger');
        }
    } catch (error) {
        console.error('Error deleting product:', error);
        showAlert('Error deleting product', 'danger');
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
    fetchProducts();
}); 