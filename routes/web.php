<?php

declare(strict_types=1);

use App\Livewire\Households\Index;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('households', Index::class)->name('households.index');
});

require __DIR__.'/settings.php';
