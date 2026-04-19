@extends('layouts.app')
@section('title', 'Permissions — SHF')

@section('header')
    <div class="d-flex align-items-center justify-content-between">
        <h2 class="font-display fw-semibold text-white shf-page-title">
            <svg class="shf-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            Permission Management
        </h2>
        <a href="{{ route('settings.index') }}" class="btn-accent-outline btn-accent-sm btn-accent-outline-white">
            <svg class="shf-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Settings
        </a>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <div class="px-3 px-sm-4 px-lg-5">
            <div class="shf-section">
                <div class="shf-section-header">
                    <div class="shf-section-number">
                        <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <span class="shf-section-title">Role × Permission Matrix</span>
                </div>
                <div class="shf-section-body">
                    <p class="small mb-2 shf-text-gray">
                        Configure which permissions each role has by default. Super Admin always has all permissions.
                    </p>
                    <p class="small mb-4 shf-text-gray">
                        <svg style="width:14px;height:14px;display:inline;vertical-align:-2px;margin-right:4px;"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Loan permissions are managed separately in <a
                            href="{{ route('loan-settings.index') }}#role-permissions"
                            style="color: var(--accent); font-weight: 600;">Loan Settings → Role Permissions</a>
                    </p>

                    <form method="POST" action="{{ route('permissions.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="table-responsive shf-sticky-thead">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Permission</th>
                                        @foreach ($roles as $role)
                                            <th class="text-center">
                                                {{ $role->name }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($permissions as $group => $perms)
                                        <tr class="bg-accent-dim">
                                            <td colspan="{{ count($roles) + 1 }}"
                                                class="font-display fw-semibold small shf-settings-tab-header">
                                                {{ $group }}
                                            </td>
                                        </tr>
                                        @foreach ($perms as $perm)
                                            <tr>
                                                <td>
                                                    <span class="fw-medium">{{ $perm->name }}</span>
                                                    @if ($perm->description)
                                                        <span
                                                            class="d-block small shf-text-gray-light">{{ $perm->description }}</span>
                                                    @endif
                                                </td>
                                                @foreach ($roles as $role)
                                                    <td class="text-center">
                                                        <input type="checkbox" name="role[{{ $role->slug }}][]"
                                                            value="{{ $perm->id }}"
                                                            {{ in_array($perm->id, $rolePermissions[$role->slug] ?? []) ? 'checked' : '' }}
                                                            class="shf-checkbox shf-icon-md">
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end gap-3 mt-4">
                            <a href="{{ route('settings.index') }}" class="btn-accent-outline"><svg class="shf-icon-md"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg> Cancel</a>
                            <button type="submit" class="btn-accent">
                                <svg class="shf-icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Save Permissions
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
