$(document).ready(function() {
    function sanitizeInput(input) {
        return $('<div>').text(input).html();
    }

    function disableForm() {
        $('#assignEmployeeForm input, #assignEmployeeForm select, #submitSelection').prop('disabled', true);
        $('#submitSelection').addClass('d-none');
        $('#successMessage').removeClass('d-none').addClass('d-flex');
    }

    function enableForm() {
        $('#assignEmployeeForm input, #assignEmployeeForm select, #submitSelection').prop('disabled', false);
        $('#submitSelection').removeClass('d-none');
        $('#successMessage').addClass('d-none').removeClass('d-flex');
    }

    function resetForm() {
        $('#assignEmployeeForm')[0].reset();
        $('#corporateselect').val('');
        $('#clientselect').empty().append('<option value="">Select Client</option>');
        $('#employeeselect').empty().append('<option value="">Select Employee</option>');
    }

    // Fetch corporates when page loads
    function fetchCorporates() {
        $.ajax({
            url: 'AssignEmployees.php',
            method: 'GET',
            data: { action: 'getCorporates' },
            dataType: 'json',
            success: function(response) {
                if (response.corporates && Array.isArray(response.corporates)) {
                    response.corporates.forEach(function(corporate) {
                        var $option = $('<option>')
                            .val(sanitizeInput(corporate.CorporateId))
                            .text(sanitizeInput(corporate.CorporateName));
                        $('#corporateselect').append($option);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching corporates:', error);
                alert('Failed to fetch corporates. Please try again.');
            }
        });
    }

    // Call fetchCorporates when page loads
    fetchCorporates();

    function fetchClientsAndEmployees(corporateId) {
        if (corporateId && /^\d+$/.test(corporateId)) {
            $.ajax({
                url: 'AssignEmployees.php',
                method: 'GET',
                data: { corporateId: sanitizeInput(corporateId) },
                dataType: 'json',
                success: function(response) {
                    $('#clientselect').empty().append('<option value="">Select Client</option>');
                    $('#employeeselect').empty().append('<option value="">Select Employee</option>');

                    if (response.clients && Array.isArray(response.clients)) {
                        response.clients.forEach(function(client) {
                            var $option = $('<option>')
                                .val(sanitizeInput(client.ClientId))
                                .text(sanitizeInput(client.ClientName));
                            $('#clientselect').append($option);
                        });
                    } else {
                        $('#clientselect').append('<option value="">No clients found</option>');
                    }

                    if (response.employees && Array.isArray(response.employees)) {
                        response.employees.forEach(function(employee) {
                            var $option = $('<option>')
                                .val(sanitizeInput(employee.EmployeeStateId))
                                .text(sanitizeInput(employee.EmployeeName));
                            $('#employeeselect').append($option);
                        });
                    } else {
                        $('#employeeselect').append('<option value="">No employees found</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching data:', error);
                    alert('Failed to fetch data. Please try again.');
                }
            });
        }
    }

    // Changed from input event to change event since we're using a select now
    $('#corporateselect').on('change', function() {
        var corporateId = $(this).val();
        if (corporateId) {
            fetchClientsAndEmployees(corporateId);
        } else {
            $('#clientselect').empty().append('<option value="">Select Client</option>');
            $('#employeeselect').empty().append('<option value="">Select Employee</option>');
        }
    });

    function isValidDateRange(startDate, endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        return start <= end;
    }

    $('#submitSelection').on('click', function() {
        const formData = {
            clientId: $('#clientselect').val(),
            employeeStateId: $('#employeeselect').val(),
            startDate: $('#startdate').val(),
            endDate: $('#enddate').val(),
            billingRate: $('#billingrate').val(),
            duedays: $('#duedays').val()
        };

        if (!formData.clientId || !formData.employeeStateId || 
            !formData.startDate || !formData.endDate || 
            !formData.billingRate || !formData.duedays) {
            alert('Please fill in all required fields.');
            return;
        }

        if (!isValidDateRange(formData.startDate, formData.endDate)) {
            alert('End date must be after start date.');
            return;
        }

        if (formData.billingRate <= 0 || formData.duedays <= 0) {
            alert('Billing rate and due days must be positive numbers.');
            return;
        }

        // Disable form before submission
        disableForm();

        $.ajax({
            url: 'AssignEmployees.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.error) {
                    alert('Error: ' + response.error);
                    enableForm();
                } else {
                    // Show success message
                    $('#successMessage').removeClass('d-none').addClass('d-flex');
                    
                    // Clear form after 2 seconds
                    setTimeout(function() {
                        $('#successMessage').addClass('d-none').removeClass('d-flex');
                        resetForm();
                        enableForm();
                    }, 2000);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error submitting data:', error);
                alert('Failed to submit data. Please try again.');
                enableForm();
            }
        });
    });
});
