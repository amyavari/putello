<?php

declare(strict_types=1);

use App\Enums\ActivityType;
use App\Livewire\Archived\Index;
use App\Livewire\Items\Create;
use App\Livewire\Items\Edit;
use App\Livewire\Items\Lend;
use App\Livewire\Items\Show;
use App\Livewire\Loans\Close;
use App\Livewire\Places;
use App\Models\Activity;
use App\Models\Household;
use App\Models\Item;
use App\Models\Loan;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

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

test('lending an item records an entry with the borrower', function (): void {
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $drill])
        ->set('borrowerName', 'Neighbor Reza')
        ->call('lend');

    assertDatabaseHas(Activity::class, [
        'type' => ActivityType::ItemLent,
        'item_id' => $drill->id,
        'subject_name' => 'Drill',
        'from_value' => null,
        'to_value' => 'Neighbor Reza',
    ]);
});

test('closing a loan as returned records an entry with the borrower', function (): void {
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    $loan = Loan::factory()
        ->for($drill)
        ->for(borrowerOf($household, 'Neighbor Reza'))
        ->create();

    Livewire::actingAs($household->owner)
        ->test(Close::class, ['loan' => $loan])
        ->set('closedAs', 'returned')
        ->call('close');

    assertDatabaseHas(Activity::class, [
        'type' => ActivityType::ItemReturned,
        'item_id' => $drill->id,
        'subject_name' => 'Drill',
        'from_value' => null,
        'to_value' => 'Neighbor Reza',
    ]);
});

test('closing a loan as lost records an entry with the path and the borrower', function (): void {
    $household = Household::factory()->create();
    $drill = itemIn(placeInside(placeIn($household, 'Storage room'), 'Shelf A'), 'Drill');
    $loan = Loan::factory()
        ->for($drill)
        ->for(borrowerOf($household, 'Neighbor Reza'))
        ->create();

    Livewire::actingAs($household->owner)
        ->test(Close::class, ['loan' => $loan])
        ->set('closedAs', 'lost')
        ->call('close');

    assertDatabaseHas(Activity::class, [
        'type' => ActivityType::ItemLost,
        'item_id' => $drill->id,
        'subject_name' => 'Drill',
        'from_value' => 'Storage room › Shelf A',
        'to_value' => 'Neighbor Reza',
    ]);
});

test('closing a loan as lost records no archive entry', function (): void {
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    $loan = Loan::factory()->for($drill)->create();

    Livewire::actingAs($household->owner)
        ->test(Close::class, ['loan' => $loan])
        ->set('closedAs', 'lost')
        ->call('close');

    assertDatabaseMissing(Activity::class, [
        'type' => ActivityType::ItemArchived,
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
