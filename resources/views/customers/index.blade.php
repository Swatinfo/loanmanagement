@extends('layouts.app')
@section('title', 'Customers — SHF')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="font-display fw-semibold text-white shf-page-title">
            <svg class="shf-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Customers
        </h2>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap5.min.css') }}">
@endpush

@section('content')
    <div class="py-4">
        <div class="px-3 px-sm-4 px-lg-5">

            {{-- Filters --}}
            <div id="customersFilterSection" class="shf-section mb-3">
                <div class="shf-section-header shf-collapsible shf-filter-collapse shf-clickable d-flex align-items-center gap-2"
                    data-target="#customersFilterBody">
                    <svg class="shf-collapse-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="shf-section-title">Filters</span>
                </div>
                <div id="customersFilterBody" class="shf-section-body shf-filter-body-collapse">
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="shf-form-label d-block mb-1">&nbsp;</label>
                            <div class="shf-per-page">
                                <span>Show</span>
                                <select id="customerPageLength">
                                    <option value="10">10</option>
                                    <option value="25" selected>25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6 col-md-auto">
                            <label class="shf-form-label d-block mb-1">Search</label>
                            <input type="text" id="customerSearch" class="shf-input"
                                placeholder="Name, mobile, email or PAN..." style="min-width:12rem;">
                        </div>
                        <div class="col-12 col-md-auto d-flex gap-2">
                            <label class="shf-form-label d-block mb-1">&nbsp;</label>
                            <button type="button" id="btnCustomerClear" class="btn-accent-outline btn-accent-sm">
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
            <div id="customersTableSection" class="shf-section shf-dt-section">
                <div id="customersMobileCards" class="d-md-none p-3"></div>
                <div class="table-responsive">
                    <table id="customersTable" class="table table-hover mb-0" style="width:100%">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Email</th>
                                <th>PAN</th>
                                <th>Loans</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            {{-- Empty State --}}
            <div id="customersEmptyState" class="shf-collapse-hidden">
                <div class="shf-section">
                    <div class="p-5 text-center">
                        <div class="shf-stat-icon mx-auto mb-3 shf-empty-icon-blue">
                            <svg class="shf-icon-xl" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="font-display fw-semibold shf-text-lg shf-text-dark-alt">No customers found</h3>
                        <p class="mt-1 small shf-text-gray">
                            Customers are created automatically when a quotation is converted to a loan.
                        </p>
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
        $(function() {
            var canEdit = @json((bool) $canEdit);

            var table = $('#customersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: { url: @json(route('customers.data')) },
                columns: [
                    { data: 'customer_name' },
                    { data: 'mobile' },
                    { data: 'email' },
                    { data: 'pan_number' },
                    { data: 'loans_count' },
                    { data: 'created_at' },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        render: function(row) {
                            var html = '<a href="' + row.show_url + '" class="btn-accent-outline btn-accent-sm">View</a>';
                            if (canEdit && row.edit_url) {
                                html += ' <a href="' + row.edit_url + '" class="btn-accent btn-accent-sm">Edit</a>';
                            }
                            return html;
                        }
                    }
                ],
                order: [[0, 'asc']],
                pageLength: 25,
                dom: 'rt<"shf-dt-bottom"ip>',
                language: {
                    info: 'Showing _START_ to _END_ of _TOTAL_ customers',
                    infoEmpty: '',
                    infoFiltered: '(filtered from _MAX_ total)',
                    emptyTable: ' ',
                    zeroRecords: ' ',
                    paginate: { previous: '&laquo;', next: '&raquo;' }
                },
                drawCallback: function(settings) {
                    var total = settings._iRecordsTotal;
                    var filtered = settings._iRecordsDisplay;
                    var $bottom = $('#customersTable_wrapper .shf-dt-bottom');

                    if (filtered === 0) {
                        $bottom.hide();
                        $('#customersTableSection').hide();
                        $('#customersEmptyState').show();
                        $('#customersMobileCards').html('');
                        if (total === 0) {
                            $('#customersFilterSection').hide();
                        }
                        return;
                    }
                    $bottom.show();
                    $('#customersFilterSection').show();
                    $('#customersTableSection').show();
                    $('#customersEmptyState').hide();

                    // Mobile cards
                    var data = this.api().rows({ page: 'current' }).data();
                    var html = '';
                    for (var i = 0; i < data.length; i++) {
                        var d = data[i];
                        html += '<div class="shf-card mb-3 p-3">'
                            + '<div class="fw-semibold shf-text-base mb-1">' + d.customer_name + '</div>'
                            + (d.mobile ? '<div class="shf-text-gray shf-text-sm">' + d.mobile + '</div>' : '')
                            + (d.email ? '<div class="shf-text-gray shf-text-sm">' + d.email + '</div>' : '')
                            + (d.pan_number ? '<div class="shf-text-gray shf-text-xs shf-font-mono">' + d.pan_number + '</div>' : '')
                            + '<div class="d-flex align-items-center gap-2 mt-2 flex-wrap">'
                            + '<span class="shf-badge shf-badge-blue">' + d.loans_count + ' loan' + (d.loans_count === 1 ? '' : 's') + '</span>'
                            + '<span class="shf-text-xs shf-text-gray-light">' + d.created_at + '</span>'
                            + '</div>'
                            + '<div class="pt-2 mt-2 shf-border-top-light d-flex gap-2">'
                            + '<a href="' + d.show_url + '" class="btn-accent-outline btn-accent-sm">View</a>'
                            + (canEdit && d.edit_url ? ' <a href="' + d.edit_url + '" class="btn-accent btn-accent-sm">Edit</a>' : '')
                            + '</div>'
                            + '</div>';
                    }
                    $('#customersMobileCards').html(html);
                }
            });

            $('#customerPageLength').on('change', function() { table.page.len(parseInt(this.value)).draw(); });

            var searchTimer = null;
            $('#customerSearch').on('keyup', function() {
                clearTimeout(searchTimer);
                var val = this.value;
                searchTimer = setTimeout(function() { table.search(val).draw(); }, 300);
            });

            $('#btnCustomerClear').on('click', function() {
                $('#customerSearch').val('');
                table.search('').ajax.reload();
            });
        });
    </script>
@endpush
