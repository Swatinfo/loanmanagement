@extends('layouts.guest')

@section('content')
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="mb-3">
            <label for="name" class="shf-form-label">{{ __('Name') }}</label>
            <input id="name" class="shf-input w-100" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
            @if ($errors->has('name'))
                <ul class="list-unstyled mt-1 mb-0 small" style="color: #c0392b;">
                    @foreach ($errors->get('name') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="shf-form-label">{{ __('Email') }}</label>
            <input id="email" class="shf-input w-100" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
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
            <a class="small fw-medium" style="color: #f15a29;" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <button type="submit" class="btn-accent ms-4">
                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                {{ __('Register') }}
            </button>
        </div>
    </form>
@endsection
