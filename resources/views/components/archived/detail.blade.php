@props(['label'])

{{-- One labeled line of an archived card: a muted label, then the value. --}}
<p class="mt-1 text-sm">
    <span class="text-ink-muted">{{ $label }}</span>
    {{ $slot }}
</p>
