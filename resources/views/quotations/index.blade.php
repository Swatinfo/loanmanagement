@extends('layouts.app')
@section('title', 'Quotations — SHF')

@section('header')
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
        <h2 class="font-display fw-semibold text-white shf-page-title">
            <svg class="shf-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Quotations
        </h2>
        @if ($permissions['create_quotation'])
            <a href="{{ route('quotations.create') }}" class="btn-accent-outline-white btn-accent-sm">
                <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Quotation
            </a>
        @endif
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap5.min.css') }}">
@endpush

@section('content')
    <div class="py-4">
        <div class="px-3 px-sm-4 px-lg-5">

            {{-- Filters --}}
            <div id="qxFilterSection" class="shf-section mb-3">
                <div class="shf-section-header shf-collapsible shf-filter-collapse shf-clickable d-flex align-items-center gap-2"
                    data-target="#qxFilterBody">
                    <svg class="shf-collapse-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="shf-section-title">Filters</span>
                </div>
                <div id="qxFilterBody" class="shf-section-body shf-filter-body-collapse">
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="shf-form-label d-block mb-1">&nbsp;</label>
                            <div class="shf-per-page">
                                <span>Show</span>
                                <select id="qxPageLength">
                                    <option value="10">10</option>
                                    <option value="25" selected>25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md">
                            <label class="shf-form-label d-block mb-1">Search</label>
                            <input type="text" id="qxSearch" class="shf-input"
                                placeholder="Customer name or filename..." style="min-width:12rem;">
                        </div>
                        <div class="col-6 col-md-auto" style="min-width: 9rem;">
                            <label class="shf-form-label d-block mb-1">Type</label>
                            <select id="qxType" class="shf-input">
                                <option value="">All Types</option>
                                <option value="proprietor">Proprietor</option>
                                <option value="partnership_llp">Partnership/LLP</option>
                                <option value="pvt_ltd">PVT LTD</option>
                                <option value="salaried">Salaried</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-auto" style="min-width: 10rem;">
                            <label class="shf-form-label d-block mb-1">Status</label>
                            <select id="qxStatus" class="shf-input">
                                <option value="not_cancelled">Active + On Hold</option>
                                <option value="active">Active</option>
                                <option value="on_hold">On Hold</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="all">All Statuses</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-auto" style="min-width: 10rem;">
                            <label class="shf-form-label d-block mb-1">Loan Status</label>
                            <select id="qxLoanStatus" class="shf-input">
                                <option value="not_converted">Not Converted</option>
                                <option value="converted">All Converted</option>
                                <option value="active">Loan Active</option>
                                <option value="completed">Loan Completed</option>
                                <option value="rejected">Loan Rejected</option>
                                <option value="all">All Quotations</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-auto" style="min-width: 9rem;">
                            <label class="shf-form-label d-block mb-1">From</label>
                            <input type="text" id="qxDateFrom" class="shf-input shf-datepicker-past"
                                autocomplete="off" placeholder="dd/mm/yyyy">
                        </div>
                        <div class="col-6 col-md-auto" style="min-width: 9rem;">
                            <label class="shf-form-label d-block mb-1">To</label>
                            <input type="text" id="qxDateTo" class="shf-input shf-datepicker-past"
                                autocomplete="off" placeholder="dd/mm/yyyy">
                        </div>
                        @if ($permissions['view_all'] && count($users) > 0)
                            <div class="col-6 col-md-auto" style="min-width: 10rem;">
                                <label class="shf-form-label d-block mb-1">Created By</label>
                                <select id="qxCreatedBy" class="shf-input">
                                    <option value="">All Users</option>
                                    @foreach ($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-12 col-md-auto d-flex gap-2">
                            <label class="shf-form-label d-block mb-1">&nbsp;</label>
                            <button type="button" id="qxFilter" class="btn-accent btn-accent-sm">
                                <svg class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Filter
                            </button>
                            <button type="button" id="qxClear" class="btn-accent-outline btn-accent-sm">
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

            {{-- Table --}}
            <div id="qxTableSection" class="shf-section shf-dt-section">
                <div id="qxMobileCards" class="d-md-none p-3"></div>
                <div class="table-responsive d-none d-md-block">
                    <table id="qxTable" class="table table-hover mb-0" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Type</th>
                                <th>Loan Amount</th>
                                <th>Status</th>
                                @if ($permissions['view_all'])
                                    <th>Created By</th>
                                @endif
                                <th>Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            {{-- Empty state --}}
            <div id="qxEmptyState" class="shf-collapse-hidden">
                <div class="shf-section">
                    <div class="p-5 text-center">
                        <div class="shf-stat-icon mx-auto mb-3 shf-empty-icon-accent">
                            <svg class="shf-icon-xl" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="font-display fw-semibold shf-text-lg shf-text-dark-alt">No quotations found</h3>
                        <p class="mt-1 small shf-text-gray">Try adjusting your filters or create a new quotation.</p>
                        @if ($permissions['create_quotation'])
                            <div class="mt-4">
                                <a href="{{ route('quotations.create') }}" class="btn-accent">+ New Quotation</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/datatables/js/dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/js/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(function () {
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            var canViewAll = @json($permissions['view_all']);
            var canDownloadBranded = @json($permissions['download_pdf_branded']);
            var canDownloadPlain = @json($permissions['download_pdf_plain']);
            var canDelete = @json($permissions['delete_quotations']);

            var table = $('#qxTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: @json(route('dashboard.quotation-data')),
                    data: function (d) {
                        d.search = { value: $('#qxSearch').val() };
                        d.customer_type = $('#qxType').val();
                        d.status = $('#qxStatus').val();
                        d.loan_status = $('#qxLoanStatus').val();
                        d.date_from = $('#qxDateFrom').val();
                        d.date_to = $('#qxDateTo').val();
                        d.created_by = $('#qxCreatedBy').val();
                    }
                },
                columns: [
                    { data: 'id', className: 'shf-text-xs text-muted' },
                    {
                        data: null,
                        render: function (row) {
                            return '<div class="fw-semibold">' + $('<div>').text(row.customer_name).html() + '</div>'
                                + (row.location_name ? '<small class="text-muted">' + $('<span>').text(row.location_name).html() + '</small>' : '');
                        }
                    },
                    {
                        data: null,
                        render: function (row) {
                            return '<span class="shf-badge ' + row.type_badge_class + ' shf-text-2xs">' + $('<span>').text(row.type_label).html() + '</span>';
                        }
                    },
                    { data: 'formatted_amount', className: 'shf-font-mono' },
                    {
                        data: null,
                        orderable: false,
                        render: function (row) {
                            var html = row.status_html;
                            if (row.is_converted) {
                                html += ' <span class="shf-badge shf-badge-blue shf-text-2xs">Converted</span>';
                            }
                            return html;
                        }
                    },
                    @if ($permissions['view_all'])
                        { data: 'created_by' },
                    @endif
                    { data: 'date' },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        render: function (row) {
                            var html = '<div class="d-flex gap-1 justify-content-end flex-wrap">';
                            html += '<a href="' + row.show_url + '" class="btn-accent-outline btn-accent-sm">View</a>';
                            if (canDownloadBranded && row.download_branded_url) {
                                html += ' <a href="' + row.download_branded_url + '" class="btn-accent-sm" style="background:linear-gradient(135deg,#2563eb,#3b82f6);color:#fff;">PDF</a>';
                            }
                            if (row.convert_url) {
                                html += ' <a href="' + row.convert_url + '" class="shf-btn-success btn-accent-sm">Convert</a>';
                            }
                            html += '</div>';
                            return html;
                        }
                    }
                ],
                order: [[@if ($permissions['view_all']) 6 @else 5 @endif, 'desc']],
                pageLength: 25,
                dom: 'rt<"shf-dt-bottom"ip>',
                language: {
                    info: 'Showing _START_ to _END_ of _TOTAL_ quotations',
                    infoEmpty: '',
                    infoFiltered: '(filtered from _MAX_ total)',
                    emptyTable: ' ',
                    zeroRecords: ' ',
                    paginate: { previous: '&laquo;', next: '&raquo;' }
                },
                drawCallback: function (settings) {
                    var total = settings._iRecordsTotal;
                    var filtered = settings._iRecordsDisplay;
                    var $bottom = $('#qxTable_wrapper .shf-dt-bottom');

                    if (filtered === 0) {
                        $bottom.hide();
                        $('#qxTableSection').hide();
                        $('#qxEmptyState').show();
                        $('#qxMobileCards').html('');
                        if (total === 0) {
                            $('#qxFilterSection').hide();
                        }
                        return;
                    }
                    $bottom.show();
                    $('#qxFilterSection').show();
                    $('#qxTableSection').show();
                    $('#qxEmptyState').hide();

                    // Mobile cards
                    var data = this.api().rows({ page: 'current' }).data();
                    var html = '';
                    for (var i = 0; i < data.length; i++) {
                        var d = data[i];
                        var status = d.status_html + (d.is_converted ? ' <span class="shf-badge shf-badge-blue shf-text-2xs">Converted</span>' : '');
                        var actions = '<a href="' + d.show_url + '" class="btn-accent-outline btn-accent-sm">View</a>';
                        if (canDownloadBranded && d.download_branded_url) {
                            actions += ' <a href="' + d.download_branded_url + '" class="btn-accent-sm" style="background:linear-gradient(135deg,#2563eb,#3b82f6);color:#fff;">PDF</a>';
                        }
                        if (d.convert_url) {
                            actions += ' <a href="' + d.convert_url + '" class="shf-btn-success btn-accent-sm">Convert</a>';
                        }
                        html += '<div class="shf-card mb-3 p-3">'
                            + '<div class="d-flex justify-content-between align-items-start mb-2 gap-2">'
                            + '<div>'
                            + '<div class="fw-semibold shf-text-base">' + $('<span>').text(d.customer_name).html() + '</div>'
                            + '<div class="shf-text-gray shf-text-xs">' + $('<span>').text(d.type_label).html() + (d.location_name ? ' • ' + $('<span>').text(d.location_name).html() : '') + '</div>'
                            + '</div>'
                            + '<span class="shf-font-mono shf-text-sm">' + d.formatted_amount + '</span>'
                            + '</div>'
                            + '<div class="d-flex align-items-center gap-2 flex-wrap mb-2">' + status + '<span class="shf-text-xs shf-text-gray-light ms-auto">' + d.date + '</span></div>'
                            + '<div class="pt-2 shf-border-top-light d-flex flex-wrap gap-1">' + actions + '</div>'
                            + '</div>';
                    }
                    $('#qxMobileCards').html(html);
                }
            });

            $('#qxPageLength').on('change', function () { table.page.len(parseInt(this.value)).draw(); });

            var searchTimer = null;
            $('#qxSearch').on('keyup', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function () { table.ajax.reload(); }, 300);
            });

            $('#qxFilter').on('click', function () { table.ajax.reload(); });
            $('#qxClear').on('click', function () {
                $('#qxType, #qxCreatedBy, #qxDateFrom, #qxDateTo').val('');
                $('#qxStatus').val('not_cancelled');
                $('#qxLoanStatus').val('not_converted');
                $('#qxSearch').val('');
                table.ajax.reload();
            });

            $('#qxType, #qxStatus, #qxLoanStatus, #qxCreatedBy').on('change', function () { table.ajax.reload(); });
        });
    </script>
@endpush
