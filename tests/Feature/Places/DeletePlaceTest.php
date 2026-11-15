<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Livewire\Places\Show;
use App\Models\Household;
use App\Models\Place;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;

test('the owner deletes a place', function (): void {
    $household = Household::factory()->create();
    $place = Place::factory()->for($household)->create(['name' => 'Kitchen']);

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->call('delete')
        ->assertHasNoErrors();

    assertModelMissing($place);
});

test('an editor can delete a place', function (): void {
    $editor = User::factory()->create();
    $household = Household::factory()->create();
    $household->members()->attach($editor, ['role' => Role::Editor]);
    $place = Place::factory()->for($household)->create(['name' => 'Kitchen']);

    Livewire::actingAs($editor)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->call('delete')
        ->assertHasNoErrors();

    assertModelMissing($place);
});

test('after deleting a place at the top the app opens the places page with a message', function (): void {
    $household = Household::factory()->create();
    $place = Place::factory()->for($household)->create(['name' => 'Kitchen']);

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->call('delete')
        ->assertRedirect(route('places.index', $household));

    expect(session('message'))->toBe('Kitchen deleted.');
});

test('after deleting a place inside another place the app opens that place with a message', function (): void {
    $household = Household::factory()->create();
    $kitchen = Place::factory()->for($household)->create(['name' => 'Kitchen']);
    $drawer = Place::factory()->inside($kitchen)->create(['name' => 'Drawer']);

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $drawer])
        ->call('delete')
        ->assertRedirect(route('places.show', [$household, $kitchen]));

    expect(session('message'))->toBe('Drawer deleted.');
});

test('a place with places inside shows why it can not be deleted', function (): void {
    $household = Household::factory()->create();
    $kitchen = Place::factory()->for($household)->create(['name' => 'Kitchen']);
    Place::factory()->inside($kitchen)->create(['name' => 'Drawer']);

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $kitchen])
        ->assertSee('Move or remove the 1 place inside first.');
});

test('a place with places inside can not be deleted', function (): void {
    $household = Household::factory()->create();
    $kitchen = Place::factory()->for($household)->create(['name' => 'Kitchen']);
    Place::factory()->inside($kitchen)->create(['name' => 'Drawer']);

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $kitchen])
        ->call('delete')
        ->assertHasErrors('place')
        ->assertNoRedirect();

    assertModelExists($kitchen);
});

test('a viewer can not delete a place', function (): void {
    $viewer = User::factory()->create();
    $household = Household::factory()->create();
    $household->members()->attach($viewer, ['role' => Role::Viewer]);
    $place = Place::factory()->for($household)->create(['name' => 'Kitchen']);

    Livewire::actingAs($viewer)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->call('delete')
        ->assertForbidden();

    assertModelExists($place);
});
