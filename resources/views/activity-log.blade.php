@extends('layouts.app')

@section('header')
    <h2 class="font-display fw-semibold text-white" style="font-size: 1.25rem; margin: 0;">
        <svg style="width:16px;height:16px;display:inline;margin-right:6px;color:rgba(255,255,255,0.85);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Activity Log
    </h2>
@endsection

@push('styles')
    <link rel="stylesheet" href="/vendor/datatables/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
    <div class="py-4">
        <div class="px-3 px-sm-4 px-lg-5">

            {{-- Filters --}}
            <div id="logFilterSection" class="shf-section mb-3">
                <div class="shf-section-body">
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="shf-form-label d-block mb-1">&nbsp;</label>
                            <div class="shf-per-page">
                                <span>Show</span>
                                <select id="logPageLength">
                                    <option value="10">10</option>
                                    <option value="25" selected>25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6 col-md-auto">
                            <label class="shf-form-label d-block mb-1">Search</label>
                            <input type="text" id="logSearch" class="shf-input" placeholder="User or action..." style="min-width:10rem;">
                        </div>
                        <div class="col-6 col-md-auto" style="min-width:10rem;">
                            <label class="shf-form-label d-block mb-1">User</label>
                            <select id="filterUser" class="shf-input">
                                <option value="">All Users</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-auto" style="min-width:11rem;">
                            <label class="shf-form-label d-block mb-1">Action</label>
                            <select id="filterAction" class="shf-input">
                                <option value="">All Actions</option>
                                @foreach($actionTypes as $type)
                                    <option value="{{ $type }}">{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-auto">
                            <label class="shf-form-label d-block mb-1">From</label>
                            <input type="text" id="logDateFrom" class="shf-input shf-datepicker" placeholder="dd/mm/yyyy" autocomplete="off">
                        </div>
                        <div class="col-6 col-md-auto">
                            <label class="shf-form-label d-block mb-1">To</label>
                            <input type="text" id="logDateTo" class="shf-input shf-datepicker" placeholder="dd/mm/yyyy" autocomplete="off">
                        </div>
                        <div class="col-12 col-md-auto d-flex gap-2">
                            <label class="shf-form-label d-block mb-1">&nbsp;</label>
                            <button type="button" id="btnLogFilter" class="btn-accent btn-accent-sm">
                                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                Filter
                            </button>
                            <button type="button" id="btnLogClear" class="btn-accent-outline btn-accent-sm"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Clear</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div id="logTableSection" class="shf-section">
                <div class="table-responsive d-none d-md-block">
                    <table id="logTable" class="table table-hover mb-0" style="width:100%">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Subject</th>
                                <th>Details</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div id="logMobileCards" class="d-md-none p-3"></div>
            </div>

            {{-- Empty State --}}
            <div id="logEmptyState" style="display:none;">
                <div class="shf-section">
                    <div class="p-5 text-center">
                        <div class="shf-stat-icon mx-auto mb-3" style="width:64px;height:64px;">
                            <svg style="width:32px;height:32px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="font-display fw-semibold" style="font-size:1.125rem;color:#111827;">No activity recorded</h3>
                        <p class="mt-1 small" style="color:#6b7280;">Activity will appear here as users perform actions.</p>
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
    function convertDate(val) {
        if (!val) return '';
        var parts = val.split('/');
        return parts.length === 3 ? parts[2] + '-' + parts[1] + '-' + parts[0] : val;
    }

    var table = $('#logTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: @json(route('activity-log.data')),
            data: function(d) {
                d.user_id = $('#filterUser').val();
                d.action_type = $('#filterAction').val();
                d.date_from = convertDate($('#logDateFrom').val());
                d.date_to = convertDate($('#logDateTo').val());
            }
        },
        columns: [
            { data: 'date_html' },
            { data: 'user_name', className: 'fw-medium' },
            { data: 'action_html' },
            { data: 'subject', className: 'text-muted' },
            { data: 'details', className: 'text-muted', render: function(data) {
                return '<span style="max-width:20rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;">' + data + '</span>';
            }},
            { data: 'ip_address', className: 'text-muted', render: function(data) {
                return '<span style="font-size:0.75rem;">' + data + '</span>';
            }}
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        dom: 'rt<"shf-dt-bottom"ip>',
        language: {
            info: 'Showing _START_ to _END_ of _TOTAL_',
            infoEmpty: '', infoFiltered: '(filtered from _MAX_)',
            emptyTable: ' ', zeroRecords: ' ',
            paginate: { previous: '&laquo;', next: '&raquo;' }
        },
        drawCallback: function(settings) {
            var total = settings._iRecordsTotal;
            var filtered = settings._iRecordsDisplay;
            var $bottom = $('#logTable_wrapper .shf-dt-bottom');

            if (filtered === 0) {
                $bottom.hide();
                $('#logTableSection').hide();
                $('#logEmptyState').show();
                $('#logMobileCards').html('');
                if (total === 0) $('#logFilterSection').hide();
                return;
            }
            $bottom.show();
            $('#logFilterSection').show();
            $('#logTableSection').show();
            $('#logEmptyState').hide();

            var data = this.api().rows({ page: 'current' }).data();
            var html = '';
            for (var i = 0; i < data.length; i++) {
                var d = data[i];
                html += '<div class="shf-card mb-3 p-3">'
                    + '<div class="d-flex align-items-center justify-content-between mb-2">'
                    + '<span class="shf-badge ' + d.action_badge + '">' + d.action_label + '</span>'
                    + '<span style="color:#9ca3af;font-size:0.7rem;">' + d.date_short + '</span></div>'
                    + '<div class="fw-medium" style="font-size:0.85rem;">' + $('<span>').text(d.user_name).html() + '</div>'
                    + (d.subject !== '—' ? '<div style="color:#6b7280;font-size:0.78rem;">' + d.subject + '</div>' : '')
                    + (d.details !== '—' ? '<div style="color:#6b7280;font-size:0.78rem;margin-top:4px;">' + d.details + '</div>' : '')
                    + (d.ip_address !== '—' ? '<div class="shf-text-xs" style="color:#9ca3af;margin-top:4px;">IP: ' + d.ip_address + '</div>' : '')
                    + '</div>';
            }
            $('#logMobileCards').html(html);
        }
    });

    // Per-page
    $('#logPageLength').on('change', function() { table.page.len(parseInt(this.value)).draw(); });

    // Search
    var searchTimer = null;
    $('#logSearch').on('keyup', function() {
        clearTimeout(searchTimer);
        var val = this.value;
        searchTimer = setTimeout(function() { table.search(val).draw(); }, 300);
    });

    // Filter / Clear
    $('#btnLogFilter').on('click', function() { table.ajax.reload(); });
    $('#btnLogClear').on('click', function() {
        $('#filterUser, #filterAction').val('');
        $('#logDateFrom, #logDateTo').val('').datepicker('update', '');
        $('#logSearch').val('');
        table.search('').ajax.reload();
    });

    // Datepicker
    $('.shf-datepicker').datepicker({
        format: 'dd/mm/yyyy', autoclose: true, todayHighlight: true, clearBtn: true
    });
});
</script>
@endpush
