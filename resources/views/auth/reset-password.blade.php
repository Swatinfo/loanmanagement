@extends('layouts.guest')

@section('content')
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="shf-form-label">{{ __('Email') }}</label>
            <input id="email" class="shf-input w-100" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
            @if ($errors->has('email'))
                <ul class="list-unstyled mt-1 mb-0 small" style="color: #c0392b;">
                    @foreach ($errors->get('email') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="shf-form-label">{{ __('Password') }}</label>
            <input id="password" class="shf-input w-100" type="password" name="password" required autocomplete="new-password">
            @if ($errors->has('password'))
                <ul class="list-unstyled mt-1 mb-0 small" style="color: #c0392b;">
                    @foreach ($errors->get('password') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <!-- Confirm Password -->
        <div class="mb-3">
            <label for="password_confirmation" class="shf-form-label">{{ __('Confirm Password') }}</label>
            <input id="password_confirmation" class="shf-input w-100" type="password" name="password_confirmation" required autocomplete="new-password">
            @if ($errors->has('password_confirmation'))
                <ul class="list-unstyled mt-1 mb-0 small" style="color: #c0392b;">
                    @foreach ($errors->get('password_confirmation') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="d-flex align-items-center justify-content-end mt-4">
            <button type="submit" class="btn-accent">
                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                {{ __('Reset Password') }}
            </button>
        </div>
    </form>
@endsection
