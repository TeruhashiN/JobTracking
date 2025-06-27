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
        followupGroup.show(); // Also show follow-up for interview
        $("#addInterviewDate").attr('required', true);
    } else if (selectedStatus === 'On Progress') {
        followupGroup.show(); // Show follow-up for On Progress
    } else if (selectedStatus === 'Applied') {
        followupGroup.show(); // Show follow-up for Applied status
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
                    const followDate = job.follow_date || '-';
                    const salaryRange = job.salary_range || '-';
                    const jobType = job.job_type ? `<span class="badge bg-primary">${job.job_type}</span>` : '-';
                    
                    jobTable.row.add([
                        job.company,
                        job.position,
                        job.applied_date,
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
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}