@props([
    'title',
    'description' => null,
])

{{-- The title of an auth page, with an optional line under it. --}}
<div class="flex w-full flex-col gap-2">
    <flux:heading level="1" class="text-[1.75rem]/[2.125rem]! font-semibold!">{{ $title }}</flux:heading>

    @if ($description !== null)
        <p class="text-ink-muted">{{ $description }}</p>
    @endif
</div>
