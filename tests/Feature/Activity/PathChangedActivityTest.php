<?php

declare(strict_types=1);

use App\Enums\ActivityType;
use App\Livewire\Places\Rename;
use App\Livewire\Places\Show;
use App\Models\Activity;
use App\Models\Household;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

test('renaming a place records a path change for an item in it', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $kettle = itemIn($kitchen, 'Kettle');

    Livewire::actingAs($household->owner)
        ->test(Rename::class, ['household' => $household, 'place' => $kitchen])
        ->set('name', 'Pantry')
        ->call('rename');

    assertDatabaseHas(Activity::class, [
        'type' => ActivityType::ItemPathChanged,
        'item_id' => $kettle->id,
        'subject_name' => 'Kettle',
        'from_value' => 'Kitchen',
        'to_value' => 'Pantry',
    ]);
});

test('moving a place records a path change for an item in it', function (): void {
    $household = Household::factory()->create();
    $leftCupboard = placeInside(placeIn($household, 'Kitchen'), 'Left cupboard');
    $kettle = itemIn($leftCupboard, 'Kettle');
    $hall = placeIn($household, 'Hall');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $leftCupboard])
        ->call('move', $hall->id);

    assertDatabaseHas(Activity::class, [
        'type' => ActivityType::ItemPathChanged,
        'item_id' => $kettle->id,
        'subject_name' => 'Kettle',
        'from_value' => 'Kitchen › Left cupboard',
        'to_value' => 'Hall › Left cupboard',
    ]);
});

test('an item in a place deeper inside also gets a path change', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $drawer = placeInside(placeInside($kitchen, 'Left cupboard'), 'Drawer');
    $spoon = itemIn($drawer, 'Spoon');

    Livewire::actingAs($household->owner)
        ->test(Rename::class, ['household' => $household, 'place' => $kitchen])
        ->set('name', 'Pantry')
        ->call('rename');

    assertDatabaseHas(Activity::class, [
        'type' => ActivityType::ItemPathChanged,
        'item_id' => $spoon->id,
        'subject_name' => 'Spoon',
        'from_value' => 'Kitchen › Left cupboard › Drawer',
        'to_value' => 'Pantry › Left cupboard › Drawer',
    ]);
});

test('an item outside the place gets no path change', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $umbrella = itemIn(placeIn($household, 'Hall'), 'Umbrella');

    Livewire::actingAs($household->owner)
        ->test(Rename::class, ['household' => $household, 'place' => $kitchen])
        ->set('name', 'Pantry')
        ->call('rename');

    assertDatabaseMissing(Activity::class, [
        'type' => ActivityType::ItemPathChanged,
        'item_id' => $umbrella->id,
    ]);
});
