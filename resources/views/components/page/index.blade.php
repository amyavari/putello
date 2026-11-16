{{-- One page's content column: at most 720px wide, aligned to the start. --}}
<section {{ $attributes->class('w-full max-w-180') }}>
    {{ $slot }}
</section>
