// Check if user is admin
function checkAdminAccess() {
    const user = JSON.parse(localStorage.getItem('user'));
    if (!user || user.role !== 'admin') {
        window.location.href = '../login.html';
    }
}

// Fetch dashboard statistics
async function fetchDashboardStats() {
    try {
        const response = await fetch('../api/admin/getStats.php');
        const data = await response.json();
        
        if (data.success) {
            updateDashboardStats(data.stats);
        }
    } catch (error) {
        console.error('Error fetching dashboard stats:', error);
    }
}

// Update dashboard statistics
function updateDashboardStats(stats) {
    document.querySelector('.stat-number:nth-child(1)').textContent = stats.totalOrders;
    document.querySelector('.stat-number:nth-child(2)').textContent = stats.totalUsers;
    document.querySelector('.stat-number:nth-child(3)').textContent = stats.totalProducts;
    document.querySelector('.stat-number:nth-child(4)').textContent = `$${stats.totalRevenue}`;
}

// Fetch recent activity
async function fetchRecentActivity() {
    try {
        const response = await fetch('../api/admin/getRecentActivity.php');
        const data = await response.json();
        
        if (data.success) {
            displayRecentActivity(data.activities);
        }
    } catch (error) {
        console.error('Error fetching recent activity:', error);
    }
}

// Display recent activity
function displayRecentActivity(activities) {
    const activityList = document.querySelector('.activity-list');
    activityList.innerHTML = '';

    activities.forEach(activity => {
        const activityItem = document.createElement('div');
        activityItem.className = 'activity-item';
        activityItem.innerHTML = `
            <div class="activity-icon">
                <i class="fas ${getActivityIcon(activity.type)}"></i>
            </div>
            <div class="activity-details">
                <p>${activity.description}</p>
                <small>${formatDate(activity.timestamp)}</small>
            </div>
        `;
        activityList.appendChild(activityItem);
    });
}

// Get activity icon based on type
function getActivityIcon(type) {
    const icons = {
        order: 'fa-shopping-cart',
        user: 'fa-user',
        product: 'fa-box',
        category: 'fa-tag'
    };
    return icons[type] || 'fa-info-circle';
}

// Format date
function formatDate(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleString();
}

// Handle logout
function handleLogout() {
    localStorage.removeItem('user');
    window.location.href = '../login.html';
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', () => {
    checkAdminAccess();
    fetchDashboardStats();
    fetchRecentActivity();

    // Add event listener for logout
    document.querySelector('.logout-btn').addEventListener('click', handleLogout);
}); 