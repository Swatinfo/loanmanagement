<section>
    <header>
        <p class="small" style="color: #6b7280;">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button type="button" class="btn-accent mt-3" style="background: linear-gradient(135deg, #c0392b, #e74c3c);" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        {{ __('Delete Account') }}
    </button>

    <!-- Bootstrap Modal for Delete Confirmation -->
    <div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionLabel" aria-hidden="true"
         @if($errors->userDeletion->isNotEmpty()) data-bs-show-on-load="true" @endif>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="modal-body p-4">
                        <h2 class="font-display fw-semibold" style="font-size: 1.125rem; color: #111827;">
                            {{ __('Are you sure you want to delete your account?') }}
                        </h2>

                        <p class="mt-2 small" style="color: #6b7280;">
                            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                        </p>

                        <div class="mt-3">
                            <label for="delete_password" class="visually-hidden">{{ __('Password') }}</label>
                            <input id="delete_password" name="password" type="password" class="shf-input" style="width: 75%;" placeholder="{{ __('Password') }}">
                            @if ($errors->userDeletion->has('password'))
                                <ul class="list-unstyled mt-1 mb-0 small" style="color: #c0392b;">
                                    @foreach ($errors->userDeletion->get('password') as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>

                    <div class="modal-footer border-0 px-4 pb-4 pt-0">
                        <button type="button" class="btn-accent-outline" data-bs-dismiss="modal">
                            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn-accent ms-2" style="background: linear-gradient(135deg, #c0392b, #e74c3c);">
                            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            {{ __('Delete Account') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
