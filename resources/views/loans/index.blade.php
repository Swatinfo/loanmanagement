@extends('layouts.app')

@section('header')
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
        <h2 class="font-display fw-semibold text-white" style="font-size: 1.25rem; margin: 0;">
            <svg style="width:16px;height:16px;display:inline;margin-right:6px;color:rgba(255,255,255,0.85);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            Loans
        </h2>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="/vendor/datatables/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="py-4">
    <div class="px-3 px-sm-4 px-lg-5">

        {{-- Stats Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="shf-stat-card">
                    <div class="shf-stat-icon">
                        <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <div class="shf-stat-value">{{ number_format($stats['total']) }}</div>
                        <div class="shf-stat-label">Total Loans</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="shf-stat-card" style="border-left: 3px solid #2563eb;">
                    <div class="shf-stat-icon" style="background:#eff6ff;color:#2563eb;">
                        <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <div class="shf-stat-value">{{ number_format($stats['active']) }}</div>
                        <div class="shf-stat-label">Active</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="shf-stat-card" style="border-left: 3px solid #16a34a;">
                    <div class="shf-stat-icon" style="background:#f0fdf4;color:#16a34a;">
                        <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="shf-stat-value">{{ number_format($stats['completed']) }}</div>
                        <div class="shf-stat-label">Completed</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="shf-stat-card" style="border-left: 3px solid #f15a29;">
                    <div class="shf-stat-icon" style="background:#fff7ed;color:#f15a29;">
                        <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <div class="shf-stat-value">{{ number_format($stats['this_month']) }}</div>
                        <div class="shf-stat-label">This Month</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div id="loansFilterSection" class="shf-section mb-3">
            <div class="shf-section-body">
                <div class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="shf-form-label d-block mb-1">&nbsp;</label>
                        <div class="shf-per-page">
                            <span>Show</span>
                            <select id="loanPageLength">
                                <option value="10">10</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6 col-md-auto">
                        <label class="shf-form-label d-block mb-1">Status</label>
                        <select id="filterStatus" class="shf-input">
                            <option value="">All Status</option>
                            @foreach(\App\Models\LoanDetail::STATUS_LABELS as $key => $label)
                                <option value="{{ $key }}">{{ $label['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-auto">
                        <label class="shf-form-label d-block mb-1">Type</label>
                        <select id="filterType" class="shf-input">
                            <option value="">All Types</option>
                            @foreach(\App\Models\LoanDetail::CUSTOMER_TYPE_LABELS as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-auto">
                        <label class="shf-form-label d-block mb-1">Bank</label>
                        <select id="filterBank" class="shf-input">
                            <option value="">All Banks</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-auto">
                        <label class="shf-form-label d-block mb-1">Branch</label>
                        <select id="filterBranch" class="shf-input">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-auto">
                        <label class="shf-form-label d-block mb-1">From</label>
                        <input type="text" id="filterDateFrom" class="shf-input shf-datepicker" placeholder="dd/mm/yyyy" autocomplete="off">
                    </div>
                    <div class="col-6 col-md-auto">
                        <label class="shf-form-label d-block mb-1">To</label>
                        <input type="text" id="filterDateTo" class="shf-input shf-datepicker" placeholder="dd/mm/yyyy" autocomplete="off">
                    </div>
                    <div class="col-12 col-md-auto d-flex gap-2">
                        <label class="shf-form-label d-block mb-1">&nbsp;</label>
                        <button type="button" id="btnLoanFilter" class="btn-accent btn-accent-sm">
                            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Filter
                        </button>
                        <button type="button" id="btnLoanClear" class="btn-accent-outline btn-accent-sm"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Clear</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Desktop Table --}}
        <div id="loansTableSection" class="shf-section">
            <div class="table-responsive d-none d-md-block">
                <table id="loansTable" class="table table-hover mb-0" style="width:100%">
                    <thead>
                        <tr>
                            <th>Loan #</th>
                            <th>Customer</th>
                            <th>Bank / Product</th>
                            <th class="text-end">Amount</th>
                            <th>Stage</th>
                            <th>Owner</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            {{-- Mobile Cards --}}
            <div id="loansMobileCards" class="d-md-none"></div>
        </div>

        {{-- Empty State --}}
        <div id="loansEmptyState" style="display:none;">
            <div class="shf-section">
                <div class="p-5 text-center">
                    <div class="shf-stat-icon mx-auto mb-3" style="width:64px;height:64px;background:#eff6ff;color:#2563eb;">
                        <svg style="width:32px;height:32px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h3 class="font-display fw-semibold" style="font-size:1.125rem;color:#111827;">No loans found</h3>
                    <p class="mt-1 small" style="color:#6b7280;">Try adjusting your filters or convert a quotation to create a loan.</p>
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

    var table = $('#loansTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("loans.data") }}',
            data: function(d) {
                d.status = $('#filterStatus').val();
                d.customer_type = $('#filterType').val();
                d.bank_id = $('#filterBank').val();
                d.branch_id = $('#filterBranch').val();
                d.date_from = convertDate($('#filterDateFrom').val());
                d.date_to = convertDate($('#filterDateTo').val());
            }
        },
        columns: [
            { data: 'loan_number' },
            { data: 'customer_name' },
            { data: 'bank_product' },
            { data: 'amount_info', className: 'text-end' },
            { data: 'current_stage_name' },
            { data: 'owner_info', orderable: false },
            { data: 'status_label' },
            { data: 'created_at' },
            { data: 'actions_html', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        dom: 'rt<"shf-dt-bottom"ip>',
        language: {
            search: '',
            searchPlaceholder: 'Search loans...',
            info: 'Showing _START_ to _END_ of _TOTAL_ loans',
            infoEmpty: '',
            infoFiltered: '(filtered from _MAX_ total)',
            emptyTable: ' ',
            zeroRecords: ' ',
            paginate: { previous: '‹', next: '›' }
        },
        drawCallback: function(settings) {
            var total = settings._iRecordsTotal;
            var filtered = settings._iRecordsDisplay;
            var $bottom = $('#loansTable_wrapper .shf-dt-bottom');

            if (filtered === 0) {
                $bottom.hide();
                $('#loansTableSection').hide();
                $('#loansEmptyState').show();
                $('#loansMobileCards').html('');
                // Keep filters visible if total > 0 (user filtered to zero)
                if (total === 0) {
                    $('#loansFilterSection').hide();
                }
                return;
            }
            $bottom.show();
            $('#loansFilterSection').show();
            $('#loansTableSection').show();
            $('#loansEmptyState').hide();

            // Build mobile cards
            var data = this.api().rows({ page: 'current' }).data();
            var html = '';
            for (var i = 0; i < data.length; i++) {
                var d = data[i];
                var locHtml = d.location_name ? '<small class="text-info" style="font-size:0.7rem;">' + d.location_name + '</small>' : '';
                html += '<div class="shf-card mb-2 p-3">'
                    + '<div class="d-flex justify-content-between align-items-start mb-2">'
                    + '<div><strong>' + d.customer_name + '</strong><br><small class="text-muted">' + d.loan_number + '</small></div>'
                    + '<div>' + d.status_label + '</div></div>'
                    + '<div class="d-flex justify-content-between align-items-center mb-1">'
                    + '<span>' + d.formatted_amount + '</span>'
                    + '<small class="text-muted">' + d.bank_name + '</small></div>'
                    + (locHtml ? '<div class="mb-1">' + locHtml + '</div>' : '')
                    + '<div class="d-flex justify-content-between align-items-center mb-2">'
                    + '<small class="text-muted">Stage: ' + d.current_stage_name + '</small>'
                    + '<small class="text-muted">Owner: ' + d.owner_info + '</small></div>'
                    + '<div>' + d.actions_html + '</div></div>';
            }
            $('#loansMobileCards').html(html || '<p class="text-muted text-center py-4">No matching loans</p>');
        }
    });

    // Custom per-page selector
    $('#loanPageLength').on('change', function() {
        table.page.len(parseInt(this.value)).draw();
    });

    // Filter buttons
    $('#btnLoanFilter').on('click', function() { table.ajax.reload(); });
    $('#btnLoanClear').on('click', function() {
        $('#filterStatus, #filterType, #filterBank, #filterBranch').val('');
        $('#filterDateFrom, #filterDateTo').val('').datepicker('update', '');
        table.ajax.reload();
    });

    // Datepicker
    $('.shf-datepicker').datepicker({ format: 'dd/mm/yyyy', autoclose: true, todayHighlight: true, clearBtn: true });
});
</script>
@endpush
