// Global variable for submit button
let submitButton;

document.addEventListener('DOMContentLoaded', function() {
    const clientForm = document.getElementById('clientForm');
    
    // Initialize submit button
    submitButton = document.querySelector('#clientForm button[type="submit"]');
    
    // Phone validation
    document.getElementById('phone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 10) value = value.slice(0, 10);
        e.target.value = value;
    });

    // Zip validation
    document.getElementById('zip').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 6) value = value.slice(0, 6);
        e.target.value = value;
    });

    // Billing period visibility control
    document.getElementById('billingfrequency').addEventListener('change', function() {
        const billingPeriodField = document.getElementById('billingperiod');
        if (this.value === 'Custom') {
            billingPeriodField.style.display = 'block';
        } else {
            billingPeriodField.style.display = 'none';
            // Auto-fill standard periods
            const daysMapping = {
                'Full Month': 30,
                'Half Month': 15,
                'One Week': 7,
                'Two Weeks': 14
            };
            billingPeriodField.value = daysMapping[this.value] || '';
        }
    });

    // Hide billing period initially
    document.getElementById('billingperiod').style.display = 'none';

    fetchCorporates();
    clientForm.addEventListener('submit', handleFormSubmit);
});
async function handleFormSubmit(e) {
    e.preventDefault();
    let isSuccess = false;
    if (!validateForm()) {
        return;
    }

    try {
        // Disable submit button during submission
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Done';
        }

        const formData = new FormData(e.target);
        
        // Add clientId if in update mode
        const updateModeInput = document.getElementById('updateModeInput');
        if (updateModeInput && updateModeInput.value === 'true') {
            const clientIdInput = document.getElementById('clientIdInput');
            if (clientIdInput) {
                formData.append('clientId', clientIdInput.value);
            }
        }
        
        const response = await fetch('ClientInsertion.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        
        if (data.clientExists) {
            handleExistingClient(data.clientInfo);
        } else if (data.success) {
            showSuccessState();
            isSuccess = true; // Flag for successful operation
        } else {
            showError(data.error || "An unknown error occurred");
        }
    } catch (error) {
        handleSubmissionError(error);
    } finally {
        // Only re-enable if NOT successful
        if (submitButton && !isSuccess) {
            submitButton.disabled = false;
            const updateModeInput = document.getElementById('updateModeInput');
            submitButton.textContent = (updateModeInput && updateModeInput.value === 'true') 
                ? 'Update Client' 
                : 'Submit';
        }
    }
}

function handleExistingClient(clientInfo) {
    // Create modal overlay
    const overlay = document.createElement('div');
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    `;

    // Create modal content
    const modal = document.createElement('div');
    modal.style.cssText = `
        background: white;
        padding: 20px;
        border-radius: 5px;
        width: 400px;
        max-width: 90%;
    `;

    // Modal content
    modal.innerHTML = `
        <h4 style="margin-top: 0;">Client Already Exists</h4>
        <p>A client with this EIN already exists. Client ID: ${clientInfo.ClientId}</p>
        
        <div style="margin: 20px 0;">
            <label style="display: block; margin: 10px 0; cursor: pointer;">
                <input type="radio" name="clientOption" value="update" checked>
                Update Information
            </label>
            <label style="display: block; margin: 10px 0; cursor: pointer;">
                <input type="radio" name="clientOption" value="continue">
                View Details
            </label>
        </div>
        
        <button id="confirmAction" style="
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 15px;
            width: 100%;
            cursor: pointer;
        ">Confirm</button>
    `;

    // Add event listener to the confirm button
    modal.querySelector('#confirmAction').addEventListener('click', function() {
        const selectedOption = modal.querySelector('input[name="clientOption"]:checked').value;
        
        if (selectedOption === 'update') {
            document.body.removeChild(overlay);
            fetchClientDataForUpdate(clientInfo.ClientId);
        } else {
            document.body.removeChild(overlay);
            showExistingClientCard(clientInfo);
        }
    });

    // Add elements to DOM
    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    // Close modal when clicking outside
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            document.body.removeChild(overlay);
        }
    });
}
async function fetchClientDataForUpdate(clientId) {
    try {
        showLoadingIndicator();
        
        const response = await fetch(`ClientInsertion.php?action=getClientData&clientId=${clientId}`);
        const data = await response.json();
        
        hideLoadingIndicator();
        
        if (data.success && data.clientData) {
            populateFormWithClientData(data.clientData);
            
            const clientForm = document.getElementById('clientForm');
            let updateModeInput = document.getElementById('updateModeInput');
            if (!updateModeInput) {
                updateModeInput = document.createElement('input');
                updateModeInput.type = 'hidden';
                updateModeInput.id = 'updateModeInput';
                updateModeInput.name = 'updateMode';
                clientForm.appendChild(updateModeInput);
            }
            updateModeInput.value = 'true';
            
            let clientIdInput = document.getElementById('clientIdInput');
            if (!clientIdInput) {
                clientIdInput = document.createElement('input');
                clientIdInput.type = 'hidden';
                clientIdInput.id = 'clientIdInput';
                clientIdInput.name = 'clientId';
                clientForm.appendChild(clientIdInput);
            }
            clientIdInput.value = clientId;
            
            // Use global submitButton variable
            if (submitButton) {
                submitButton.textContent = 'Update Client';
            }
            
            showUpdateModeNotification();
        } else {
            showError("Failed to load client data for update");
        }
    } catch (error) {
        hideLoadingIndicator();
        handleSubmissionError(error);
    }
}


function showLoadingIndicator() {
    const loadingOverlay = document.createElement('div');
    loadingOverlay.id = 'loadingOverlay';
    loadingOverlay.style.position = 'fixed';
    loadingOverlay.style.top = '0';
    loadingOverlay.style.left = '0';
    loadingOverlay.style.width = '100%';
    loadingOverlay.style.height = '100%';
    loadingOverlay.style.backgroundColor = 'rgba(255,255,255,0.7)';
    loadingOverlay.style.display = 'flex';
    loadingOverlay.style.justifyContent = 'center';
    loadingOverlay.style.alignItems = 'center';
    loadingOverlay.style.zIndex = '9999';
    
    const spinner = document.createElement('div');
    spinner.style.border = '4px solid #f3f3f3';
    spinner.style.borderTop = '4px solid #3498db';
    spinner.style.borderRadius = '50%';
    spinner.style.width = '40px';
    spinner.style.height = '40px';
    spinner.style.animation = 'spin 2s linear infinite';
    
    const styleElement = document.createElement('style');
    styleElement.textContent = `
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    
    document.head.appendChild(styleElement);
    loadingOverlay.appendChild(spinner);
    document.body.appendChild(loadingOverlay);
}

function hideLoadingIndicator() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
        document.body.removeChild(loadingOverlay);
    }
}

function showUpdateModeNotification() {
    const existingNotification = document.getElementById('updateModeNotification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    const notification = document.createElement('div');
    notification.id = 'updateModeNotification';
    notification.style.backgroundColor = '#d4edda';
    notification.style.color = '#155724';
    notification.style.padding = '10px 15px';
    notification.style.marginBottom = '15px';
    notification.style.borderRadius = '4px';
    notification.style.border = '1px solid #c3e6cb';
    notification.textContent = 'Update Mode: You are currently updating an existing client.';
    
    const cancelButton = document.createElement('button');
    cancelButton.type = 'button';
    cancelButton.textContent = 'Cancel Update';
    cancelButton.style.marginLeft = '10px';
    cancelButton.style.backgroundColor = '#dc3545';
    cancelButton.style.color = 'white';
    cancelButton.style.border = 'none';
    cancelButton.style.borderRadius = '4px';
    cancelButton.style.padding = '5px 10px';
    cancelButton.style.cursor = 'pointer';
    cancelButton.style.fontSize = '12px';
    
    cancelButton.addEventListener('click', function() {
        resetFormState();
        notification.remove();
        
        const updateModeInput = document.getElementById('updateModeInput');
        if (updateModeInput) updateModeInput.remove();
        
        const clientIdInput = document.getElementById('clientIdInput');
        if (clientIdInput) clientIdInput.remove();
        
        // Use the existing submitButton variable
        if (submitButton) {
            submitButton.textContent = 'Submit';
        }
    });
    
    notification.appendChild(cancelButton);
    const clientForm = document.getElementById('clientForm');
    clientForm.parentElement.insertBefore(notification, clientForm);
}

function populateFormWithClientData(clientData) {
    document.getElementById('clientName').value = clientData.ClientName || '';
    document.getElementById('phone').value = clientData.Phone || '';
    document.getElementById('email').value = clientData.EmailId || '';
    document.getElementById('country').value = clientData.Country || '';
    document.getElementById('address1').value = clientData.Addr1 || '';
    document.getElementById('address2').value = clientData.Addr2 || '';
    document.getElementById('address3').value = clientData.Addr3 || '';
    document.getElementById('city').value = clientData.City || '';
    document.getElementById('state').value = clientData.State || '';
    document.getElementById('zip').value = clientData.Zip || '';
    document.getElementById('ein').value = clientData.EIN || '';
    
    if (clientData.CorporateId) {
        const corporateSelect = document.getElementById('corporateid');
        if (corporateSelect) {
            setTimeout(() => {
                corporateSelect.value = clientData.CorporateId;
            }, 500);
        }
    }
    
    if (clientData.Billingfrequency) {
        const frequencySelect = document.getElementById('billingfrequency');
        const periodInput = document.getElementById('billingperiod');
        
        frequencySelect.value = clientData.Billingfrequency;
        
        // Trigger the change event to show/hide appropriately
        const event = new Event('change');
        frequencySelect.dispatchEvent(event);
        
        if (clientData.Billingfrequency === 'Custom' && clientData.Billingperiod) {
            periodInput.value = clientData.Billingperiod;
        }
    }
    
    if (clientData.Billingperiod) {
        const periodInput = document.getElementById('billingperiod');
        if (periodInput) {
            periodInput.value = clientData.Billingperiod;
        }
    }
}

function showExistingClientCard(clientInfo) {
    const formContainer = document.getElementById('clientForm').parentElement;
    formContainer.innerHTML = '';

    const card = document.createElement('div');
    card.className = 'card';
    card.style.width = '100%';
    card.style.marginTop = '20px';
    card.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
    card.style.borderRadius = '4px';

    const cardHeader = document.createElement('div');
    cardHeader.className = 'card-header';
    cardHeader.style.backgroundColor = '#f8f9fa';
    cardHeader.style.padding = '15px';
    cardHeader.style.borderBottom = '1px solid #ddd';

    const cardTitle = document.createElement('h5');
    cardTitle.textContent = 'Existing Client Information';
    cardTitle.style.margin = '0';
    cardTitle.style.fontWeight = 'bold';

    const cardBody = document.createElement('div');
    cardBody.className = 'card-body';
    cardBody.style.padding = '20px';

    const clientNameElement = document.createElement('p');
    clientNameElement.innerHTML = `<strong>Client Name:</strong> ${clientInfo.ClientName || 'N/A'}`;
    
    const clientEinElement = document.createElement('p');
    clientEinElement.innerHTML = `<strong>EIN:</strong> ${clientInfo.EIN || 'N/A'}`;
    
    const clientEmailElement = document.createElement('p');
    clientEmailElement.innerHTML = `<strong>Email:</strong> ${clientInfo.EmailId || 'N/A'}`;

    const backButton = document.createElement('button');
    backButton.textContent = 'Back to Form';
    backButton.className = 'btn btn-primary mt-3';
    backButton.style.backgroundColor = '#007bff';
    backButton.style.color = 'white';
    backButton.style.border = 'none';
    backButton.style.borderRadius = '4px';
    backButton.style.padding = '10px 15px';
    backButton.style.cursor = 'pointer';
    backButton.style.marginTop = '15px';
    
    backButton.addEventListener('click', function() {
        location.reload();
    });

    cardHeader.appendChild(cardTitle);
    cardBody.appendChild(clientNameElement);
    cardBody.appendChild(clientEinElement);
    cardBody.appendChild(clientEmailElement);
    cardBody.appendChild(backButton);
    
    card.appendChild(cardHeader);
    card.appendChild(cardBody);
    
    formContainer.appendChild(card);
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
        ein: document.getElementById("ein").value,
        corporateid: document.getElementById("corporateid").value,
        billingfrequency: document.getElementById("billingfrequency").value
    };

    const emptyFields = Object.entries(validationRules)
        .filter(([field, value]) => value === "")
        .map(([field]) => field);

    if (emptyFields.length > 0) {
        showError(`Please fill out all required fields: ${emptyFields.join(', ')}`);
        return false;
    }

    if (!/^\d{10}$/.test(validationRules.phone)) {
        showError("Phone number must be exactly 10 digits");
        document.getElementById("phone").focus();
        return false;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(validationRules.email)) {
        showError("Invalid email address");
        document.getElementById("email").focus();
        return false;
    }

    if (!/^\d{6}$/.test(validationRules.zip)) {
        showError("Zip code must be exactly 6 digits");
        document.getElementById("zip").focus();
        return false;
    }

    return true;
}

// In showSuccessState() - use the existing submitButton variable
function showSuccessState() {
    const successMessage = document.getElementById('successMessage');
    if (successMessage) {
        successMessage.style.display = 'block';
    } else {
        const messageDiv = document.createElement('div');
        messageDiv.id = 'successMessage';
        messageDiv.className = 'alert alert-success';
        messageDiv.style.marginBottom = '20px';
        messageDiv.style.backgroundColor = '#d4edda';
        messageDiv.style.padding = '15px';
        messageDiv.style.borderRadius = '4px';
        messageDiv.style.border = '1px solid #c3e6cb';
        messageDiv.style.color = '#155724';
        
        const updateNotification = document.getElementById('updateModeNotification');
        if (updateNotification) {
            updateNotification.remove();
        }
        
        const updateModeInput = document.getElementById('updateModeInput');
        if (updateModeInput && updateModeInput.value === 'true') {
            messageDiv.textContent = 'Client information updated successfully!';
        } else {
            messageDiv.textContent = 'Client added successfully!';
        }
        
        const formContainer = document.getElementById('clientForm').parentElement;
        formContainer.insertBefore(messageDiv, document.getElementById('clientForm'));
    }
    
    const formInputs = document.querySelectorAll('#clientForm input, #clientForm select');
    formInputs.forEach(input => {
        input.disabled = true;
    });
    
    // Use the existing submitButton variable
    if (submitButton) {
        submitButton.disabled = true;
    }
    const addNewButton = document.createElement('button');
    addNewButton.textContent = 'Add New Client';
    addNewButton.className = 'btn btn-primary mt-3';
    addNewButton.style.backgroundColor = '#007bff';
    addNewButton.style.color = 'white';
    addNewButton.style.border = 'none';
    addNewButton.style.borderRadius = '4px';
    addNewButton.style.padding = '10px 15px';
    addNewButton.style.cursor = 'pointer';
    addNewButton.style.marginTop = '15px';
    
    addNewButton.addEventListener('click', resetFormState);
    
    document.getElementById('clientForm').insertAdjacentElement('afterend', addNewButton);
    
    window.scrollTo(0, 0);
}

function resetFormState() {
    const formInputs = document.querySelectorAll('#clientForm input, #clientForm select');
    formInputs.forEach(input => {
        input.disabled = false;
        if (input.type !== 'submit' && input.type !== 'button') {
            input.value = '';
        }
    });
    
    // Use the existing submitButton variable
    if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = 'Submit';
    }
    
    const successMessage = document.getElementById('successMessage');
    if (successMessage) {
        successMessage.style.display = 'none';
    }
    
    const addNewButton = document.querySelector('#clientForm + button');
    if (addNewButton) {
        addNewButton.remove();
    }
    
    const updateNotification = document.getElementById('updateModeNotification');
    if (updateNotification) {
        updateNotification.remove();
    }
    
    const updateModeInput = document.getElementById('updateModeInput');
    if (updateModeInput) updateModeInput.remove();
    
    const clientIdInput = document.getElementById('clientIdInput');
    if (clientIdInput) clientIdInput.remove();
}

function showError(message) {
    const errorMessage = message || "An error occurred. Please try again.";
    alert(errorMessage);
}

function handleSubmissionError(error) {
    console.error('Error:', error);
    showError('An error occurred while submitting the form. Please try again.');
}

function fetchCorporates() {
    fetch('ClientInsertion.php?action=getCorporates')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log("Corporates data:", data);
            
            if (data.corporates && Array.isArray(data.corporates)) {
                var corporateSelect = document.getElementById('corporateid');
                corporateSelect.innerHTML = '<option value="">Select Corporate</option>';
                
                data.corporates.forEach(function(corporate) {
                    var option = document.createElement('option');
                    option.value = corporate.CorporateId;
                    option.textContent = corporate.CorporateName;
                    corporateSelect.appendChild(option);
                });
            } else {
                console.error('No corporates data found in the response:', data);
                showError('No corporates found. Please check the database connection.');
            }
        })
        .catch(error => {
            console.error('Error fetching corporates:', error);
            showError('Error loading corporates: ' + error.message);
        });
}