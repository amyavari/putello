/**
 * After wire:navigate opens a page, focus moves to its content, so keyboard and screen reader
 * users start there instead of at the menu (UI rules). The first load keeps the browser's focus.
 */
let isFirstPage = true;

document.addEventListener('livewire:navigated', () => {
    if (isFirstPage) {
        isFirstPage = false;

        return;
    }

    document.getElementById('content')?.focus({ preventScroll: true });
});
