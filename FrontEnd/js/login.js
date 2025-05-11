document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const errorMessage = document.getElementById('errorMessage');

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Clear previous error messages
        errorMessage.textContent = '';
        errorMessage.style.display = 'none';
        
        // Get form values
        const email = emailInput.value.trim();
        const password = passwordInput.value;
        
        // Basic validation
        if (!email || !password) {
            showError('Please enter both email and password');
            return;
        }
        
        try {
            console.log('Attempting login with email:', email);
            
            // Send login request
            const response = await fetch('http://localhost/Ecommerce/BackEnd/api/auth/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email, password }),
                credentials: 'include'
            });
            
            const data = await response.json();
            console.log('Login response:', data);
            
            if (data.success) {
                // Store user data in localStorage
                const userData = {
                    ...data.user,
                    isAdmin: data.isAdmin,
                    permissions: data.permissions
                };
                localStorage.setItem('user', JSON.stringify(userData));
                console.log('User data stored:', userData);
                
                // Redirect based on role
                const redirectUrl = data.redirect;
                console.log('Redirecting to:', redirectUrl);
                
                // Add a small delay to ensure localStorage is updated
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 100);
            } else {
                showError(data.error || 'Login failed');
            }
        } catch (error) {
            console.error('Login error:', error);
            showError('An error occurred during login');
        }
    });
    
    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
    }
}); 