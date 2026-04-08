@extends('layouts.guest')

@section('content')
    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-3 small fw-medium" style="color: #27ae60;">
            {{ session('status') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-3 small rounded p-3" style="color: #dc2626; background: #fef2f2; border: 1px solid #fca5a5;">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="shf-form-label">{{ __('Email') }}</label>
            <input id="email" class="shf-input w-100" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
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
            <div class="position-relative">
                <input id="password" class="shf-input w-100" type="password" name="password" required autocomplete="current-password" style="padding-right: 2.5rem;">
                <button type="button" onclick="var p=document.getElementById('password'),i=this.querySelector('svg');if(p.type==='password'){p.type='text';i.innerHTML='<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l18 18\'/>';}else{p.type='password';i.innerHTML='<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15 12a3 3 0 11-6 0 3 3 0 016 0z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z\'/>';}" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;padding:4px;cursor:pointer;color:#9ca3af;" title="Toggle password visibility">
                    <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>
            </div>
            @if ($errors->has('password'))
                <ul class="list-unstyled mt-1 mb-0 small" style="color: #c0392b;">
                    @foreach ($errors->get('password') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <!-- Remember Me -->
        <div class="mb-3">
            <label for="remember_me" class="d-inline-flex align-items-center">
                <input id="remember_me" type="checkbox" class="shf-checkbox" name="remember" style="width:16px;height:16px;">
                <span class="ms-2 small" style="color: #6b7280;">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="d-flex align-items-center justify-content-end mt-4">
            <button type="submit" class="btn-accent" style="padding-left: 2rem; padding-right: 2rem;">
                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                {{ __('Log in') }}
            </button>
        </div>
    </form>
@endsection
