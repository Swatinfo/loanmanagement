@extends('layouts.guest')

@section('content')
    <div class="mb-3 small" style="color: #6b7280;">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="shf-form-label">{{ __('Password') }}</label>
            <input id="password" class="shf-input w-100" type="password" name="password" required autocomplete="current-password">
            @if ($errors->has('password'))
                <ul class="list-unstyled mt-1 mb-0 small" style="color: #c0392b;">
                    @foreach ($errors->get('password') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn-accent">
                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                {{ __('Confirm') }}
            </button>
        </div>
    </form>
@endsection
