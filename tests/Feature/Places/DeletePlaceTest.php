<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Livewire\Places\Show;
use App\Models\Household;
use Livewire\Livewire;

use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;

test('the owner deletes a place', function (): void {
    $household = Household::factory()->create();
    $place = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->call('delete')
        ->assertHasNoErrors();

    assertModelMissing($place);
});

test('an editor can delete a place', function (): void {
    $household = Household::factory()->create();
    $editor = memberOf($household, Role::Editor);
    $place = placeIn($household, 'Kitchen');

    Livewire::actingAs($editor)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->call('delete')
        ->assertHasNoErrors();

    assertModelMissing($place);
});

test('after deleting a place at the top the app opens the places page with a message', function (): void {
    $household = Household::factory()->create();
    $place = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->call('delete')
        ->assertRedirect(route('places.index', $household));

    expect(session('message'))->toBe('Kitchen deleted.');
});

test('after deleting a place inside another place the app opens that place with a message', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $drawer = placeInside($kitchen, 'Drawer');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $drawer])
        ->call('delete')
        ->assertRedirect(route('places.show', [$household, $kitchen]));

    expect(session('message'))->toBe('Drawer deleted.');
});

test('a place with places inside shows why it can not be deleted', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    placeInside($kitchen, 'Drawer');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $kitchen])
        ->assertSee('Move or remove the 1 place inside first.');
});

test('a place with places inside can not be deleted', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    placeInside($kitchen, 'Drawer');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $kitchen])
        ->call('delete')
        ->assertHasErrors('place')
        ->assertNoRedirect();

    assertModelExists($kitchen);
});

test('a viewer can not delete a place', function (): void {
    $household = Household::factory()->create();
    $viewer = memberOf($household, Role::Viewer);
    $place = placeIn($household, 'Kitchen');

    Livewire::actingAs($viewer)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->call('delete')
        ->assertForbidden();

    assertModelExists($place);
});
