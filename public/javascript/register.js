document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const registerForm = document.getElementById('registerForm');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const imgInput = document.getElementById('img');
    const imgPreview = document.getElementById('imgPreview');
    
    // Initialize form functionality
    setupPasswordToggles();
    setupImagePreview();
    setupFormValidation();

    // Password visibility toggle functionality
    function setupPasswordToggles() {
        // Get existing toggle elements (they're already in your HTML)
        const passwordToggle = document.getElementById('togglePassword');
        const confirmPasswordToggle = document.getElementById('toggleConfirmPassword');
        
        // Add event listeners
        passwordToggle.addEventListener('click', function() {
            togglePasswordVisibility(passwordInput, passwordToggle);
        });
        
        confirmPasswordToggle.addEventListener('click', function() {
            togglePasswordVisibility(confirmPasswordInput, confirmPasswordToggle);
        });
    }

    function togglePasswordVisibility(input, icon) {
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    }

    // Image preview functionality
    function setupImagePreview() {
        imgInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imgPreview.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" class="img-preview">
                        <span class="img-name">${file.name}</span>
                    `;
                }
                reader.readAsDataURL(file);
            } else {
                imgPreview.innerHTML = '';
            }
        });
    }

    // Form validation and submission
    function setupFormValidation() {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Clear previous errors
            clearErrors();
            
            // Get submit button reference
            const submitBtn = document.querySelector('.btn');
            
            if (validateForm()) {
                try {
                    // Show loading state
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registering...';
                    
                    // Create FormData object
                    const formData = new FormData(registerForm);
                    
                    // Send data to server
                    const response = await fetch('../api/register.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.error) {
                        showError('formError', result.error);
                    } else {
                        // Registration successful - redirect to login
                        window.location.href = 'login.html?registration=success';
                    }
                } catch (error) {
                    showError('formError', 'An error occurred. Please try again.');
                    console.error('Registration error:', error);
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'SIGN UP';
                    }
                }
            }
        });
    }

    function validateForm() {
        let isValid = true;
        
        // Validate required fields
        const requiredFields = ['fname', 'lname', 'email', 'password', 'confirmPassword', 'phone', 'address', 'region'];
        requiredFields.forEach(field => {
            const value = document.getElementById(field).value.trim();
            if (!value) {
                showError(`${field}Error`, 'This field is required');
                isValid = false;
            }
        });

        // Validate email format
        const email = document.getElementById('email').value.trim();
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showError('emailError', 'Please enter a valid email address');
            isValid = false;
        }

        // Validate password length
        const password = passwordInput.value;
        if (password.length < 8) {
            showError('passwordError', 'Password must be at least 8 characters');
            isValid = false;
        }

        // Validate password match
        const confirmPassword = confirmPasswordInput.value;
        if (password !== confirmPassword) {
            showError('confirmPasswordError', 'Passwords do not match');
            isValid = false;
        }

        return isValid;
    }

    function clearErrors() {
        document.querySelectorAll('.error-text').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
            el.previousElementSibling?.classList.remove('error-highlight');
        });
    }

    function showError(fieldId, message) {
        const errorElement = document.getElementById(fieldId);
        const inputElement = document.getElementById(fieldId.replace('Error', ''));
        
        if (errorElement && inputElement) {
            // Highlight the problematic field
            inputElement.classList.add('error-highlight');
            
            // Position error message properly
            errorElement.textContent = message;
            errorElement.style.display = 'block';
            
            // Ensure the container has enough space
            const container = inputElement.closest('.input-container');
            if (container) {
                container.style.marginBottom = '25px';
            }
        }
    }

    // Add necessary styles
// In your register.js, update the style injection to:
const style = document.createElement('style');
style.textContent = `
    .input-field {
        position: relative;
        margin-bottom: 5px;
    }
    .password-toggle {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #acacac;
        z-index: 2;
        font-size: 1.1rem;
    }
    .password-toggle:hover {
        color: #777;
    }
    .error-text {
        color: #e74c3c;
        font-size: 0.8rem;
        margin: 3px 0 15px 15px;
        display: block;
        opacity: 1;
        max-height: 100px;
        transition: all 0.3s ease;
        position: relative;
        top: -5px;
        width: 100%;
        clear: both;
    }
    .error-highlight {
        border: 1px solid #e74c3c !important;
        background-color: #fff6f6 !important;
    }
    .password-container {
        margin-bottom: 5px;
    }
    .img-preview {
        max-width: 100px;
        max-height: 100px;
        display: block;
        margin-top: 10px;
    }
    .img-name {
        display: block;
        margin-top: 5px;
        font-size: 0.9rem;
        color: #666;
    }
`;
document.head.appendChild(style);
});