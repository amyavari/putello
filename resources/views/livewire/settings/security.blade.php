<div>
    <x-settings.layout>
        <x-settings.section :heading="__('Password')">
            <form method="POST" wire:submit="updatePassword" class="space-y-4">
                <flux:input
                    wire:model="current_password"
                    :label="__('Current password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    viewable
                />
                <flux:input
                    wire:model="password"
                    :label="__('New password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                    viewable
                />
                <flux:input
                    wire:model="password_confirmation"
                    :label="__('Confirm password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                    viewable
                />

                <x-button.secondary type="submit" data-test="update-password-button">{{ __('Update password') }}</x-button.secondary>
            </form>
        </x-settings.section>

        @if ($canManageTwoFactor)
            <x-settings.section :heading="__('Two-factor authentication')" :description="__('Manage your two-factor authentication settings')" wire:cloak>
                @if ($twoFactorEnabled)
                    <div class="space-y-4">
                        <p>{{ __('You will be prompted for a secure, random pin during login, which you can retrieve from the TOTP-supported application on your phone.') }}</p>

                        <x-button.danger-outline wire:click="disable">{{ __('Disable 2FA') }}</x-button.danger-outline>
                    </div>

                    <livewire:settings.two-factor.recovery-codes :$requiresConfirmation />
                @else
                    <div class="space-y-4">
                        <p class="text-ink-muted">
                            {{ __('When you enable two-factor authentication, you will be prompted for a secure pin during login. This pin can be retrieved from a TOTP-supported application on your phone.') }}
                        </p>

                        <flux:button variant="primary" wire:click="enable">{{ __('Enable 2FA') }}</flux:button>
                    </div>
                @endif
            </x-settings.section>

            <flux:modal name="two-factor-setup-modal" class="w-full md:w-[30rem]" @close="closeModal" wire:model="showModal">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg" class="font-semibold">{{ $this->modalConfig['title'] }}</flux:heading>
                        <p class="mt-1 text-sm text-ink-muted">{{ $this->modalConfig['description'] }}</p>
                    </div>

                    @if ($showVerificationStep)
                        <div x-data x-init="$nextTick(() => $el.querySelector('input')?.focus())">
                            <flux:otp name="code" wire:model="code" length="6" label="OTP Code" label:sr-only />
                        </div>

                        <div class="flex gap-3 max-md:*:first:flex-7 max-md:*:last:flex-3">
                            <flux:button variant="primary" wire:click="confirmTwoFactor" x-bind:disabled="$wire.code.length < 6">
                                {{ __('Confirm') }}
                            </flux:button>

                            <flux:button variant="ghost" wire:click="resetVerification">{{ __('Back') }}</flux:button>
                        </div>
                    @else
                        @error('setupData')
                            <flux:callout variant="danger" icon="x-circle" heading="{{ $message }}" />
                        @enderror

                        <x-two-factor.qr-code :svg="$qrCodeSvg" />

                        <flux:button :disabled="$errors->has('setupData')" variant="primary" class="w-full" wire:click="showVerificationIfNecessary">
                            {{ $this->modalConfig['buttonText'] }}
                        </flux:button>

                        <x-two-factor.manual-key :key="$manualSetupKey" />
                    @endif
                </div>
            </flux:modal>
        @endif

        @if ($canManagePasskeys)
            <section wire:cloak>
                <x-page.section-heading>{{ __('Passkeys') }}</x-page.section-heading>
                <p class="text-sm text-ink-muted">{{ __('Manage your passkeys for passwordless sign-in') }}</p>

                @if (count($passkeys) === 0)
                    <flux:card class="mt-2 py-8 text-center">
                        <p class="font-semibold">{{ __('No passkeys yet') }}</p>
                        <p class="mt-1 text-sm text-ink-muted">{{ __('Add a passkey to sign in without a password') }}</p>
                    </flux:card>
                @else
                    <x-list.card class="mt-2">
                        @foreach ($passkeys as $passkey)
                            <x-passkeys.row :$passkey wire:key="passkey-{{ $passkey['id'] }}" />
                        @endforeach
                    </x-list.card>
                @endif

                <div class="mt-4">
                    <x-passkey-registration />
                </div>
            </section>
        @endif
    </x-settings.layout>

    <flux:modal name="delete-passkey-modal" class="w-full md:w-[30rem]" @close="closeDeleteModal" wire:model="showDeleteModal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg" class="font-semibold">{{ __('Remove passkey') }}</flux:heading>
                <p class="mt-1 text-sm text-ink-muted">
                    {{ __('Are you sure you want to remove the passkey ":name"? You will no longer be able to use it to sign in.', ['name' => $deletingPasskeyName]) }}
                </p>
            </div>

            <x-modal.actions>
                <x-button.danger wire:click="deletePasskey">{{ __('Remove passkey') }}</x-button.danger>
            </x-modal.actions>
        </div>
    </flux:modal>
</div>
