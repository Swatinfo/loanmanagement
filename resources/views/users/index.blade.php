@extends('layouts.app')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="font-display fw-semibold text-white" style="font-size: 1.25rem; line-height: 1.75rem; margin: 0;">
            <svg style="width:16px;height:16px;display:inline;margin-right:6px;color:rgba(255,255,255,0.85);" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            User Management
        </h2>
        @if (auth()->user()->hasPermission('create_users'))
            <a href="{{ route('users.create') }}" class="btn-accent btn-accent-sm">
                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New User
            </a>
        @endif
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="/vendor/datatables/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
    <div class="py-4">
        <div class="px-3 px-sm-4 px-lg-5">
            {{-- Filters --}}
            <div id="usersFilterSection" class="shf-section mb-3">
                <div class="shf-section-body">
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="shf-form-label d-block mb-1">&nbsp;</label>
                            <div class="shf-per-page">
                                <span>Show</span>
                                <select id="userPageLength">
                                    <option value="10">10</option>
                                    <option value="25" selected>25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6 col-md-auto">
                            <label class="shf-form-label d-block mb-1">Search</label>
                            <input type="text" id="userSearch" class="shf-input" placeholder="Name, email or phone..." style="min-width:10rem;">
                        </div>
                        <div class="col-6 col-md-auto">
                            <label class="shf-form-label d-block mb-1">System Role</label>
                            <select id="filterRole" class="shf-input">
                                <option value="">All Roles</option>
                                <option value="super_admin">Super Admin</option>
                                <option value="admin">Admin</option>
                                <option value="staff">Staff</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-auto">
                            <label class="shf-form-label d-block mb-1">Status</label>
                            <select id="filterStatus" class="shf-input">
                                <option value="">All</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-auto">
                            <label class="shf-form-label d-block mb-1">Task Role</label>
                            <select id="filterTaskRole" class="shf-input">
                                <option value="">All</option>
                                @foreach (\App\Models\User::TASK_ROLE_LABELS as $role => $label)
                                    <option value="{{ $role }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-auto d-flex gap-2">
                            <label class="shf-form-label d-block mb-1">&nbsp;</label>
                            <button type="button" id="btnUserFilter" class="btn-accent btn-accent-sm">
                                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                Filter
                            </button>
                            <button type="button" id="btnUserClear" class="btn-accent-outline btn-accent-sm"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Clear</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Desktop Table --}}
            <div id="usersTableSection" class="shf-section">
                <div class="table-responsive d-none d-md-block">
                    <table id="usersTable" class="table table-hover mb-0" style="width:100%">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Loan Role</th>
                                <th>Branch</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                {{-- Mobile Cards --}}
                <div id="usersMobileCards" class="d-md-none p-3"></div>
            </div>

            {{-- Empty State --}}
            <div id="usersEmptyState" style="display:none;">
                <div class="shf-section">
                    <div class="p-5 text-center">
                        <div class="shf-stat-icon mx-auto mb-3" style="width:64px;height:64px;background:#eff6ff;color:#2563eb;">
                            <svg style="width:32px;height:32px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <h3 class="font-display fw-semibold" style="font-size:1.125rem;color:#111827;">No users found</h3>
                        <p class="mt-1 small" style="color:#6b7280;">Try adjusting your search filters.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="/vendor/datatables/js/dataTables.min.js"></script>
<script src="/vendor/datatables/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function() {
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    var table = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: @json(route('users.data')),
            data: function(d) {
                d.role = $('#filterRole').val();
                d.status = $('#filterStatus').val();
                d.task_role = $('#filterTaskRole').val();
            }
        },
        columns: [
            { data: 'name_html' },
            { data: 'email' },
            { data: 'role_html' },
            { data: 'loan_role_html', orderable: false },
            { data: 'branch_html', orderable: false },
            { data: 'status_html' },
            { data: 'created_html' },
            { data: 'actions_html', orderable: false, searchable: false, className: 'text-end' }
        ],
        order: [[6, 'desc']],
        pageLength: 25,
        dom: 'rt<"shf-dt-bottom"ip>',
        language: {
            info: 'Showing _START_ to _END_ of _TOTAL_ users',
            infoEmpty: '',
            infoFiltered: '(filtered from _MAX_ total)',
            emptyTable: ' ',
            zeroRecords: ' ',
            paginate: { previous: '&laquo;', next: '&raquo;' }
        },
        drawCallback: function(settings) {
            var total = settings._iRecordsTotal;
            var filtered = settings._iRecordsDisplay;
            var $bottom = $('#usersTable_wrapper .shf-dt-bottom');

            if (filtered === 0) {
                $bottom.hide();
                $('#usersTableSection').hide();
                $('#usersEmptyState').show();
                $('#usersMobileCards').html('');
                if (total === 0) {
                    $('#usersFilterSection').hide();
                }
                return;
            }
            $bottom.show();
            $('#usersFilterSection').show();
            $('#usersTableSection').show();
            $('#usersEmptyState').hide();

            // Build mobile cards
            var data = this.api().rows({ page: 'current' }).data();
            var html = '';
            for (var i = 0; i < data.length; i++) {
                var d = data[i];
                var statusBadge = d.is_active ? 'shf-badge-green' : 'shf-badge-red';
                var statusLabel = d.is_active ? 'Active' : 'Inactive';
                html += '<div class="shf-card mb-3 p-3">'
                    + '<div class="d-flex align-items-start justify-content-between mb-2">'
                    + '<div>'
                    + '<div class="fw-semibold" style="font-size:0.9rem;">' + $('<span>').text(d.name).html() + '</div>'
                    + '<div style="color:#6b7280;font-size:0.78rem;">' + $('<span>').text(d.email).html() + '</div>'
                    + (d.phone ? '<div style="color:#6b7280;font-size:0.72rem;">' + $('<span>').text(d.phone).html() + '</div>' : '')
                    + '</div>'
                    + '<span class="shf-badge ' + statusBadge + '">' + statusLabel + '</span>'
                    + '</div>'
                    + '<div class="d-flex align-items-center gap-2 mb-2 flex-wrap">'
                    + d.role_html
                    + (d.task_role_label ? ' <span class="shf-badge shf-badge-blue shf-text-xs">' + $('<span>').text(d.task_role_label).html() + '</span>' : '')
                    + (d.branches ? ' <small class="shf-text-xs" style="color:#6b7280;">' + $('<span>').text(d.branches).html() + '</small>' : '')
                    + ' <span class="shf-text-xs" style="color:#9ca3af;">' + d.created_at + '</span>'
                    + '</div>'
                    + '<div class="pt-2" style="border-top:1px solid #f0f0f0;">' + d.actions_html + '</div>'
                    + '</div>';
            }
            $('#usersMobileCards').html(html);
        }
    });

    // Per-page
    $('#userPageLength').on('change', function() { table.page.len(parseInt(this.value)).draw(); });

    // Search with debounce
    var searchTimer = null;
    $('#userSearch').on('keyup', function() {
        clearTimeout(searchTimer);
        var val = this.value;
        searchTimer = setTimeout(function() { table.search(val).draw(); }, 300);
    });

    // Filter / Clear buttons
    $('#btnUserFilter').on('click', function() { table.ajax.reload(); });
    $('#btnUserClear').on('click', function() {
        $('#filterRole, #filterStatus, #filterTaskRole').val('');
        $('#userSearch').val('');
        table.search('').ajax.reload();
    });

    // Toggle active via AJAX
    $(document).on('click', '.btn-toggle-active', function() {
        var $btn = $(this);
        Swal.fire({
            title: 'Are you sure?',
            text: 'This will change the user\'s active status.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f15a29',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, proceed',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.post($btn.data('url'), { _token: csrfToken }, function() {
                    table.ajax.reload(null, false);
                });
            }
        });
    });

    // Delete via AJAX
    $(document).on('click', '.btn-delete-user', function() {
        var $btn = $(this);
        Swal.fire({
            title: 'Delete this user?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: $btn.data('url'),
                    type: 'DELETE',
                    data: { _token: csrfToken },
                    success: function(r) {
                        Swal.fire({ icon: 'success', title: r.message || 'User deleted', timer: 1500, showConfirmButton: false });
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete user.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
