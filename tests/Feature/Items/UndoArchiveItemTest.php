<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Livewire\Items;
use App\Livewire\Places\Show;
use App\Models\Household;
use App\Models\Item;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertNotSoftDeleted;
use function Pest\Laravel\assertSoftDeleted;

test('the message after archiving offers undo', function (): void {
    $household = Household::factory()->create();
    $johnsRoom = placeIn($household, 'John’s room');
    $medal = itemIn($johnsRoom, 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Items\Show::class, ['household' => $household, 'item' => $medal])
        ->call('archive');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $johnsRoom])
        ->assertSeeInOrder(['Medal archived.', 'Undo']);
});

test('undo puts the item back in the place it was in', function (): void {
    $household = Household::factory()->create();
    $johnsRoom = placeIn($household, 'John’s room');
    $medal = Item::factory()->archived()->create([
        'household_id' => $household->id,
        'name' => 'Medal',
    ]);
    session(['archived_item_id' => $medal->id]);

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $johnsRoom])
        ->call('undoArchive')
        ->assertHasNoErrors();

    assertNotSoftDeleted($medal);
    assertDatabaseHas(Item::class, [
        'id' => $medal->id,
        'place_id' => $johnsRoom->id,
    ]);
});

test('after undo the place lists the item again', function (): void {
    $household = Household::factory()->create();
    $johnsRoom = placeIn($household, 'John’s room');
    $medal = Item::factory()->archived()->create([
        'household_id' => $household->id,
        'name' => 'Medal',
    ]);
    session(['archived_item_id' => $medal->id]);

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $johnsRoom])
        ->call('undoArchive')
        ->assertSeeInOrder(['Items in this place', 'Medal']);
});

test('after undo the page shows a message', function (): void {
    $household = Household::factory()->create();
    $johnsRoom = placeIn($household, 'John’s room');
    $medal = Item::factory()->archived()->create([
        'household_id' => $household->id,
        'name' => 'Medal',
    ]);
    session(['archived_item_id' => $medal->id]);

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $johnsRoom])
        ->call('undoArchive')
        ->assertSee('Medal restored. It is now in John’s room.');
});

test('an item of another household can not be restored with undo', function (): void {
    $household = Household::factory()->create();
    $johnsRoom = placeIn($household, 'John’s room');
    $itemOfAnotherHousehold = Item::factory()->archived()->create();
    session(['archived_item_id' => $itemOfAnotherHousehold->id]);

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $johnsRoom])
        ->call('undoArchive')
        ->assertNotFound();

    assertSoftDeleted($itemOfAnotherHousehold);
});

test('a viewer can not undo', function (): void {
    $household = Household::factory()->create();
    $viewer = memberOf($household, Role::Viewer);
    $johnsRoom = placeIn($household, 'John’s room');
    $medal = Item::factory()->archived()->create([
        'household_id' => $household->id,
        'name' => 'Medal',
    ]);
    session(['archived_item_id' => $medal->id]);

    Livewire::actingAs($viewer)
        ->test(Show::class, ['household' => $household, 'place' => $johnsRoom])
        ->call('undoArchive')
        ->assertForbidden();

    assertSoftDeleted($medal);
});

test('undo goes away when the page shows another message', function (): void {
    $household = Household::factory()->create();
    $johnsRoom = placeIn($household, 'John’s room');
    $medal = Item::factory()->archived()->create([
        'household_id' => $household->id,
        'name' => 'Medal',
    ]);
    session(['archived_item_id' => $medal->id]);

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $johnsRoom])
        ->dispatch('place-renamed')
        ->assertSee('Renamed to John’s room.')
        ->assertDontSee('Undo');
});
