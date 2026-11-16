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
                <flux:card wire:key="archived-item-{{ $item->id }}" class="flex items-start gap-3">
                    <div class="min-w-0 flex-1">
                        <flux:heading>{{ $item->name }}</flux:heading>
                        @if ($this->wasIn($item))
                            <flux:text class="mt-1">{{ __('archived.was_in', ['path' => $this->wasIn($item)]) }}</flux:text>
                        @endif
                        <flux:text class="mt-1">{{ __('archived.archived_on', ['date' => $this->archivedOn($item)]) }}</flux:text>
                    </div>

                    @can('edit', $household)
                        <x-button.secondary wire:click="chooseItemToRestore('{{ $item->id }}')" size="sm">
                            {{ __('archived.restore') }}
                        </x-button.secondary>
                    @endcan
                </flux:card>
            @endforeach
        </div>

        @can('edit', $household)
            <flux:modal name="restore-item" class="w-full md:w-[30rem]">
                <div class="space-y-4">
                    <livewire:places.picker :$household is-for-an-item key="restore-picker" lazy />

                    <flux:error name="placeId" />
                </div>
            </flux:modal>
        @endcan
    @endif
</section>
