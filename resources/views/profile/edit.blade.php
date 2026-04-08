@extends('layouts.app')

@section('header')
    <h2 class="font-display fw-semibold text-white" style="font-size: 1.25rem; line-height: 1.75rem; margin: 0;">
        <svg style="width:16px;height:16px;display:inline;margin-right:6px;color:rgba(255,255,255,0.85);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        {{ __('Profile') }}
    </h2>
@endsection

@section('content')
    <div class="py-4">
        <div class="px-3 px-sm-4 px-lg-5">
            <div class="shf-section mb-4">
                <div class="shf-section-header">
                    <div class="shf-section-number">1</div>
                    <span class="shf-section-title">Profile Information</span>
                </div>
                <div class="shf-section-body">
                    <div style="max-width: 36rem;">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            </div>

            <div class="shf-section mb-4">
                <div class="shf-section-header">
                    <div class="shf-section-number">2</div>
                    <span class="shf-section-title">Update Password</span>
                </div>
                <div class="shf-section-body">
                    <div style="max-width: 36rem;">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>

            @if(auth()->user()->role === 'super_admin')
            <div class="shf-section mb-4">
                <div class="shf-section-header">
                    <div class="shf-section-number">
                        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <span class="shf-section-title">Danger Zone</span>
                </div>
                <div class="shf-section-body">
                    <div style="max-width: 36rem;">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection
