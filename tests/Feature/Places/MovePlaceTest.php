<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Livewire\Places\Picker;
use App\Livewire\Places\Show;
use App\Models\Household;
use App\Models\Place;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

test('the owner and editors get the place picker', function (Role $role): void {
    $household = Household::factory()->create();
    $member = memberOf($household, $role);
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::actingAs($member)
        ->test(Show::class, ['household' => $household, 'place' => $kitchen])
        /** @phpstan-ignore method.notFound (a Livewire macro on TestResponse) */
        ->assertSeeLivewire(Picker::class);
})->with([
    'owner' => ['role' => Role::Owner],
    'editor' => ['role' => Role::Editor],
]);

test('a viewer does not get the place picker', function (): void {
    $household = Household::factory()->create();
    $viewer = memberOf($household, Role::Viewer);
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::actingAs($viewer)
        ->test(Show::class, ['household' => $household, 'place' => $kitchen])
        /** @phpstan-ignore method.notFound (a Livewire macro on TestResponse) */
        ->assertDontSeeLivewire(Picker::class);
});

test('the owner moves a place into another place', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Left cupboard');
    $storageRoom = placeIn($household, 'Storage room');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $cupboard])
        ->call('move', $storageRoom->id)
        ->assertHasNoErrors();

    assertDatabaseHas(Place::class, [
        'id' => $cupboard->id,
        'parent_id' => $storageRoom->id,
    ]);
});

test('the owner moves a place to the top of the household', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Left cupboard');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $cupboard])
        ->call('move', null) // The household was chosen
        ->assertHasNoErrors();

    assertDatabaseHas(Place::class, [
        'id' => $cupboard->id,
        'parent_id' => null,
    ]);
});

test('an editor can move a place', function (): void {
    $household = Household::factory()->create();
    $editor = memberOf($household, Role::Editor);
    $kitchen = placeIn($household, 'Kitchen');
    $storageRoom = placeIn($household, 'Storage room');

    Livewire::actingAs($editor)
        ->test(Show::class, ['household' => $household, 'place' => $kitchen])
        ->call('move', $storageRoom->id)
        ->assertHasNoErrors();

    assertDatabaseHas(Place::class, [
        'id' => $kitchen->id,
        'parent_id' => $storageRoom->id,
    ]);
});

test('after moving a place the app opens it with a message', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Left cupboard');
    $storageRoom = placeIn($household, 'Storage room');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $cupboard])
        ->call('move', $storageRoom->id)
        ->assertRedirect(route('places.show', [$household, $cupboard]));

    expect(session('message'))->toBe('Left cupboard moved. It is now in Storage room.');
});

test('after moving a place to the top the message names the household', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Left cupboard');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $cupboard])
        ->call('move', null);

    expect(session('message'))->toBe('Left cupboard moved. It is now in Home.');
});

test('a place can not be moved inside itself', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $kitchen])
        ->call('move', $kitchen->id)
        ->assertHasErrors('placeId');

    assertDatabaseHas(Place::class, [
        'id' => $kitchen->id,
        'parent_id' => null,
    ]);
});

test('a place can not be moved inside a place directly inside it', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Left cupboard');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $kitchen])
        ->call('move', $cupboard->id)
        ->assertHasErrors('placeId');

    assertDatabaseHas(Place::class, [
        'id' => $kitchen->id,
        'parent_id' => null,
    ]);
});

test('a place can not be moved inside a place deeper inside it', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Left cupboard');
    $drawer = placeInside($cupboard, 'Drawer 1');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $kitchen])
        ->call('move', $drawer->id)
        ->assertHasErrors('placeId');

    assertDatabaseHas(Place::class, [
        'id' => $kitchen->id,
        'parent_id' => null,
    ]);
});

test('a place can not be moved to the place it is already in', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Left cupboard');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $cupboard])
        ->call('move', $kitchen->id)
        ->assertHasErrors('placeId');
});

test('a place at the top can not be moved to the top', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $kitchen])
        ->call('move', null)
        ->assertHasErrors('placeId');
});

test('a place can not be moved next to a place with the same name ignoring case', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Cupboard');
    $storageRoom = placeIn($household, 'Storage room');
    placeInside($storageRoom, 'cupboard');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $cupboard])
        ->call('move', $storageRoom->id)
        ->assertHasErrors('placeId')
        ->assertSee('There is already a place called “Cupboard” here.');

    assertDatabaseHas(Place::class, [
        'id' => $cupboard->id,
        'parent_id' => $kitchen->id,
    ]);
});

test('a place can not be moved into a place of another household', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $placeOfAnotherHousehold = Place::factory()->create();

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $kitchen])
        ->call('move', $placeOfAnotherHousehold->id)
        ->assertHasErrors('placeId');

    assertDatabaseHas(Place::class, [
        'id' => $kitchen->id,
        'parent_id' => null,
    ]);
});

test('a viewer can not move a place', function (): void {
    $household = Household::factory()->create();
    $viewer = memberOf($household, Role::Viewer);
    $kitchen = placeIn($household, 'Kitchen');
    $storageRoom = placeIn($household, 'Storage room');

    Livewire::actingAs($viewer)
        ->test(Show::class, ['household' => $household, 'place' => $kitchen])
        ->call('move', $storageRoom->id)
        ->assertForbidden();

    assertDatabaseHas(Place::class, [
        'id' => $kitchen->id,
        'parent_id' => null,
    ]);
});
