<?php

declare(strict_types=1);

use App\Http\Controllers\HomeController;
use App\Http\Middleware\SaveCurrentHousehold;
use App\Livewire\Archived;
use App\Livewire\Households;
use App\Livewire\Items;
use App\Livewire\Places;
use App\Livewire\Search;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/', HomeController::class)->name('home');
    Route::livewire('households', Households\Index::class)->name('households.index');

    Route::prefix('households/{household}')
        ->can('view', 'household')
        ->middleware(SaveCurrentHousehold::class)
        ->group(function (): void {
            Route::livewire('search', Search\Index::class)->name('search.index');
            Route::livewire('places', Places\Index::class)->name('places.index');
            Route::livewire('places/create', Places\Create::class)->name('places.create');
            Route::livewire('places/{place}', Places\Show::class)
                ->scopeBindings()
                ->name('places.show');
            Route::livewire('items/create', Items\Create::class)->name('items.create');
            Route::livewire('items/{item}', Items\Show::class)
                ->scopeBindings()
                ->name('items.show');
            Route::livewire('items/{item}/edit', Items\Edit::class)
                ->scopeBindings()
                ->name('items.edit');
            Route::livewire('items/{item}/lend', Items\Lend::class)
                ->scopeBindings()
                ->name('items.lend');
            Route::livewire('archived', Archived\Index::class)->name('archived.index');
        });
});

require __DIR__.'/settings.php';
