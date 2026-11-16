<x-settings.danger-zone>
    <div class="flex items-center justify-between gap-4 max-md:flex-col max-md:items-stretch">
        <div>
            <p class="font-semibold">{{ __('Delete account') }}</p>
            <p class="text-sm text-ink-muted">{{ __('Delete your account and all of its resources') }}</p>
        </div>

        <flux:modal.trigger name="confirm-user-deletion">
            <x-button.danger-outline x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
                {{ __('Delete account') }}
            </x-button.danger-outline>
        </flux:modal.trigger>
    </div>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="w-full md:w-[30rem]">
        <form method="POST" wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg" class="font-semibold">{{ __('Are you sure you want to delete your account?') }}</flux:heading>

                <p class="mt-1 text-sm text-ink-muted">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                    <x-cannot-be-undone />
                </p>
            </div>

            <flux:input wire:model="password" :label="__('Password')" type="password" viewable />

            <x-modal.actions>
                <x-button.danger type="submit">{{ __('Delete account') }}</x-button.danger>
            </x-modal.actions>
        </form>
    </flux:modal>
</x-settings.danger-zone>
