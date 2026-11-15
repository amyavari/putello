<section class="w-full max-w-3xl">
    <x-places.breadcrumbs :$household :household-name="$this->householdName" :places="[]" />

    <flux:heading size="xl" level="1" class="mt-2">{{ __('archived.title') }}</flux:heading>

    @if ($this->isEmpty())
        <flux:card class="mt-6 text-center">
            <flux:heading>{{ __('archived.empty.heading') }}</flux:heading>
            <flux:text class="mt-1">{{ __('archived.empty.text') }}</flux:text>
        </flux:card>
    @else
        <div class="mt-6 flex items-baseline gap-2">
            <flux:heading size="lg" level="2">{{ __('archived.items') }}</flux:heading>
            <flux:text size="sm">{{ $this->archivedItems->count() }}</flux:text>
        </div>

        <div class="mt-2 space-y-3">
            @foreach ($this->archivedItems as $item)
                <flux:card wire:key="archived-item-{{ $item->id }}">
                    <flux:heading>{{ $item->name }}</flux:heading>
                    <flux:text class="mt-1">{{ __('archived.archived_on', ['date' => $this->archivedOn($item)]) }}</flux:text>
                </flux:card>
            @endforeach
        </div>
    @endif
</section>
