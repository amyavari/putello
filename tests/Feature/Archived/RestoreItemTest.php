<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Livewire\Archived\Index;
use App\Livewire\Places\Picker;
use App\Models\Household;
use App\Models\Item;
use App\Models\Place;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertNotSoftDeleted;
use function Pest\Laravel\assertSoftDeleted;

test('the owner and editors see restore', function (Role $role): void {
    $household = Household::factory()->create();
    $member = memberOf($household, $role);
    archivedItemOf($household, 'Umbrella');

    Livewire::actingAs($member)
        ->test(Index::class, ['household' => $household])
        ->assertSeeInOrder(['Umbrella', 'Restore']);
})->with([
    'owner' => ['role' => Role::Owner],
    'editor' => ['role' => Role::Editor],
]);

test('a viewer does not see restore', function (): void {
    $household = Household::factory()->create();
    $viewer = memberOf($household, Role::Viewer);
    archivedItemOf($household, 'Umbrella');

    Livewire::actingAs($viewer)
        ->test(Index::class, ['household' => $household])
        ->assertSee('Umbrella')
        ->assertDontSee('Restore');
});

test('the archived page includes the place picker', function (): void {
    $household = Household::factory()->create();
    archivedItemOf($household, 'Umbrella');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        /** @phpstan-ignore method.notFound (a Livewire macro on TestResponse) */
        ->assertSeeLivewire(Picker::class);
});

test('the owner restores an item into the chosen place', function (): void {
    $household = Household::factory()->create();
    $umbrella = archivedItemOf($household, 'Umbrella');
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->call('chooseItemToRestore', $umbrella->id)
        ->call('restore', $kitchen->id)
        ->assertHasNoErrors();

    assertNotSoftDeleted($umbrella);
    assertDatabaseHas(Item::class, [
        'id' => $umbrella->id,
        'place_id' => $kitchen->id,
    ]);
});

test('an editor can restore an item', function (): void {
    $household = Household::factory()->create();
    $editor = memberOf($household, Role::Editor);
    $umbrella = archivedItemOf($household, 'Umbrella');
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::actingAs($editor)
        ->test(Index::class, ['household' => $household])
        ->call('chooseItemToRestore', $umbrella->id)
        ->call('restore', $kitchen->id)
        ->assertHasNoErrors();

    assertNotSoftDeleted($umbrella);
});

test('after restoring the app opens the item with a message', function (): void {
    $household = Household::factory()->create();
    $umbrella = archivedItemOf($household, 'Umbrella');
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->call('chooseItemToRestore', $umbrella->id)
        ->call('restore', $kitchen->id)
        ->assertRedirect(route('items.show', [$household, $umbrella]));

    expect(session('message'))->toBe('Umbrella restored. It is now in Kitchen.');
});

test('the household can not be chosen', function (): void {
    $household = Household::factory()->create();
    $umbrella = archivedItemOf($household, 'Umbrella');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->call('chooseItemToRestore', $umbrella->id)
        ->call('restore', null) // The household was chosen
        ->assertHasErrors('placeId');

    assertSoftDeleted($umbrella);
});

test('a place of another household can not be chosen', function (): void {
    $household = Household::factory()->create();
    $umbrella = archivedItemOf($household, 'Umbrella');
    $placeOfAnotherHousehold = Place::factory()->create();

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->call('chooseItemToRestore', $umbrella->id)
        ->call('restore', $placeOfAnotherHousehold->id)
        ->assertHasErrors('placeId');

    assertSoftDeleted($umbrella);
});

test('an item of another household can not be restored', function (): void {
    $household = Household::factory()->create();
    $itemOfAnotherHousehold = Item::factory()->archived()->create();
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->call('chooseItemToRestore', $itemOfAnotherHousehold->id)
        ->call('restore', $kitchen->id)
        ->assertNotFound();

    assertSoftDeleted($itemOfAnotherHousehold);
});

test('a viewer can not restore an item', function (): void {
    $household = Household::factory()->create();
    $viewer = memberOf($household, Role::Viewer);
    $umbrella = archivedItemOf($household, 'Umbrella');
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::actingAs($viewer)
        ->test(Index::class, ['household' => $household])
        ->call('chooseItemToRestore', $umbrella->id)
        ->call('restore', $kitchen->id)
        ->assertForbidden();

    assertSoftDeleted($umbrella);
});
