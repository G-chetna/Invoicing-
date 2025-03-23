document.addEventListener('DOMContentLoaded', function() {
    const clientForm = document.getElementById('clientForm');
    
    // Add input event listeners for real-time validation
    document.getElementById('phone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 10) value = value.slice(0, 10);
        e.target.value = value;
    });

    document.getElementById('zip').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 6) value = value.slice(0, 6);
        e.target.value = value;
    });

    fetchCorporates();  // Fetch the corporates when the page is loaded
    
    clientForm.addEventListener('submit', handleFormSubmit);
});

async function handleFormSubmit(e) {
    e.preventDefault();
    
    if (!validateForm()) {
        return;
    }

    try {
        const formData = new FormData(this);
        const response = await fetch('ClientInsertion.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        
        if (data.success) {
            showSuccessState();
        } else {
            showError(data.error);
        }
    } catch (error) {
        handleSubmissionError(error);
    }
}

function validateForm() {
    const validationRules = {
        name: document.getElementById("clientName").value,
        phone: document.getElementById("phone").value,
        email: document.getElementById("email").value,
        country: document.getElementById("country").value,
        address1: document.getElementById("address1").value,
        city: document.getElementById("city").value,
        state: document.getElementById("state").value,
        zip: document.getElementById("zip").value,
        ein: document.getElementById("ein").value
    };

    // Validate required fields
    if (Object.values(validationRules).some(value => value === "")) {
        showError("All fields must be filled out");
        return false;
    }

    // Validate phone number format (exactly 10 digits)
    if (!/^\d{10}$/.test(validationRules.phone)) {
        showError("Phone number must be exactly 10 digits");
        document.getElementById("phone").focus();
        return false;
    }

    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(validationRules.email)) {
        showError("Invalid email address");
        document.getElementById("email").focus();
        return false;
    }

    // Validate zip code format (exactly 6 digits)
    if (!/^\d{6}$/.test(validationRules.zip)) {
        showError("Zip code must be exactly 6 digits");
        document.getElementById("zip").focus();
        return false;
    }

    return true;
}

function showSuccessState() {
    // Show success message
    const successMessage = document.getElementById('successMessage');
    successMessage.style.display = 'block';
    
    // Disable all form fields
    const formInputs = document.querySelectorAll('#clientForm input');
    formInputs.forEach(input => {
        input.disabled = true;
    });
    
    // Disable submit button
    const submitButton = document.querySelector('#clientForm button[type="submit"]');
    submitButton.disabled = true;
    
    // Add "Add New Client" button
    const addNewButton = document.createElement('button');
    addNewButton.textContent = 'Add New Client';
    addNewButton.className = 'btn btn-primary mt-3';
    addNewButton.onclick = resetFormState;
    
    // Insert the new button after the form
    document.getElementById('clientForm').insertAdjacentElement('afterend', addNewButton);
    
    // Scroll to top to show success message
    window.scrollTo(0, 0);
}

function resetFormState() {
    // Enable all form fields
    const formInputs = document.querySelectorAll('#clientForm input');
    formInputs.forEach(input => {
        input.disabled = false;
        input.value = '';
    });
    
    // Enable submit button
    const submitButton = document.querySelector('#clientForm button[type="submit"]');
    submitButton.disabled = false;
    
    // Hide success message
    const successMessage = document.getElementById('successMessage');
    successMessage.style.display = 'none';
    
    // Remove "Add New Client" button
    const addNewButton = document.querySelector('#clientForm + button');
    if (addNewButton) {
        addNewButton.remove();
    }
}

function showError(message) {
    alert(message);
}

function fetchCorporates() {
    fetch('ClientInsertion.php?action=getCorporates')
        .then(response => response.json())  // Parse the JSON response
        .then(data => {
            // Check if the 'corporates' field is present in the response
            if (data.success && Array.isArray(data.corporates)) {
                const corporateSelect = document.getElementById('corporateid');  // Assuming 'corporateid' is the ID of the select element

                // Clear any existing options before adding new ones
                corporateSelect.innerHTML = '<option value="">Select Corporate</option>';

                // Loop through each corporate and add it as an option in the select dropdown
                data.corporates.forEach(corporate => {
                    const option = document.createElement('option');
                    option.value = corporate.CorporateId;  // Set the value as the CorporateId
                    option.textContent = corporate.CorporateName;  // Set the text as the CorporateName
                    corporateSelect.appendChild(option);
                });
            } else {
                // Handle the case where no corporates are found
                showError('No corporates available.');
            }
        })
        .catch(error => {
            // Handle any errors that occur during the fetch
            console.error('Error fetching corporates:', error);
            showError('Failed to fetch corporates. Please try again.');
        });
}
function fetchCorporates() {
    fetch('ClientInsertion.php?action=getCorporates')
        .then(response => response.json())  // Parse the JSON response
        .then(data => {
            // Check if the 'corporates' field is present in the response
            if (data.success && Array.isArray(data.corporates)) {
                const corporateSelect = document.getElementById('corporateid');  // Assuming 'corporateid' is the ID of the select element

                // Clear any existing options before adding new ones
                corporateSelect.innerHTML = '<option value="">Select Corporate</option>';

                // Loop through each corporate and add it as an option in the select dropdown
                data.corporates.forEach(corporate => {
                    const option = document.createElement('option');
                    option.value = corporate.CorporateId;  // Set the value as the CorporateId
                    option.textContent = corporate.CorporateName;  // Set the text as the CorporateName
                    corporateSelect.appendChild(option);
                });
            } else {
                // Handle the case where no corporates are found
                showError('No corporates available.');
            }
        })
        .catch(error => {
            // Handle any errors that occur during the fetch
            console.error('Error fetching corporates:', error);
            showError('Failed to fetch corporates. Please try again.');
        });
}
function handleSubmissionError(error) {
    console.error('Error:', error);
    showError('An error occurred. Please try again.');
}
