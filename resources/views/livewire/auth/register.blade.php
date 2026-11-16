<x-layouts::auth :title="__('Sign up')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Sign up')" :description="__('Find anything at home, together.')" />

        <!-- Session Status -->
        <x-auth-session-status :status="session('status')" />

        <x-auth.card>

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-4">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email')"
                :description:trailing="__('Use the email you were invited with, if someone invited you.')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Sign up') }}
                </flux:button>
            </div>
        </form>
        </x-auth.card>

        <x-auth.footer>
            {{ __('Already have an account?') }}
            <x-auth.link :href="route('login')">{{ __('Log in') }}</x-auth.link>
        </x-auth.footer>
    </div>
</x-layouts::auth>
