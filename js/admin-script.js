document.getElementById('currentYear').textContent = new Date().getFullYear()
document.querySelectorAll('.dropdown-btn').forEach(button => {
    button.addEventListener('click', function () {
        var dropdownContent = this.nextElementSibling
        if (dropdownContent.style.display === 'block') {
            dropdownContent.style.display = 'none'
        } else {
            dropdownContent.style.display = 'block'
        }
    })
})

// Close the dropdown if the user clicks outside of it
window.onclick = function (event) {
    if (!event.target.matches('.dropdown-btn')) {
        var dropdowns = document.getElementsByClassName('dropdown-content')
        for (var i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i]
            if (openDropdown.style.display === 'block') {
                openDropdown.style.display = 'none'
            }
        }
    }
}

function deleteQuestion() {
    var selectedQuestion = document.getElementById('deleteQuestion').value
    // Here you would typically make an AJAX request to your server to delete the selected question
    alert('Delete question: ' + selectedQuestion) // This is just a placeholder for demonstration
}

function getCSRFToken() {
    // Function to get CSRF token from server
    return fetch('/get-csrf-token')
        .then(response => response.json())
        .then(data => data.csrfToken)
}

function addCSRFTokenToForms() {
    // Add CSRF token to all forms
    const forms = document.querySelectorAll('form')
    forms.forEach(form => {
        const csrfToken = getCSRFToken()
        const csrfInput = document.createElement('input')
        csrfInput.type = 'hidden'
        csrfInput.name = 'csrfToken'
        csrfInput.value = csrfToken
        form.appendChild(csrfInput)
    })
}

function addCSRFTokenToAjaxRequests() {
    // Add CSRF token to all AJAX requests
    const xhrOpen = XMLHttpRequest.prototype.open
    XMLHttpRequest.prototype.open = function (
        method,
        url,
        async,
        user,
        password
    ) {
        const csrfToken = getCSRFToken()
        if (url.includes('?')) {
            url += `&csrfToken=${csrfToken}`
        } else {
            url += `?csrfToken=${csrfToken}`
        }
        xhrOpen.call(this, method, url, async, user, password)
    }
}

addCSRFTokenToForms()
addCSRFTokenToAjaxRequests()
