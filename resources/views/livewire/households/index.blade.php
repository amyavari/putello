<section class="w-full max-w-3xl">
    <div class="flex items-center justify-between gap-3">
        <flux:heading size="xl" level="1">{{ __('households.title') }}</flux:heading>

        <flux:modal.trigger name="add-household">
            <flux:button variant="primary" icon="plus">{{ __('households.add') }}</flux:button>
        </flux:modal.trigger>
    </div>

    @if ($this->households->isEmpty())
        <flux:card class="mt-6 text-center">
            <flux:heading>{{ __('households.empty.heading') }}</flux:heading>
            <flux:text class="mt-1">{{ __('households.empty.text') }}</flux:text>
        </flux:card>
    @else
        <flux:card class="mt-6 divide-y divide-zinc-200 p-0 dark:divide-zinc-700">
            @foreach ($this->households as $household)
                <a
                    href="{{ route('places.index', $household) }}"
                    wire:key="household-{{ $household->id }}"
                    wire:navigate
                    class="flex items-center gap-3 px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-700/50"
                >
                    <div class="min-w-0 flex-1">
                        <flux:heading>{{ $household->membership->label ?? $household->name }}</flux:heading>
                        <flux:text>{{ trans_choice('households.member_count', $household->members_count) }}</flux:text>
                    </div>

                    <flux:badge size="sm">
                        {{ $household->membership->role->label() }}
                    </flux:badge>

                    <flux:icon.chevron-right variant="micro" class="text-zinc-400 rtl:rotate-180" />
                </a>
            @endforeach
        </flux:card>
    @endif

    <flux:modal name="add-household" class="w-full md:w-[30rem]">
        <form wire:submit="add" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('households.add') }}</flux:heading>
                <flux:text class="mt-1">{{ __('households.add_description') }}</flux:text>
            </div>

            <flux:input
                wire:model="name"
                :label="__('households.name')"
                :description:trailing="__('households.name_hint')"
                autocomplete="off"
            />

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary" icon="plus" class="flex-[7]">{{ __('households.add') }}</flux:button>

                <flux:modal.close>
                    <flux:button variant="ghost" class="flex-[3]">{{ __('households.cancel') }}</flux:button>
                </flux:modal.close>
            </div>
        </form>
    </flux:modal>
</section>
