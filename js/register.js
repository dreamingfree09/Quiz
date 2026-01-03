document.addEventListener('DOMContentLoaded', event => {
    // Function to validate the username
    window.validateUsername = function () {
        const username = document.getElementById('username')
        const errorDiv = document.getElementById('username-error')
        if (username.value.length < 6 || username.value.length > 20) {
            errorDiv.textContent = 'Username must be between 6 and 20 characters.'
        } else {
            errorDiv.textContent = ''
        }
    }

    // Function to validate the email
    window.validateEmail = function () {
        const email = document.getElementById('email')
        const errorDiv = document.getElementById('email-error')
        const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/
        if (!regex.test(email.value)) {
            errorDiv.textContent = 'Invalid email format.'
        } else {
            errorDiv.textContent = ''
        }
    }

    // Function to validate the password
    window.validatePassword = function () {
        const password = document.getElementById('password')
        const errorDiv = document.getElementById('password-error')
        if (password.value.length < 8 || password.value.length > 20) {
            errorDiv.textContent = 'Password must be between 8 and 20 characters.'
        } else {
            errorDiv.textContent = ''
        }
    }

    // Function to validate the confirm password
    window.validateConfirmPassword = function () {
        const password = document.getElementById('password')
        const confirmPassword = document.getElementById('confirm-password')
        const errorDiv = document.getElementById('confirm-password-error')
        if (confirmPassword.value !== password.value) {
            errorDiv.textContent = 'Passwords do not match.'
        } else {
            errorDiv.textContent = ''
        }
    }

    // Set the current year in the footer
    document.getElementById('currentYear').textContent = new Date().getFullYear()

    console.log('REGISTER')
})
