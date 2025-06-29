$(document).ready(function() {
let jobTable;

// Initialize DataTable
jobTable = $("#job-applications-table").DataTable({
    pageLength: 10,
    responsive: true,
    columnDefs: [
        { orderable: false, targets: [8] } // Disable sorting on Actions column
    ],
    order: [[2, 'desc']] // Sort by Applied Date (newest first)
});

// Load job applications from database
loadJobApplications();

// Handle status change in Add Job Modal to show/hide conditional fields
$("#addStatus").change(function() {
    const selectedStatus = $(this).val();
    const interviewGroup = $("#addInterviewDate").closest('.form-group');
    const followupGroup = $("#addFollowupDate").closest('.form-group');
    
    // Hide all conditional fields by default
    interviewGroup.hide();
    followupGroup.hide();
    
    // Remove required attributes
    $("#addInterviewDate").removeAttr('required');
    
    // Show relevant fields based on status
    if (selectedStatus === 'Interview') {
        interviewGroup.show();
        $("#addInterviewDate").attr('required', true);
    } else if (selectedStatus === 'On Progress') {
        followupGroup.show(); // Show follow-up for On Progress
    }
    
    // Clear values when hiding fields
    if (!interviewGroup.is(':visible')) {
        $("#addInterviewDate").val('');
    }
    if (!followupGroup.is(':visible')) {
        $("#addFollowupDate").val('');
    }
});

// Initially hide conditional fields when modal opens
$("#addJobModal").on('show.bs.modal', function() {
    // Reset form
    $("#addJobForm")[0].reset();
    
    // Hide conditional fields
    $("#addInterviewDate").closest('.form-group').hide();
    $("#addFollowupDate").closest('.form-group').hide();
    
    // Set today's date as default for applied date
    $("#addAppliedDate").val(new Date().toISOString().split('T')[0]);
    
    // Remove any required attributes from conditional fields
    $("#addInterviewDate").removeAttr('required');
});

function loadJobApplications() {
    $.ajax({
        url: '../track/get_jobs.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                jobTable.clear();
                
                response.data.forEach(function(job) {
                    const interviewDate = job.interview_date ? formatDateTime(job.interview_date) : '-';
                    const followDate = job.follow_date ? formatDateOnly(job.follow_date) : '-';
                    const appliedDate = job.applied_date ? formatDateOnly(job.applied_date) : '-';
                    const salaryRange = job.salary_range || '-';
                    const jobType = job.job_type ? `<span class="badge bg-primary">${job.job_type}</span>` : '-';
                    
                    jobTable.row.add([
                        job.company,
                        job.position,
                        appliedDate,
                        `<select class="form-select form-select-sm status-select" data-id="${job.track_id}">
                            <option value="Applied" ${job.status === 'Applied' ? 'selected' : ''}>Applied</option>
                            <option value="Interview" ${job.status === 'Interview' ? 'selected' : ''}>Interview</option>
                            <option value="On Progress" ${job.status === 'On Progress' ? 'selected' : ''}>On Progress</option>
                            <option value="Accepted" ${job.status === 'Accepted' ? 'selected' : ''}>Accepted</option>
                            <option value="Rejected" ${job.status === 'Rejected' ? 'selected' : ''}>Rejected</option>
                        </select>`,
                        interviewDate,
                        followDate,
                        salaryRange,
                        jobType,
                        `<div class="form-button-action">
                            <button type="button" data-bs-toggle="tooltip" title="Edit Application" class="btn btn-link btn-primary btn-lg edit-btn" data-id="${job.track_id}">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button type="button" data-bs-toggle="tooltip" title="Delete Application" class="btn btn-link btn-danger delete-btn" data-id="${job.track_id}">
                                <i class="fa fa-times"></i>
                            </button>
                            <button type="button" data-bs-toggle="tooltip" title="View Details" class="btn btn-link btn-info view-btn" data-id="${job.track_id}" data-notes="${job.notes || ''}">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>`
                    ]);
                });
                
                jobTable.draw();
                
                // Reinitialize tooltips
                $('[data-bs-toggle="tooltip"]').tooltip();
            } else {
                console.error('Error loading job applications:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
        }
    });
}

// Add Job Application with enhanced validation
$("#addJobButton").click(function() {
    const status = $("#addStatus").val();
    
    // Validate required fields based on status
    if (!$("#addCompany").val() || !$("#addPosition").val() || !$("#addAppliedDate").val() || !status) {
        $.notify({
            icon: 'icon-close',
            title: 'Error!',
            message: 'Please fill in all required fields (Company, Position, Applied Date, and Status).',
        },{
            type: 'danger',
            placement: {
                from: "bottom",
                align: "right"
            },
            time: 3000,
        });
        return;
    }
    
    // Additional validation for Interview status
    if (status === 'Interview' && !$("#addInterviewDate").val()) {
        $.notify({
            icon: 'icon-close',
            title: 'Error!',
            message: 'Interview date is required when status is set to Interview.',
        },{
            type: 'danger',
            placement: {
                from: "bottom",
                align: "right"
            },
            time: 3000,
        });
        return;
    }

    const formData = {
        company: $("#addCompany").val().trim(),
        position: $("#addPosition").val().trim(),
        applied_date: $("#addAppliedDate").val(),
        status: status,
        interview_date: $("#addInterviewDate").val() || null,
        follow_date: $("#addFollowupDate").val() || null,
        salary_range: $("#addSalary").val().trim() || null,
        job_type: $("#addJobType").val() || null,
        notes: $("#addNotes").val().trim() || null
    };

    // Show loading state
    $("#addJobButton").prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Adding...');

    $.ajax({
        url: '../track/add_job.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Clear form and close modal
                $("#addJobForm")[0].reset();
                $("#addJobModal").modal('hide');
                
                // Reload table
                loadJobApplications();

                // Show success notification
                $.notify({
                    icon: 'icon-bell',
                    title: 'Success!',
                    message: response.message,
                },{
                    type: 'success',
                    placement: {
                        from: "bottom",
                        align: "right"
                    },
                    time: 2000,
                });
            } else {
                $.notify({
                    icon: 'icon-close',
                    title: 'Error!',
                    message: response.message,
                },{
                    type: 'danger',
                    placement: {
                        from: "bottom",
                        align: "right"
                    },
                    time: 3000,
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText);
            $.notify({
                icon: 'icon-close',
                title: 'Error!',
                message: 'Failed to add job application. Please try again.',
            },{
                type: 'danger',
                placement: {
                    from: "bottom",
                    align: "right"
                },
                time: 3000,
            });
        },
        complete: function() {
            // Reset button state
            $("#addJobButton").prop('disabled', false).html('<i class="fa fa-plus"></i> Add Application');
        }
    });
});


// Edit button click handler - FIXED VERSION
$(document).on('click', '.edit-btn', function () {
    const trackId = $(this).data('id');
    
    // Get the row data using a more reliable method
    const row = $(this).closest('tr');
    const cells = row.find('td');
    
    // Extract data from each cell
    const company = cells.eq(0).text().trim();
    const position = cells.eq(1).text().trim();
    const appliedDateText = cells.eq(2).text().trim();
    
    // Get status from the select dropdown
    const statusSelect = cells.eq(3).find('select.status-select');
    const status = statusSelect.val();
    
    const interviewDateText = cells.eq(4).text().trim();
    const followDateText = cells.eq(5).text().trim();
    const salaryText = cells.eq(6).text().trim();
    
    // Get job type from the badge or text content
    const jobTypeCell = cells.eq(7);
    let jobType = '';
    const jobTypeBadge = jobTypeCell.find('.badge');
    if (jobTypeBadge.length > 0) {
        jobType = jobTypeBadge.text().trim();
    } else {
        jobType = jobTypeCell.text().trim();
    }
    
    // Get notes from the view button data attribute
    const viewBtn = row.find('.view-btn');
    const notes = viewBtn.data('notes') || '';

    // Populate the edit modal
    $('#edit-track-id').val(trackId);
    $('#edit-company').val(company);
    $('#edit-position').val(position);
    $('#edit-applied-date').val(convertToDateFormat(appliedDateText));
    $('#edit-status').val(status);
    $('#edit-status').trigger('change');
    $('#edit-interview-date').val(convertToDateTimeFormat(interviewDateText));
    $('#edit-follow-date').val(convertToDateFormat(followDateText));
    $('#edit-salary-range').val(salaryText === '-' ? '' : salaryText);
    $('#edit-job-type').val(jobType === '-' ? '' : jobType);
    $('#edit-notes').val(notes);

    // Show the modal
    $('#editJobModal').modal('show');
});

// Handle status change in Edit Job Modal to show/hide conditional fields
$("#edit-status").change(function() {
    const selectedStatus = $(this).val();
    const interviewGroup = $("#edit-interview-date").closest('.form-group');
    const followupGroup = $("#edit-follow-date").closest('.form-group');

    // Hide all conditional fields
    interviewGroup.hide();
    followupGroup.hide();

    // Remove required attributes
    $("#edit-interview-date").removeAttr('required');

    // Show relevant fields
    if (selectedStatus === 'Interview') {
        interviewGroup.show();
        $("#edit-interview-date").attr('required', true);
    } else if (selectedStatus === 'On Progress') {
        followupGroup.show();
    }

    // Clear values when hiding
    if (!interviewGroup.is(':visible')) {
        $("#edit-interview-date").val('');
    }
    if (!followupGroup.is(':visible')) {
        $("#edit-follow-date").val('');
    }
});


// Helper function to convert displayed date to input format (YYYY-MM-DD)
function convertToDateFormat(dateStr) {
    if (!dateStr || dateStr === '-' || dateStr.trim() === '') {
        return '';
    }
    
    try {
        // Handle different date formats that might be displayed
        // Check if it's already in YYYY-MM-DD format
        if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
            return dateStr;
        }
        
        // Parse the displayed date (assuming it's in a readable format like "January 15, 2024")
        const date = new Date(dateStr);
        if (!isNaN(date.getTime())) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
        
        return '';
    } catch (error) {
        console.error('Error converting date:', error);
        return '';
    }
}

// Helper function to convert displayed datetime to input format (YYYY-MM-DDTHH:MM)
function convertToDateTimeFormat(dateTimeStr) {
    if (!dateTimeStr || dateTimeStr === '-' || dateTimeStr.trim() === '') {
        return '';
    }
    
    try {
        // Parse the displayed datetime
        const date = new Date(dateTimeStr);
        if (!isNaN(date.getTime())) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        }
        
        return '';
    } catch (error) {
        console.error('Error converting datetime:', error);
        return '';
    }
}

// Alternative approach: Get data directly from server when edit button is clicked
$(document).on('click', '.edit-btn', function () {
    const trackId = $(this).data('id');
    
    // Show loading state
    const editBtn = $(this);
    const originalHtml = editBtn.html();
    editBtn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);
    
    // Fetch job data from server
    $.ajax({
        url: '../track/get_job_details.php', // You'll need to create this file
        type: 'GET',
        data: { track_id: trackId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const job = response.data;
                
                // Populate the edit modal with server data
                $('#edit-track-id').val(job.track_id);
                $('#edit-company').val(job.company);
                $('#edit-position').val(job.position);
                $('#edit-applied-date').val(job.applied_date);
                $('#edit-status').val(job.status);
                $('#edit-interview-date').val(job.interview_date || '');
                $('#edit-follow-date').val(job.follow_date || '');
                $('#edit-salary-range').val(job.salary_range || '');
                $('#edit-job-type').val(job.job_type || '');
                $('#edit-notes').val(job.notes || '');
                
                // Show the modal
                $('#editJobModal').modal('show');
            } else {
                alert('Error loading job details: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching job details:', error);
            alert('Failed to load job details. Please try again.');
        },
        complete: function() {
            // Restore button state
            editBtn.html(originalHtml).prop('disabled', false);
        }
    });
});

// Delete row functionality
$('#job-applications-table tbody').on('click', '.delete-btn', function() {
    const jobId = $(this).data('id');
    const row = $(this).closest('tr');
    
    swal({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        type: 'warning',
        buttons: {
            confirm: {
                text: 'Yes, delete it!',
                className: 'btn btn-success'
            },
            cancel: {
                visible: true,
                className: 'btn btn-danger'
            }
        }
    }).then((willDelete) => {
        if (willDelete) {
            $.ajax({
                url: '../track/delete_job.php',
                type: 'POST',
                data: { track_id: jobId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        loadJobApplications(); // Reload table
                        swal("Deleted!", response.message, "success");
                    } else {
                        swal("Error!", response.message, "error");
                    }
                },
                error: function() {
                    swal("Error!", "Failed to delete job application.", "error");
                }
            });
        }
    });
});

// Status change functionality with enhanced logic
$('#job-applications-table tbody').on('change', '.status-select', function() {
    const newStatus = $(this).val();
    const jobId = $(this).data('id');
    const company = $(this).closest('tr').find('td:first').text();
    const oldStatus = $(this).data('old-status') || 'Unknown';
    
    // Store old status for potential rollback
    $(this).data('old-status', newStatus);
    
    // Special handling for Interview status
    if (newStatus === 'Interview') {
        swal({
            title: 'Interview Status',
            text: 'Do you want to set an interview date for this application?',
            type: 'info',
            buttons: {
                cancel: {
                    text: 'Skip for now',
                    visible: true,
                    className: 'btn btn-secondary'
                },
                confirm: {
                    text: 'Set Interview Date',
                    className: 'btn btn-primary'
                }
            }
        }).then((setDate) => {
            if (setDate) {
                // You could implement an inline date picker here
                // For now, we'll just update the status
                updateJobStatus(jobId, newStatus, company);
            } else {
                updateJobStatus(jobId, newStatus, company);
            }
        });
    } else {
        updateJobStatus(jobId, newStatus, company);
    }
});

// Separate function to handle status updates
function updateJobStatus(jobId, newStatus, company) {
    $.ajax({
        url: '../track/update_status.php',
        type: 'POST',
        data: { 
            track_id: jobId,
            status: newStatus 
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $.notify({
                    icon: 'icon-check',
                    title: 'Status Updated!',
                    message: `${company} application status changed to ${newStatus}.`,
                },{
                    type: 'info',
                    placement: {
                        from: "bottom",
                        align: "right"
                    },
                    time: 2000,
                });
            } else {
                $.notify({
                    icon: 'icon-close',
                    title: 'Error!',
                    message: response.message,
                },{
                    type: 'danger',
                    placement: {
                        from: "bottom",
                        align: "right"
                    },
                    time: 3000,
                });
                // Reload to revert changes
                loadJobApplications();
            }
        },
        error: function() {
            $.notify({
                icon: 'icon-close',
                title: 'Error!',
                message: 'Failed to update status.',
            },{
                type: 'danger',
                placement: {
                    from: "bottom",
                    align: "right"
                },
                time: 3000,
            });
            // Reload to revert changes
            loadJobApplications();
        }
    });
}

// edit
$('#editJobForm').on('submit', function (e) {
    e.preventDefault();

    const formData = $(this).serialize();

    $.ajax({
        url: '../track/edit_job.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                $('#editJobModal').modal('hide');
                loadJobApplications(); // Refresh table
                
                // Show success notification instead of alert
                $.notify({
                    icon: 'icon-check',
                    title: 'Success!',
                    message: res.message,
                },{
                    type: 'success',
                    placement: {
                        from: "bottom",
                        align: "right"
                    },
                    time: 2000,
                });
            } else {
                $.notify({
                    icon: 'icon-close',
                    title: 'Error!',
                    message: res.message,
                },{
                    type: 'danger',
                    placement: {
                        from: "bottom",
                        align: "right"
                    },
                    time: 3000,
                });
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', xhr.responseText);
            $.notify({
                icon: 'icon-close',
                title: 'Error!',
                message: 'Failed to update job application. Please try again.',
            },{
                type: 'danger',
                placement: {
                    from: "bottom",
                    align: "right"
                },
                time: 3000,
            });
        }
    });
});


// View details functionality
$('#job-applications-table tbody').on('click', '.view-btn', function() {
    const row = $(this).closest('tr');
    const data = jobTable.row(row).data();
    const notes = $(this).data('notes') || 'No additional notes';
    
    swal({
        title: 'Application Details',
        text: `Company: ${data[0]}\nPosition: ${data[1]}\nApplied: ${data[2]}\nInterview: ${data[4]}\nFollow-up: ${data[5]}\nSalary: ${data[6]}\n\nNotes: ${notes}`,
        type: 'info',
        buttons: {
            confirm: {
                className: 'btn btn-info'
            }
        }
    });
});

// Initialize tooltips
$('[data-bs-toggle="tooltip"]').tooltip();

});

// Helper function to format datetime
function formatDateTime(datetime) {
    if (!datetime) return '-';
    const date = new Date(datetime);
    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    };
    return date.toLocaleString(undefined, options); 
}

// this is for the Date only
function formatDateOnly(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString(undefined, options);
}
