<x-settings.layout>
    <x-settings.section :heading="__('Profile')">
        <form wire:submit="updateProfileInformation" class="w-full space-y-4">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input
                    wire:model="email"
                    :label="__('Email')"
                    :description:trailing="__('Changing your email means verifying the new one.')"
                    type="email"
                    required
                    autocomplete="email"
                />

                @if ($this->hasUnverifiedEmail)
                    <p class="mt-4 text-sm">
                        {{ __('Your email address is unverified.') }}

                        <button type="button" class="cursor-pointer text-accent underline underline-offset-2" wire:click.prevent="resendVerificationNotification">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>
                @endif
            </div>

            <flux:button variant="primary" type="submit">{{ __('Save changes') }}</flux:button>
        </form>
    </x-settings.section>

    @if ($this->showDeleteUser)
        <livewire:settings.delete-user-form />
    @endif
</x-settings.layout>
