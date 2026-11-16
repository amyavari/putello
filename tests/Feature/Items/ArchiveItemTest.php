<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Livewire\Items\Show;
use App\Livewire\Places;
use App\Livewire\Places\Index;
use App\Models\Household;
use App\Models\Item;
use App\Models\Loan;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\assertNotSoftDeleted;
use function Pest\Laravel\assertSoftDeleted;
use function Pest\Laravel\get;

test('the owner and editors see archive in more actions', function (Role $role): void {
    $household = Household::factory()->create();
    $member = memberOf($household, $role);
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($member)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        ->assertSee('Archive');
})->with([
    'owner' => ['role' => Role::Owner],
    'editor' => ['role' => Role::Editor],
]);

test('the owner archives an item', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        ->call('archive')
        ->assertHasNoErrors();

    assertSoftDeleted($medal);
});

test('archiving takes the item out of its place', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        ->call('archive');

    assertDatabaseHas(Item::class, [
        'id' => $medal->id,
        'place_id' => null,
    ]);
});

test('an editor can archive an item', function (): void {
    $household = Household::factory()->create();
    $editor = memberOf($household, Role::Editor);
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($editor)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        ->call('archive')
        ->assertHasNoErrors();

    assertSoftDeleted($medal);
});

test('after archiving the app opens the place the item was in with a message', function (): void {
    $household = Household::factory()->create();
    $johnsRoom = placeIn($household, 'John’s room');
    $medal = itemIn($johnsRoom, 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        ->call('archive')
        ->assertRedirect(route('places.show', [$household, $johnsRoom]));

    expect(session('message'))->toBe('Medal archived.');
});

test('the place page does not list archived items', function (): void {
    $household = Household::factory()->create();
    $johnsRoom = placeIn($household, 'John’s room');
    itemIn($johnsRoom, 'Medal');
    archivedItemOf($household, 'Umbrella');

    Livewire::actingAs($household->owner)
        ->test(Places\Show::class, ['household' => $household, 'place' => $johnsRoom])
        ->assertSee('Medal')
        ->assertDontSee('Umbrella');
});

test('archived items are not counted on the places page', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);
    itemIn(placeIn($household, 'Kitchen'), 'Kettle');
    archivedItemOf($household, 'Umbrella');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSeeInOrder(['Home', '1 item', 'Kitchen', '1 item'])
        ->assertDontSee('2 items');
});

test('a place whose items are all archived can be deleted', function (): void {
    $household = Household::factory()->create();
    $johnsRoom = placeIn($household, 'John’s room');
    $medal = itemIn($johnsRoom, 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        ->call('archive');

    Livewire::actingAs($household->owner)
        ->test(Places\Show::class, ['household' => $household, 'place' => $johnsRoom])
        ->call('delete')
        ->assertHasNoErrors();

    assertModelMissing($johnsRoom);
});

test('the page of an archived item gives a not found page', function (): void {
    $household = Household::factory()->create();
    $umbrella = archivedItemOf($household, 'Umbrella');
    actingAs($household->owner);

    get(route('items.show', [$household, $umbrella]))
        ->assertNotFound();
});

test('a viewer can not archive an item', function (): void {
    $household = Household::factory()->create();
    $viewer = memberOf($household, Role::Viewer);
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($viewer)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        ->call('archive')
        ->assertForbidden();

    assertNotSoftDeleted($medal);
});

test('a lent item shows why it can not be archived', function (): void {
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    Loan::factory()->for($drill)->create();

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $drill])
        ->assertSee('Close the loan first.');
});

test('a lent item can not be archived', function (): void {
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    Loan::factory()->for($drill)->create();

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $drill])
        ->call('archive')
        ->assertHasErrors('item')
        ->assertNoRedirect();

    assertNotSoftDeleted($drill);
});
