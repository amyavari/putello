{{-- The line under an auth card, such as "No account yet? Sign up". --}}
<p {{ $attributes->class('flex flex-wrap items-center gap-x-1') }}>
    {{ $slot }}
</p>
