{{-- The Settings pages: the title, links to the three pages, then the page's sections. --}}
<x-page>
    <x-page.title>{{ __('Settings') }}</x-page.title>

    <flux:navlist :aria-label="__('Settings')" class="mt-2 flex-row! gap-1 *:w-auto!">
        <flux:navlist.item :href="route('profile.edit')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
        <flux:navlist.item :href="route('security.edit')" wire:navigate>{{ __('Security') }}</flux:navlist.item>
        <flux:navlist.item :href="route('appearance.edit')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
    </flux:navlist>

    <div class="mt-6 space-y-6">
        {{ $slot }}
    </div>
</x-page>
