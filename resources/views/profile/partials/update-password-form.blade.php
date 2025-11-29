<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div class="col-md-12">
            <label for="update_password_current_password">{{__('Current Password')}}</label>
            <input id="update_password_current_password" name="current_password" type="password" class="mt-1 form-control" autocomplete="current-password" />
            {{-- <span class="mt-2 text-danger">{{ $errors->updatePassword->get('current_password') }}</span> --}}
        </div>

        <div class="col-md-12">
            <label for="update_password_password">{{__('New Password')}}</label>
            <input id="update_password_password" name="password" type="password" class="mt-1 form-control" autocomplete="new-password" />
            {{-- <span class="mt-2 text-danger" >{{ $errors->updatePassword->get('password') }}</span> --}}
        </div>

        <div class="col-md-12">
            <label for="update_password_password_confirmation">{{ __('Confirm Password') }}</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 form-control" autocomplete="new-password" />
            {{-- <span class="mt-2 text-danger">{{ $errors->updatePassword->get('password_confirmation') }}</span> --}}
        </div>

        <div class="col-md-12">
            <button class="btn btn-success my-2">{{ __('Save') }}</button>

            @if (session('status') === 'password-updated')
                <p
                    {{-- x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)" --}}
                    class="text-sm text-success"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
