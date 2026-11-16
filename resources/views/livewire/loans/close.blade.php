@use('App\Support\ShortDate')

<form wire:submit="close" class="space-y-6">
    <div>
        <flux:heading size="lg" class="font-semibold">{{ __('loans.close_loan') }}</flux:heading>
        <p class="mt-1 text-sm text-ink-muted">
            {{ __('loans.lent_since', [
                'item' => $loan->item->name,
                'borrower' => $loan->borrower->name,
                'date' => ShortDate::of($loan->lent_at),
            ]) }}
        </p>
    </div>

    <flux:radio.group wire:model.live="closedAs" :label="__('loans.what_happened')" variant="cards" indicator="start" class="flex-col">
        <flux:radio value="returned" :label="__('loans.returned')" :description="__('loans.returned_hint', ['path' => $path])" />
    </flux:radio.group>

    @if ($closedAs === null)
        <p class="text-sm text-ink-muted">{{ __('loans.choose_returned_or_lost') }}</p>
    @endif

    <x-modal.actions>
        <flux:button type="submit" variant="primary" :disabled="$closedAs === null">{{ __('loans.close_loan') }}</flux:button>
    </x-modal.actions>
</form>
