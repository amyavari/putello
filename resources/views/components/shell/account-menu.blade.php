{{-- The account button at the bottom of the menu: Settings, a divider, then Log out in red. --}}
<flux:dropdown position="top" align="start">
    <flux:sidebar.profile
        :name="auth()->user()->name"
        :initials="auth()->user()->initials()"
        icon:trailing="chevrons-up-down"
        :aria-label="__('shell.account')"
        data-test="sidebar-menu-button"
    />

    <flux:menu>
        <flux:menu.item :href="route('profile.edit')" icon="cog-6-tooth" wire:navigate>
            {{ __('shell.settings') }}
        </flux:menu.item>

        <flux:menu.separator />

        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <flux:menu.item
                as="button"
                type="submit"
                icon="arrow-right-start-on-rectangle"
                variant="danger"
                class="w-full cursor-pointer"
                data-test="logout-button"
            >
                {{ __('shell.log_out') }}
            </flux:menu.item>
        </form>
    </flux:menu>
</flux:dropdown>
