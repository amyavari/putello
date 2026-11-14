<section class="w-full max-w-3xl">
    @if ($message !== null)
        <div
            wire:key="message-{{ md5($message) }}"
            x-data="{ visible: true }"
            x-init="setTimeout(() => visible = false, 5000)"
            x-show="visible"
            class="mb-6"
        >
            <flux:callout variant="success" icon="check-circle" :heading="$message" role="status">
                <x-slot name="controls">
                    <flux:button variant="ghost" size="sm" x-on:click="visible = false">
                        {{ __('households.dismiss') }}
                    </flux:button>
                </x-slot>
            </flux:callout>
        </div>
    @endif

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
                <div wire:key="household-{{ $household->id }}" class="flex items-center gap-3 px-4 py-3">
                    <div class="min-w-0 flex-1">
                        <flux:heading>{{ $household->membership->label ?? $household->name }}</flux:heading>
                        <flux:text>{{ trans_choice('households.member_count', $household->members_count) }}</flux:text>
                    </div>

                    <flux:badge size="sm">
                        {{ $household->membership->role->label() }}
                    </flux:badge>
                </div>
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
