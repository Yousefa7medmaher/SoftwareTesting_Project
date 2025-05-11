// Orders Management Functions
let orders = [];
let currentOrder = null;

// Fetch all orders
async function fetchOrders() {
    try {
        const response = await fetch('../api/orders/getOrders.php');
        const data = await response.json();
        orders = data;
        displayOrders();
    } catch (error) {
        console.error('Error fetching orders:', error);
        showAlert('Error loading orders', 'danger');
    }
}

// Display orders in the table
function displayOrders(filteredOrders = null) {
    const tableBody = document.getElementById('ordersTableBody');
    tableBody.innerHTML = '';

    const ordersToDisplay = filteredOrders || orders;

    ordersToDisplay.forEach(order => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>#${order.id}</td>
            <td>${order.user_name}</td>
            <td>${formatDate(order.created_at)}</td>
            <td>$${order.total_amount}</td>
            <td>
                <span class="status-badge status-${order.status}">
                    ${order.status}
                </span>
            </td>
            <td>
                <button class="btn btn-primary" onclick="viewOrderDetails(${order.id})">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-danger" onclick="cancelOrder(${order.id})">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Filter orders by status
function filterOrders() {
    const status = document.getElementById('statusFilter').value;
    const filteredOrders = status 
        ? orders.filter(order => order.status === status)
        : orders;
    displayOrders(filteredOrders);
}

// View order details
async function viewOrderDetails(orderId) {
    try {
        const response = await fetch(`../api/orders/getOrderDetails.php?id=${orderId}`);
        const data = await response.json();
        
        if (data.success) {
            currentOrder = data.order;
            displayOrderDetails(data.order);
            document.getElementById('orderModal').style.display = 'block';
        } else {
            showAlert(data.message || 'Error loading order details', 'danger');
        }
    } catch (error) {
        console.error('Error fetching order details:', error);
        showAlert('Error loading order details', 'danger');
    }
}

// Display order details in modal
function displayOrderDetails(order) {
    const orderDetails = document.getElementById('orderDetails');
    orderDetails.innerHTML = `
        <div class="order-info">
            <p><strong>Order ID:</strong> #${order.id}</p>
            <p><strong>Customer:</strong> ${order.user_name}</p>
            <p><strong>Date:</strong> ${formatDate(order.created_at)}</p>
            <p><strong>Status:</strong> 
                <select id="orderStatus" class="status-select">
                    <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Pending</option>
                    <option value="processing" ${order.status === 'processing' ? 'selected' : ''}>Processing</option>
                    <option value="completed" ${order.status === 'completed' ? 'selected' : ''}>Completed</option>
                    <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                </select>
            </p>
        </div>
        <div class="order-items">
            <h3>Order Items</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${order.items.map(item => `
                        <tr>
                            <td>${item.product_name}</td>
                            <td>${item.quantity}</td>
                            <td>$${item.price}</td>
                            <td>$${item.total}</td>
                        </tr>
                    `).join('')}
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"><strong>Total Amount</strong></td>
                        <td><strong>$${order.total_amount}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;
}

// Close order modal
function closeOrderModal() {
    document.getElementById('orderModal').style.display = 'none';
    currentOrder = null;
}

// Update order status
async function updateOrderStatus() {
    if (!currentOrder) return;

    const newStatus = document.getElementById('orderStatus').value;
    
    try {
        const response = await fetch(`../api/orders/updateOrderStatus.php?id=${currentOrder.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status: newStatus })
        });

        const data = await response.json();
        
        if (data.success) {
            showAlert('Order status updated successfully', 'success');
            closeOrderModal();
            fetchOrders();
        } else {
            showAlert(data.message || 'Error updating order status', 'danger');
        }
    } catch (error) {
        console.error('Error updating order status:', error);
        showAlert('Error updating order status', 'danger');
    }
}

// Cancel order
async function cancelOrder(orderId) {
    if (!confirm('Are you sure you want to cancel this order?')) return;

    try {
        const response = await fetch(`../api/orders/updateOrderStatus.php?id=${orderId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status: 'cancelled' })
        });

        const data = await response.json();
        
        if (data.success) {
            showAlert('Order cancelled successfully', 'success');
            fetchOrders();
        } else {
            showAlert(data.message || 'Error cancelling order', 'danger');
        }
    } catch (error) {
        console.error('Error cancelling order:', error);
        showAlert('Error cancelling order', 'danger');
    }
}

// Format date
function formatDate(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleString();
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
    fetchOrders();
}); 