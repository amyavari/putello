{{-- A list of rows in one card, divided by lines. The rows bring their own padding. --}}
<flux:card {{ $attributes->class('divide-y divide-border overflow-hidden p-0') }}>
    {{ $slot }}
</flux:card>
