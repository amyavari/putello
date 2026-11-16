@props(['passkey'])

{{-- One saved passkey: its name and device, when it was added and last used, and a button that removes it. --}}
<div {{ $attributes->class('flex min-h-14 items-center gap-3 px-4 py-3') }}>
    <span aria-hidden="true" class="flex size-10 shrink-0 items-center justify-center rounded-full bg-accent-soft text-on-accent-soft">
        <flux:icon.key class="size-5" />
    </span>

    <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2">
            <p class="truncate font-semibold">{{ $passkey['name'] }}</p>

            @if ($passkey['authenticator'])
                <x-badge>{{ $passkey['authenticator'] }}</x-badge>
            @endif
        </div>

        <p class="text-sm text-ink-muted">
            {{ __('Added :time', ['time' => $passkey['created_at_diff']]) }}

            @if ($passkey['last_used_at_diff'])
                · {{ __('Last used :time', ['time' => $passkey['last_used_at_diff']]) }}
            @endif
        </p>
    </div>

    <flux:button
        variant="ghost"
        square
        icon="trash"
        wire:click="confirmDelete({{ $passkey['id'] }})"
        :aria-label="__('Remove passkey :name', ['name' => $passkey['name']])"
        class="shrink-0 text-danger-ink!"
    />
</div>
