<x-page>
    <x-places.breadcrumbs :$household :household-name="$this->householdName" :places="[]" />

    <x-page.title class="mt-1">{{ __('archived.title') }}</x-page.title>

    @if ($this->isEmpty())
        <flux:card class="mt-6 py-12 text-center">
            <p class="font-semibold">{{ __('archived.empty.heading') }}</p>
            <p class="mt-1 text-sm text-ink-muted">{{ __('archived.empty.text') }}</p>
        </flux:card>
    @else
        <x-page.section-heading class="mt-6">
            {{ __('archived.items') }}
            <span class="ms-1 text-sm font-medium text-ink-muted">{{ $this->archivedItems->count() }}</span>
        </x-page.section-heading>

        <div class="mt-2 space-y-3">
            @foreach ($this->archivedItems as $item)
                <flux:card wire:key="archived-item-{{ $item->id }}" class="flex items-start gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold">{{ $item->name }}</p>

                        @if ($this->wasIn($item))
                            <x-archived.detail :label="__('archived.was_in')">{{ $this->wasIn($item) }}</x-archived.detail>
                        @endif

                        <x-archived.detail :label="__('archived.archived_on')">{{ $this->archivedOn($item) }}</x-archived.detail>
                    </div>

                    @can('edit', $household)
                        <x-button.secondary wire:click="chooseItemToRestore('{{ $item->id }}')">
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
</x-page>
