<div class="mt-4 space-y-4 border-t border-border pt-4" wire:cloak x-data="{ showRecoveryCodes: false }">
    <div>
        <h3 class="font-semibold">{{ __('2FA recovery codes') }}</h3>
        <p class="mt-1 text-sm text-ink-muted">
            {{ __('Recovery codes let you regain access if you lose your 2FA device. Store them in a secure password manager.') }}
        </p>
    </div>

    <div class="flex flex-wrap gap-3">
        <x-button.secondary
            x-show="!showRecoveryCodes"
            wire:click="viewRecoveryCodes"
            @click="showRecoveryCodes = true;"
            aria-expanded="false"
            aria-controls="recovery-codes-section"
        >
            {{ __('View recovery codes') }}
        </x-button.secondary>

        <x-button.secondary
            x-show="showRecoveryCodes"
            @click="showRecoveryCodes = false"
            aria-expanded="true"
            aria-controls="recovery-codes-section"
        >
            {{ __('Hide recovery codes') }}
        </x-button.secondary>

        @if (filled($recoveryCodes))
            <flux:button x-show="showRecoveryCodes" wire:click="regenerateRecoveryCodes">
                {{ __('Regenerate codes') }}
            </flux:button>
        @endif
    </div>

    <div
        x-show="showRecoveryCodes"
        x-transition
        id="recovery-codes-section"
        class="space-y-3"
        x-bind:aria-hidden="!showRecoveryCodes"
    >
        @error('recoveryCodes')
            <flux:callout variant="danger" icon="x-circle" heading="{{ $message }}" />
        @enderror

        @if (filled($recoveryCodes))
            <div class="grid gap-1 rounded-lg bg-bg-subtle p-4 font-mono text-sm" role="list" aria-label="{{ __('Recovery codes') }}">
                @foreach ($recoveryCodes as $code)
                    <div role="listitem" class="select-text" wire:loading.class="opacity-50 animate-pulse">{{ $code }}</div>
                @endforeach
            </div>

            <p class="text-sm text-ink-muted">
                {{ __('Each recovery code can be used once to access your account and will be removed after use. If you need more, click Regenerate codes above.') }}
            </p>
        @endif
    </div>
</div>
