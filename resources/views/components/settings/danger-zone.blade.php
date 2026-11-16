{{-- The danger zone: a red-edged card for actions that can't be undone. --}}
<section>
    <x-page.section-heading>{{ __('Danger zone') }}</x-page.section-heading>

    <flux:card class="mt-2 border-danger-ink!">
        {{ $slot }}
    </flux:card>
</section>
