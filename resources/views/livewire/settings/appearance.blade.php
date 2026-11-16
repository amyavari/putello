<x-settings.layout>
    <x-settings.section :heading="__('Appearance')">
        <flux:radio.group x-data x-model="$flux.appearance" :aria-label="__('Appearance')">
            <flux:radio value="light" :label="__('Light')" />
            <flux:radio value="dark" :label="__('Dark')" />
            <flux:radio value="system" :label="__('Same as my device')" />
        </flux:radio.group>
    </x-settings.section>
</x-settings.layout>
