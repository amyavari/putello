<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Livewire\Items\Show;
use App\Livewire\Places\Picker;
use App\Models\Household;
use App\Models\Item;
use App\Models\Place;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

test('the owner and editors get the place picker', function (Role $role): void {
    $household = Household::factory()->create();
    $member = memberOf($household, $role);
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($member)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        /** @phpstan-ignore method.notFound (a Livewire macro on TestResponse) */
        ->assertSeeLivewire(Picker::class);
})->with([
    'owner' => ['role' => Role::Owner],
    'editor' => ['role' => Role::Editor],
]);

test('a viewer does not get the place picker', function (): void {
    $household = Household::factory()->create();
    $viewer = memberOf($household, Role::Viewer);
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($viewer)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        /** @phpstan-ignore method.notFound (a Livewire macro on TestResponse) */
        ->assertDontSeeLivewire(Picker::class);
});

test('the owner moves an item to another place', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        ->call('move', $kitchen->id)
        ->assertHasNoErrors();

    assertDatabaseHas(Item::class, [
        'id' => $medal->id,
        'place_id' => $kitchen->id,
    ]);
});

test('an editor can move an item', function (): void {
    $household = Household::factory()->create();
    $editor = memberOf($household, Role::Editor);
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::actingAs($editor)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        ->call('move', $kitchen->id)
        ->assertHasNoErrors();

    assertDatabaseHas(Item::class, [
        'id' => $medal->id,
        'place_id' => $kitchen->id,
    ]);
});

test('after moving an item the app opens it with a message', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        ->call('move', $kitchen->id)
        ->assertRedirect(route('items.show', [$household, $medal]));

    expect(session('message'))->toBe('Medal moved. It is now in Kitchen.');
});

test('an item can not be moved to the place it is already in', function (): void {
    $household = Household::factory()->create();
    $johnsRoom = placeIn($household, 'John’s room');
    $medal = itemIn($johnsRoom, 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        ->call('move', $johnsRoom->id)
        ->assertHasErrors('placeId')
        ->assertSee('Medal can’t be moved there. Choose another place.')
        ->assertNoRedirect();
});

test('an item can not be moved to the household', function (): void {
    $household = Household::factory()->create();
    $johnsRoom = placeIn($household, 'John’s room');
    $medal = itemIn($johnsRoom, 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        ->call('move', null) // The household was chosen
        ->assertHasErrors('placeId');

    assertDatabaseHas(Item::class, [
        'id' => $medal->id,
        'place_id' => $johnsRoom->id,
    ]);
});

test('an item can not be moved into a place of another household', function (): void {
    $household = Household::factory()->create();
    $johnsRoom = placeIn($household, 'John’s room');
    $medal = itemIn($johnsRoom, 'Medal');
    $placeOfAnotherHousehold = Place::factory()->create();

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        ->call('move', $placeOfAnotherHousehold->id)
        ->assertHasErrors('placeId');

    assertDatabaseHas(Item::class, [
        'id' => $medal->id,
        'place_id' => $johnsRoom->id,
    ]);
});

test('a viewer can not move an item', function (): void {
    $household = Household::factory()->create();
    $viewer = memberOf($household, Role::Viewer);
    $johnsRoom = placeIn($household, 'John’s room');
    $medal = itemIn($johnsRoom, 'Medal');
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::actingAs($viewer)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        ->call('move', $kitchen->id)
        ->assertForbidden();

    assertDatabaseHas(Item::class, [
        'id' => $medal->id,
        'place_id' => $johnsRoom->id,
    ]);
});
