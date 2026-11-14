<section class="w-full max-w-3xl">
    <flux:heading size="xl" level="1">{{ __('households.title') }}</flux:heading>

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
</section>
