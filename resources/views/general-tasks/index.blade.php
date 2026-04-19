@extends('layouts.app')
@section('title', 'Tasks — SHF')

@section('header')
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
        <h2 class="font-display fw-semibold text-white shf-page-title">
            <svg class="shf-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            Tasks
        </h2>
        <button class="btn-accent btn-accent-sm" data-bs-toggle="modal" data-bs-target="#taskModal"
            onclick="resetTaskForm()">
            <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Task
        </button>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap5.min.css') }}">
@endpush

@section('content')
    <div class="py-4">
        <div class="px-3 px-sm-4 px-lg-5">

            {{-- Filters --}}
            <div class="shf-section mb-3">
                <div class="shf-section-header shf-collapsible shf-filter-collapse shf-clickable d-flex align-items-center gap-2"
                    data-target="#taskFilterBody">
                    <svg class="shf-collapse-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="shf-section-title">Filters</span>
                    <span id="taskFilterCount" class="shf-filter-count shf-collapse-hidden">0</span>
                </div>
                <div id="taskFilterBody" class="shf-section-body shf-filter-body-collapse">
                    <div class="row g-2 align-items-end">
                        <div class="col-6 col-md-auto">
                            <label class="shf-form-label d-block mb-1">View</label>
                            <select id="filterView" class="shf-input">
                                <option value="my_tasks_and_assigned">My & Assigned</option>
                                <option value="my_tasks">Created by Me</option>
                                <option value="assigned_to_me">Assigned to Me</option>
                                @if ($isBdh)
                                    <option value="my_branch">My Branch</option>
                                @endif
                                @if ($canViewAll)
                                    <option value="all">All Tasks</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-6 col-md-auto">
                            <label class="shf-form-label d-block mb-1">Status</label>
                            <select id="filterStatus" class="shf-input">
                                <option value="active" selected>Active</option>
                                <option value="">All</option>
                                @foreach (\App\Models\GeneralTask::STATUS_LABELS as $key => $info)
                                    <option value="{{ $key }}">{{ $info['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-auto">
                            <label class="shf-form-label d-block mb-1">Priority</label>
                            <select id="filterPriority" class="shf-input">
                                <option value="">All</option>
                                @foreach (\App\Models\GeneralTask::PRIORITY_LABELS as $key => $info)
                                    <option value="{{ $key }}">{{ $info['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-auto">
                            <label class="shf-form-label d-block mb-1">&nbsp;</label>
                            <input type="text" id="filterSearch" placeholder="Search..." class="shf-input">
                        </div>
                        <div class="col-12 col-md-auto d-flex gap-2">
                            <label class="shf-form-label d-block mb-1">&nbsp;</label>
                            <button type="button" id="btnFilter" class="btn-accent btn-accent-sm">
                                <svg class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Filter
                            </button>
                            <button type="button" id="btnClear" class="btn-accent-outline btn-accent-sm">
                                <svg class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table + Mobile Cards --}}
            <div id="tasksTableSection" class="shf-section shf-dt-section">
                <div id="tasksMobileCards" class="d-md-none"></div>
                <div class="table-responsive">
                    <table id="tasksTable" class="table table-hover mb-0" style="width:100%">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Assigned</th>
                                <th>Loan</th>
                                <th>Priority</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Completed</th>
                                <th>Created</th>
                                <th style="width:100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            {{-- Empty State --}}
            <div id="tasksEmptyState" class="shf-collapse-hidden">
                <div class="shf-section">
                    <div class="p-5 text-center">
                        <div class="shf-stat-icon mx-auto mb-3">
                            <svg class="shf-icon-xl" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <h3 class="font-display fw-semibold shf-text-lg shf-text-dark-alt">No tasks found</h3>
                        <p class="mt-1 small shf-text-gray">Create a new task to get started.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Create/Edit Task Modal --}}
    <div class="modal fade" id="taskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border: none; border-radius: 12px;">
                <form id="taskForm" method="POST" action="{{ route('general-tasks.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="taskFormMethod" value="POST">
                    <div class="modal-header"
                        style="background: var(--primary-dark-solid); color: #fff; border-radius: 12px 12px 0 0;">
                        <h5 class="modal-title font-display" id="taskModalTitle">Create New Task / નવું ટાસ્ક બનાવો</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger mb-3">
                                <ul class="mb-0 small">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="mb-3">
                            <label class="shf-form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="taskTitle" class="shf-input" required
                                maxlength="255" value="{{ old('title') }}">
                        </div>
                        <div class="mb-3">
                            <label class="shf-form-label">Description</label>
                            <textarea name="description" id="taskDescription" class="shf-input" rows="3" maxlength="5000">{{ old('description') }}</textarea>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="shf-form-label">Assign To</label>
                                <select name="assigned_to" id="taskAssignedTo" class="shf-input">
                                    <option value="">Self (no one)</option>
                                    @foreach ($users as $u)
                                        <option value="{{ $u->id }}"
                                            {{ (string) old('assigned_to', $u->id === auth()->id() ? $u->id : '') === (string) $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Leave blank or select yourself for a personal task</small>
                            </div>
                            <div class="col-md-6">
                                <label class="shf-form-label">Priority</label>
                                <select name="priority" id="taskPriority" class="shf-input">
                                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="normal" {{ old('priority', 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="shf-form-label">Due Date <span class="text-danger">*</span></label>
                                <input type="text" name="due_date" id="taskDueDate" class="shf-input shf-datepicker"
                                    autocomplete="off" placeholder="dd/mm/yyyy" required value="{{ old('due_date') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="shf-form-label">Link to Loan (optional)</label>
                                <input type="text" id="taskLoanSearch" class="shf-input"
                                    placeholder="Search loan #, app # or customer..." autocomplete="off">
                                <input type="hidden" name="loan_detail_id" id="taskLoanId">
                                <div id="taskLoanResults" class="position-relative">
                                    <div id="taskLoanDropdown" class="dropdown-menu w-100 shadow"
                                        style="max-height:200px; overflow-y:auto;"></div>
                                </div>
                                <div id="taskLoanChip" class="d-none mt-2">
                                    <span class="shf-badge shf-badge-blue shf-text-xs" id="taskLoanChipText"></span>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-1"
                                        onclick="clearLoanLink()">&times; Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-accent-outline btn-accent-sm"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-accent btn-accent-sm">Save Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation --}}
    <form id="deleteTaskForm" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>

@endsection

@push('scripts')
    <script src="{{ asset('vendor/datatables/js/dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/js/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(function() {
            // ── Re-open modal on validation errors ──
            @if ($errors->any())
                $('#taskModal').modal('show');
            @endif

            // ── FAB deep-link: ?create=1 opens the task create modal on page load ──
            if (new URLSearchParams(window.location.search).get('create') === '1') {
                $('#taskModal').modal('show');
                history.replaceState(null, '', window.location.pathname);
            }

            // ── Datepicker ──
            $('.shf-datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true
            });

            // ── DataTable Language ──
            var dtLang = {
                processing: '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-secondary"></div></div>',
                emptyTable: ' ',
                zeroRecords: ' ',
                info: 'Showing _START_ to _END_ of _TOTAL_',
                infoEmpty: '',
                infoFiltered: '(filtered from _MAX_)',
                paginate: {
                    previous: '&laquo;',
                    next: '&raquo;'
                }
            };

            var viewIcon =
                '<svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
            var editIcon =
                '<svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>';
            var deleteIcon =
                '<svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';

            // ── DataTable ──
            var table = $('#tasksTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: @json(route('general-tasks.data')),
                    data: function(d) {
                        d.view = $('#filterView').val();
                        d.status = $('#filterStatus').val();
                        d.priority = $('#filterPriority').val();
                    }
                },
                columns: [{
                        data: 'title',
                        render: function(data, type, row) {
                            var html = '<a href="' + row.show_url +
                                '" class="fw-medium text-decoration-none" style="color:var(--primary-dark-solid);">' +
                                data + '</a>';
                            if (row.description) html += '<br><small class="text-muted">' + row
                                .description + '</small>';
                            return html;
                        }
                    },
                    {
                        data: 'assignee_name',
                        render: function(data, type, row) {
                            if (row.is_self_task) return '<span class="text-muted">Self</span>';
                            return data + '<br><small class="text-muted">by ' + row.creator_name +
                                '</small>';
                        }
                    },
                    {
                        data: 'loan_info',
                        render: function(data) {
                            return data || '<span class="text-muted">&mdash;</span>';
                        }
                    },
                    {
                        data: 'priority_html',
                        orderable: true
                    },
                    {
                        data: 'due_date_html',
                        orderable: true
                    },
                    {
                        data: 'status_html',
                        orderable: true
                    },
                    {
                        data: 'completed_at',
                        render: function(data) {
                            return data || '<span class="text-muted">&mdash;</span>';
                        }
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        render: function(data, type, row) {
                            var html = '<div class="d-flex gap-1 justify-content-end">';
                            html += '<a href="' + row.show_url + '" class="btn-accent-sm">' +
                                viewIcon + ' View</a>';
                            if (row.can_edit) {
                                html += '<button class="btn-accent-sm btn-accent-outline shf-edit-task-btn" data-edit=\'' +
                                    JSON.stringify(row.edit_data).replace(/'/g, '&#39;') + '\'>' + editIcon + ' Edit</button>';
                            }
                            if (row.can_delete) {
                                html +=
                                    '<button class="btn-accent-sm shf-btn-danger-alt" onclick="deleteTask(' +
                                    row.id + ')">' + deleteIcon + ' Del</button>';
                            }
                            html += '</div>';
                            return html;
                        }
                    }
                ],
                order: [
                    [7, 'desc']
                ],
                pageLength: 50,
                dom: 'rt<"shf-dt-bottom"ip>',
                language: dtLang,
                createdRow: function(row, data) {
                    if (data.due_urgency === 'overdue') {
                        $(row).css('background-color', 'rgba(220, 53, 69, 0.08)').css('border-left', '3px solid #dc3545');
                    } else if (data.due_urgency === 'due_today') {
                        $(row).css('background-color', 'rgba(255, 193, 7, 0.10)').css('border-left', '3px solid #ffc107');
                    } else if (data.due_urgency === 'due_tomorrow') {
                        $(row).css('background-color', 'rgba(255, 193, 7, 0.05)').css('border-left', '3px solid #ffe082');
                    } else if (data.priority === 'urgent') {
                        $(row).css('border-left', '3px solid #dc3545');
                    }
                },
                drawCallback: function(settings) {
                    var total = settings._iRecordsDisplay;
                    var hasData = total > 0;
                    $('#tasksTableSection').toggle(hasData);
                    $('#tasksTable_wrapper .shf-dt-bottom').toggle(hasData);
                    if (!hasData) {
                        $('#tasksEmptyState').show();
                        $('#tasksMobileCards').html('');
                    } else {
                        $('#tasksEmptyState').hide();
                        // Build mobile cards from current page data
                        var data = this.api().rows({
                            page: 'current'
                        }).data();
                        var html = '';
                        for (var i = 0; i < data.length; i++) {
                            var d = data[i];
                            var cardStyle = '';
                            if (d.due_urgency === 'overdue') cardStyle = 'border-left:3px solid #dc3545;background:rgba(220,53,69,0.08);';
                            else if (d.due_urgency === 'due_today') cardStyle = 'border-left:3px solid #ffc107;background:rgba(255,193,7,0.10);';
                            else if (d.due_urgency === 'due_tomorrow') cardStyle = 'border-left:3px solid #ffe082;background:rgba(255,193,7,0.05);';
                            else if (d.priority === 'urgent') cardStyle = 'border-left:3px solid #dc3545;';
                            html += '<div class="shf-card mb-2 p-3" style="' + cardStyle + '">' +
                                '<div class="d-flex justify-content-between align-items-start mb-1">' +
                                '<a href="' + d.show_url +
                                '" class="fw-medium text-decoration-none" style="color:var(--primary-dark-solid);">' +
                                d.title + '</a>' +
                                d.priority_html + '</div>';
                            if (d.description) html += '<p class="text-muted small mb-1">' + d
                                .description + '</p>';
                            html += '<div class="d-flex flex-wrap gap-2 mt-1 align-items-center">' +
                                d.status_html +
                                (d.is_self_task ? '<small class="text-muted">Self</small>' :
                                    '<small class="text-muted">Assigned: ' + d.assignee_name +
                                    '</small>');
                            if (d.due_date_raw) html += '<small>' + d.due_date_html + '</small>';
                            if (d.completed_at) html += '<small class="text-success">Completed: ' + d.completed_at + '</small>';
                            if (d.loan_info) html += '<span>' + d.loan_info + '</span>';
                            html += '</div>' +
                                '<div class="d-flex gap-1 mt-2">' +
                                '<a href="' + d.show_url + '" class="btn-accent-sm shf-text-xs">' +
                                viewIcon + ' View</a>';
                            if (d.can_edit) {
                                html += '<button class="btn-accent-sm btn-accent-outline shf-text-xs shf-edit-task-btn" data-edit=\'' +
                                    JSON.stringify(d.edit_data).replace(/'/g, '&#39;') + '\'>' + editIcon + ' Edit</button>';
                            }
                            if (d.can_delete) {
                                html +=
                                    '<button class="btn-accent-sm shf-text-xs shf-btn-danger-alt" onclick="deleteTask(' +
                                    d.id + ')">' + deleteIcon + ' Delete</button>';
                            }
                            html += '</div></div>';
                        }
                        $('#tasksMobileCards').html(html);
                    }
                }
            });

            // ── Edit task button (delegated for dynamic content) ──
            $(document).on('click', '.shf-edit-task-btn', function() {
                var editData = $(this).data('edit');
                editTask(editData);
            });

            // ── Filters ──
            $('#btnFilter').on('click', function() {
                table.draw();
                updateFilterCount();
            });
            $('#btnClear').on('click', function() {
                $('#filterView').val('my_tasks_and_assigned');
                $('#filterStatus').val('active');
                $('#filterPriority').val('');
                $('#filterSearch').val('');
                table.search('').draw();
                updateFilterCount();
            });
            $('#filterSearch').on('keyup', function(e) {
                if (e.key === 'Enter') {
                    table.search(this.value).draw();
                }
            });

            function updateFilterCount() {
                var count = 0;
                if ($('#filterView').val() !== 'my_tasks') count++;
                if ($('#filterStatus').val()) count++;
                if ($('#filterPriority').val()) count++;
                var badge = $('#taskFilterCount');
                badge.text(count);
                count > 0 ? badge.removeClass('shf-collapse-hidden') : badge.addClass('shf-collapse-hidden');
            }

            // ── Loan Search Autocomplete ──
            var loanTimer;
            $('#taskLoanSearch').on('input', function() {
                clearTimeout(loanTimer);
                var q = $(this).val().trim();
                if (q.length < 2) {
                    $('#taskLoanDropdown').removeClass('show').empty();
                    return;
                }
                loanTimer = setTimeout(function() {
                    $.get(@json(route('general-tasks.search-loans')), {
                        q: q
                    }, function(loans) {
                        var $dd = $('#taskLoanDropdown');
                        if (!loans.length) {
                            $dd.html(
                                '<span class="dropdown-item text-muted">No loans found</span>'
                            ).addClass('show');
                            return;
                        }
                        var html = '';
                        loans.forEach(function(loan) {
                            var label = '#' + loan.loan_number;
                            if (loan.application_number) label += ' / App: ' + loan
                                .application_number;
                            label += ' — ' + loan.customer_name;
                            if (loan.bank_name) label += ' (' + loan.bank_name +
                                ')';
                            html +=
                                '<a class="dropdown-item shf-loan-pick" href="#" data-id="' +
                                loan.id + '" data-label="' + label.replace(/"/g,
                                    '&quot;') + '">' + label + '</a>';
                        });
                        $dd.html(html).addClass('show');
                    });
                }, 300);
            });

            $(document).on('click', '.shf-loan-pick', function(e) {
                e.preventDefault();
                $('#taskLoanId').val($(this).data('id'));
                $('#taskLoanSearch').val('').hide();
                $('#taskLoanChipText').text($(this).data('label'));
                $('#taskLoanChip').removeClass('d-none');
                $('#taskLoanDropdown').removeClass('show').empty();
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#taskLoanResults, #taskLoanSearch').length) {
                    $('#taskLoanDropdown').removeClass('show');
                }
            });
        });

        // ── Priority → Due Date auto-fill ──
        var priorityDays = {
            low: 10,
            normal: 7,
            high: 4,
            urgent: 2
        };

        function setDueDateFromPriority(priority, $datepicker) {
            var days = priorityDays[priority] || 10;
            var d = new Date();
            d.setDate(d.getDate() + days);
            var dd = ('0' + d.getDate()).slice(-2) + '/' + ('0' + (d.getMonth() + 1)).slice(-2) + '/' + d.getFullYear();
            $datepicker.datepicker('update', dd);
        }
        $('#taskPriority').on('change', function() {
            setDueDateFromPriority($(this).val(), $('#taskDueDate'));
        });

        // ── Date conversion + validation on submit ──
        $('#taskForm').on('submit', function(e) {
            var $form = $(this);
            var valid = true;
            $form.find('.shf-client-error').remove();
            $form.find('.is-invalid').removeClass('is-invalid');

            // Title required
            var $title = $('#taskTitle');
            if (!$.trim($title.val())) {
                $title.addClass('is-invalid').after(
                    '<div class="text-danger small mt-1 shf-client-error">ટાસ્કનું નામ લખો / Please enter task title</div>'
                );
                valid = false;
            }

            // Due date required
            var $dueDate = $('#taskDueDate');
            if (!$.trim($dueDate.val())) {
                $dueDate.addClass('is-invalid').after(
                    '<div class="text-danger small mt-1 shf-client-error">ટાસ્કની છેલ્લી તારીખ પસંદ કરો / Please select a due date</div>'
                );
                valid = false;
            }

            // Priority required
            var $priority = $('#taskPriority');
            if (!$priority.val()) {
                $priority.addClass('is-invalid').after(
                    '<div class="text-danger small mt-1 shf-client-error">ટાસ્કની પ્રાથમિકતા પસંદ કરો / Please select priority</div>'
                );
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
                $form.find('.is-invalid').first().focus();
                return false;
            }

        });
        $(document).on('input change', '#taskForm .is-invalid', function() {
            $(this).removeClass('is-invalid').next('.shf-client-error').remove();
        });

        function resetTaskForm() {
            $('#taskForm').attr('action', @json(route('general-tasks.store')));
            $('#taskFormMethod').val('POST');
            $('#taskModalTitle').text('Create New Task / નવું ટાસ્ક બનાવો');
            $('#taskForm').find('.shf-client-error').remove();
            $('#taskForm').find('.is-invalid').removeClass('is-invalid');
            $('#taskTitle, #taskDescription').val('');
            $('#taskAssignedTo').val({{ auth()->id() }});
            $('#taskPriority').val('normal');
            setDueDateFromPriority('normal', $('#taskDueDate'));
            $('#taskLoanId').val('');
            $('#taskLoanSearch').val('').show();
            $('#taskLoanChip').addClass('d-none');
        }

        function editTask(taskData) {
            $('#taskForm').attr('action', taskData.update_url);
            $('#taskFormMethod').val('PUT');
            $('#taskModalTitle').text('Edit Task / ટાસ્ક સુધારો');
            $('#taskTitle').val(taskData.title);
            $('#taskDescription').val(taskData.description);
            $('#taskAssignedTo').val(taskData.assigned_to || '');
            $('#taskPriority').val(taskData.priority);
            $('#taskDueDate').val(taskData.due_date_formatted || '');
            if (taskData.loan_detail_id) {
                $('#taskLoanId').val(taskData.loan_detail_id);
                $('#taskLoanSearch').hide();
                $('#taskLoanChipText').text(taskData.loan_label);
                $('#taskLoanChip').removeClass('d-none');
            } else {
                clearLoanLink();
            }
            $('#taskModal').modal('show');
        }

        function clearLoanLink() {
            $('#taskLoanId').val('');
            $('#taskLoanSearch').val('').show();
            $('#taskLoanChip').addClass('d-none');
        }

        function deleteTask(id) {
            Swal.fire({
                title: 'Delete Task?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    var form = document.getElementById('deleteTaskForm');
                    form.action = '/general-tasks/' + id;
                    form.submit();
                }
            });
        }
    </script>
@endpush
