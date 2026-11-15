<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Livewire\Places\Show;
use App\Models\Household;
use App\Models\Place;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

test('the owner renames a place', function (): void {
    $household = Household::factory()->create();
    $place = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->set('name', 'Pantry')
        ->call('rename')
        ->assertHasNoErrors();

    assertDatabaseHas(Place::class, [
        'id' => $place->id,
        'name' => 'Pantry',
    ]);
});

test('an editor can rename a place', function (): void {
    $household = Household::factory()->create();
    $editor = memberOf($household, Role::Editor);
    $place = placeIn($household, 'Kitchen');

    Livewire::actingAs($editor)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->set('name', 'Pantry')
        ->call('rename')
        ->assertHasNoErrors();

    assertDatabaseHas(Place::class, [
        'id' => $place->id,
        'name' => 'Pantry',
    ]);
});

test('the name field starts with the current name', function (): void {
    $household = Household::factory()->create();
    $place = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->assertSet('name', 'Kitchen');
});

test('after renaming the page shows the new name with a message', function (): void {
    $household = Household::factory()->create();
    $place = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->set('name', 'Pantry')
        ->call('rename')
        ->assertSeeInOrder(['Renamed to Pantry.', 'Pantry'])
        ->assertDontSee('Kitchen');
});

test('the new name is trimmed', function (): void {
    $household = Household::factory()->create();
    $place = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->set('name', '  Pantry  ')
        ->call('rename');

    assertDatabaseHas(Place::class, [
        'id' => $place->id,
        'name' => 'Pantry',
    ]);
});

test('the new name is required', function (): void {
    $household = Household::factory()->create();
    $place = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->set('name', '')
        ->call('rename')
        ->assertHasErrors(['name' => 'required'])
        ->assertSee('Give the place a name so you can find it later.');

    assertDatabaseHas(Place::class, [
        'id' => $place->id,
        'name' => 'Kitchen',
    ]);
});

test('the new name can not be longer than 255 characters', function (): void {
    $household = Household::factory()->create();
    $place = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->set('name', str_repeat('a', 256))
        ->call('rename')
        ->assertHasErrors(['name' => 'max']);

    assertDatabaseHas(Place::class, [
        'id' => $place->id,
        'name' => 'Kitchen',
    ]);
});

test('a place can not take the name of a sibling ignoring case', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    placeInside($kitchen, 'Cupboard');
    $drawer = placeInside($kitchen, 'Drawer');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $drawer])
        ->set('name', 'cupboard')
        ->call('rename')
        ->assertHasErrors('name')
        ->assertSee('There is already a place called “cupboard” here.');

    assertDatabaseHas(Place::class, [
        'id' => $drawer->id,
        'name' => 'Drawer',
    ]);
});

test('a place can be renamed to its own name with another case', function (): void {
    $household = Household::factory()->create();
    $place = placeIn($household, 'kitchen');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->set('name', 'Kitchen')
        ->call('rename')
        ->assertHasNoErrors();

    assertDatabaseHas(Place::class, [
        'id' => $place->id,
        'name' => 'Kitchen',
    ]);
});

test('the same name can be used inside another place', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    placeInside($kitchen, 'Cupboard');
    $garage = placeIn($household, 'Garage');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $garage])
        ->set('name', 'Cupboard')
        ->call('rename')
        ->assertHasNoErrors();

    assertDatabaseHas(Place::class, [
        'id' => $garage->id,
        'name' => 'Cupboard',
    ]);
});

test('the same name can be used in another household', function (): void {
    $household = Household::factory()->create();
    Place::factory()->create(['name' => 'Pantry']);
    $place = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->set('name', 'Pantry')
        ->call('rename')
        ->assertHasNoErrors();

    assertDatabaseHas(Place::class, [
        'id' => $place->id,
        'name' => 'Pantry',
    ]);
});

test('a viewer can not rename a place', function (): void {
    $household = Household::factory()->create();
    $viewer = memberOf($household, Role::Viewer);
    $place = placeIn($household, 'Kitchen');

    Livewire::actingAs($viewer)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->set('name', 'Pantry')
        ->call('rename')
        ->assertForbidden();

    assertDatabaseHas(Place::class, [
        'id' => $place->id,
        'name' => 'Kitchen',
    ]);
});
