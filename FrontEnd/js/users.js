// User Management Functions
let users = [];

// Fetch all users
async function fetchUsers() {
    try {
        const response = await fetch('../api/users/getUsers.php');
        const data = await response.json();
        users = data;
        displayUsers();
    } catch (error) {
        console.error('Error fetching users:', error);
        showAlert('Error loading users', 'danger');
    }
}

// Display users in the table
function displayUsers() {
    const tableBody = document.getElementById('usersTableBody');
    tableBody.innerHTML = '';

    users.forEach(user => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${user.id}</td>
            <td>${user.name}</td>
            <td>${user.email}</td>
            <td>
                <span class="badge ${user.role === 'admin' ? 'badge-primary' : 'badge-secondary'}">
                    ${user.role}
                </span>
            </td>
            <td>
                <span class="badge ${user.status === 'active' ? 'badge-success' : 'badge-danger'}">
                    ${user.status}
                </span>
            </td>
            <td>${formatDate(user.last_login)}</td>
            <td>
                <button class="btn btn-primary" onclick="editUser(${user.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger" onclick="deleteUser(${user.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Format date
function formatDate(dateString) {
    if (!dateString) return 'Never';
    const date = new Date(dateString);
    return date.toLocaleString();
}

// Open add user modal
function openAddUserModal() {
    document.getElementById('modalTitle').textContent = 'Add New User';
    document.getElementById('userId').value = '';
    document.getElementById('userForm').reset();
    document.getElementById('userPassword').required = true;
    document.getElementById('userModal').style.display = 'block';
}

// Open edit user modal
function editUser(userId) {
    const user = users.find(u => u.id === userId);
    if (!user) return;

    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('userId').value = user.id;
    document.getElementById('userName').value = user.name;
    document.getElementById('userEmail').value = user.email;
    document.getElementById('userRole').value = user.role;
    document.getElementById('userStatus').value = user.status;
    document.getElementById('userPassword').required = false;
    document.getElementById('userPassword').value = '';

    document.getElementById('userModal').style.display = 'block';
}

// Close user modal
function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
}

// Handle user form submission
async function handleUserSubmit(event) {
    event.preventDefault();

    const userId = document.getElementById('userId').value;
    const formData = new FormData();
    
    formData.append('name', document.getElementById('userName').value);
    formData.append('email', document.getElementById('userEmail').value);
    formData.append('role', document.getElementById('userRole').value);
    formData.append('status', document.getElementById('userStatus').value);
    
    const password = document.getElementById('userPassword').value;
    if (password) {
        formData.append('password', password);
    }

    try {
        const url = userId 
            ? `../api/users/updateUser.php?id=${userId}`
            : '../api/users/addUser.php';
        
        const response = await fetch(url, {
            method: userId ? 'PUT' : 'POST',
            body: formData
        });

        const data = await response.json();
        
        if (data.success) {
            showAlert(userId ? 'User updated successfully' : 'User added successfully', 'success');
            closeUserModal();
            fetchUsers();
        } else {
            showAlert(data.message || 'Error saving user', 'danger');
        }
    } catch (error) {
        console.error('Error saving user:', error);
        showAlert('Error saving user', 'danger');
    }
}

// Delete user
async function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user?')) return;

    try {
        const response = await fetch(`../api/users/deleteUser.php?id=${userId}`, {
            method: 'DELETE'
        });

        const data = await response.json();
        
        if (data.success) {
            showAlert('User deleted successfully', 'success');
            fetchUsers();
        } else {
            showAlert(data.message || 'Error deleting user', 'danger');
        }
    } catch (error) {
        console.error('Error deleting user:', error);
        showAlert('Error deleting user', 'danger');
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
    fetchUsers();
}); 