<?php

declare(strict_types=1);

use App\Enums\ActivityType;
use App\Livewire\Archived\Index;
use App\Livewire\Items\Create;
use App\Livewire\Items\Edit;
use App\Livewire\Items\Show;
use App\Livewire\Places;
use App\Models\Activity;
use App\Models\Household;
use App\Models\Item;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

test('adding an item records an entry with the path of its place', function (): void {
    $household = Household::factory()
        ->for(User::factory()->state(['name' => 'Ali']), 'owner')
        ->create();
    $leftCupboard = placeInside(placeIn($household, 'Kitchen'), 'Left cupboard');

    Livewire::withQueryParams(['inside' => $leftCupboard->id])
        ->actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->set('name', 'Kettle')
        ->call('add');

    assertDatabaseHas(Activity::class, [
        'household_id' => $household->id,
        'user_id' => $household->owner->id,
        'user_name' => 'Ali',
        'type' => ActivityType::ItemCreated,
        'item_id' => Item::query()->sole()->id,
        'place_id' => null,
        'subject_name' => 'Kettle',
        'from_value' => null,
        'to_value' => 'Kitchen › Left cupboard',
    ]);
});

test('editing an item records an entry with the old and the new name', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Edit::class, ['household' => $household, 'item' => $medal])
        ->set('name', 'Gold medal')
        ->call('save');

    assertDatabaseHas(Activity::class, [
        'type' => ActivityType::ItemEdited,
        'item_id' => $medal->id,
        'subject_name' => 'Gold medal',
        'from_value' => 'Medal',
        'to_value' => 'Gold medal',
    ]);
});

test('moving an item records an entry with the old and the new path', function (): void {
    $household = Household::factory()->create();
    $kettle = itemIn(placeInside(placeIn($household, 'Kitchen'), 'Left cupboard'), 'Kettle');
    $hall = placeIn($household, 'Hall');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $kettle])
        ->call('move', $hall->id);

    assertDatabaseHas(Activity::class, [
        'type' => ActivityType::ItemMoved,
        'item_id' => $kettle->id,
        'subject_name' => 'Kettle',
        'from_value' => 'Kitchen › Left cupboard',
        'to_value' => 'Hall',
    ]);
});

test('archiving an item records an entry with the path it was in', function (): void {
    $household = Household::factory()->create();
    $kettle = itemIn(placeInside(placeIn($household, 'Kitchen'), 'Left cupboard'), 'Kettle');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $kettle])
        ->call('archive');

    assertDatabaseHas(Activity::class, [
        'type' => ActivityType::ItemArchived,
        'item_id' => $kettle->id,
        'subject_name' => 'Kettle',
        'from_value' => 'Kitchen › Left cupboard',
        'to_value' => null,
    ]);
});

test('restoring an item records an entry with the path of its new place', function (): void {
    $household = Household::factory()->create();
    $umbrella = archivedItemOf($household, 'Umbrella');
    $coatRack = placeInside(placeIn($household, 'Hall'), 'Coat rack');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->call('chooseItemToRestore', $umbrella->id)
        ->call('restore', $coatRack->id);

    assertDatabaseHas(Activity::class, [
        'type' => ActivityType::ItemRestored,
        'item_id' => $umbrella->id,
        'subject_name' => 'Umbrella',
        'from_value' => null,
        'to_value' => 'Hall › Coat rack',
    ]);
});

test('undo records a restore entry with the path of the place', function (): void {
    $household = Household::factory()->create();
    $leftCupboard = placeInside(placeIn($household, 'Kitchen'), 'Left cupboard');
    $kettle = archivedItemOf($household, 'Kettle');
    session(['archived_item_id' => $kettle->id]);

    Livewire::actingAs($household->owner)
        ->test(Places\Show::class, ['household' => $household, 'place' => $leftCupboard])
        ->call('undoArchive');

    assertDatabaseHas(Activity::class, [
        'type' => ActivityType::ItemRestored,
        'item_id' => $kettle->id,
        'subject_name' => 'Kettle',
        'from_value' => null,
        'to_value' => 'Kitchen › Left cupboard',
    ]);
});
