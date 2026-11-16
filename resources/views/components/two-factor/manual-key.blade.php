@props(['key'])

{{-- The setup key to type in by hand, with a button that copies it. --}}
<div
    class="space-y-2"
    x-data="{
        copied: false,
        async copy() {
            try {
                await navigator.clipboard.writeText({{ Js::from($key) }});
                this.copied = true;
                setTimeout(() => this.copied = false, 1500);
            } catch (e) {
                console.warn('Could not copy to clipboard');
            }
        }
    }"
>
    <p class="text-sm text-ink-muted">{{ __('or, enter the code manually') }}</p>

    <div class="flex h-11 items-stretch overflow-hidden rounded-lg border border-border-strong bg-surface">
        @empty($key)
            <div class="flex w-full items-center justify-center">
                <flux:icon.loading variant="mini" class="text-ink-muted" />
            </div>
        @else
            <input type="text" readonly value="{{ $key }}" aria-label="{{ __('Setup key') }}" class="w-full bg-transparent px-3 font-mono text-ink outline-none" />

            <button type="button" x-on:click="copy()" aria-label="{{ __('Copy the setup key') }}" class="flex w-11 shrink-0 cursor-pointer items-center justify-center border-s border-border text-ink-muted hover:bg-surface-hover">
                <flux:icon.clipboard-document x-show="! copied" class="size-5" />
                <flux:icon.check x-show="copied" class="size-5 text-success-ink" />
            </button>
        @endempty
    </div>
</div>
