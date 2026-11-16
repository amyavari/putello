@props(['svg'])

{{-- The QR code to scan with an authenticator app, on white so every app can read it. --}}
<div class="mx-auto flex aspect-square w-64 items-center justify-center overflow-hidden rounded-lg border border-border bg-white p-4">
    @empty($svg)
        <flux:icon.loading class="text-ink-muted" />
    @else
        {!! $svg !!}
    @endempty
</div>
