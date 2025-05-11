// Check if user is logged in
function checkAuthStatus() {
    const user = localStorage.getItem('user');
    const loginLink = document.querySelector('a[href="login.html"]');
    const registerLink = document.querySelector('a[href="register.html"]');
    
    if (user) {
        // User is logged in
        const userData = JSON.parse(user);
        
        // Replace login/register links with user info and logout
        if (loginLink && registerLink) {
            loginLink.parentElement.innerHTML = `
                <span style="color: white;">Welcome, ${userData.name}</span>
            `;
            registerLink.parentElement.innerHTML = `
                <a href="#" id="logoutBtn" style="color: white;">Logout</a>
            `;
            
            // Add logout functionality
            document.getElementById('logoutBtn').addEventListener('click', function(e) {
                e.preventDefault();
                logout();
            });
        }
    } else {
        // User is not logged in
        // Make sure login and register links are visible
        if (loginLink && registerLink) {
            loginLink.style.display = 'inline';
            registerLink.style.display = 'inline';
        }
    }
}

// Logout function
function logout() {
    // Remove user data and token from localStorage
    localStorage.removeItem('user');
    localStorage.removeItem('userToken');
    
    // Redirect to home page
    window.location.href = 'index.html';
}

// Check auth status when page loads
document.addEventListener('DOMContentLoaded', checkAuthStatus); 