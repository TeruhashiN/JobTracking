$(document).ready(function() {
let jobTable;

// ========== STATUS STYLING FUNCTIONS ==========
// Function to get status badge HTML with proper styling
function getStatusBadge(status) {
    const statusMap = {
        'Applied': {
            class: 'status-applied',
            text: 'Applied'
        },
        'Interview': {
            class: 'status-interview',
            text: 'Interview'
        },
        'On Progress': {
            class: 'status-onprogress',
            text: 'On Progress'
        },
        'Accepted': {
            class: 'status-accepted',
            text: 'Accepted'
        },
        'Rejected': {
            class: 'status-rejected',
            text: 'Rejected'
        }
    };

    const statusInfo = statusMap[status] || { class: 'status-applied', text: status };
    return `<span class="status-badge ${statusInfo.class}">${statusInfo.text}</span>`;
}

// Function to get row class based on status
function getRowClass(status) {
    const rowClassMap = {
        'Applied': 'status-applied-row',
        'Interview': 'status-interview-row',
        'On Progress': 'status-onprogress-row',
        'Accepted': 'status-accepted-row',
        'Rejected': 'status-rejected-row'
    };
    
    return rowClassMap[status] || 'status-applied-row';
}

// Function to apply status styling to table rows
function applyStatusStyling() {
    $('#job-applications-table tbody tr').each(function() {
        const statusCell = $(this).find('td:nth-child(4)'); // Status is in 4th column
        const statusSelect = statusCell.find('.status-select');
        
        if (statusSelect.length > 0) {
            const statusText = statusSelect.val();
            
            // Remove existing status classes
            $(this).removeClass('status-applied-row status-interview-row status-onprogress-row status-accepted-row status-rejected-row');
            
            // Add appropriate row class
            $(this).addClass(getRowClass(statusText));
        }
    });
}

// Function to update a single row's status
function updateRowStatus(row, status) {
    const $row = $(row);
    
    // Remove existing status classes
    $row.removeClass('status-applied-row status-interview-row status-onprogress-row status-accepted-row status-rejected-row');
    
    // Add new status class
    $row.addClass(getRowClass(status));
}

// ========== ENHANCED DATATABLE INITIALIZATION WITH PROPER STATUS HANDLING ==========
jobTable = $("#job-applications-table").DataTable({
    pageLength: 10,
    responsive: true,
    columnDefs: [
        { orderable: false, targets: [8] }, // Disable sorting on Actions column
        {
            targets: [3], // Status column
            render: function(data, type, row, meta) {
                if (type === 'display') {
                    // Get the track_id from the row's stored data
                    const rowData = jobTable.row(meta.row).data();
                    const trackId = rowData.DT_RowData ? rowData.DT_RowData.track_id : '';
                    
                    return `<select class="form-select form-select-sm status-select" data-id="${trackId}" data-row-index="${meta.row}">
                        <option value="Applied" ${data === 'Applied' ? 'selected' : ''}>Applied</option>
                        <option value="Interview" ${data === 'Interview' ? 'selected' : ''}>Interview</option>
                        <option value="On Progress" ${data === 'On Progress' ? 'selected' : ''}>On Progress</option>
                        <option value="Accepted" ${data === 'Accepted' ? 'selected' : ''}>Accepted</option>
                        <option value="Rejected" ${data === 'Rejected' ? 'selected' : ''}>Rejected</option>
                    </select>`;
                }
                return data;
            }
        }
    ],
    order: [[2, 'desc']], // Sort by Applied Date (newest first)
    drawCallback: function() {
        // Apply status styling after each draw
        setTimeout(function() {
            applyStatusStyling();
            // Re-attach event handlers for status selects
            attachStatusChangeHandlers();
        }, 50);
        
        // Reinitialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
    }
});


// ========== ENHANCED STATUS CHANGE FUNCTIONALITY ==========
function attachStatusChangeHandlers() {
    // Remove existing handlers to prevent duplicates
    $('.status-select').off('change.statusUpdate');
    
    // Attach new handlers
    $('.status-select').on('change.statusUpdate', function() {
        const newStatus = $(this).val();
        const jobId = $(this).data('id');
        const rowIndex = $(this).data('row-index');
        const row = $(this).closest('tr');
        
        console.log('Status change triggered:', {
            newStatus: newStatus,
            jobId: jobId,
            rowIndex: rowIndex
        });
        
        // Validate that we have a job ID
        if (!jobId) {
            console.error('No job ID found for status update');
            
            // Try to get it from the row data
            const rowData = jobTable.row(row).data();
            const alternateId = rowData.DT_RowData ? rowData.DT_RowData.track_id : null;
            
            if (alternateId) {
                console.log('Found alternate ID:', alternateId);
                $(this).attr('data-id', alternateId);
                updateJobStatusEnhanced(alternateId, newStatus, row);
            } else {
                $.notify({
                    icon: 'icon-close',
                    title: 'Error!',
                    message: 'Job Application ID is missing. Please refresh the page and try again.',
                },{
                    type: 'danger',
                    placement: {
                        from: "bottom",
                        align: "right"
                    },
                    time: 3000,
                });
                
                // Reload the table to fix the issue
                loadJobApplications();
                return;
            }
        } else {
            updateJobStatusEnhanced(jobId, newStatus, row);
        }
    });
}

// Enhanced status update function with better error handling
function updateJobStatusEnhanced(jobId, newStatus, row) {
    // Get company name for notification
    const company = $(row).find('td:first').text();
    
    // Update row styling immediately for better UX
    updateRowStatus(row, newStatus);
    
    // Show loading state
    const statusSelect = $(row).find('.status-select');
    const originalHtml = statusSelect.prop('outerHTML');
    statusSelect.prop('disabled', true);
    
    // Validate inputs
    if (!jobId || !newStatus) {
        console.error('Missing required parameters:', { jobId, newStatus });
        $.notify({
            icon: 'icon-close',
            title: 'Error!',
            message: 'Invalid data. Please refresh the page and try again.',
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
                
                // Re-enable the select
                statusSelect.prop('disabled', false);
            } else {
                console.error('Server error:', response.message);
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
        error: function(xhr, status, error) {
            console.error('AJAX error:', {
                status: status,
                error: error,
                responseText: xhr.responseText
            });
            
            $.notify({
                icon: 'icon-close',
                title: 'Error!',
                message: 'Failed to update status. Please check your connection and try again.',
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

// ========== DEBUGGING FUNCTION ==========
// Add this function to help debug the issue
function debugTableData() {
    console.log('=== TABLE DEBUG INFO ===');
    
    jobTable.rows().every(function(rowIdx, tableLoop, rowLoop) {
        const data = this.data();
        const node = this.node();
        const statusSelect = $(node).find('.status-select');
        
        console.log(`Row ${rowIdx}:`, {
            data: data,
            DT_RowData: data.DT_RowData,
            statusSelectId: statusSelect.data('id'),
            statusValue: statusSelect.val()
        });
    });
    
    console.log('=== END DEBUG INFO ===');
}

// Make debug function globally accessible
window.debugTableData = debugTableData;

// Load job applications from database
loadJobApplications();

// ========== MODAL HANDLERS ==========
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

// ========== ENHANCED DATA LOADING WITH PROPER ID HANDLING ==========
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
                    
                    // Create the row data array with track_id included
                    const rowData = [
                        job.company,
                        job.position,
                        appliedDate,
                        job.status, // This will be processed by columnDefs
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
                    ];
                    
                    // Add the row with track_id stored properly
                    const newRow = jobTable.row.add(rowData);
                    
                    // Store additional data in the row's DT_RowData
                    newRow.data().DT_RowData = {
                        track_id: job.track_id,
                        company: job.company,
                        position: job.position,
                        notes: job.notes || ''
                    };
                });
                
                jobTable.draw();
            } else {
                console.error('Error loading job applications:', response.message);
                $.notify({
                    icon: 'icon-close',
                    title: 'Error!',
                    message: 'Failed to load job applications: ' + response.message,
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
            console.error('AJAX Error:', error);
            $.notify({
                icon: 'icon-close',
                title: 'Error!',
                message: 'Failed to load job applications. Please check your connection.',
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
}

// ========== ADD JOB FUNCTIONALITY ==========
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

// ========== EDIT FUNCTIONALITY ==========
// Enhanced edit button click handler that fetches data from server
$(document).on('click', '.edit-btn', function () {
    const trackId = $(this).data('id');
    
    // Show loading state
    const editBtn = $(this);
    const originalHtml = editBtn.html();
    editBtn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);
    
    // Fetch job data from server
    $.ajax({
        url: '../track/get_job_details.php',
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
                
                // Trigger status change to show/hide fields
                $('#edit-status').trigger('change');
                
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

// Edit form submission
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
                
                // Show success notification
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

// ========== DELETE FUNCTIONALITY ==========
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

// ========== STATUS CHANGE FUNCTIONALITY ==========
// Enhanced status change functionality with styling updates
$('#job-applications-table tbody').on('change', '.status-select', function() {
    const newStatus = $(this).val();
    const jobId = $(this).data('id');
    const company = $(this).closest('tr').find('td:first').text();
    const row = $(this).closest('tr');
    
    // Update row styling immediately for better UX
    updateRowStatus(row, newStatus);
    
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
                updateJobStatus(jobId, newStatus, company);
            } else {
                updateJobStatus(jobId, newStatus, company);
            }
        });
    } else {
        updateJobStatus(jobId, newStatus, company);
    }
});

// Enhanced status update function
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

// ========== VIEW DETAILS FUNCTIONALITY ==========
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

// ========== ADDITIONAL STATUS FUNCTIONS ==========
// Function to get status statistics for dashboard
function getStatusStatistics() {
    const stats = {
        applied: 0,
        interview: 0,
        onprogress: 0,
        accepted: 0,
        rejected: 0,
        total: 0
    };
    
    $('#job-applications-table tbody tr').each(function() {
        const statusSelect = $(this).find('.status-select');
        if (statusSelect.length > 0) {
            const status = statusSelect.val();
            stats.total++;
            
            switch(status.toLowerCase()) {
                case 'applied':
                    stats.applied++;
                    break;
                case 'interview':
                    stats.interview++;
                    break;
                case 'on progress':
                    stats.onprogress++;
                    break;
                case 'accepted':
                    stats.accepted++;
                    break;
                case 'rejected':
                    stats.rejected++;
                    break;
            }
        }
    });
    
    return stats;
}

// Function to filter table by status
function filterByStatus(status) {
    const table = $('#job-applications-table').DataTable();
    
    if (status === 'all') {
        table.column(3).search('').draw();
    } else {
        table.column(3).search(status).draw();
    }
}

// Make filter function globally accessible
window.filterByStatus = filterByStatus;

// Initialize tooltips
$('[data-bs-toggle="tooltip"]').tooltip();

});

// ========== HELPER FUNCTIONS ==========
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

// Helper function for date only formatting
function formatDateOnly(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString(undefined, options);
}