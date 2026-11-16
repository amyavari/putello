{{-- A form's main button and Cancel, under a line at the end of the form. On small screens they share the line 70/30. --}}
<div {{ $attributes->class('flex gap-3 border-t border-border pt-4 max-md:*:first:flex-7 max-md:*:last:flex-3') }}>
    {{ $slot }}
</div>
