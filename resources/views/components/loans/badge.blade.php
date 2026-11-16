@use('App\Support\ShortDate')
@props(['loan'])

{{-- The gold badge of an open loan: who has the item and since when. --}}
<span {{ $attributes->class('inline-flex items-center gap-1 self-start rounded-md bg-lent-soft px-2 py-0.5 text-[0.8125rem]/[1.125rem] font-medium text-lent-ink') }}>
    <span aria-hidden="true" class="size-2 shrink-0 rounded-full bg-lent"></span>
    {{ __('loans.lent_to', ['borrower' => $loan->borrower->name, 'date' => ShortDate::of($loan->lent_at)]) }}
</span>
