@props(['href'])

{{-- A text link on the auth pages. --}}
<a href="{{ $href }}" wire:navigate {{ $attributes->class('text-accent underline underline-offset-2 hover:text-accent-hover') }}>{{ $slot }}</a>
