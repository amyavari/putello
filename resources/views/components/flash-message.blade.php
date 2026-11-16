@props(['message', 'undo' => null])

{{-- The green message after an action, with "Undo" where possible. It goes away after 5 seconds or on Dismiss. --}}
@if ($message !== null)
    <div
        wire:key="message-{{ md5($message) }}"
        x-data="{ visible: true }"
        x-init="setTimeout(() => visible = false, 5000)"
        x-show="visible"
        role="status"
        class="mb-6 flex min-h-11 items-center gap-3 rounded-xl bg-success-soft ps-4 pe-1 text-success-ink"
    >
        <flux:icon.check class="size-5 shrink-0" />

        <p class="min-w-0 flex-1 py-2 text-[0.9375rem]/5 font-semibold">
            {{ $message }}

            @if ($undo !== null)
                <button type="button" wire:click="{{ $undo }}" class="ms-1 cursor-pointer text-accent underline underline-offset-2 hover:text-accent-hover">
                    {{ __('items.undo') }}
                </button>
            @endif
        </p>

        <flux:button variant="ghost" square icon="x-mark" x-on:click="visible = false" :aria-label="__('places.dismiss')" class="shrink-0 text-success-ink!" />
    </div>
@endif
