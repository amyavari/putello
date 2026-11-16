<x-layouts::auth :title="__('Two-factor authentication')">
    <div class="flex flex-col gap-6">
        <div
            class="relative w-full h-auto"
            x-cloak
            x-data="{
                showRecoveryInput: @js($errors->has('recovery_code')),
                code: '',
                recovery_code: '',
                focusOtp() {
                    this.$nextTick(() => this.$refs.otp?.querySelector('input')?.focus());
                },
                init() {
                    if (! this.showRecoveryInput) {
                        this.focusOtp();
                    }
                },
                toggleInput() {
                    this.showRecoveryInput = !this.showRecoveryInput;

                    this.code = '';
                    this.recovery_code = '';

                    $nextTick(() => {
                        this.showRecoveryInput
                            ? this.$refs.recovery_code?.focus()
                            : this.focusOtp();
                    });
                },
            }"
        >
            <div x-show="!showRecoveryInput">
                <x-auth-header
                    :title="__('Authentication code')"
                    :description="__('Enter the authentication code provided by your authenticator application.')"
                />
            </div>

            <div x-show="showRecoveryInput">
                <x-auth-header
                    :title="__('Recovery code')"
                    :description="__('Please confirm access to your account by entering one of your emergency recovery codes.')"
                />
            </div>

            <form method="POST" action="{{ route('two-factor.login.store') }}" class="space-y-6">
                @csrf

                <x-auth.card class="space-y-5">
                    <div x-show="!showRecoveryInput">
                        <div x-ref="otp">
                            <flux:otp
                                x-model="code"
                                length="6"
                                name="code"
                                label="OTP Code"
                                label:sr-only
                             />
                        </div>
                    </div>

                    <div x-show="showRecoveryInput">
                        <div>
                            <flux:input
                                type="text"
                                name="recovery_code"
                                x-ref="recovery_code"
                                x-bind:required="showRecoveryInput"
                                autocomplete="one-time-code"
                                x-model="recovery_code"
                            />
                        </div>

                        @error('recovery_code')
                            <p class="mt-2 text-sm text-danger-ink">{{ $message }}</p>
                        @enderror
                    </div>

                    <flux:button
                        variant="primary"
                        type="submit"
                        class="w-full"
                    >
                        {{ __('Continue') }}
                    </flux:button>
                </x-auth.card>

                <x-auth.footer>
                    {{ __('or you can') }}
                    <button type="button" x-show="!showRecoveryInput" @click="toggleInput()" class="cursor-pointer text-accent underline underline-offset-2 hover:text-accent-hover">{{ __('login using a recovery code') }}</button>
                    <button type="button" x-show="showRecoveryInput" @click="toggleInput()" class="cursor-pointer text-accent underline underline-offset-2 hover:text-accent-hover">{{ __('login using an authentication code') }}</button>
                </x-auth.footer>
            </form>
        </div>
    </div>
</x-layouts::auth>
