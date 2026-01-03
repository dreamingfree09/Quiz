document.addEventListener('DOMContentLoaded', event => {
    // Function to validate the email in the login form
    window.validateLoginEmail = function () {
        const email = document.getElementById('login-email')
        const errorDiv = document.getElementById('login-email-error')
        const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/
        if (!regex.test(email.value)) {
            errorDiv.textContent = 'Invalid email format.'
        } else {
            errorDiv.textContent = ''
        }
    }

    // Function to validate the password in the login form
    window.validateLoginPassword = function () {
        const password = document.getElementById('login-password')
        const errorDiv = document.getElementById('login-password-error')
        if (password.value.length < 8) {
            errorDiv.textContent = 'Password must be at least 8 characters.'
        } else {
            errorDiv.textContent = ''
        }
    }

    // Set the current year in the footer
    document.getElementById('currentYear').textContent = new Date().getFullYear()

    console.log('LOGIN')
})
