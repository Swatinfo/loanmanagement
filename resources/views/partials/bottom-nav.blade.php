{{-- Mobile bottom navigation — visible below xl (< 1200px). Matches the existing
     navbar-expand-xl breakpoint. Included from layouts/app.blade.php only when
     body.has-bottom-nav is set (i.e. not on loan deep-workflow pages). --}}

@php
    $user = auth()->user();
    $primaries = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'url' => route('dashboard'), 'active' => request()->routeIs('dashboard'), 'show' => true],
        ['key' => 'loans', 'label' => 'Loans', 'url' => $user->hasPermission('view_loans') ? route('loans.index') : '#', 'active' => request()->routeIs('loans.*'), 'show' => $user->hasPermission('view_loans')],
        ['key' => 'dvr', 'label' => 'DVR', 'url' => $user->hasPermission('view_dvr') ? route('dvr.index') : '#', 'active' => request()->routeIs('dvr.*'), 'show' => $user->hasPermission('view_dvr')],
        ['key' => 'tasks', 'label' => 'Tasks', 'url' => route('general-tasks.index'), 'active' => request()->routeIs('general-tasks.*'), 'show' => true],
    ];
    $primaries = array_values(array_filter($primaries, fn ($p) => $p['show']));
    $moreActive = request()->routeIs('quotations.*', 'customers.*', 'users.*', 'settings.*', 'permissions.*', 'loan-settings.*', 'activity-log*', 'reports.*', 'profile.*', 'notifications.*', 'roles.*');
@endphp

@if (count($primaries) >= 2)
    <nav class="shf-bottom-nav d-xl-none" aria-label="Main navigation">
        @foreach ($primaries as $item)
            <a href="{{ $item['url'] }}"
                class="shf-bottom-nav-item {{ $item['active'] ? 'shf-bottom-nav-active' : '' }}"
                aria-label="{{ $item['label'] }}"
                @if ($item['active']) aria-current="page" @endif>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    @switch($item['key'])
                        @case('dashboard')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        @break

                        @case('loans')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        @break

                        @case('dvr')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        @break

                        @case('tasks')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        @break
                    @endswitch
                </svg>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach

        {{-- More: offcanvas with remaining modules --}}
        <button type="button"
            class="shf-bottom-nav-item {{ $moreActive ? 'shf-bottom-nav-active' : '' }}"
            data-bs-toggle="offcanvas" data-bs-target="#shfMoreOffcanvas"
            aria-controls="shfMoreOffcanvas" aria-label="More menu"
            style="background: none; border-left: none; border-right: none; border-bottom: none;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <span>More</span>
        </button>
    </nav>

    {{-- More offcanvas (bottom sheet on mobile) --}}
    <div class="offcanvas offcanvas-bottom" tabindex="-1" id="shfMoreOffcanvas"
        aria-labelledby="shfMoreOffcanvasLabel" style="height: auto; max-height: 75vh; border-radius: 16px 16px 0 0;">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title font-display" id="shfMoreOffcanvasLabel">More</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body pt-0 pb-4">
            <div class="list-group list-group-flush">
                @if ($user->hasPermission('create_quotation') || $user->hasPermission('view_own_quotations') || $user->hasPermission('view_all_quotations'))
                    <a href="{{ route('quotations.index') }}" class="list-group-item list-group-item-action">Quotations</a>
                @endif
                @if ($user->hasPermission('view_customers'))
                    <a href="{{ route('customers.index') }}" class="list-group-item list-group-item-action">Customers</a>
                @endif
                @if ($user->hasPermission('view_users'))
                    <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action">Users</a>
                @endif
                <a href="{{ route('notifications.index') }}" class="list-group-item list-group-item-action">Notifications</a>
                @if ($user->hasPermission('view_settings'))
                    <a href="{{ route('settings.index') }}" class="list-group-item list-group-item-action">Quotation Settings</a>
                @endif
                @if ($user->hasPermission('manage_workflow_config'))
                    <a href="{{ route('loan-settings.index') }}" class="list-group-item list-group-item-action">Loan Settings</a>
                @endif
                @if ($user->hasPermission('manage_permissions'))
                    <a href="{{ route('permissions.index') }}" class="list-group-item list-group-item-action">Permissions</a>
                    <a href="{{ route('roles.index') }}" class="list-group-item list-group-item-action">Roles</a>
                @endif
                @if ($user->hasPermission('view_activity_log'))
                    <a href="{{ route('activity-log') }}" class="list-group-item list-group-item-action">Activity Log</a>
                @endif
                @if ($user->hasPermission('view_reports'))
                    <a href="{{ route('reports.turnaround') }}" class="list-group-item list-group-item-action">Reports</a>
                @endif
                <a href="{{ route('profile.edit') }}" class="list-group-item list-group-item-action">Profile</a>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="list-group-item list-group-item-action text-danger w-100 text-start">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
@endif
