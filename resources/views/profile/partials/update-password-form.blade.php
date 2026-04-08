<section>
    <header>
        <p class="small" style="color: #6b7280;">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-4">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password" class="shf-form-label">{{ __('Current Password') }}</label>
            <div class="position-relative mt-1">
                <input id="update_password_current_password" name="current_password" type="password" class="shf-input w-100" style="padding-right: 2.5rem;" autocomplete="current-password">
                <button type="button" class="shf-password-toggle" data-target="update_password_current_password" tabindex="-1" style="position:absolute;top:0;right:0;bottom:0;display:flex;align-items:center;padding-right:12px;background:none;border:none;color:#9ca3af;cursor:pointer;">
                    <svg class="shf-eye-open" style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg class="shf-eye-closed" style="width:20px;height:20px;display:none;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
            @if ($errors->updatePassword->has('current_password'))
                <ul class="list-unstyled mt-1 mb-0 small" style="color: #c0392b;">
                    @foreach ($errors->updatePassword->get('current_password') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="shf-form-label">{{ __('New Password') }}</label>
            <div class="position-relative mt-1">
                <input id="update_password_password" name="password" type="password" class="shf-input w-100" style="padding-right: 2.5rem;" autocomplete="new-password">
                <button type="button" class="shf-password-toggle" data-target="update_password_password" tabindex="-1" style="position:absolute;top:0;right:0;bottom:0;display:flex;align-items:center;padding-right:12px;background:none;border:none;color:#9ca3af;cursor:pointer;">
                    <svg class="shf-eye-open" style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg class="shf-eye-closed" style="width:20px;height:20px;display:none;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
            @if ($errors->updatePassword->has('password'))
                <ul class="list-unstyled mt-1 mb-0 small" style="color: #c0392b;">
                    @foreach ($errors->updatePassword->get('password') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="mb-3">
            <label for="update_password_password_confirmation" class="shf-form-label">{{ __('Confirm Password') }}</label>
            <div class="position-relative mt-1">
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="shf-input w-100" style="padding-right: 2.5rem;" autocomplete="new-password">
                <button type="button" class="shf-password-toggle" data-target="update_password_password_confirmation" tabindex="-1" style="position:absolute;top:0;right:0;bottom:0;display:flex;align-items:center;padding-right:12px;background:none;border:none;color:#9ca3af;cursor:pointer;">
                    <svg class="shf-eye-open" style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg class="shf-eye-closed" style="width:20px;height:20px;display:none;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
            @if ($errors->updatePassword->has('password_confirmation'))
                <ul class="list-unstyled mt-1 mb-0 small" style="color: #c0392b;">
                    @foreach ($errors->updatePassword->get('password_confirmation') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn-accent"><svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> {{ __('Save') }}</button>

            @if (session('status') === 'password-updated')
                <p class="small fw-medium shf-saved-msg mb-0" style="color: #27ae60;">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
