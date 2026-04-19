@extends('layouts.app')
@section('title', 'Dashboard — SHF')

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap5.min.css') }}">
@endpush

@section('header')
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2">
        <h2 class="font-display fw-semibold text-white shf-page-title">
            <svg class="shf-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Dashboard
        </h2>
        {{-- Primary create CTAs (New Quotation / Task / Visit) live in the mobile
             FAB (partials/mobile-fab.blade.php) and on their respective listing
             pages. They're not surfaced on the dashboard header any more. --}}
    </div>
@endsection

@section('content')
    <div class="py-4">
        <div class="px-3 px-sm-4 px-lg-5">

            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                @php $statCol = ($canViewQuotations && $loanStats) ? '2' : '4'; @endphp

                {{-- Quotation Stats --}}
                @if ($canViewQuotations)
                    <div class="col-6 col-md-4 col-xl-{{ $statCol }}">
                        <div class="shf-stat-card">
                            <div class="shf-stat-icon">
                                <svg class="shf-icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <div class="shf-stat-value">{{ number_format($stats['total']) }}</div>
                                <div class="shf-stat-label">Quotations</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-{{ $statCol }}">
                        <div class="shf-stat-card">
                            <div class="shf-stat-icon">
                                <svg class="shf-icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <div class="shf-stat-value">{{ number_format($stats['today']) }}</div>
                                <div class="shf-stat-label">Today</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-{{ $statCol }}">
                        <div class="shf-stat-card">
                            <div class="shf-stat-icon">
                                <svg class="shf-icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <div class="shf-stat-value">{{ number_format($stats['this_month']) }}</div>
                                <div class="shf-stat-label">This Month</div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Loan Stats --}}
                @if ($loanStats)
                    <div class="col-6 col-md-4 col-xl-{{ $statCol }}">
                        <div class="shf-stat-card shf-stat-card-blue">
                            <div class="shf-stat-icon shf-stat-icon-blue">
                                <svg class="shf-icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <div class="shf-stat-value">{{ number_format($loanStats['active']) }}</div>
                                <div class="shf-stat-label">Active Loans</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-{{ $statCol }}">
                        <div class="shf-stat-card shf-stat-card-accent">
                            <div class="shf-stat-icon shf-stat-icon-accent">
                                <svg class="shf-icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                            <div>
                                <div class="shf-stat-value">{{ number_format($loanStats['my_tasks']) }}</div>
                                <div class="shf-stat-label">My Tasks</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-{{ $statCol }}">
                        <div class="shf-stat-card shf-stat-card-green">
                            <div class="shf-stat-icon shf-stat-icon-green">
                                <svg class="shf-icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <div class="shf-stat-value">{{ number_format($loanStats['completed_month']) }}</div>
                                <div class="shf-stat-label">Completed</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Dashboard Tabs --}}
            <div class="shf-tabs">
                <button class="shf-tab{{ $defaultTab === 'personal-tasks' ? ' active' : '' }}"
                    data-tab="dash-personal-tasks">
                    Personal Tasks
                    @if ($personalTaskStats['pending'] > 0)
                        <span
                            class="shf-badge shf-badge-orange shf-badge-username ms-1 shf-text-2xs">{{ $personalTaskStats['pending'] }}</span>
                    @endif
                </button>
                @if ($loanStats)
                    <button class="shf-tab{{ $defaultTab === 'tasks' ? ' active' : '' }}" data-tab="dash-tasks">
                        My Tasks
                        @if ($loanStats['my_tasks'] > 0)
                            <span
                                class="shf-badge shf-badge-orange shf-badge-username ms-1 shf-text-2xs">{{ $loanStats['my_tasks'] }}</span>
                        @endif
                    </button>
                    <button class="shf-tab{{ $defaultTab === 'loans' ? ' active' : '' }}"
                        data-tab="dash-loans">Loans</button>
                @endif
                @if ($canViewDvr)
                    <button class="shf-tab{{ $defaultTab === 'dvr' ? ' active' : '' }}" data-tab="dash-dvr">
                        DVR
                        @if ($dvrStats && ($dvrStats['overdue_follow_ups'] + $dvrStats['pending_follow_ups']) > 0)
                            <span class="shf-badge {{ $dvrStats['overdue_follow_ups'] > 0 ? 'shf-badge-red' : 'shf-badge-orange' }} shf-badge-username ms-1 shf-text-2xs">
                                {{ $dvrStats['overdue_follow_ups'] + $dvrStats['pending_follow_ups'] }}
                            </span>
                        @endif
                    </button>
                @endif
                @if ($canViewQuotations)
                    <button class="shf-tab{{ $defaultTab === 'quotations' ? ' active' : '' }}"
                        data-tab="dash-quotations">Quotations</button>
                @endif
            </div>

            {{-- My Tasks Tab --}}
            @if ($loanStats)
                @php
                    $authUser = auth()->user();
                    $isBankEmp = $authUser->hasRole('bank_employee');
                    $isAdminOrMgr = $authUser->hasAnyRole(['super_admin', 'admin', 'branch_manager', 'bdh']);
                    // Bank employees participate in these stages (via default_role or phase actions)
                    $bankEmpStageKeys = ['bsm_osv', 'rate_pf', 'sanction', 'legal_verification', 'esign'];
                    $taskStages = \App\Models\Stage::where('is_enabled', true)
                        ->when($isBankEmp, fn($q) => $q->whereIn('stage_key', $bankEmpStageKeys))
                        ->when(!$isBankEmp, fn($q) => $q->whereNull('parent_stage_key'))
                        ->orderBy('sequence_order')
                        ->get();
                @endphp
                <div class="settings-tab-pane shf-collapse-hidden" id="tab-dash-tasks"{!! $defaultTab !== 'tasks' ? '' : '' !!}>
                    <div class="shf-section shf-section-no-top-radius">
                        <div id="tasksFilterToggle"
                            class="shf-section-header shf-collapsible shf-filter-collapse shf-clickable d-flex align-items-center gap-2 border-bottom"
                            data-target="#tasksFilterBar">
                            <svg class="shf-collapse-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <span class="shf-section-title shf-text-xs">Filters</span>
                            <span id="tasksFilterCount" class="shf-filter-count shf-collapse-hidden">0</span>
                        </div>
                        <div id="tasksFilterBar" class="shf-section-body border-bottom shf-filter-body-collapse">
                            <div class="row g-2 align-items-end">
                                <div class="col-6 col-md-auto">
                                    <label class="shf-form-label d-block mb-1 shf-text-xs">Stage</label>
                                    <select id="dashTaskStage" class="shf-input shf-input-sm">
                                        <option value="">All Stages</option>
                                        @foreach ($taskStages as $st)
                                            <option value="{{ $st->stage_key }}">{{ $st->stage_name_en }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-md-auto">
                                    <label class="shf-form-label d-block mb-1 shf-text-xs">Status</label>
                                    <select id="dashTaskStatus" class="shf-input shf-input-sm">
                                        <option value="">All</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-auto">
                                    <input type="text" id="taskSearch" placeholder="Search..."
                                        class="shf-input shf-input-sm" style="max-width:180px;">
                                </div>
                                <div class="col-6 col-md-auto d-flex gap-1">
                                    <button id="dashTaskFilter"
                                        class="btn-accent btn-accent-sm shf-text-xs">Filter</button>
                                    <button id="dashTaskClear"
                                        class="btn-accent-outline btn-accent-sm shf-text-xs">Clear</button>
                                </div>
                            </div>
                        </div>
                        <div id="tasksMobileCards" class="d-md-none p-3"></div>
                        <div id="tasksDesktop" class="shf-dt-section">
                            <div class="table-responsive">
                                <table id="tasksTable" class="table table-hover w-100 mb-0">
                                    <thead>
                                        <tr>
                                            <th>Loan #</th>
                                            <th>Customer</th>
                                            <th>Bank</th>
                                            <th class="text-end">Amount</th>
                                            <th>Stage</th>
                                            <th>Status</th>
                                            <th>Assigned</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div id="tasksEmptyState" class="shf-collapse-hidden"></div>
                    </div>
                </div>
            @endif

            {{-- Personal Tasks Tab --}}
            <div class="settings-tab-pane shf-collapse-hidden" id="tab-dash-personal-tasks">
                <div class="shf-section shf-section-no-top-radius">
                    <div class="shf-section-header d-flex align-items-center justify-content-between">
                        <span class="shf-section-title">Personal Tasks</span>
                        <a href="{{ route('general-tasks.index') }}"
                            class="btn-accent-outline btn-accent-outline-white btn-accent-sm shf-text-xs">
                            <svg class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            View All
                        </a>
                    </div>
                    <div id="personalTasksMobileCards" class="d-md-none p-3"></div>
                    <div id="personalTasksDesktop" class="shf-dt-section">
                        <div class="table-responsive">
                            <table id="personalTasksTable" class="table table-hover w-100 mb-0">
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
                    <div id="personalTasksEmptyState" class="shf-collapse-hidden"></div>
                </div>
            </div>

            {{-- DVR Tab --}}
            @if ($canViewDvr)
                <div class="settings-tab-pane shf-collapse-hidden" id="tab-dash-dvr">
                    <div class="shf-section shf-section-no-top-radius">
                        <div class="shf-section-header d-flex align-items-center justify-content-between">
                            <span class="shf-section-title">Daily Visit Reports</span>
                            <a href="{{ route('dvr.index') }}"
                                class="btn-accent-outline btn-accent-outline-white btn-accent-sm shf-text-xs">
                                <svg class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                                View All
                            </a>
                        </div>
                        {{-- Filter --}}
                        <div class="shf-section-body border-bottom py-2 px-3">
                            <div class="row g-2 align-items-end">
                                <div class="col-6 col-md-auto">
                                    <label class="shf-form-label d-block mb-1 shf-text-xs">Show</label>
                                    <select id="dashDvrFilter" class="shf-input shf-input-sm">
                                        <option value="pending">Pending + Today</option>
                                        <option value="overdue">Overdue Only</option>
                                        <option value="today">Today's Visits</option>
                                        <option value="active">Active (excl. completed)</option>
                                        <option value="all">All (incl. completed)</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-auto">
                                    <label class="shf-form-label d-block mb-1 shf-text-xs">&nbsp;</label>
                                    <input type="text" id="dashDvrSearch" class="shf-input shf-input-sm" placeholder="Search...">
                                </div>
                                <div class="col-12 col-md-auto d-flex gap-2">
                                    <button type="button" id="dashDvrFilterBtn" class="btn-accent btn-accent-sm shf-text-xs">
                                        <svg class="shf-icon-xs" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg> Filter
                                    </button>
                                    <button type="button" id="dashDvrClearBtn" class="btn-accent-outline btn-accent-sm shf-text-xs">
                                        <svg class="shf-icon-xs" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg> Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="dashDvrMobileCards" class="d-md-none p-3"></div>
                        <div id="dashDvrDesktop" class="shf-dt-section">
                            <div class="table-responsive">
                                <table id="dashDvrTable" class="table table-hover w-100 mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Contact</th>
                                            <th>Type</th>
                                            <th>Follow-up</th>
                                            <th>Created</th>
                                            <th style="width:100px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div id="dashDvrEmptyState" class="shf-collapse-hidden"></div>
                    </div>
                </div>
            @endif

            {{-- Quotations Tab --}}
            @if ($canViewQuotations)
                <div class="settings-tab-pane shf-collapse-hidden" id="tab-dash-quotations"{!! $defaultTab !== 'quotations' ? '' : '' !!}>
                    <div class="shf-section shf-section-no-top-radius">
                        <div class="shf-section-header">
                            <div class="shf-section-number">
                                <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="shf-section-title">Quotation History</span>
                        </div>

                        <!-- Filters -->
                        <div id="quotFilterToggle"
                            class="shf-section-header shf-collapsible shf-filter-collapse shf-clickable d-flex align-items-center gap-2 border-bottom"
                            data-target="#quotFilterBody" style="padding-top:0;">
                            <svg class="shf-collapse-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <span class="shf-section-title shf-text-xs">Filters</span>
                            <span id="quotFilterCount" class="shf-filter-count shf-collapse-hidden">0</span>
                        </div>
                        <div id="quotFilterBody" class="shf-section-body shf-filter-body-collapse"
                            style="border-bottom: 1px solid #f0f0f0;">
                            <div class="row g-3 align-items-end">
                                <div class="col-auto">
                                    <label class="shf-form-label d-block mb-1">&nbsp;</label>
                                    <div class="shf-per-page">
                                        <span>Show</span>
                                        <select id="dt-page-length">
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50" selected>50</option>
                                            <option value="100">100</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md">
                                    <label class="shf-form-label d-block mb-1">Search</label>
                                    <input type="text" id="filter-search" placeholder="Customer name or filename..."
                                        class="shf-input">
                                </div>

                                <div class="col-6 col-md-auto" style="min-width: 10rem;">
                                    <label class="shf-form-label d-block mb-1">Type</label>
                                    <select id="filter-type" class="shf-input">
                                        <option value="">All Types</option>
                                        <option value="proprietor">Proprietor</option>
                                        <option value="partnership_llp">Partnership/LLP</option>
                                        <option value="pvt_ltd">PVT LTD</option>
                                        <option value="salaried">Salaried</option>
                                    </select>
                                </div>

                                <div class="col-6 col-md-auto" style="min-width: 10rem;">
                                    <label class="shf-form-label d-block mb-1">Status</label>
                                    <select id="filter-status" class="shf-input">
                                        <option value="not_cancelled">Active + On Hold</option>
                                        <option value="active">Active</option>
                                        <option value="on_hold">On Hold</option>
                                        <option value="cancelled">Cancelled</option>
                                        <option value="all">All Statuses</option>
                                    </select>
                                </div>

                                <div class="col-6 col-md-auto" style="min-width: 10rem;">
                                    <label class="shf-form-label d-block mb-1">Loan Status</label>
                                    <select id="filter-loan-status" class="shf-input">
                                        <option value="not_converted">Not Converted</option>
                                        <option value="active">Loan Active</option>
                                        <option value="converted">All Converted</option>
                                        <option value="completed">Loan Completed</option>
                                        <option value="rejected">Loan Rejected</option>
                                        <option value="all">All Quotations</option>
                                    </select>
                                </div>

                                <div class="col-6 col-md-auto" style="min-width: 9rem;">
                                    <label class="shf-form-label d-block mb-1">From</label>
                                    <input type="text" id="filter-date-from" class="shf-input shf-datepicker"
                                        placeholder="dd/mm/yyyy" autocomplete="off">
                                </div>

                                <div class="col-6 col-md-auto" style="min-width: 9rem;">
                                    <label class="shf-form-label d-block mb-1">To</label>
                                    <input type="text" id="filter-date-to" class="shf-input shf-datepicker"
                                        placeholder="dd/mm/yyyy" autocomplete="off">
                                </div>

                                @if ($permissions['view_all'] && count($users) > 0)
                                    <div class="col-6 col-md-auto" style="min-width: 10rem;">
                                        <label class="shf-form-label d-block mb-1">Created By</label>
                                        <select id="filter-created-by" class="shf-input">
                                            <option value="">All Users</option>
                                            @foreach ($users as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div class="col-12 col-md-auto d-flex gap-2">
                                    <button type="button" id="btn-filter" class="btn-accent btn-accent-sm">
                                        <svg class="shf-icon-md" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        Filter
                                    </button>
                                    <button type="button" id="btn-clear" class="btn-accent-outline btn-accent-sm">
                                        <svg class="shf-icon-md" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Clear
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Desktop DataTable (hidden on mobile) -->
                        <div class="d-none d-md-block">
                            <div class="table-responsive">
                                <table id="quotations-table" class="table table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Customer</th>
                                            <th>Type</th>
                                            <th>Loan Amount</th>
                                            <th>Banks</th>
                                            <th class="no-sort">Status</th>
                                            @if ($permissions['view_all'])
                                                <th>Created By</th>
                                            @endif
                                            <th>Date</th>
                                            <th class="text-end no-sort">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Mobile card layout (visible on mobile only) -->
                        <div class="d-md-none">
                            <div id="mobile-cards-container" class="p-3"></div>
                            <div id="mobile-load-more" class="text-center pb-3 shf-collapse-hidden">
                                <button type="button" id="btn-load-more" class="btn-accent-outline btn-accent-sm">
                                    Load More
                                </button>
                            </div>
                        </div>

                        <!-- Empty state (shown by JS when no records) -->
                        <div id="empty-state" class="p-5 text-center shf-collapse-hidden">
                            <div class="shf-stat-icon mx-auto mb-3" style="width: 64px; height: 64px;">
                                <svg class="shf-icon-xl" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="font-display fw-semibold shf-text-lg shf-text-dark-alt">No
                                quotations found</h3>
                            <p id="empty-state-text" class="mt-1 small shf-text-gray">
                                Get started by creating your first quotation.
                            </p>
                            @if (auth()->user()->hasPermission('create_quotation'))
                                <div id="empty-state-cta" class="mt-4">
                                    <a href="{{ route('quotations.create') }}" class="btn-accent">
                                        <svg class="shf-icon-md" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        New Quotation
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>{{-- /tab-dash-quotations --}}
            @endif

            {{-- Loans Tab --}}
            @if ($loanStats)
                <div class="settings-tab-pane shf-collapse-hidden" id="tab-dash-loans"{!! $defaultTab !== 'loans' ? '' : '' !!}>
                    <div class="shf-section shf-section-no-top-radius">
                        <div id="loansFilterToggle"
                            class="shf-section-header shf-collapsible shf-filter-collapse shf-clickable d-flex align-items-center gap-2 border-bottom"
                            data-target="#loansFilterBar">
                            <svg class="shf-collapse-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <span class="shf-section-title shf-text-xs">Filters</span>
                            <span id="dashLoansFilterCount" class="shf-filter-count shf-collapse-hidden">0</span>
                        </div>
                        <div id="loansFilterBar" class="shf-section-body border-bottom shf-filter-body-collapse">
                            <div class="row g-2 align-items-end">
                                <div class="col-6 col-md-auto">
                                    <label class="shf-form-label d-block mb-1 shf-text-xs">Status</label>
                                    <select id="dashLoanStatus" class="shf-input shf-input-sm">
                                        <option value="">All Status</option>
                                        @foreach (\App\Models\LoanDetail::STATUS_LABELS as $key => $label)
                                            <option value="{{ $key }}"
                                                {{ $key === 'active' ? 'selected' : '' }}>{{ $label['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-md-auto">
                                    <label class="shf-form-label d-block mb-1 shf-text-xs">Type</label>
                                    <select id="dashLoanType" class="shf-input shf-input-sm">
                                        <option value="">All Types</option>
                                        @foreach (\App\Models\LoanDetail::CUSTOMER_TYPE_LABELS as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if (!$isBankEmp)
                                    <div class="col-6 col-md-auto">
                                        <label class="shf-form-label d-block mb-1 shf-text-xs">Bank</label>
                                        <select id="dashLoanBank" class="shf-input shf-input-sm">
                                            <option value="">All Banks</option>
                                            @foreach (\App\Models\Bank::active()->orderBy('name')->get() as $b)
                                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                @if ($isAdminOrMgr)
                                    <div class="col-6 col-md-auto">
                                        <label class="shf-form-label d-block mb-1 shf-text-xs">Branch</label>
                                        <select id="dashLoanBranch" class="shf-input shf-input-sm">
                                            <option value="">All Branches</option>
                                            @foreach (\App\Models\Branch::active()->orderBy('name')->get() as $br)
                                                <option value="{{ $br->id }}">{{ $br->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div class="col-6 col-md-auto">
                                    <label class="shf-form-label d-block mb-1 shf-text-xs">Stage</label>
                                    <select id="dashLoanStage" class="shf-input shf-input-sm">
                                        <option value="">All Stages</option>
                                        @foreach ($taskStages as $st)
                                            <option value="{{ $st->stage_key }}">{{ $st->stage_name_en }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if ($isAdminOrMgr)
                                    <div class="col-6 col-md-auto">
                                        <label class="shf-form-label d-block mb-1 shf-text-xs">Owner Role</label>
                                        <select id="dashLoanRole" class="shf-input shf-input-sm">
                                            <option value="">All Roles</option>
                                            @foreach (\App\Models\Role::orderBy('id')->get() as $r)
                                                <option value="{{ $r->slug }}">{{ $r->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div class="col-6 col-md-auto">
                                    <label class="shf-form-label d-block mb-1 shf-text-xs">From</label>
                                    <input type="text" id="dashLoanDateFrom"
                                        class="shf-input shf-input-sm shf-datepicker" placeholder="dd/mm/yyyy"
                                        autocomplete="off">
                                </div>
                                <div class="col-6 col-md-auto">
                                    <label class="shf-form-label d-block mb-1 shf-text-xs">To</label>
                                    <input type="text" id="dashLoanDateTo"
                                        class="shf-input shf-input-sm shf-datepicker" placeholder="dd/mm/yyyy"
                                        autocomplete="off">
                                </div>
                                <div class="col-6 col-md-auto">
                                    <input type="text" id="loanDashSearch" placeholder="Search..."
                                        class="shf-input shf-input-sm" style="max-width:180px;">
                                </div>
                                <div class="col-6 col-md-auto d-flex gap-1">
                                    <button id="dashLoanFilter"
                                        class="btn-accent btn-accent-sm shf-text-xs">Filter</button>
                                    <button id="dashLoanClear"
                                        class="btn-accent-outline btn-accent-sm shf-text-xs">Clear</button>
                                </div>
                            </div>
                        </div>
                        <div id="loansMobileCardsDash" class="d-md-none p-3"></div>
                        <div id="loansDashDesktop" class="shf-dt-section">
                            <div class="table-responsive">
                                <table id="loansTableDash" class="table table-hover w-100 mb-0">
                                    <thead>
                                        <tr>
                                            <th>Loan #</th>
                                            <th>Customer</th>
                                            <th>Bank</th>
                                            <th class="text-end">Amount</th>
                                            <th>Stage</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div id="loansDashFooter" class="text-center py-3 shf-border-top-light">
                            <a href="{{ route('loans.index') }}" class="btn-accent-outline btn-accent-sm"><svg
                                    class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg> View All Loans</a>
                        </div>
                        <div id="loansDashEmptyState" class="shf-collapse-hidden"></div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content" style="border-radius: var(--radius); border: 1px solid var(--border);">
                <div class="modal-body text-center p-4">
                    <div class="shf-stat-icon mx-auto mb-3"
                        style="width: 48px; height: 48px; background: #fef2f2; color: #dc2626;">
                        <svg style="width:24px;height:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <h5 class="font-display fw-semibold mb-2" style="font-size: 1rem;">Delete Quotation?</h5>
                    <p class="small mb-0 shf-text-gray">This action cannot be undone.</p>
                </div>
                <div class="modal-footer justify-content-center gap-2 border-0 pt-0 pb-4">
                    <button type="button" class="btn-accent-outline btn-accent-sm" data-bs-dismiss="modal"><svg
                            class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg> Cancel</button>
                    <button type="button" id="btn-confirm-delete"
                        class="btn-accent btn-accent-sm shf-btn-danger-alt"><svg class="shf-icon-md" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg> Delete</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Task Modal (dashboard) --}}
    <div class="modal fade" id="dashCreateTaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border: none; border-radius: 12px;">
                <form id="dashTaskForm" method="POST" action="{{ route('general-tasks.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="dashTaskFormMethod" value="POST">
                    <div class="modal-header"
                        style="background: var(--primary-dark-solid); color: #fff; border-radius: 12px 12px 0 0;">
                        <h5 class="modal-title font-display" id="dashTaskModalTitle">Create New Task / નવું ટાસ્ક બનાવો
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="shf-form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="dashTaskTitle" class="shf-input" required
                                maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label class="shf-form-label">Description</label>
                            <textarea name="description" class="shf-input" rows="3" maxlength="5000"></textarea>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="shf-form-label">Assign To</label>
                                <select name="assigned_to" class="shf-input">
                                    <option value="">Self (no one)</option>
                                    @foreach ($activeUsers as $u)
                                        <option value="{{ $u->id }}"
                                            {{ $u->id === auth()->id() ? 'selected' : '' }}>{{ $u->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Leave blank or select yourself for a personal task</small>
                            </div>
                            <div class="col-md-6">
                                <label class="shf-form-label">Priority</label>
                                <select name="priority" class="shf-input">
                                    <option value="low">Low</option>
                                    <option value="normal" selected>Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="shf-form-label">Due Date <span class="text-danger">*</span></label>
                                <input type="text" name="due_date" id="dashTaskDueDate"
                                    class="shf-input shf-datepicker" autocomplete="off" placeholder="dd/mm/yyyy"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="shf-form-label">Link to Loan (optional)</label>
                                <input type="text" id="dashTaskLoanSearch" class="shf-input"
                                    placeholder="Search loan #, app # or customer..." autocomplete="off">
                                <input type="hidden" name="loan_detail_id" id="dashTaskLoanId">
                                <div id="dashTaskLoanResults" class="position-relative">
                                    <div id="dashTaskLoanDropdown" class="dropdown-menu w-100 shadow"
                                        style="max-height:200px; overflow-y:auto;"></div>
                                </div>
                                <div id="dashTaskLoanChip" class="d-none mt-2">
                                    <span class="shf-badge shf-badge-blue shf-text-xs" id="dashTaskLoanChipText"></span>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-1"
                                        onclick="clearDashLoanLink()">&times; Remove</button>
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

    {{-- Create DVR Modal (dashboard) --}}
    @if (auth()->user()->hasPermission('create_dvr'))
        <div class="modal fade" id="dashCreateDvrModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content" style="border: none; border-radius: 12px;">
                    <form id="dashDvrForm" method="POST" action="{{ route('dvr.store') }}">
                        @csrf
                        <input type="hidden" name="_from_dashboard" value="1">
                        <div class="modal-header"
                            style="background: var(--primary-dark-solid); color: #fff; border-radius: 12px 12px 0 0;">
                            <h5 class="modal-title font-display">New Visit / નવી મુલાકાત</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="shf-form-label">Contact Phone</label>
                                    <input type="text" name="contact_phone" id="dashDvrContactPhone" class="shf-input"
                                        maxlength="20" placeholder="Search by phone or name..." autocomplete="off">
                                    <div id="dashDvrContactResults" class="position-relative">
                                        <div id="dashDvrContactDropdown" class="dropdown-menu w-100 shadow"
                                            style="max-height:220px; overflow-y:auto;"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="shf-form-label">Contact Name <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_name" id="dashDvrContactName" class="shf-input"
                                        required maxlength="255" autocomplete="off">
                                </div>
                            </div>
                            <div class="row g-3 mt-1">
                                <div class="col-md-4">
                                    <label class="shf-form-label">Contact Type <span class="text-danger">*</span></label>
                                    <select name="contact_type" id="dashDvrContactType" class="shf-input" required>
                                        <option value="">Select...</option>
                                        @foreach ($dvrConfig['contactTypes'] ?? [] as $ct)
                                            <option value="{{ $ct['key'] }}">{{ $ct['label_en'] }} / {{ $ct['label_gu'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="shf-form-label">Purpose <span class="text-danger">*</span></label>
                                    <select name="purpose" class="shf-input" required>
                                        <option value="">Select...</option>
                                        @foreach ($dvrConfig['purposes'] ?? [] as $p)
                                            <option value="{{ $p['key'] }}">{{ $p['label_en'] }} / {{ $p['label_gu'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="shf-form-label">Visit Date <span class="text-danger">*</span></label>
                                    <input type="text" name="visit_date" id="dashDvrVisitDate" class="shf-input shf-datepicker-past"
                                        autocomplete="off" placeholder="dd/mm/yyyy" required>
                                </div>
                            </div>
                            <div class="row g-3 mt-1">
                                <div class="col-md-6">
                                    <label class="shf-form-label">Notes</label>
                                    <textarea name="notes" class="shf-input" rows="3" maxlength="5000"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="shf-form-label">Outcome</label>
                                    <textarea name="outcome" class="shf-input" rows="3" maxlength="5000"></textarea>
                                </div>
                            </div>
                            <div class="mt-3 p-3 rounded" style="background:var(--bg);border:1px solid var(--border);">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="follow_up_needed" id="dashDvrFollowUpNeeded" value="1">
                                    <label class="form-check-label shf-form-label" for="dashDvrFollowUpNeeded">
                                        Follow-up Needed / ફોલો-અપ જરૂરી
                                    </label>
                                </div>
                                <div id="dashDvrFollowUpFields" class="row g-3" style="display:none;">
                                    <div class="col-md-4">
                                        <label class="shf-form-label">Follow-up Date</label>
                                        <input type="text" name="follow_up_date" class="shf-input shf-datepicker-future"
                                            autocomplete="off" placeholder="dd/mm/yyyy">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="shf-form-label">Follow-up Notes</label>
                                        <input type="text" name="follow_up_notes" class="shf-input" maxlength="5000"
                                            placeholder="What to do on follow-up...">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-accent-outline btn-accent-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn-accent btn-accent-sm">Save Visit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Push Notification Prompt Modal — auto-opens on every dashboard load when not subscribed. Suppressed during impersonation. --}}
    @unless (app('impersonate')->isImpersonating())
    <div class="modal fade" id="pushPromptModal" tabindex="-1" aria-labelledby="pushPromptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-display fw-semibold d-flex align-items-center gap-2" id="pushPromptModalLabel">
                        <svg class="shf-icon-md shf-text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span id="pushPromptModalTitle">Enable Notifications</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Enable state --}}
                    <div id="pushPromptEnableState">
                        <p class="mb-2">Get alerted on your phone or desktop — even when SHF is closed.</p>
                        <p class="text-muted small mb-0">ફોન અથવા ડેસ્કટોપ પર સૂચના મેળવો — SHF બંધ હોય ત્યારે પણ.</p>
                    </div>

                    {{-- Blocked state --}}
                    <div id="pushPromptBlockedState" style="display:none;">
                        <p class="mb-1 text-danger"><strong>Notifications are blocked in your browser.</strong></p>
                        <p class="text-muted small mb-3">તમારા બ્રાઉઝરમાં નોટિફિકેશન બ્લોક છે.</p>
                        <p class="mb-1"><strong>To re-enable / ફરીથી ચાલુ કરવા:</strong></p>
                        <ol class="small mb-0">
                            <li>
                                Tap the <strong>lock</strong> icon in the address bar<br>
                                <span class="text-muted">સરનામા પટ્ટીમાં <strong>લોક</strong> આઇકન પર ટેપ કરો</span>
                            </li>
                            <li>
                                Open <strong>Permissions</strong> → <strong>Notifications</strong><br>
                                <span class="text-muted"><strong>પરવાનગી</strong> → <strong>સૂચનાઓ</strong> ખોલો</span>
                            </li>
                            <li>
                                Select <strong>Allow</strong><br>
                                <span class="text-muted"><strong>મંજૂરી આપો</strong> પસંદ કરો</span>
                            </li>
                            <li>
                                Reload this page<br>
                                <span class="text-muted">આ પાનું રીલોડ કરો</span>
                            </li>
                        </ol>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn-accent-outline btn-accent-sm" data-bs-dismiss="modal">Not Now / હમણાં નહીં</button>
                    <button type="button" class="btn-accent btn-accent-sm" id="pushPromptEnableBtn">Enable / ચાલુ કરો</button>
                </div>
            </div>
        </div>
    </div>
    @endunless
@endsection

@push('scripts')
    <script src="{{ asset('vendor/datatables/js/dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/js/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(function() {
            function convertDate(val) {
                if (!val) return '';
                var parts = val.split('/');
                return parts.length === 3 ? parts[2] + '-' + parts[1] + '-' + parts[0] : val;
            }

            // Variables needed by DataTable init (must be set before trigger('click'))
            var canViewAll = @json($permissions['view_all'] ?? false);
            var canDownload = @json($permissions['download_pdf'] ?? false);
            var canDownloadBranded = @json($permissions['download_pdf_branded'] ?? false);
            var canDownloadPlain = @json($permissions['download_pdf_plain'] ?? false);
            var canDelete = @json($permissions['delete_quotations'] ?? false);
            var hasFilters = false;
            var deleteUrl = null;
            var deleteModal = document.getElementById('deleteModal') ? new bootstrap.Modal(document.getElementById(
                'deleteModal')) : null;
            var dataUrl = @json(route('dashboard.quotation-data'));
            var showingCached = false;

            // Dashboard tab switching
            var dtInitialized = false;
            $('.shf-tab').on('click', function() {
                var tab = $(this).data('tab');
                $('.shf-tab').removeClass('active');
                $(this).addClass('active');
                $('.settings-tab-pane').hide();
                $('#tab-' + tab).show();

                // Auto-collapse filters on mobile after tab becomes visible
                if (window.shfCollapseFiltersOnMobile) window.shfCollapseFiltersOnMobile();

                // Lazy-init DataTables when tabs first shown, reload on re-visit
                if (tab === 'dash-quotations') {
                    if (!dtInitialized) {
                        initDataTable();
                        dtInitialized = true;
                    } else if (table) {
                        table.ajax.reload(null, false);
                    }
                }
                if (tab === 'dash-tasks') {
                    if (!tasksTableInit) {
                        initTasksTable();
                        tasksTableInit = true;
                    } else if (tasksTable) {
                        tasksTable.ajax.reload(null, false);
                    }
                }
                if (tab === 'dash-personal-tasks') {
                    if (!personalTasksTableInit) {
                        initPersonalTasksTable();
                        personalTasksTableInit = true;
                    } else if (personalTasksTable) {
                        personalTasksTable.ajax.reload(null, false);
                    }
                }
                if (tab === 'dash-loans') {
                    if (!loansTableInit) {
                        initLoansTable();
                        loansTableInit = true;
                    } else if (loansTable) {
                        loansTable.ajax.reload(null, false);
                    }
                }
                if (tab === 'dash-dvr') {
                    if (!dvrTableInit) {
                        initDvrTable();
                        dvrTableInit = true;
                    } else if (dvrTable) {
                        dvrTable.ajax.reload(null, false);
                    }
                }
            });

            // Show default active tab on page load
            $('.shf-tab.active').first().trigger('click');

            // Tasks & Loans & DVR DataTable state
            var tasksTable = null,
                tasksTableInit = false;
            var loansTable = null,
                loansTableInit = false;
            var personalTasksTable = null,
                personalTasksTableInit = false;
            var dvrTable = null,
                dvrTableInit = false;

            // --- Suppress DataTables error popup (always) ---
            $.fn.dataTable.ext.errMode = 'none';

            var emptyTasksHtml = '<div class="p-5 text-center">' +
                '<div class="shf-stat-icon mx-auto mb-3 shf-empty-icon-green">' +
                '<svg class="shf-icon-xl" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' +
                '</div>' +
                '<h3 class="font-display fw-semibold shf-text-lg shf-text-dark-alt">All caught up!</h3>' +
                '<p class="mt-1 small shf-text-gray">No pending tasks assigned to you.</p></div>';

            var emptyLoansHtml = '<div class="p-5 text-center">' +
                '<div class="shf-stat-icon mx-auto mb-3 shf-empty-icon-blue">' +
                '<svg class="shf-icon-xl" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>' +
                '</div>' +
                '<h3 class="font-display fw-semibold shf-text-lg shf-text-dark-alt">No active loans</h3>' +
                '<p class="mt-1 small shf-text-gray">All loans are completed or no loans found.</p></div>';

            var emptyDvrHtml = '<div class="p-5 text-center">' +
                '<div class="shf-stat-icon mx-auto mb-3 shf-empty-icon-green">' +
                '<svg class="shf-icon-xl" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' +
                '</div>' +
                '<h3 class="font-display fw-semibold shf-text-lg shf-text-dark-alt">No pending follow-ups!</h3>' +
                '<p class="mt-1 small shf-text-gray">All follow-ups are done or no visits recorded.</p></div>';

            // --- DVR DataTable ---
            function initDvrTable() {
                if (dvrTable) return;
                var dtLang = {
                    processing: '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-secondary"></div></div>',
                    emptyTable: ' ', zeroRecords: ' ',
                    info: 'Showing _START_ to _END_ of _TOTAL_', infoEmpty: '', infoFiltered: '(filtered from _MAX_)',
                    paginate: { previous: '&laquo;', next: '&raquo;' }
                };
                var viewIcon = '<svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';

                dvrTable = $('#dashDvrTable').DataTable({
                    processing: true, serverSide: true,
                    ajax: {
                        url: @json(route('dashboard.dvr-data')),
                        data: function(d) {
                            d.dvr_filter = $('#dashDvrFilter').val();
                        }
                    },
                    columns: [
                        { data: 'visit_date', render: function(data, type, row) {
                            return '<a href="' + row.show_url + '" class="fw-medium text-decoration-none" style="color:var(--primary-dark-solid);">' + data + '</a>';
                        }},
                        { data: 'contact_name', render: function(data, type, row) {
                            var html = '<strong>' + data + '</strong>';
                            if (row.contact_phone) html += '<br><small class="text-muted">' + row.contact_phone + '</small>';
                            if (row.user_name) html += '<br><small class="shf-text-xs text-muted">by ' + row.user_name + '</small>';
                            return html;
                        }},
                        { data: 'contact_type' },
                        { data: 'follow_up_html' },
                        { data: 'visit_date' },
                        { data: null, orderable: false, searchable: false, className: 'text-end', render: function(data, type, row) {
                            var html = '<div class="d-flex gap-1 justify-content-end flex-wrap">';
                            html += '<a href="' + row.show_url + '" class="btn-accent-sm shf-text-xs">' + viewIcon + '</a>';
                            if (row.follow_up_needed && !row.is_follow_up_done) {
                                html += '<a href="' + row.show_url + '" class="btn-accent-sm shf-text-xs btn-accent-outline">Follow-up</a>';
                            }
                            html += '</div>';
                            return html;
                        }}
                    ],
                    order: [[3, 'asc']], pageLength: 50,
                    dom: 'rt<"shf-dt-bottom"ip>',
                    language: dtLang,
                    createdRow: function(row, data) {
                        if (data.follow_up_urgency === 'overdue') {
                            $(row).css('background-color', 'rgba(220,53,69,0.08)').css('border-left', '3px solid #dc3545');
                        } else if (data.follow_up_urgency === 'due_today') {
                            $(row).css('background-color', 'rgba(255,193,7,0.10)').css('border-left', '3px solid #ffc107');
                        } else if (data.follow_up_urgency === 'due_tomorrow') {
                            $(row).css('background-color', 'rgba(255,193,7,0.05)').css('border-left', '3px solid #ffe082');
                        } else if (data.follow_up_urgency === 'due_soon') {
                            $(row).css('border-left', '3px solid #3b82f6');
                        }
                    },
                    drawCallback: function(settings) {
                        var total = settings._iRecordsDisplay;
                        var hasData = total > 0;
                        $('#dashDvrDesktop').toggle(hasData);
                        $('#dashDvrTable_wrapper .shf-dt-bottom').toggle(hasData);
                        if (!hasData) {
                            $('#dashDvrEmptyState').html(emptyDvrHtml).show();
                            $('#dashDvrMobileCards').html('');
                        } else {
                            $('#dashDvrEmptyState').hide();
                            var data = this.api().rows({ page: 'current' }).data();
                            var html = '';
                            for (var i = 0; i < data.length; i++) {
                                var d = data[i];
                                var cardStyle = '';
                                if (d.follow_up_urgency === 'overdue') cardStyle = 'border-left:3px solid #dc3545;background:rgba(220,53,69,0.08);';
                                else if (d.follow_up_urgency === 'due_today') cardStyle = 'border-left:3px solid #ffc107;background:rgba(255,193,7,0.10);';
                                else if (d.follow_up_urgency === 'due_tomorrow') cardStyle = 'border-left:3px solid #ffe082;background:rgba(255,193,7,0.05);';
                                else if (d.follow_up_urgency === 'due_soon') cardStyle = 'border-left:3px solid #3b82f6;';
                                html += '<div class="shf-card mb-2 p-3" style="' + cardStyle + '">'
                                    + '<div class="d-flex justify-content-between align-items-start mb-1">'
                                    + '<a href="' + d.show_url + '" class="fw-medium text-decoration-none" style="color:var(--primary-dark-solid);">' + d.contact_name + '</a>'
                                    + '<small class="text-muted">' + d.visit_date + '</small></div>';
                                if (d.contact_phone) html += '<small class="text-muted d-block">' + d.contact_phone + '</small>';
                                html += '<div class="d-flex flex-wrap gap-2 mt-1 align-items-center">'
                                    + '<span class="shf-badge shf-badge-blue shf-text-2xs">' + d.contact_type + '</span>'
                                    + '<span class="shf-badge shf-badge-gray shf-text-2xs">' + d.purpose + '</span></div>';
                                if (d.follow_up_html && d.follow_up_html !== '—') html += '<div class="mt-1">' + d.follow_up_html + '</div>';
                                html += '<div class="d-flex gap-1 mt-2 flex-wrap">'
                                    + '<a href="' + d.show_url + '" class="btn-accent-sm shf-text-xs">' + viewIcon + ' View</a>';
                                if (d.follow_up_needed && !d.is_follow_up_done) {
                                    html += '<a href="' + d.show_url + '" class="btn-accent-sm shf-text-xs btn-accent-outline">Follow-up</a>';
                                }
                                html += '</div></div>';
                            }
                            $('#dashDvrMobileCards').html(html);
                        }
                    }
                });

                // DVR filters
                $('#dashDvrFilterBtn').on('click', function() { dvrTable.ajax.reload(); });
                $('#dashDvrClearBtn').on('click', function() {
                    $('#dashDvrFilter').val('pending');
                    $('#dashDvrSearch').val('');
                    dvrTable.search('').ajax.reload();
                });
                $('#dashDvrSearch').on('keyup', function(e) {
                    if (e.key === 'Enter') dvrTable.search(this.value).draw();
                });
            }

            // --- Tasks DataTable ---
            function initTasksTable() {
                if (tasksTable) return;
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
                tasksTable = $('#tasksTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: @json(route('dashboard.task-data')),
                        data: function(d) {
                            d.stage = $('#dashTaskStage').val();
                            d.task_status = $('#dashTaskStatus').val();
                        }
                    },
                    columns: [{
                            data: 'loan_number'
                        },
                        {
                            data: 'customer_name'
                        },
                        {
                            data: 'bank_name'
                        },
                        {
                            data: 'formatted_amount',
                            className: 'text-end'
                        },
                        {
                            data: 'stage_name'
                        },
                        {
                            data: 'status_label'
                        },
                        {
                            data: 'assigned_at',
                            className: 'text-muted'
                        },
                        {
                            data: 'actions_html',
                            orderable: false,
                            searchable: false,
                            className: 'text-end'
                        }
                    ],
                    order: [
                        [5, 'asc']
                    ],
                    pageLength: 50,
                    dom: 'rt<"shf-dt-bottom"ip>',
                    language: dtLang,
                    drawCallback: function(settings) {
                        var total = settings._iRecordsDisplay;
                        var hasData = total > 0;
                        $('#tasksFilterToggle').toggle(hasData);
                        if (!hasData) {
                            $('#tasksFilterBar').hide();
                        } else {
                            if (window.innerWidth >= 768) $('#tasksFilterBar').show();
                        }
                        $('#tasksDesktop').toggle(hasData);
                        $('#tasksTable_wrapper .shf-dt-bottom').toggle(hasData);
                        if (!hasData) {
                            $('#tasksEmptyState').html(emptyTasksHtml).show();
                            $('#tasksMobileCards').html('');
                        } else {
                            $('#tasksEmptyState').hide();
                            var data = this.api().rows({
                                page: 'current'
                            }).data();
                            var html = '';
                            for (var i = 0; i < data.length; i++) {
                                var d = data[i];
                                var loc = d.location_name ?
                                    '<small class="location-info shf-text-2xs">' + d.location_name +
                                    '</small>' : '';
                                html += '<div class="shf-card mb-2 p-3">' +
                                    '<div class="d-flex justify-content-between align-items-start mb-2">' +
                                    '<div style="min-width:0;flex:1;"><strong>' + (d
                                        .customer_name_plain || d.customer_name) +
                                    '</strong><br><small class="text-muted">' + d.loan_number +
                                    '</small></div>' +
                                    '<div class="ms-2 flex-shrink-0">' + d.status_label +
                                    '</div></div>' +
                                    '<div class="d-flex justify-content-between align-items-center mb-1">' +
                                    '<span>' + d.formatted_amount + '</span>' +
                                    '<small class="text-muted">' + (d.bank_name_plain || '') +
                                    '</small></div>' +
                                    (loc ? '<div class="mb-1">' + loc + '</div>' : '') +
                                    '<div class="mb-1"><small class="text-muted">Owner: ' + (d
                                        .owner_info || '—') + '</small></div>' +
                                    '<div class="d-flex flex-wrap gap-1 mb-2"><small class="text-muted me-1">Stage:</small>' +
                                    d.stage_name + '</div>' +
                                    '<div>' + d.actions_html + '</div></div>';
                            }
                            $('#tasksMobileCards').html(html);
                        }
                    }
                });
                var taskSearchTimer = null;
                $('#taskSearch').on('keyup', function() {
                    clearTimeout(taskSearchTimer);
                    var val = this.value;
                    taskSearchTimer = setTimeout(function() {
                        tasksTable.search(val).draw();
                    }, 300);
                });
                $('#dashTaskFilter').on('click', function() {
                    tasksTable.ajax.reload();
                });
                $('#dashTaskClear').on('click', function() {
                    $('#dashTaskStage, #dashTaskStatus').val('');
                    $('#taskSearch').val('');
                    tasksTable.search('').ajax.reload();
                    setTimeout(updateTasksFilterCount, 50);
                });

                function updateTasksFilterCount() {
                    var count = 0;
                    if ($('#dashTaskStage').val()) count++;
                    if ($('#dashTaskStatus').val()) count++;
                    if ($('#taskSearch').val()) count++;
                    var $b = $('#tasksFilterCount');
                    count > 0 ? $b.text(count).removeClass('shf-collapse-hidden') : $b.addClass(
                        'shf-collapse-hidden');
                }
                $(document).on('change', '#dashTaskStage, #dashTaskStatus', updateTasksFilterCount);
                $('#taskSearch').on('keyup', updateTasksFilterCount);
            }

            // --- Personal Tasks Dashboard DataTable ---
            function initPersonalTasksTable() {
                if (personalTasksTable) return;
                var emptyPersonalHtml = '<div class="p-5 text-center">' +
                    '<div class="shf-stat-icon mx-auto mb-3">' +
                    '<svg class="shf-icon-xl" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>' +
                    '</div>' +
                    '<h3 class="font-display fw-semibold shf-text-lg shf-text-dark-alt">No personal tasks</h3>' +
                    '<p class="mt-1 small shf-text-gray">Create a task to get started.</p>' +
                    '<a href="' + @json(route('general-tasks.index')) +
                    '" class="btn-accent btn-accent-sm mt-2">Go to Tasks</a></div>';
                var viewIcon =
                    '<svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
                var deleteIcon =
                    '<svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';
                personalTasksTable = $('#personalTasksTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: @json(route('general-tasks.data')),
                        data: function(d) {
                            d.view = 'all';
                            d.status = 'active';
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
                                return data + '<br><small class="text-muted">by ' + row
                                    .creator_name + '</small>';
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
                                    html +=
                                        '<button class="btn-accent-sm btn-accent-outline" onclick=\'editTaskDash(' +
                                        JSON.stringify(row.edit_data) + ')\'>' + viewIcon +
                                        ' Edit</button>';
                                }
                                if (row.can_delete) {
                                    html +=
                                        '<button class="btn-accent-sm shf-btn-danger-alt" onclick="deleteTaskDash(' +
                                        row.id + ')">' + deleteIcon + ' Del</button>';
                                }
                                html += '</div>';
                                return html;
                            }
                        }
                    ],
                    createdRow: function(row, data) {
                        if (data.due_urgency === 'overdue') {
                            $(row).css('background-color', 'rgba(220, 53, 69, 0.08)').css('border-left',
                                '3px solid #dc3545');
                        } else if (data.due_urgency === 'due_today') {
                            $(row).css('background-color', 'rgba(255, 193, 7, 0.10)').css('border-left',
                                '3px solid #ffc107');
                        } else if (data.due_urgency === 'due_tomorrow') {
                            $(row).css('background-color', 'rgba(255, 193, 7, 0.05)').css('border-left',
                                '3px solid #ffe082');
                        } else if (data.priority === 'urgent') {
                            $(row).css('border-left', '3px solid #dc3545');
                        }
                    },
                    order: [
                        [7, 'desc']
                    ],
                    pageLength: 10,
                    dom: 'rt<"shf-dt-bottom"ip>',
                    language: {
                        processing: '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-secondary"></div></div>',
                        emptyTable: ' ',
                        zeroRecords: ' ',
                        info: 'Showing _START_ to _END_ of _TOTAL_',
                        infoEmpty: '',
                        paginate: {
                            previous: '&laquo;',
                            next: '&raquo;'
                        }
                    },
                    drawCallback: function(settings) {
                        var total = settings._iRecordsDisplay;
                        var hasData = total > 0;
                        $('#personalTasksDesktop').toggle(hasData);
                        $('#personalTasksTable_wrapper .shf-dt-bottom').toggle(hasData);
                        if (!hasData) {
                            $('#personalTasksEmptyState').html(emptyPersonalHtml).show();
                            $('#personalTasksMobileCards').html('');
                        } else {
                            $('#personalTasksEmptyState').hide();
                            var data = this.api().rows({
                                page: 'current'
                            }).data();
                            var html = '';
                            for (var i = 0; i < data.length; i++) {
                                var d = data[i];
                                var cardStyle = '';
                                if (d.due_urgency === 'overdue') cardStyle =
                                    'border-left:3px solid #dc3545;background:rgba(220,53,69,0.08);';
                                else if (d.due_urgency === 'due_today') cardStyle =
                                    'border-left:3px solid #ffc107;background:rgba(255,193,7,0.10);';
                                else if (d.due_urgency === 'due_tomorrow') cardStyle =
                                    'border-left:3px solid #ffe082;background:rgba(255,193,7,0.05);';
                                else if (d.priority === 'urgent') cardStyle =
                                    'border-left:3px solid #dc3545;';
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
                                if (d.completed_at) html += '<small class="text-success">Completed: ' +
                                    d.completed_at + '</small>';
                                if (d.loan_info) html += '<span>' + d.loan_info + '</span>';
                                html += '</div>' +
                                    '<div class="d-flex gap-1 mt-2">' +
                                    '<a href="' + d.show_url + '" class="btn-accent-sm shf-text-xs">' +
                                    viewIcon +
                                    ' View</a>';
                                if (d.can_edit) {
                                    html +=
                                        '<button class="btn-accent-sm btn-accent-outline shf-text-xs" onclick=\'editTaskDash(' +
                                        JSON.stringify(d.edit_data) + ')\'>' + viewIcon +
                                        ' Edit</button>';
                                }
                                if (d.can_delete) {
                                    html +=
                                        '<button class="btn-accent-sm shf-text-xs shf-btn-danger-alt" onclick="deleteTaskDash(' +
                                        d.id + ')">' + deleteIcon + ' Del</button>';
                                }
                                html += '</div></div>';
                            }
                            $('#personalTasksMobileCards').html(html);
                        }
                    }
                });
            }

            // --- Loans Dashboard DataTable ---
            function initLoansTable() {
                if (loansTable) return;
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
                loansTable = $('#loansTableDash').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: @json(route('dashboard.loan-data')),
                        data: function(d) {
                            d.status = $('#dashLoanStatus').val();
                            d.customer_type = $('#dashLoanType').val();
                            d.bank = $('#dashLoanBank').val();
                            d.branch = $('#dashLoanBranch').val();
                            d.stage = $('#dashLoanStage').val();
                            d.role = $('#dashLoanRole').val();
                            d.date_from = convertDate($('#dashLoanDateFrom').val());
                            d.date_to = convertDate($('#dashLoanDateTo').val());
                        }
                    },
                    columns: [{
                            data: 'loan_number'
                        },
                        {
                            data: 'customer_name'
                        },
                        {
                            data: 'bank_name'
                        },
                        {
                            data: 'formatted_amount',
                            className: 'text-end'
                        },
                        {
                            data: 'stage_name'
                        },
                        {
                            data: 'status_label'
                        },
                        {
                            data: 'created_at',
                            className: 'text-muted'
                        },
                        {
                            data: 'actions_html',
                            orderable: false,
                            searchable: false,
                            className: 'text-end'
                        }
                    ],
                    order: [
                        [6, 'desc']
                    ],
                    pageLength: 50,
                    dom: 'rt<"shf-dt-bottom"ip>',
                    language: dtLang,
                    drawCallback: function(settings) {
                        var total = settings._iRecordsDisplay;
                        var hasData = total > 0;
                        $('#loansFilterToggle').toggle(hasData);
                        if (!hasData) {
                            $('#loansFilterBar').hide();
                        } else {
                            if (window.innerWidth >= 768) $('#loansFilterBar').show();
                        }
                        $('#loansDashDesktop').toggle(hasData);
                        $('#loansDashFooter').toggle(hasData);
                        $('#loansTableDash_wrapper .shf-dt-bottom').toggle(hasData);
                        if (!hasData) {
                            $('#loansDashEmptyState').html(emptyLoansHtml).show();
                            $('#loansMobileCardsDash').html('');
                        } else {
                            $('#loansDashEmptyState').hide();
                            var data = this.api().rows({
                                page: 'current'
                            }).data();
                            var html = '';
                            for (var i = 0; i < data.length; i++) {
                                var d = data[i];
                                var loc = d.location_name ?
                                    '<small class="location-info shf-text-2xs">' + d.location_name +
                                    '</small>' : '';
                                var ownerPlain = (d.owner_info || '—').replace(/<br\s*\/?>/gi, ' · ')
                                    .replace(/<[^>]+>/g, '');
                                html += '<div class="shf-card mb-2 p-3">' +
                                    '<div class="d-flex justify-content-between align-items-start mb-2">' +
                                    '<div style="min-width:0;flex:1;"><strong>' + d.customer_name +
                                    '</strong><br><small class="text-muted">' + d.loan_number +
                                    '</small></div>' +
                                    '<div class="ms-2 flex-shrink-0">' + d.status_label +
                                    '</div></div>' +
                                    '<div class="d-flex justify-content-between align-items-center mb-1">' +
                                    '<span>' + d.formatted_amount + '</span>' +
                                    '<small class="text-muted">' + (d.bank_name_plain || '') +
                                    '</small></div>' +
                                    (loc ? '<div class="mb-1">' + loc + '</div>' : '') +
                                    '<div class="mb-1"><small class="text-muted">Owner: ' + ownerPlain +
                                    '</small></div>' +
                                    '<div class="d-flex flex-wrap gap-1 mb-2"><small class="text-muted me-1">Stage:</small>' +
                                    d.stage_name + '</div>' +
                                    '<div>' + d.actions_html + '</div></div>';
                            }
                            $('#loansMobileCardsDash').html(html);
                        }
                    }
                });
                var loanSearchTimer = null;
                $('#loanDashSearch').on('keyup', function() {
                    clearTimeout(loanSearchTimer);
                    var val = this.value;
                    loanSearchTimer = setTimeout(function() {
                        loansTable.search(val).draw();
                    }, 300);
                });
                $('#dashLoanFilter').on('click', function() {
                    loansTable.ajax.reload();
                });
                $('#dashLoanClear').on('click', function() {
                    $('#dashLoanStatus, #dashLoanType, #dashLoanBank, #dashLoanBranch, #dashLoanStage, #dashLoanRole')
                        .val('');
                    $('#dashLoanDateFrom, #dashLoanDateTo').val('').datepicker('update', '');
                    $('#loanDashSearch').val('');
                    loansTable.search('').ajax.reload();
                    setTimeout(updateDashLoansFilterCount, 50);
                });

                function updateDashLoansFilterCount() {
                    var count = 0;
                    $('#dashLoanStatus, #dashLoanType, #dashLoanBank, #dashLoanBranch, #dashLoanStage, #dashLoanRole')
                        .each(function() {
                            if ($(this).val()) count++;
                        });
                    if ($('#dashLoanDateFrom').val()) count++;
                    if ($('#dashLoanDateTo').val()) count++;
                    if ($('#loanDashSearch').val()) count++;
                    var $b = $('#dashLoansFilterCount');
                    count > 0 ? $b.text(count).removeClass('shf-collapse-hidden') : $b.addClass(
                        'shf-collapse-hidden');
                }
                $(document).on('change',
                    '#dashLoanStatus, #dashLoanType, #dashLoanBank, #dashLoanBranch, #dashLoanStage, #dashLoanRole, #dashLoanDateFrom, #dashLoanDateTo',
                    updateDashLoansFilterCount);
                $('#loanDashSearch').on('keyup', updateDashLoansFilterCount);
            }

            // Auto-init tables for the default active tab
            @if ($loanStats)
                if (@json($defaultTab) === 'dash-tasks' || @json($defaultTab) === 'tasks') {
                    initTasksTable();
                    tasksTableInit = true;
                }
                if (@json($defaultTab) === 'dash-loans' || @json($defaultTab) === 'loans') {
                    initLoansTable();
                    loansTableInit = true;
                }
            @endif

            // --- Auto-reload when back online ---
            window.addEventListener('online', function() {
                if (showingCached) {
                    showingCached = false;
                    $('#offline-indicator').remove();
                    try {
                        table.ajax.reload();
                    } catch (e) {}
                    loadMobileCards(true);
                }
            });

            function showCachedIndicator() {
                if ($('#offline-indicator').length) return;
                showingCached = true;
                $('<div id="offline-indicator" class="text-center py-2 small" style="background:#fff8e1;color:#8a6d00;border-bottom:1px solid #ffe082;">' +
                    '<svg style="width:14px;height:14px;vertical-align:-2px;margin-right:4px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728M5.636 18.364a9 9 0 010-12.728"/>' +
                    '</svg>' +
                    'Offline — showing cached data / ઑફલાઇન — કેશ ડેટા દર્શાવે છે' +
                    '</div>').insertBefore('#quotations-table_wrapper, #mobile-cards-container').first();
            }

            // --- LocalStorage cache helpers ---
            function cacheResponse(data) {
                try {
                    localStorage.setItem('shf_dt_cache', JSON.stringify(data));
                } catch (e) {}
            }

            function getCachedResponse() {
                try {
                    var s = localStorage.getItem('shf_dt_cache');
                    return s ? JSON.parse(s) : null;
                } catch (e) {
                    return null;
                }
            }

            function cacheMobileData(data) {
                try {
                    localStorage.setItem('shf_mobile_cache', JSON.stringify(data));
                } catch (e) {}
            }

            function getCachedMobileData() {
                try {
                    var s = localStorage.getItem('shf_mobile_cache');
                    return s ? JSON.parse(s) : null;
                } catch (e) {
                    return null;
                }
            }

            // --- Bootstrap Datepicker Init ---
            $('.shf-datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                clearBtn: true
            });

            // Init datepicker inside create task modal on show
            $('#dashCreateTaskModal').on('shown.bs.modal', function() {
                $(this).find('.shf-datepicker').datepicker({
                    format: 'dd/mm/yyyy',
                    autoclose: true,
                    todayHighlight: true,
                    clearBtn: true
                });
            });

            // Init datepickers inside DVR modal on show
            $('#dashCreateDvrModal').on('shown.bs.modal', function() {
                $(this).find('.shf-datepicker-past').datepicker({
                    format: 'dd/mm/yyyy', autoclose: true, todayHighlight: true, endDate: '+0d'
                });
                $(this).find('.shf-datepicker-future').datepicker({
                    format: 'dd/mm/yyyy', autoclose: true, todayHighlight: true, startDate: '+1d'
                });
                // Auto-fill today's date
                if (!$('#dashDvrVisitDate').val()) {
                    var today = new Date();
                    var dd = ('0' + today.getDate()).slice(-2) + '/' + ('0' + (today.getMonth() + 1)).slice(-2) + '/' + today.getFullYear();
                    $('#dashDvrVisitDate').datepicker('update', dd);
                }
            });

            // DVR follow-up toggle with +7 day default
            $('#dashDvrFollowUpNeeded').on('change', function() {
                $('#dashDvrFollowUpFields').toggle(this.checked);
                if (this.checked && !$('#dashDvrForm [name="follow_up_date"]').val()) {
                    var d = new Date(); d.setDate(d.getDate() + 7);
                    var dd = ('0' + d.getDate()).slice(-2) + '/' + ('0' + (d.getMonth() + 1)).slice(-2) + '/' + d.getFullYear();
                    $('#dashDvrForm [name="follow_up_date"]').datepicker('update', dd);
                }
            });

            // DVR form validation
            $('#dashDvrForm').on('submit', function(e) {
                var valid = SHF.validateForm($(this), {
                    contact_name: { required: true, maxlength: 255, label: 'Contact Name / સંપર્ક નામ' },
                    contact_type: { required: true, label: 'Contact Type / સંપર્ક પ્રકાર' },
                    purpose: { required: true, label: 'Purpose / હેતુ' },
                    visit_date: { required: true, dateFormat: 'd/m/Y', label: 'Visit Date / મુલાકાત તારીખ' },
                    follow_up_date: {
                        label: 'Follow-up Date / ફોલો-અપ તારીખ',
                        custom: function() {
                            if ($('#dashDvrFollowUpNeeded').is(':checked')) {
                                var val = $('#dashDvrForm [name="follow_up_date"]').val();
                                if (!val) return 'Follow-up Date is required when follow-up is needed / ફોલો-અપ તારીખ જરૂરી છે';
                                var parts = val.split('/');
                                if (parts.length === 3) {
                                    var inputDate = new Date(parts[2], parts[1] - 1, parts[0]);
                                    var today = new Date(); today.setHours(0,0,0,0);
                                    if (inputDate <= today) return 'Follow-up Date must be a future date / ફોલો-અપ તારીખ ભવિષ્યની હોવી જોઈએ';
                                }
                            }
                            return null;
                        }
                    }
                });
                if (!valid) e.preventDefault();
            });

            // DVR Contact Search on dashboard
            var dashContactTimer;
            $('#dashDvrContactPhone, #dashDvrContactName').on('input', function() {
                clearTimeout(dashContactTimer);
                var q = $(this).val().trim();
                if (q.length < 2) { $('#dashDvrContactDropdown').removeClass('show').empty(); return; }
                dashContactTimer = setTimeout(function() {
                    $.get(@json(route('dvr.search-contacts')), { q: q }, function(contacts) {
                        var $dd = $('#dashDvrContactDropdown');
                        if (!contacts.length) { $dd.removeClass('show').empty(); return; }
                        var html = '';
                        contacts.forEach(function(c) {
                            var label = '<strong>' + $('<span>').text(c.name).html() + '</strong>';
                            if (c.phone) label += ' <span class="text-muted">' + $('<span>').text(c.phone).html() + '</span>';
                            label += ' <span class="shf-badge shf-badge-gray shf-text-2xs">' + c.source + '</span>';
                            html += '<a class="dropdown-item shf-dash-dvr-contact-pick py-2" href="#" data-name="' + $('<span>').text(c.name).html().replace(/"/g, '&quot;')
                                + '" data-phone="' + $('<span>').text(c.phone || '').html().replace(/"/g, '&quot;')
                                + '" data-type="' + (c.type || '') + '">'
                                + label + '</a>';
                        });
                        $dd.html(html).addClass('show');
                    });
                }, 300);
            });
            $(document).on('click', '.shf-dash-dvr-contact-pick', function(e) {
                e.preventDefault();
                $('#dashDvrContactName').val($(this).data('name'));
                $('#dashDvrContactPhone').val($(this).data('phone'));
                var ct = $(this).data('type');
                if (ct && $('#dashDvrContactType option[value="' + ct + '"]').length) {
                    $('#dashDvrContactType').val(ct);
                }
                $('#dashDvrContactDropdown').removeClass('show').empty();
            });
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#dashDvrContactResults, #dashDvrContactPhone, #dashDvrContactName').length) {
                    $('#dashDvrContactDropdown').removeClass('show');
                }
            });

            // ── Priority → Due Date auto-fill ──
            var dashPriorityDays = {
                low: 10,
                normal: 7,
                high: 4,
                urgent: 2
            };

            function setDashDueDateFromPriority(priority) {
                var days = dashPriorityDays[priority] || 10;
                var d = new Date();
                d.setDate(d.getDate() + days);
                var dd = ('0' + d.getDate()).slice(-2) + '/' + ('0' + (d.getMonth() + 1)).slice(-2) + '/' + d
                    .getFullYear();
                $('#dashTaskDueDate').datepicker('update', dd);
            }
            $('#dashTaskForm select[name="priority"]').on('change', function() {
                setDashDueDateFromPriority($(this).val());
            });

            // ── Dashboard Task Form: validation + date conversion + loan search ──
            $('#dashTaskForm').on('submit', function(e) {
                var $form = $(this);
                var valid = true;
                $form.find('.shf-client-error').remove();
                $form.find('.is-invalid').removeClass('is-invalid');

                // Title required
                var $title = $('#dashTaskTitle');
                if (!$.trim($title.val())) {
                    $title.addClass('is-invalid').after(
                        '<div class="text-danger small mt-1 shf-client-error">ટાસ્કનું નામ લખો / Please enter task title</div>'
                    );
                    valid = false;
                }

                // Due date required
                var $dueDate = $('#dashTaskDueDate');
                if (!$.trim($dueDate.val())) {
                    $dueDate.addClass('is-invalid').after(
                        '<div class="text-danger small mt-1 shf-client-error">ટાસ્કની છેલ્લી તારીખ પસંદ કરો / Please select a due date</div>'
                    );
                    valid = false;
                }

                // Priority required
                var $priority = $form.find('select[name="priority"]');
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
            $(document).on('input change', '#dashTaskForm .is-invalid', function() {
                $(this).removeClass('is-invalid').next('.shf-client-error').remove();
            });

            // Reset dashboard task form when "Create" opens (not edit)
            function resetDashTaskForm() {
                $('#dashTaskForm').attr('action', @json(route('general-tasks.store')));
                $('#dashTaskFormMethod').val('POST');
                $('#dashTaskModalTitle').text('Create New Task / નવું ટાસ્ક બનાવો');
                $('#dashTaskForm').find('.shf-client-error').remove();
                $('#dashTaskForm').find('.is-invalid').removeClass('is-invalid');
                $('#dashTaskTitle, #dashTaskForm textarea').val('');
                $('#dashTaskForm select[name="assigned_to"]').val({{ auth()->id() }});
                $('#dashTaskForm select[name="priority"]').val('normal');
                setDashDueDateFromPriority('normal');
                clearDashLoanLink();
            }

            // Only reset on create button click, not on editTaskDash
            $('[data-dash-create-task]').on('click', function() {
                resetDashTaskForm();
            });

            // Dashboard loan search autocomplete
            var dashLoanTimer;
            $('#dashTaskLoanSearch').on('input', function() {
                clearTimeout(dashLoanTimer);
                var q = $(this).val().trim();
                if (q.length < 2) {
                    $('#dashTaskLoanDropdown').removeClass('show').empty();
                    return;
                }
                dashLoanTimer = setTimeout(function() {
                    $.get(@json(route('general-tasks.search-loans')), {
                        q: q
                    }, function(loans) {
                        var $dd = $('#dashTaskLoanDropdown');
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
                                '<a class="dropdown-item shf-dash-loan-pick" href="#" data-id="' +
                                loan.id + '" data-label="' + label.replace(/"/g,
                                    '&quot;') + '">' + label + '</a>';
                        });
                        $dd.html(html).addClass('show');
                    });
                }, 300);
            });

            $(document).on('click', '.shf-dash-loan-pick', function(e) {
                e.preventDefault();
                $('#dashTaskLoanId').val($(this).data('id'));
                $('#dashTaskLoanSearch').val('').hide();
                $('#dashTaskLoanChipText').text($(this).data('label'));
                $('#dashTaskLoanChip').removeClass('d-none');
                $('#dashTaskLoanDropdown').removeClass('show').empty();
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#dashTaskLoanResults, #dashTaskLoanSearch').length) {
                    $('#dashTaskLoanDropdown').removeClass('show');
                }
            });

            // Convert dd/mm/yyyy → yyyy-mm-dd for server
            function getDateValue(selector) {
                var val = $(selector).val();
                if (!val) return '';
                var parts = val.split('/');
                if (parts.length === 3) return parts[2] + '-' + parts[1] + '-' + parts[0];
                return val;
            }

            // --- Desktop DataTable ---
            var table = null;

            function initDataTable() {
                if (table || $.fn.DataTable.isDataTable('#quotations-table')) return;
                table = $('#quotations-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: function(data, callback, settings) {
                        data.customer_type = $('#filter-type').val();
                        data.loan_status = $('#filter-loan-status').val();
                        data.status = $('#filter-status').val();
                        data.date_from = getDateValue('#filter-date-from');
                        data.date_to = getDateValue('#filter-date-to');
                        data.created_by = $('#filter-created-by').val() || '';

                        $.ajax({
                            url: dataUrl,
                            data: data,
                            success: function(res) {
                                cacheResponse(res);
                                $('#offline-indicator').remove();
                                showingCached = false;
                                callback(res);
                            },
                            error: function() {
                                var cached = getCachedResponse();
                                if (cached) {
                                    cached.draw = data.draw;
                                    showCachedIndicator();
                                    callback(cached);
                                } else {
                                    callback({
                                        draw: data.draw,
                                        data: [],
                                        recordsTotal: 0,
                                        recordsFiltered: 0
                                    });
                                }
                            }
                        });
                    },
                    columns: buildColumns(),
                    order: [
                        [getDateColumnIndex(), 'desc']
                    ],
                    pageLength: 50,
                    lengthMenu: [10, 25, 50, 100],
                    dom: 'rt<"shf-dt-bottom"ip>',
                    language: {
                        processing: '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div></div>',
                        emptyTable: ' ',
                        zeroRecords: ' ',
                        info: 'Showing _START_ to _END_ of _TOTAL_',
                        infoEmpty: '',
                        infoFiltered: '(filtered from _MAX_)',
                        paginate: {
                            previous: '&laquo;',
                            next: '&raquo;'
                        }
                    },
                    searching: true,
                    createdRow: function(row, data) {
                        if (data.is_cancelled) {
                            $(row).css('background', '#fff1f2');
                        } else if (data.is_on_hold) {
                            $(row).css('background', '#fff7ed');
                        } else if (data.is_converted) {
                            $(row).css('background', '#f0fdf4');
                        }
                    },
                    drawCallback: function(settings) {
                        var total = settings._iRecordsDisplay;
                        var $bottom = $('#quotations-table_wrapper .shf-dt-bottom');
                        if (total === 0) {
                            updateEmptyState();
                            $('#empty-state').show();
                            $bottom.hide();
                        } else {
                            $('#empty-state').hide();
                            $bottom.show();
                        }
                    }
                });
            } // end initDataTable

            // Custom per-page selector
            $('#dt-page-length').on('change', function() {
                if (table) {
                    table.page.len(parseInt(this.value)).draw();
                }
            });

            // DataTable init is handled by the tab click trigger at page load
            // (see: $('.shf-tab.active').first().trigger('click') above)

            function buildColumns() {
                var cols = [{
                        data: 'id',
                        className: 'text-muted',
                        width: '50px'
                    },
                    {
                        data: 'customer_name',
                        className: 'fw-medium',
                        render: function(data, type, row) {
                            var loc = row.location_name ?
                                '<br><small class="location-info shf-text-2xs">' + $('<span>')
                                .text(row.location_name).html() + '</small>' : '';
                            return $('<span>').text(data).html() + loc;
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            return '<span class="shf-badge ' + data.type_badge_class + '">' + data
                                .type_label + '</span>';
                        }
                    },
                    {
                        data: 'formatted_amount',
                        className: 'fw-medium'
                    },
                    {
                        data: 'banks',
                        orderable: false,
                        render: function(data) {
                            if (!data || data.length === 0)
                                return '<span class="shf-text-gray-light">—</span>';
                            var html = '<div class="d-flex flex-wrap gap-1">';
                            var show = data.slice(0, 2);
                            for (var i = 0; i < show.length; i++) {
                                html += '<span class="shf-tag" style="padding:2px 8px;font-size:0.7rem;">' +
                                    $('<span>').text(show[i]).html() + '</span>';
                            }
                            if (data.length > 2) {
                                html +=
                                    '<span class="shf-badge shf-badge-gray shf-text-2xs">+' + (
                                        data.length - 2) + '</span>';
                            }
                            html += '</div>';
                            return html;
                        }
                    },
                    {
                        data: 'status_html',
                        orderable: false,
                        render: function(data, type, row) {
                            var html = data || '';
                            if (row.is_on_hold && row.hold_follow_up_date) {
                                html += '<div class="shf-text-2xs shf-text-gray-light mt-1">Follow-up: ' +
                                    $('<span>').text(row.hold_follow_up_date).html() + '</div>';
                            }
                            return html;
                        }
                    }
                ];

                if (canViewAll) {
                    cols.push({
                        data: 'created_by',
                        className: 'text-muted'
                    });
                }

                cols.push({
                    data: 'date',
                    className: 'text-muted text-nowrap'
                });

                cols.push({
                    data: null,
                    orderable: false,
                    className: 'text-end',
                    render: function(data) {
                        var html = '<div class="d-flex align-items-center justify-content-end gap-2">';
                        // View
                        html += '<a href="' + data.show_url +
                            '" class="shf-text-accent" title="View Details">' +
                            '<svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>' +
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>' +
                            '</svg></a>';
                        // Download Branded
                        if (data.download_branded_url) {
                            html += '<a href="' + data.download_branded_url +
                                '" class="shf-text-success-alt" title="Download Branded PDF">' +
                                '<svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>' +
                                '</svg></a>';
                        }
                        // Download Plain
                        if (data.download_plain_url) {
                            html += '<a href="' + data.download_plain_url +
                                '" style="color:#6b7280;" title="Download Plain PDF">' +
                                '<svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>' +
                                '</svg></a>';
                        }
                        // Convert to Loan / View Loan
                        if (data.convert_url) {
                            html += '<a href="' + data.convert_url +
                                '" style="color:#2563eb;" title="Convert to Loan">' +
                                '<svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>' +
                                '</svg></a>';
                        } else if (data.loan_url) {
                            html += '<a href="' + data.loan_url +
                                '" style="color:#16a34a;" title="View Loan">' +
                                '<svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>' +
                                '</svg></a>';
                        }
                        // Hold
                        if (data.hold_url) {
                            html += '<button type="button" class="btn btn-link p-0 btn-hold-quotation" data-id="' + data.id +
                                '" data-name="' + $('<span>').text(data.customer_name).html() +
                                '" style="color:#d97706;" title="Put on Hold">' +
                                '<svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>' +
                                '</svg></button>';
                        }
                        // Resume
                        if (data.resume_url) {
                            html += '<button type="button" class="btn btn-link p-0 btn-resume-quotation" data-url="' +
                                data.resume_url + '" data-name="' + $('<span>').text(data.customer_name).html() +
                                '" style="color:#16a34a;" title="Resume">' +
                                '<svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>' +
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>' +
                                '</svg></button>';
                        }
                        // Cancel
                        if (data.cancel_url) {
                            html += '<button type="button" class="btn btn-link p-0 btn-cancel-quotation" data-id="' + data.id +
                                '" data-name="' + $('<span>').text(data.customer_name).html() +
                                '" style="color:#c0392b;" title="Cancel">' +
                                '<svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>' +
                                '</svg></button>';
                        }
                        // Delete
                        if (data.delete_url) {
                            html +=
                                '<button type="button" class="btn btn-link p-0 btn-delete shf-text-error" data-url="' +
                                data.delete_url + '" title="Delete">' +
                                '<svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>' +
                                '</svg></button>';
                        }
                        html += '</div>';
                        return html;
                    }
                });

                return cols;
            }

            function getDateColumnIndex() {
                // Columns: #, Customer, Type, Amount, Banks, Status, [Created By?], Date, Actions
                return canViewAll ? 7 : 6;
            }

            function updateEmptyState() {
                hasFilters = $('#filter-search').val() || $('#filter-type').val() ||
                    $('#filter-status').val() !== 'not_cancelled' ||
                    $('#filter-loan-status').val() !== 'not_converted' ||
                    $('#filter-date-from').val() || $('#filter-date-to').val() ||
                    ($('#filter-created-by').length && $('#filter-created-by').val());

                if (hasFilters) {
                    $('#empty-state-text').text('Try adjusting your search filters.');
                    $('#empty-state-cta').hide();
                } else {
                    $('#empty-state-text').text('Get started by creating your first quotation.');
                    $('#empty-state-cta').show();
                }
            }

            // --- Filter Buttons ---
            var searchTimer = null;
            $('#filter-search').on('keyup', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    if (table) table.search($('#filter-search').val()).draw();
                    loadMobileCards(true);
                }, 400);
            });

            // Re-draw on datepicker change
            $('.shf-datepicker').on('changeDate clearDate', function() {
                if (table) table.draw();
                loadMobileCards(true);
            });

            $('#btn-filter').on('click', function() {
                if (table) table.search($('#filter-search').val()).draw();
                loadMobileCards(true);
            });

            $('#btn-clear').on('click', function() {
                $('#filter-search').val('');
                $('#filter-type').val('');
                $('#filter-status').val('not_cancelled');
                $('#filter-loan-status').val('not_converted');
                $('#filter-date-from').datepicker('clearDates');
                $('#filter-date-to').datepicker('clearDates');
                $('#filter-created-by').val('');
                if (table) table.search('').draw();
                loadMobileCards(true);
                setTimeout(updateQuotFilterCount, 50);
            });

            function updateQuotFilterCount() {
                var count = 0;
                if ($('#filter-search').val()) count++;
                if ($('#filter-type').val()) count++;
                if ($('#filter-status').val() && $('#filter-status').val() !== 'not_cancelled') count++;
                if ($('#filter-loan-status').val() && $('#filter-loan-status').val() !== 'not_converted') count++;
                if ($('#filter-date-from').val()) count++;
                if ($('#filter-date-to').val()) count++;
                if ($('#filter-created-by').val()) count++;
                var $b = $('#quotFilterCount');
                count > 0 ? $b.text(count).removeClass('shf-collapse-hidden') : $b.addClass('shf-collapse-hidden');
            }
            $(document).on('change',
                '#filter-type, #filter-status, #filter-loan-status, #filter-date-from, #filter-date-to, #filter-created-by',
                updateQuotFilterCount);
            $('#filter-search').on('keyup', updateQuotFilterCount);

            // --- Hold / Cancel: navigate to show page (full modal lives there) ---
            $(document).on('click', '.btn-hold-quotation', function() {
                var id = $(this).data('id');
                window.location.href = '/quotations/' + id + '?action=hold';
            });
            $(document).on('click', '.btn-cancel-quotation', function() {
                var id = $(this).data('id');
                window.location.href = '/quotations/' + id + '?action=cancel';
            });

            // --- Resume: SweetAlert confirm + POST ---
            $(document).on('click', '.btn-resume-quotation', function() {
                var url = $(this).data('url');
                var name = $(this).data('name');
                Swal.fire({
                    title: 'Resume quotation?',
                    text: 'Resume "' + name + '" and move it back to active.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#f15a29',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, resume',
                    cancelButtonText: 'Cancel'
                }).then(function(result) {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: url,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        },
                        success: function(res) {
                            showToast(res.message || 'Quotation resumed.', 'success');
                            if (table) table.draw(false);
                            loadMobileCards(true);
                        },
                        error: function(xhr) {
                            var msg = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) || 'Failed to resume quotation.';
                            showToast(msg, 'error');
                        }
                    });
                });
            });

            // --- AJAX Delete ---
            $(document).on('click', '.btn-delete', function() {
                deleteUrl = $(this).data('url');
                deleteModal.show();
            });

            $('#btn-confirm-delete').on('click', function() {
                if (!deleteUrl) return;
                var $btn = $(this);
                $btn.prop('disabled', true).text('Deleting...');

                $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    success: function(res) {
                        deleteModal.hide();
                        showToast(res.message || 'Quotation deleted.', 'success');
                        table.draw(false);
                        loadMobileCards(true);
                        // Update stats (reload page stats section)
                        updateStats();
                    },
                    error: function(xhr) {
                        deleteModal.hide();
                        var msg = xhr.responseJSON ? xhr.responseJSON.message :
                            'Failed to delete quotation.';
                        showToast(msg, 'error');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('Delete');
                        deleteUrl = null;
                    }
                });
            });

            // --- Mobile Cards ---
            var mobileStart = 0;
            var mobileLength = 10;
            var mobileTotal = 0;
            var mobileLoading = false;

            function loadMobileCards(reset) {
                if (mobileLoading) return;
                if (reset) {
                    mobileStart = 0;
                    $('#mobile-cards-container').empty();
                }
                mobileLoading = true;

                $.ajax({
                    url: dataUrl,
                    data: {
                        draw: 1,
                        start: mobileStart,
                        length: mobileLength,
                        'search[value]': $('#filter-search').val() || '',
                        customer_type: $('#filter-type').val() || '',
                        status: $('#filter-status').val() || 'not_cancelled',
                        loan_status: $('#filter-loan-status').val() || 'not_converted',
                        date_from: getDateValue('#filter-date-from'),
                        date_to: getDateValue('#filter-date-to'),
                        created_by: $('#filter-created-by').val() || ''
                    },
                    success: function(res) {
                        cacheMobileData(res);
                        $('#offline-indicator').remove();
                        showingCached = false;
                        renderMobileCards(res);
                    },
                    error: function() {
                        var cached = getCachedMobileData();
                        if (cached) {
                            showCachedIndicator();
                            renderMobileCards(cached);
                        }
                    },
                    complete: function() {
                        mobileLoading = false;
                    }
                });
            }

            function renderMobileCards(res) {
                mobileTotal = res.recordsFiltered;
                var container = $('#mobile-cards-container');

                if (res.data.length === 0 && mobileStart === 0) {
                    container.html('');
                    return;
                }

                for (var i = 0; i < res.data.length; i++) {
                    container.append(buildMobileCard(res.data[i]));
                }

                mobileStart += res.data.length;

                if (mobileStart < mobileTotal) {
                    $('#mobile-load-more').show();
                } else {
                    $('#mobile-load-more').hide();
                }
            }

            function buildMobileCard(q) {
                var banksHtml = '';
                if (q.banks && q.banks.length > 0) {
                    banksHtml = '<div class="d-flex flex-wrap gap-1 justify-content-end">';
                    var show = q.banks.slice(0, 2);
                    for (var i = 0; i < show.length; i++) {
                        banksHtml += '<span class="shf-tag shf-text-xs" style="padding:2px 6px;">' + $('<span>')
                            .text(show[i]).html() + '</span>';
                    }
                    if (q.banks.length > 2) {
                        banksHtml += '<span class="shf-badge shf-badge-gray shf-text-2xs">+' + (q.banks.length -
                            2) + '</span>';
                    }
                    banksHtml += '</div>';
                }

                var createdByHtml = '';
                if (canViewAll && q.created_by) {
                    createdByHtml = '<div class="shf-text-gray" style="font-size:0.72rem">By ' + $('<span>').text(q
                        .created_by).html() + '</div>';
                }

                var actionsHtml =
                    '<div class="d-flex align-items-center gap-3 pt-2 mt-1 shf-border-top-light">';
                actionsHtml += '<a href="' + q.show_url +
                    '" class="d-flex align-items-center gap-1" style="color:#f15a29;font-size:0.78rem;text-decoration:none;">' +
                    '<svg class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>' +
                    '</svg>View</a>';

                if (q.download_branded_url) {
                    actionsHtml += '<a href="' + q.download_branded_url +
                        '" class="d-flex align-items-center gap-1" style="color:#27ae60;font-size:0.78rem;text-decoration:none;">' +
                        '<svg class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>' +
                        '</svg>Branded</a>';
                }
                if (q.download_plain_url) {
                    actionsHtml += '<a href="' + q.download_plain_url +
                        '" class="d-flex align-items-center gap-1" style="color:#6b7280;font-size:0.78rem;text-decoration:none;">' +
                        '<svg class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>' +
                        '</svg>Plain</a>';
                }

                if (q.convert_url) {
                    actionsHtml += '<a href="' + q.convert_url +
                        '" class="d-flex align-items-center gap-1" style="color:#2563eb;font-size:0.78rem;text-decoration:none;">' +
                        '<svg class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>' +
                        '</svg>Convert</a>';
                } else if (q.loan_url) {
                    actionsHtml += '<a href="' + q.loan_url +
                        '" class="d-flex align-items-center gap-1" style="color:#16a34a;font-size:0.78rem;text-decoration:none;">' +
                        '<svg class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>' +
                        '</svg>Loan</a>';
                }

                if (q.hold_url) {
                    actionsHtml += '<button type="button" class="btn btn-link p-0 d-flex align-items-center gap-1 btn-hold-quotation" data-id="' +
                        q.id + '" data-name="' + $('<span>').text(q.customer_name).html() +
                        '" style="color:#d97706;font-size:0.78rem;text-decoration:none;">' +
                        '<svg class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>' +
                        '</svg>Hold</button>';
                }
                if (q.resume_url) {
                    actionsHtml += '<button type="button" class="btn btn-link p-0 d-flex align-items-center gap-1 btn-resume-quotation" data-url="' +
                        q.resume_url + '" data-name="' + $('<span>').text(q.customer_name).html() +
                        '" style="color:#16a34a;font-size:0.78rem;text-decoration:none;">' +
                        '<svg class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>' +
                        '</svg>Resume</button>';
                }
                if (q.cancel_url) {
                    actionsHtml += '<button type="button" class="btn btn-link p-0 d-flex align-items-center gap-1 btn-cancel-quotation" data-id="' +
                        q.id + '" data-name="' + $('<span>').text(q.customer_name).html() +
                        '" style="color:#c0392b;font-size:0.78rem;text-decoration:none;">' +
                        '<svg class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>' +
                        '</svg>Cancel</button>';
                }
                if (q.delete_url) {
                    actionsHtml +=
                        '<button type="button" class="btn btn-link p-0 d-flex align-items-center gap-1 ms-auto btn-delete" data-url="' +
                        q.delete_url + '" style="color:#c0392b;font-size:0.78rem;text-decoration:none;">' +
                        '<svg class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>' +
                        '</svg>Delete</button>';
                }
                actionsHtml += '</div>';

                var locLine = q.location_name ? '<div class="location-info shf-text-2xs">' + $('<span>')
                    .text(q.location_name).html() + '</div>' : '';

                var statusLine = '';
                if (q.status_html) {
                    statusLine = '<div class="mt-1">' + q.status_html;
                    if (q.is_on_hold && q.hold_follow_up_date) {
                        statusLine += ' <span class="shf-text-2xs shf-text-gray-light">Follow-up: ' +
                            $('<span>').text(q.hold_follow_up_date).html() + '</span>';
                    }
                    statusLine += '</div>';
                }

                return '<div class="shf-card mb-3 p-3">' +
                    '<div class="d-flex align-items-start justify-content-between mb-2">' +
                    '<div>' +
                    '<div class="fw-semibold shf-text-base">' + $('<span>').text(q.customer_name)
                    .html() + '</div>' +
                    locLine +
                    '<div class="mt-1"><span class="shf-badge ' + q.type_badge_class + '">' + q.type_label +
                    '</span></div>' +
                    statusLine +
                    '</div>' +
                    '<span class="shf-text-gray" style="font-size:0.72rem">#' + q.id + '</span>' +
                    '</div>' +
                    '<div class="d-flex align-items-center justify-content-between py-2 shf-border-top-light">' +
                    '<div>' +
                    '<div class="fw-semibold shf-text-sm">' + $('<span>').text(q.formatted_amount)
                    .html() + '</div>' +
                    '<div class="shf-text-gray" style="font-size:0.72rem">' + q.date + '</div>' +
                    '</div>' +
                    '<div class="d-flex align-items-center gap-2">' + banksHtml + '</div>' +
                    '</div>' +
                    createdByHtml +
                    actionsHtml +
                    '</div>';
            }

            $('#btn-load-more').on('click', function() {
                loadMobileCards(false);
            });

            // Initial mobile load (cache handles offline)
            loadMobileCards(true);

            // --- Stats Update After Delete ---
            function updateStats() {
                $.get(window.location.href, function(html) {
                    var $html = $(html);
                    var newStats = $html.find('.shf-stat-value');
                    $('.shf-stat-value').each(function(i) {
                        $(this).text($(newStats[i]).text());
                    });
                });
            }

            // --- Toast Helper ---
            function showToast(message, type) {
                type = type || 'success';
                var iconColor = type === 'success' ? '#4ade80' : (type === 'error' ? '#f87171' : '#facc15');
                var iconPath = type === 'success' ?
                    'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' :
                    'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';

                var $toast = $(
                    '<div class="shf-toast-wrapper">' +
                    '<div class="shf-toast ' + type + '">' +
                    '<svg style="width:16px;height:16px;color:' + iconColor +
                    ';flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="' + iconPath +
                    '"/>' +
                    '</svg>' +
                    '<span>' + $('<span>').text(message).html() + '</span>' +
                    '<button type="button" class="shf-toast-close shf-tab-close">' +
                    '<svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>' +
                    '</svg>' +
                    '</button>' +
                    '</div>' +
                    '</div>'
                );

                $('body').append($toast);
                $toast.find('.shf-toast-close').on('click', function() {
                    $toast.find('.shf-toast').css('animation', 'toastOut 0.3s ease forwards');
                    setTimeout(function() {
                        $toast.remove();
                    }, 300);
                });
                setTimeout(function() {
                    $toast.find('.shf-toast').css('animation', 'toastOut 0.3s ease forwards');
                    setTimeout(function() {
                        $toast.remove();
                    }, 300);
                }, 5000);
            }
        });
    </script>
    <script>
        function clearDashLoanLink() {
            $('#dashTaskLoanId').val('');
            $('#dashTaskLoanSearch').val('').show();
            $('#dashTaskLoanChip').addClass('d-none');
        }

        function editTaskDash(editData) {
            $('#dashTaskForm').attr('action', editData.update_url);
            $('#dashTaskFormMethod').val('PUT');
            $('#dashTaskModalTitle').text('Edit Task / ટાસ્ક સુધારો');
            $('#dashTaskForm').find('.shf-client-error').remove();
            $('#dashTaskForm').find('.is-invalid').removeClass('is-invalid');
            $('#dashTaskTitle').val(editData.title);
            $('#dashTaskForm textarea[name="description"]').val(editData.description || '');
            $('#dashTaskForm select[name="assigned_to"]').val(editData.assigned_to || '');
            $('#dashTaskForm select[name="priority"]').val(editData.priority);
            $('#dashTaskDueDate').val(editData.due_date_formatted || '');
            if (editData.loan_detail_id) {
                $('#dashTaskLoanId').val(editData.loan_detail_id);
                $('#dashTaskLoanSearch').hide();
                $('#dashTaskLoanChipText').text(editData.loan_label);
                $('#dashTaskLoanChip').removeClass('d-none');
            } else {
                clearDashLoanLink();
            }
            $('#dashCreateTaskModal').modal('show');
        }

        function deleteTaskDash(id) {
            Swal.fire({
                title: 'Delete Task?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '/general-tasks/' + id;
                    form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                        '<input type="hidden" name="_method" value="DELETE">';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>

    {{-- Push Notification Prompt: auto-open on every dashboard visit unless already subscribed --}}
    <script>
        (function () {
            if (!window.SHFPush || typeof SHFPush.supported !== 'function' || !SHFPush.supported()) {
                return;
            }
            var modalEl = document.getElementById('pushPromptModal');
            if (!modalEl) {
                return;
            }

            var enableState = document.getElementById('pushPromptEnableState');
            var blockedState = document.getElementById('pushPromptBlockedState');
            var title = document.getElementById('pushPromptModalTitle');
            var enableBtn = document.getElementById('pushPromptEnableBtn');
            var modal = null;
            var enableLabel = enableBtn.textContent;

            var showBlockedMode = function () {
                enableState.style.display = 'none';
                blockedState.style.display = '';
                title.textContent = 'Notifications Blocked / નોટિફિકેશન બ્લોક છે';
                enableBtn.style.display = 'none';
            };

            var showEnableMode = function () {
                enableState.style.display = '';
                blockedState.style.display = 'none';
                title.textContent = 'Enable Notifications';
                enableBtn.style.display = '';
            };

            enableBtn.addEventListener('click', function () {
                enableBtn.disabled = true;
                enableBtn.textContent = 'Enabling…';
                SHFPush.enable().then(function () {
                    if (modal) { modal.hide(); }
                }).catch(function (err) {
                    SHFPush.status().then(function (s) {
                        if (s.permission === 'denied') {
                            showBlockedMode();
                        } else {
                            alert(err && err.message ? err.message : 'Failed to enable notifications.');
                        }
                    });
                }).finally(function () {
                    enableBtn.disabled = false;
                    enableBtn.textContent = enableLabel;
                });
            });

            SHFPush.status().then(function (s) {
                if (s.subscribed) { return; }
                if (s.permission === 'denied') {
                    showBlockedMode();
                } else {
                    showEnableMode();
                }
                modal = new bootstrap.Modal(modalEl);
                modal.show();
            }).catch(function () {
                // If status check fails, fall back to enable mode — user can still try
                showEnableMode();
                modal = new bootstrap.Modal(modalEl);
                modal.show();
            });
        })();
    </script>
@endpush
