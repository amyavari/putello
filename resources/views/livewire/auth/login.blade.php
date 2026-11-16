<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Log in')" />

        <!-- Session Status -->
        <x-auth-session-status :status="session('status')" />

        <x-passkey-verify />

        <x-auth.card>

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-4">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
            />

            <!-- Password -->
            <div>
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    viewable
                />

            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Keep me logged in')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Log in') }}
                </flux:button>
            </div>

            @if (Route::has('password.request'))
                <x-auth.link :href="route('password.request')" class="self-start">{{ __('Forgot your password?') }}</x-auth.link>
            @endif
        </form>
        </x-auth.card>

        @if (Route::has('register'))
            <x-auth.footer>
                {{ __('No account yet?') }}
                <x-auth.link :href="route('register')">{{ __('Sign up') }}</x-auth.link>
            </x-auth.footer>
        @endif
    </div>
</x-layouts::auth>
