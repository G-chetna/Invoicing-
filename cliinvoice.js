document.addEventListener("DOMContentLoaded", function () {
    // Initialize form state
    $('#employee-table-container').hide();
    $('#submitSelection').hide();
    
    // Fetch corporates using AJAX
    function fetchCorporates() {
        $.ajax({
            url: 'cliinvoice.php',
            method: 'GET',
            data: { action: 'getCorporates' },
            dataType: 'json'
        }).done(function(data) {
            if (data.success) {
                const corporateSelect = $('#corporateid');
                corporateSelect.html('<option value="">Select Corporate</option>');
                data.corporates.forEach(corporate => {
                    corporateSelect.append(
                        $('<option>').val(corporate.CorporateId).text(corporate.CorporateName)
                    );
                });
            }
        }).fail(function(jqXHR, textStatus) {
            alert('Error loading corporates: ' + textStatus);
        });
    }

    // Initial corporate load
    fetchCorporates();

    // Date calculation function
    function calculateDates(BillingFrequency, lastInvoiceDate) {
        const startDate = lastInvoiceDate ? 
            new Date(new Date(lastInvoiceDate).setDate(new Date(lastInvoiceDate).getDate() + 1)) :
            new Date();
        
        const endDate = new Date(startDate);
        
        switch(BillingFrequency) {
            case 'Full Month': endDate.setMonth(endDate.getMonth() + 1); break;
            case 'Half Month': endDate.setDate(endDate.getDate() + 15); break;
            case 'Two Weeks': endDate.setDate(endDate.getDate() + 14); break;
            case 'One Week': endDate.setDate(endDate.getDate() + 7); break;
            default: endDate.setDate(endDate.getDate() + 30);
        }
        
        return {
            startDate: startDate.toISOString().split('T')[0],
            endDate: endDate.toISOString().split('T')[0]
        };
    }

    // Corporate change handler
    $('#corporateid').change(function() {
        const corporateId = $(this).val();
        const clientDropdown = $('#clientId');
        
        if (!corporateId) {
            clientDropdown.prop('disabled', true).html('<option value="">Select Client</option>');
            return;
        }

        $.ajax({
            url: 'cliinvoice.php',
            method: 'GET',
            data: { action: 'getClients', corporateId: corporateId },
            dataType: 'json'
        }).done(function(data) {
            if (data.success) {
                clientDropdown.prop('disabled', false).html('<option value="">Select Client</option>');
                data.clients.forEach(client => {
                    clientDropdown.append($('<option>').val(client.ClientId).text(client.ClientName));
                });
            }
        }).fail(function(jqXHR, textStatus) {
            alert('Error loading clients: ' + textStatus);
        });
    });

    // Client change handler
    $('#clientId').change(function() {
        const clientId = $(this).val();
        if (!clientId) return;

        // Clear previous data
        $('#employee-table tbody').empty();
        $('#employee-table-container').hide();
        toggleSubmitButton();

        // Get client details
        $.ajax({
            url: 'cliinvoice.php',
            method: 'GET',
            data: { action: 'getClientDetails', clientId: clientId },
            dataType: 'json'
        }).done(function(data) {
            if (data.success) {
                const dates = calculateDates(data.billingFrequency, data.lastInvoiceDate);
                $('#startDate').val(dates.startDate);
                $('#endDate').val(dates.endDate);
            }
        }).fail(function(jqXHR, textStatus) {
            alert('Error loading client details: ' + textStatus);
        });
    });

    // Add Employee button
    $('#add-employee').click(function() {
        const clientId = $('#clientId').val();
        if (!clientId) {
            alert('Please select a client first.');
            return;
        }

        $.ajax({
            url: 'cliinvoice.php',
            method: 'GET',
            data: { clientId: clientId },
            dataType: 'json'
        }).done(function(response) {
            const newRow = createEmployeeRow(response.data || response);
            $('#employee-table tbody').append(newRow);
            $('#employee-table-container').show();
            toggleSubmitButton();
        }).fail(function(jqXHR, textStatus) {
            alert('Error loading employees: ' + textStatus);
        });
    });

    // Create employee row
    function createEmployeeRow(employees) {
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();
        
        const row = $(`
            <tr>
                <td>
                    <select class="form-control employee-select" name="employeeSelect[]">
                        <option value="">Select Employee</option>
                    </select>
                </td>
                <td><input type="date" class="form-control start-date" name="startDate[]" value="${startDate}"></td>
                <td><input type="date" class="form-control end-date" name="endDate[]" value="${endDate}"></td>
                <td><input type="number" class="form-control hours" name="hours[]" min="0" step="0.01"></td>
                <td><input type="text" class="form-control billing-rate" readonly></td>
                <td><input type="text" class="form-control total-amount" name="totalAmount[]" readonly></td>
                <td><button type="button" class="btn btn-danger remove-row">Remove</button></td>
            </tr>
        `);

        const select = row.find('.employee-select');
        employees.forEach(employee => {
            select.append(
                $('<option>')
                    .val(employee.ClientEmployeeStateId)
                    .text(employee.EmployeeName)
                    .data('billingRate', employee.BillingRate)
            );
        });

        return row;
    }

    // Toggle submit button visibility
    function toggleSubmitButton() {
        $('#submitSelection').toggle($('#employee-table tbody tr').length > 0);
    }

    // Remove row handler
    $(document).on('click', '.remove-row', function() {
        $(this).closest('tr').remove();
        updateTotalCalculations();
        toggleSubmitButton();
        if ($('#employee-table tbody tr').length === 0) {
            $('#employee-table-container').hide();
        }
    });

    // Update totals calculations
    function updateTotalCalculations() {
        let totalBill = 0;
        $('.total-amount').each(function() {
            totalBill += parseFloat($(this).val()) || 0;
        });

        const discountPercentage = parseFloat($('#discountPercentage').val()) || 0;
        const discountAmount = (totalBill * discountPercentage) / 100;
        const finalAmount = totalBill - discountAmount;

        $('#totalBill').val(totalBill.toFixed(2));
        $('#discountAmount').val(discountAmount.toFixed(2));
        $('#finalAmount').val(finalAmount.toFixed(2));
    }

    // Event handlers
    $(document)
        .on('change', '.employee-select', function() {
            const row = $(this).closest('tr');
            const billingRate = $(this).find('option:selected').data('billingRate') || 0;
            row.find('.billing-rate').val(billingRate);
            updateRowTotal(row);
            
            // Update the hidden employeeId field
            const employeeId = $(this).val();
            let hiddenField = row.find('input[name="employeeId[]"]');
            if (hiddenField.length === 0) {
                row.append(`<input type="hidden" name="employeeId[]" value="${employeeId}">`);
            } else {
                hiddenField.val(employeeId);
            }
        })
        .on('input', '.hours', function() {
            updateRowTotal($(this).closest('tr'));
        })
        .on('input', '#discountPercentage', function() {
            const val = Math.min(parseFloat($(this).val()) || 0, 100);
            $(this).val(val);
            updateTotalCalculations();
        });

    // Update row total
    function updateRowTotal(row) {
        const hours = parseFloat(row.find('.hours').val()) || 0;
        const rate = parseFloat(row.find('.billing-rate').val()) || 0;
        row.find('.total-amount').val((hours * rate).toFixed(2));
        updateTotalCalculations();
    }

    $('#invoiceNumber').on('input', function() {
        const invoiceNumber = $(this).val().replace(/\D/g, '');
        $(this).val(invoiceNumber);
        $(this).removeClass('is-invalid');
        $('#invoiceNumberError').text('');
        
        if (invoiceNumber.length === 16) {
            checkInvoiceNumber(invoiceNumber);
        }
    });
    
    function checkInvoiceNumber(invoiceNumber) {
        $.ajax({
            url: 'cliinvoice.php',
            method: 'GET',
            data: {
                action: 'checkInvoiceNumber',
                invoiceNumber: invoiceNumber
            },
            success: function(response) {
                if (response.exists) {
                    $('#invoiceNumber').addClass('is-invalid');
                    $('#invoiceNumberError').text('Invoice number already exists. Please use another one.');
                }
            }
        });
    }
    
    // Modified form submission handler
    $('#invoiceForm').on('submit', function(e) {
        e.preventDefault();
        
        const invoiceNumber = $('#invoiceNumber').val();
        if (!/^\d{16}$/.test(invoiceNumber)) {
            $('#invoiceNumber').addClass('is-invalid');
            $('#invoiceNumberError').text('Please enter a valid 16-digit invoice number');
            return;
        }
    
        // Ensure required fields are present
        if (!$('#corporateid').val()) {
            alert('Please select a Corporate');
            return;
        }
        
        if (!$('#clientId').val()) {
            alert('Please select a Client');
            return;
        }
        
        // Check if there are employee rows
        if ($('#employee-table tbody tr').length === 0) {
            alert('Please add at least one employee');
            return;
        }
        
        // Make sure all employees are selected and hours are entered
        let isValid = true;
        $('#employee-table tbody tr').each(function() {
            const $row = $(this);
            const employeeId = $row.find('.employee-select').val();
            const hours = $row.find('.hours').val();
            
            if (!employeeId) {
                alert('Please select an employee for all rows');
                isValid = false;
                return false;
            }
            
            if (!hours) {
                alert('Please enter hours for all employees');
                isValid = false;
                return false;
            }
            
            // Ensure the hidden input for employeeId exists
            if ($row.find('input[name="employeeId[]"]').length === 0) {
                $row.append(`<input type="hidden" name="employeeId[]" value="${employeeId}">`);
            }
        });
        
        if (!isValid) return;
        
        // Direct form submission to generate_invoice.php
        this.action = 'generate_invoice.php';
        this.method = 'POST';
        this.submit();
    });

    // Clear form function
    function clearForm() {
        $('#corporateid').val('');
        $('#clientId').prop('disabled', true).html('<option value="">Select Client</option>');
        $('#employee-table tbody').empty();
        $('#employee-table-container').hide();
        $('#invoiceForm')[0].reset();
        toggleSubmitButton();
    }
});