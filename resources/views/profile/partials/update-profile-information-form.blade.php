<section>
    <header>
        <p class="small" style="color: #6b7280;">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-4">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="shf-form-label">{{ __('Name') }}</label>
            <input id="name" name="name" type="text" class="shf-input w-100 mt-1" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @if ($errors->has('name'))
                <ul class="list-unstyled mt-1 mb-0 small" style="color: #c0392b;">
                    @foreach ($errors->get('name') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="mb-3">
            <label for="email" class="shf-form-label">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" class="shf-input w-100 mt-1" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @if ($errors->has('email'))
                <ul class="list-unstyled mt-1 mb-0 small" style="color: #c0392b;">
                    @foreach ($errors->get('email') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="small mt-2" style="color: #374151;">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="btn btn-link p-0 small text-decoration-underline" style="color: #f15a29;">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 small fw-medium" style="color: #27ae60;">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn-accent"><svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> {{ __('Save') }}</button>

            @if (session('status') === 'profile-updated')
                <p class="small fw-medium shf-saved-msg mb-0" style="color: #27ae60;">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
