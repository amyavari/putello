<x-page>
    <x-page.title>
        {{ __('households.title') }}

        <x-slot:actions>
            <flux:modal.trigger name="add-household">
                <flux:button variant="primary" icon="plus">{{ __('households.add') }}</flux:button>
            </flux:modal.trigger>
        </x-slot:actions>
    </x-page.title>

    @if ($this->households->isEmpty())
        <flux:card class="mt-6 py-12 text-center">
            <p class="font-semibold">{{ __('households.empty.heading') }}</p>
            <p class="mt-1 text-sm text-ink-muted">{{ __('households.empty.text') }}</p>
        </flux:card>
    @else
        <x-list.card class="mt-6">
            @foreach ($this->households as $household)
                <x-list.link-row
                    :href="route('places.index', $household)"
                    :name="$household->membership->label ?? $household->name"
                    :detail="trans_choice('households.member_count', $household->members_count)"
                    wire:key="household-{{ $household->id }}"
                >
                    <x-badge>{{ $household->membership->role->label() }}</x-badge>
                </x-list.link-row>
            @endforeach
        </x-list.card>
    @endif

    <flux:modal name="add-household" class="w-full md:w-[30rem]">
        <form wire:submit="add" class="space-y-6">
            <div>
                <flux:heading size="lg" class="font-semibold">{{ __('households.add') }}</flux:heading>
                <p class="mt-1 text-sm text-ink-muted">{{ __('households.add_description') }}</p>
            </div>

            <flux:input
                wire:model="name"
                :label="__('households.name')"
                :description:trailing="__('households.name_hint')"
                autocomplete="off"
            />

            <x-modal.actions>
                <flux:button type="submit" variant="primary" icon="plus">{{ __('households.add') }}</flux:button>
            </x-modal.actions>
        </form>
    </flux:modal>
</x-page>
