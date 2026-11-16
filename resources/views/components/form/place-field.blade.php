@props(['label', 'placeName'])

{{-- The place of a form as one line, with "Change place" (or "Choose place" while there is none), which opens the Place picker. --}}
<div class="space-y-2">
    <p class="text-[0.9375rem]/5 font-medium">{{ $label }}</p>

    <div class="flex items-center justify-between gap-3">
        <span class="min-w-0 truncate">{{ $placeName }}</span>

        <flux:modal.trigger name="choose-place">
            <x-button.secondary>{{ $placeName === null ? __('places.choose_place') : __('places.change_place') }}</x-button.secondary>
        </flux:modal.trigger>
    </div>

    <flux:error name="placeId" />
</div>
