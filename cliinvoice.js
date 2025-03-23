document.addEventListener("DOMContentLoaded", function () {
    // Fetch corporates and populate the dropdown
    fetchCorporates();

    // Hide employee table container by default
    $('#employee-table-container').hide();
    $('#submitSelection').hide();

    // Event: When Corporate is selected, fetch related clients
    document.getElementById("corporateid").addEventListener("change", function () {
        const corporateId = this.value;
        const clientDropdown = document.getElementById("clientId");

        if (!corporateId) {
            clientDropdown.disabled = true;
            clientDropdown.innerHTML = '<option value="">Select Client</option>';
            return;
        }

        fetch(`cliinvoice.php?action=getClients&corporateId=${corporateId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    clientDropdown.disabled = false;
                    clientDropdown.innerHTML = '<option value="">Select Client</option>';
                    data.clients.forEach(client => {
                        const option = document.createElement("option");
                        option.value = client.ClientId;
                        option.textContent = client.ClientName;
                        clientDropdown.appendChild(option);
                    });
                } else {
                    alert(data.message || "Failed to fetch clients.");
                }
            })
            .catch(error => console.error("Error fetching clients:", error));
    });

    // Event: When Client is selected, fetch billing frequency and last invoice date
    document.getElementById("clientId").addEventListener("change", function () {
        const clientId = this.value;

        if (!clientId) {
            return;
        }

        // Fetch client details (billing frequency and last invoice date)
        fetch(`cliinvoice.php?action=getClientDetails&clientId=${clientId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const BillingFrequency = data.billingFrequency;
                    const lastInvoiceDate = data.lastInvoiceDate;

                    // Calculate start and end dates based on billing frequency
                    const { startDate, endDate } = calculateDates(BillingFrequency, lastInvoiceDate);

                    // Set the calculated dates in the client fields
                    document.getElementById("startDate").value = startDate;
                    document.getElementById("endDate").value = endDate;
                } else {
                    alert(data.message || "Failed to fetch client details.");
                }
            })
            .catch(error => console.error("Error fetching client details:", error));
    });

    // Add Employee button functionality
    $('#add-employee').on('click', function () {
        const clientId = $('#clientId').val();

        if (!clientId) {
            alert('Please select a client first.');
            return;
        }

        // Show the employee table container and submit button
        $('#employee-table-container').show();
        toggleSubmitButton();

        // Get the client's start and end dates
        const startDate = document.getElementById("startDate").value;
        const endDate = document.getElementById("endDate").value;

        // Create a new row for the employee
        const newRow = $(`
            <tr>
                <td>
                    <select class="form-control employee-select">
                        <option value="">Select Employee</option>
                    </select>
                </td>
                <td><input type="date" class="form-control start-date" value="${startDate}" /></td>
                <td><input type="date" class="form-control end-date" value="${endDate}" /></td>
                <td><input type="number" class="form-control hours" min="0" step="0.01" /></td>
                <td><input type="text" class="form-control billing-rate" readonly /></td>
                <td><input type="text" class="form-control total-amount" readonly /></td>
                <td><button type="button" class="btn btn-danger remove-row">Remove</button></td>
            </tr>
        `);

        // Append the new row to the table
        $('#employee-table tbody').append(newRow);

        // Fetch employees with billing rates for the selected client
        $.ajax({
            url: 'cliinvoice.php',
            method: 'GET',
            data: { clientId: clientId },
            dataType: 'json',
            success: function (response) {
                console.log('Response:', response); // Log the response
                const employeeSelect = newRow.find('.employee-select');
                
                // Check if response.data exists, if not, use response directly
                const employees = response.data || response;
                
                if (Array.isArray(employees) && employees.length > 0) {
                    employees.forEach(function (employee) {
                        employeeSelect.append(
                            $('<option>')
                                .val(employee.ClientEmployeeStateId)
                                .text(employee.EmployeeName)
                                .data('billingRate', employee.BillingRate)
                        );
                    });
                } else {
                    console.error('No employees found or invalid response format:', response);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error fetching employees:', error);
                console.log('Raw Response:', xhr.responseText); // Log the raw response
                alert('Error fetching employees. Please try again.');
            }
        });
    });

    // Function to calculate start and end dates
    function calculateDates(BillingFrequency, lastInvoiceDate) {
        const today = new Date();
        let startDate, endDate;

        if (lastInvoiceDate) {
            startDate = new Date(lastInvoiceDate);
            startDate.setDate(startDate.getDate() + 1); // Start from the day after the last invoice
        } else {
            startDate = new Date(); // Use today's date if no previous invoice exists
        }

        switch (BillingFrequency) {
            case 'Full Month':
                endDate = new Date(startDate);
                endDate.setMonth(endDate.getMonth() + 1); // Add 1 month
                break;
            case 'Half Month':
                endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + 15); // Add 15 days
                break;
            case 'Two Weeks':
                endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + 14); // Add 14 days
                break;
            case 'One Week':
                endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + 7); // Add 7 days
                break;
            case 'Custom':
                endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + 30); // Default to 30 days
                break;
            default:
                endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + 30); // Default to 30 days
                break;
        }

        // Format dates as YYYY-MM-DD
        const formatDate = (date) => date.toISOString().split('T')[0];

        return {
            startDate: formatDate(startDate),
            endDate: formatDate(endDate)
        };
    }

    // Toggle submit button based on rows
    function toggleSubmitButton() {
        const hasRows = $('#employee-table tbody tr').length > 0;
        if (hasRows) {
            $('#submitSelection').show();
        } else {
            $('#submitSelection').hide();
        }
    }

    // Remove row functionality
    $(document).on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
        updateTotalCalculations();
        if ($('#employee-table tbody tr').length === 0) {
            $('#employee-table-container').hide();
        }
        toggleSubmitButton(); // Update the visibility of the Submit button
    });

    function updateTotalCalculations() {
        let totalBill = 0;

        // Sum up all total amounts
        $('.total-amount').each(function () {
            totalBill += parseFloat($(this).val()) || 0;
        });

        // Update total bill
        $('#totalBill').val(totalBill.toFixed(2));

        // Calculate discount and final amount
        const discountPercentage = parseFloat($('#discountPercentage').val()) || 0;
        const discountAmount = (totalBill * discountPercentage) / 100;
        const finalAmount = totalBill - discountAmount;

        // Update discount amount and final amount fields
        $('#discountAmount').val(discountAmount.toFixed(2));
        $('#finalAmount').val(finalAmount.toFixed(2));
    }

    // Employee selection change handler
    $(document).on('change', '.employee-select', function () {
        const row = $(this).closest('tr');
        const selectedOption = $(this).find('option:selected');
        const billingRate = selectedOption.data('billingRate') || '';

        // Update billing rate
        row.find('.billing-rate').val(billingRate);

        // Calculate total amount if hours exist
        const hours = parseFloat(row.find('.hours').val()) || 0;
        const total = (hours * billingRate).toFixed(2);
        row.find('.total-amount').val(total);
        
        // Update totals
        updateTotalCalculations();
    });

    // Hours input handler
    $(document).on('input', '.hours', function () {
        const row = $(this).closest('tr');
        const hours = parseFloat($(this).val()) || 0;
        const billingRate = parseFloat(row.find('.billing-rate').val()) || 0;
        row.find('.total-amount').val((hours * billingRate).toFixed(2));
        updateTotalCalculations();
    });

    // Discount percentage change handler
    $('#discountPercentage').on('input', function () {
        if (parseFloat($(this).val()) > 100) {
            $(this).val(100);
        }
        updateTotalCalculations();
    });

    // Date validation
    $(document).on('change', '.end-date', function () {
        const startDate = $(this).closest('tr').find('.start-date').val();
        const endDate = $(this).val();

        if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
            alert('End date cannot be earlier than start date');
            $(this).val('');
        }
    });

    $(document).on('change', '.start-date', function () {
        const endDate = $(this).closest('tr').find('.end-date').val();
        const startDate = $(this).val();

        if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
            alert('End date cannot be earlier than start date');
            $(this).closest('tr').find('.end-date').val('');
        }
    });

    // Clear form function
    function clearForm() {
        $('#corporateId').val('');
        $('#clientId').html('<option value="">Select Client</option>').prop('disabled', true);
        $('#employee-table tbody').empty();
        $('#employee-table-container').hide();
        $('#totalBill').val('');
        $('#discountPercentage').val('');
        $('#discountDescription').val('');
        $('#discountAmount').val('');
        $('#finalAmount').val('');
        toggleSubmitButton(); // Update the visibility of the Submit button
    }

    // New code for setting the dates
    window.onload = function () {
        const today = new Date();
        const dd = String(today.getDate()).padStart(2, '0');
        const mm = String(today.getMonth() + 1).padStart(2, '0'); // Months are 0-based
        const yyyy = today.getFullYear();
        const formattedDate = `${yyyy}-${mm}-${dd}`;

        // Set InvoiceDate and PaymentDate
        document.getElementById("invoiceDate").value = formattedDate;
        document.getElementById("paymentDate").value = formattedDate;

        // Set DueDate (+14 days)
        const dueDate = new Date();
        dueDate.setDate(today.getDate() + 14);
        const dueDd = String(dueDate.getDate()).padStart(2, '0');
        const dueMm = String(dueDate.getMonth() + 1).padStart(2, '0');
        const dueYyyy = dueDate.getFullYear();
        const formattedDueDate = `${dueYyyy}-${dueMm}-${dueDd}`;
        document.getElementById("dueDate").value = formattedDueDate;
    };

    function fetchCorporates() {
        fetch('cliinvoice.php?action=getCorporates')
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

    function showError(message) {
        alert(message);
    }

    // Prevent form submission and show modal instead
    $('#invoiceForm').on('submit', function(e) {
        e.preventDefault();
        $('#invoiceNumberModal').modal('show');
    });

    // Handle invoice number input validation
    $('#invoiceNumberInput').on('input', function() {
        // Remove non-digit characters
        this.value = this.value.replace(/\D/g, '');
        
        // Reset validation states
        $(this).removeClass('is-invalid');
        $('#invoiceNumberError').text('');
    });

    // Handle invoice number submission
    $('#submitInvoiceNumber').on('click', function() {
        const invoiceInput = $('#invoiceNumberInput');
        const invoiceNumber = invoiceInput.val().trim();
        
        // Basic validation
        if (!invoiceNumber || invoiceNumber.length !== 16) {
            invoiceInput.addClass('is-invalid');
            $('#invoiceNumberError').text('Please enter a valid 16-digit invoice number');
            return;
        }
        
        // Check against database
        $.ajax({
            url: 'cliinvoice.php',
            method: 'GET',
            data: {
                action: 'checkInvoiceNumber',
                invoiceNumber: invoiceNumber
            },
            success: function(response) {
                if (response.success) {
                    if (response.exists) {
                        invoiceInput.addClass('is-invalid');
                        $('#invoiceNumberError').text('This invoice number already existss. Please enter a different one.');
                    } else {
                        // Add invoice number to form and submit
                        const form = $('#invoiceForm')[0];
                        const formData = new FormData(form);
                        formData.append('invoiceNumber', invoiceNumber);
                        
                        $.ajax({
                            url: 'cliinvoice.php',
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(submitResponse) {
                                if (submitResponse.success) {
                                    $('#invoiceNumberModal').modal('hide');
                                    alert('Invoice submitted successfully!');
                                 //  window.location.href = `generate_invoice.php?invoiceNumber=${invoiceNumber}`;
                                } else {
                                    alert(submitResponse.message || 'Failed to submit invoice');
                                }
                            },
                            error: function() {
                                alert('Error submitting invoice. Please try again.');
                            }
                        });
                    }
                } else {
                    alert('Error checking invoice number. Please try again.');
                }
            },
            error: function() {
                alert('Error checking invoice number. Please try again.');
            }
        });
    });

    // Clear invoice number input when modal is hidden
    $('#invoiceNumberModal').on('hidden.bs.modal', function() {
        $('#invoiceNumberInput').val('').removeClass('is-invalid');
        $('#invoiceNumberError').text('');
    });
});
fetch('cliinvoice.php?action=getCorporates')
    .then(response => {
        console.log(response); // Log the raw response
        return response.json();
    })
    .then(data => {
        console.log(data); // Log the parsed JSON
    })
    .catch(error => {
        console.error('Error:', error);
    });