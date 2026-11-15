<?php

declare(strict_types=1);

use App\Http\Middleware\SaveCurrentHousehold;
use App\Livewire\Households;
use App\Livewire\Items;
use App\Livewire\Places;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::livewire('households', Households\Index::class)->name('households.index');

    Route::prefix('households/{household}')
        ->can('view', 'household')
        ->middleware(SaveCurrentHousehold::class)
        ->group(function (): void {
            Route::livewire('places', Places\Index::class)->name('places.index');
            Route::livewire('places/create', Places\Create::class)->name('places.create');
            Route::livewire('places/{place}', Places\Show::class)
                ->scopeBindings()
                ->name('places.show');
            Route::livewire('items/{item}', Items\Show::class)
                ->scopeBindings()
                ->name('items.show');
        });
});

require __DIR__.'/settings.php';
