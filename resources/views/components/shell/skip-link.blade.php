{{-- Hidden until it gets keyboard focus; it jumps over the menu to the page content (UI rules). --}}
<a
    href="#content"
    class="sr-only z-50 rounded-lg bg-accent px-4 py-3 font-semibold text-accent-foreground focus:not-sr-only focus:fixed focus:start-4 focus:top-4"
>
    {{ __('shell.skip_to_content') }}
</a>
