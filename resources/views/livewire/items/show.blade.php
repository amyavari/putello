@use('App\Support\ShortDate')

<x-page>
    <x-flash-message :$message />

    <x-places.breadcrumbs :$household :household-name="$this->householdName" :places="$this->path" />

    <x-page.title class="mt-1">{{ $item->name }}</x-page.title>

    @can('edit', $household)
        <div class="mt-6 flex items-center gap-3">
            <flux:modal.trigger name="move-item">
                <flux:button variant="primary">{{ __('items.move') }}</flux:button>
            </flux:modal.trigger>

            @unless ($item->isLent())
                <x-button.secondary :href="route('items.lend', [$household, $item])" wire:navigate>
                    {{ __('loans.lend') }}
                </x-button.secondary>
            @endunless

            <x-items.more-actions :$household :$item />
        </div>
    @endcan

    <x-page.section-heading class="mt-6">{{ __('items.place') }}</x-page.section-heading>

    <flux:card class="mt-2">
        <x-items.place-path :$household :places="$this->path" />
    </flux:card>

    <x-page.section-heading class="mt-6">{{ __('loans.loan') }}</x-page.section-heading>

    <flux:card class="mt-2">
        @if ($item->currentLoan === null)
            <p class="text-ink-muted">{{ __('loans.not_lent') }}</p>
        @else
            <div class="flex flex-col items-start gap-1">
                <x-loans.badge :loan="$item->currentLoan" />

                @if ($item->currentLoan->due_at !== null)
                    <p class="text-sm text-ink-muted">{{ __('loans.due', ['date' => ShortDate::of($item->currentLoan->due_at)]) }}</p>
                @endif
            </div>
        @endif
    </flux:card>

    <x-page.section-heading class="mt-6">{{ __('items.notes') }}</x-page.section-heading>

    <flux:card class="mt-2">
        @if ($item->notes === null)
            <p class="text-ink-muted">{{ __('items.no_notes') }}</p>
        @else
            <p>{{ $item->notes }}</p>
        @endif
    </flux:card>

    @can('edit', $household)
        <flux:modal name="move-item" class="w-full md:w-[30rem]">
            <div class="space-y-4">
                <livewire:places.picker :$household :$item :key="'picker-'.$item->place_id" lazy />

                <flux:error name="placeId" />
            </div>
        </flux:modal>
    @endcan
</x-page>
