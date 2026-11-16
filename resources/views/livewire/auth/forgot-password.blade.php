<x-layouts::auth :title="__('Forgot password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Forgot your password?')" :description="__('Enter your email and we’ll send a link to choose a new one.')" />

        <!-- Session Status -->
        <x-auth-session-status :status="session('status')" />

        <x-auth.card>

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email')"
                type="email"
                required
                autofocus
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="email-password-reset-link-button">
                {{ __('Email me a reset link') }}
            </flux:button>
        </form>
        </x-auth.card>

        <x-auth.link :href="route('login')" class="self-start">{{ __('Back to log in') }}</x-auth.link>
    </div>
</x-layouts::auth>
