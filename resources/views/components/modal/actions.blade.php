{{-- A modal's main button, then Cancel, on one line. On small screens they share it 70/30. --}}
<div {{ $attributes->class('flex gap-3 max-md:*:first:flex-7 max-md:*:last:flex-3') }}>
    {{ $slot }}
    <flux:modal.close>
        <flux:button variant="ghost" class="w-full">{{ __('places.cancel') }}</flux:button>
    </flux:modal.close>
</div>
