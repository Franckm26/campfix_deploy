<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Welcome Credentials Management - CampFix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header-section h2 {
            margin: 0;
            font-weight: 700;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stats-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-box {
            flex: 1;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #667eea;
        }
        .stat-label {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        .table-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-sent {
            background: #d4edda;
            color: #155724;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-no-credentials {
            background: #f8d7da;
            color: #721c24;
        }
        table.dataTable tbody tr {
            cursor: pointer;
        }
        table.dataTable tbody tr:hover {
            background: #f8f9fa;
        }
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .user-detail {
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .user-detail label {
            font-weight: 600;
            color: #666;
            margin-bottom: 5px;
            display: block;
        }
        .user-detail value {
            color: #333;
            font-size: 16px;
        }
        .send-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
        }
        .send-btn:hover {
            opacity: 0.9;
        }
        #tableSearch {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 8px 15px;
        }
        #tableSearch:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            outline: none;
        }
        .alert ul {
            margin-left: 20px;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="container">
            <h2><i class="fas fa-envelope me-2"></i> Welcome Credentials Management</h2>
            <p class="mb-0">Manage and send welcome credentials to students</p>
        </div>
    </div>

    <div class="container">
        <!-- Statistics -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-number" id="totalStudents">{{ count($students) }}</div>
                <div class="stat-label">Total Students</div>
            </div>
            <div class="stat-box">
                <div class="stat-number" id="sentCount">{{ $students->where('status', 'sent')->count() }}</div>
                <div class="stat-label">Credentials Sent</div>
            </div>
            <div class="stat-box">
                <div class="stat-number" id="pendingCount">{{ $students->where('status', 'pending')->count() }}</div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-box">
                <div class="stat-number" id="noCredentialsCount">{{ $students->where('has_credentials', false)->count() }}</div>
                <div class="stat-label">No Credentials</div>
            </div>
        </div>

        <!-- Legend -->
        <div class="alert alert-info">
            <h6><i class="fas fa-info-circle me-2"></i> Status Legend:</h6>
            <div class="row">
                <div class="col-md-4">
                    <span class="status-badge status-sent">✓ Sent</span> - Credentials sent successfully
                </div>
                <div class="col-md-4">
                    <span class="status-badge status-pending">Pending</span> - Credentials ready but not sent yet
                </div>
                <div class="col-md-4">
                    <span class="status-badge status-no-credentials">No Credentials</span> - No password generated (user created before welcome email system)
                </div>
            </div>
        </div>

        <!-- Students Table -->
        <div class="table-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0"><i class="fas fa-users me-2"></i> Students List</h4>
                <div style="width: 300px;">
                    <input type="text" id="tableSearch" class="form-control" placeholder="🔍 Search students...">
                </div>
            </div>
            
            <table id="studentsTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Sent At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    <tr data-student-id="{{ $student['id'] }}" 
                        data-student-name="{{ $student['name'] }}"
                        data-student-email="{{ $student['email'] }}"
                        data-student-sid="{{ $student['student_id'] }}"
                        data-student-status="{{ $student['status'] }}"
                        data-has-credentials="{{ $student['has_credentials'] ? 'true' : 'false' }}"
                        onclick="showStudentModal(this)">
                        <td>{{ $student['student_id'] }}</td>
                        <td>{{ $student['name'] }}</td>
                        <td>{{ $student['email'] }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ ucfirst($student['role']) }}</span>
                        </td>
                        <td>
                            @if(!$student['has_credentials'])
                                <span class="status-badge status-no-credentials">No Credentials</span>
                            @elseif($student['status'] === 'sent')
                                <span class="status-badge status-sent">✓ Sent</span>
                            @else
                                <span class="status-badge status-pending">Pending</span>
                            @endif
                        </td>
                        <td>
                            @if($student['sent_at'])
                                {{ \Carbon\Carbon::parse($student['sent_at'])->format('M d, Y h:i A') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Send Credentials Modal -->
    <div class="modal fade" id="sendCredentialsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-paper-plane me-2"></i> Send Welcome Credentials</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="user-detail">
                        <label>Student ID</label>
                        <div id="modalStudentId"></div>
                    </div>
                    <div class="user-detail">
                        <label>Name</label>
                        <div id="modalName"></div>
                    </div>
                    <div class="user-detail">
                        <label>Email</label>
                        <div id="modalEmail"></div>
                    </div>
                    <div class="user-detail">
                        <label>Status</label>
                        <div id="modalStatus"></div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>What will happen:</strong> Two separate emails will be sent to this student:
                        <ul class="mb-0 mt-2">
                            <li>Email address notification</li>
                            <li>Temporary password notification</li>
                        </ul>
                    </div>

                    <div id="noCredentialsWarning" class="alert alert-danger" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Cannot Send - No Credentials Available</strong>
                        <p class="mb-0 mt-2">This student does not have credentials in the system. This happens when:</p>
                        <ul class="mt-2 mb-0">
                            <li>User was created before the welcome email system</li>
                            <li>No temporary password was generated yet</li>
                        </ul>
                        <p class="mb-0 mt-2"><strong>Solution:</strong> Run the welcome email cron job to generate credentials for all users.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="send-btn" id="sendCredentialsBtn" onclick="sendCredentials()">
                        <i class="fas fa-paper-plane me-2"></i> Send Credentials Now
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        let currentStudentId = null;
        let table;

        $(document).ready(function() {
            // Initialize DataTable
            table = $('#studentsTable').DataTable({
                pageLength: 25,
                order: [[1, 'asc']], // Sort by name
                dom: 'lrtip', // Hide default search box
                language: {
                    lengthMenu: "Show _MENU_ students per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ students",
                    infoFiltered: "(filtered from _MAX_ total students)"
                }
            });

            // Custom search box
            $('#tableSearch').on('keyup', function() {
                table.search(this.value).draw();
            });
        });

        function showStudentModal(row) {
            const studentId = row.dataset.studentId;
            const studentName = row.dataset.studentName;
            const studentEmail = row.dataset.studentEmail;
            const studentSid = row.dataset.studentSid;
            const status = row.dataset.studentStatus;
            const hasCredentials = row.dataset.hasCredentials === 'true';

            currentStudentId = studentId;

            // Populate modal
            document.getElementById('modalStudentId').textContent = studentSid;
            document.getElementById('modalName').textContent = studentName;
            document.getElementById('modalEmail').textContent = studentEmail;
            
            // Status with badge
            const statusBadge = status === 'sent' 
                ? '<span class="status-badge status-sent">✓ Sent</span>'
                : '<span class="status-badge status-pending">Pending</span>';
            document.getElementById('modalStatus').innerHTML = statusBadge;

            // Show/hide warning and button
            const warningDiv = document.getElementById('noCredentialsWarning');
            const sendBtn = document.getElementById('sendCredentialsBtn');
            
            if (!hasCredentials) {
                warningDiv.style.display = 'block';
                sendBtn.disabled = true;
                sendBtn.style.opacity = '0.5';
            } else {
                warningDiv.style.display = 'none';
                sendBtn.disabled = false;
                sendBtn.style.opacity = '1';
            }

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('sendCredentialsModal'));
            modal.show();
        }

        async function sendCredentials() {
            if (!currentStudentId) return;

            const sendBtn = document.getElementById('sendCredentialsBtn');
            const originalHtml = sendBtn.innerHTML;
            
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Sending...';

            try {
                const response = await fetch('/api/welcome-credentials/send/' + currentStudentId, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('sendCredentialsModal')).hide();
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Credentials Sent!',
                        text: 'Welcome credentials have been sent successfully.',
                        confirmButtonColor: '#667eea',
                        timer: 3000
                    });

                    // Reload page to update status
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to Send',
                        text: data.message || 'Could not send credentials. Please try again.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            } catch (error) {
                console.error('Error sending credentials:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.',
                    confirmButtonColor: '#dc3545'
                });
            } finally {
                sendBtn.disabled = false;
                sendBtn.innerHTML = originalHtml;
            }
        }
    </script>
</body>
</html>
