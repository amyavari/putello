<x-page>
    <x-places.breadcrumbs :$household :household-name="$this->householdName" :places="$this->path" :$item />

    <x-page.title class="mt-1">{{ __('loans.lend_item', ['name' => $item->name]) }}</x-page.title>

    <flux:card class="mt-6">
        <form wire:submit="lend" class="space-y-4">
            <div class="relative">
                <flux:input
                    wire:model.live.debounce.300ms="borrowerName"
                    :label="__('loans.borrower')"
                    :description:trailing="$this->isNewBorrower() ? __('loans.new_borrower') : null"
                    autocomplete="off"
                />

                @if ($this->matchingBorrowers->isNotEmpty())
                    <div
                        role="listbox"
                        aria-label="{{ __('loans.saved_borrowers') }}"
                        class="absolute inset-x-0 top-full z-10 mt-1 flex flex-col rounded-[14px] border border-border bg-surface p-2 shadow-lg"
                    >
                        @foreach ($this->matchingBorrowers as $borrower)
                            <button
                                type="button"
                                role="option"
                                aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                                wire:key="borrower-{{ $borrower->id }}"
                                wire:click="$set('borrowerName', {{ Js::from($borrower->name) }})"
                                class="flex h-11 items-center rounded-[10px] px-3 text-start hover:bg-surface-hover aria-selected:bg-bg-subtle"
                            >
                                {{ $borrower->name }}
                            </button>
                        @endforeach

                        <p class="px-3 pt-2 pb-1 text-sm text-ink-muted">{{ __('loans.keep_typing') }}</p>
                    </div>
                @endif
            </div>

            <div class="flex flex-wrap gap-4 *:min-w-40 *:flex-1">
                <flux:input wire:model="lentAt" type="date" :label="__('loans.lent_on')" />

                <flux:input wire:model="dueAt" type="date" :label="__('loans.due_back_optional')" />
            </div>

            <flux:textarea wire:model="note" :label="__('loans.note_optional')" rows="2" />

            <x-form.actions>
                <flux:button type="submit" variant="primary">{{ __('loans.lend') }}</flux:button>

                <flux:button :href="route('items.show', [$household, $item])" wire:navigate variant="ghost">
                    {{ __('items.cancel') }}
                </flux:button>
            </x-form.actions>
        </form>
    </flux:card>
</x-page>
