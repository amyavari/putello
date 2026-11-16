<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Livewire\Items\Create;
use App\Livewire\Places\Picker;
use App\Models\Household;
use App\Models\Item;
use App\Models\Place;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;

test('the add item page renders the add item form', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    actingAs($household->owner);

    get(route('items.create', [$household, 'inside' => $kitchen]))
        ->assertOk()
        /** @phpstan-ignore method.notFound (a Livewire macro on TestResponse) */
        ->assertSeeLivewire(Create::class);
});

test('the owner adds an item to a place', function (): void {
    $household = Household::factory()->create();
    $room = placeIn($household, 'John’s room');

    Livewire::withQueryParams(['inside' => $room->id])
        ->actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->set('name', 'Medal')
        ->set('notes', 'Taekwondo 2019')
        ->call('add')
        ->assertHasNoErrors();

    assertDatabaseHas(Item::class, [
        'household_id' => $household->id,
        'place_id' => $room->id,
        'name' => 'Medal',
        'notes' => 'Taekwondo 2019',
    ]);
});

test('an editor can add an item', function (): void {
    $household = Household::factory()->create();
    $editor = memberOf($household, Role::Editor);
    $room = placeIn($household, 'John’s room');

    Livewire::withQueryParams(['inside' => $room->id])
        ->actingAs($editor)
        ->test(Create::class, ['household' => $household])
        ->set('name', 'Medal')
        ->call('add')
        ->assertHasNoErrors();

    assertDatabaseHas(Item::class, [
        'place_id' => $room->id,
        'name' => 'Medal',
    ]);
});

test('the form shows the place the item goes in', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $drawer = placeInside($kitchen, 'Drawer 1');

    Livewire::withQueryParams(['inside' => $drawer->id])
        ->actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->assertSeeInOrder(['Place', 'Drawer 1']);
});

test('the breadcrumb shows the household and the places of the path', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);
    $kitchen = placeIn($household, 'Kitchen');
    $drawer = placeInside($kitchen, 'Drawer 1');

    Livewire::withQueryParams(['inside' => $drawer->id])
        ->actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->assertSeeInOrder(['Home', 'Kitchen', 'Drawer 1', 'Add item']);
});

test('the add item page includes the place picker', function (): void {
    $household = Household::factory()->create();
    $room = placeIn($household, 'John’s room');

    Livewire::withQueryParams(['inside' => $room->id])
        ->actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->assertSeeLivewire(Picker::class);
});

test('the owner adds an item to the place chosen in the picker', function (): void {
    $household = Household::factory()->create();
    $room = placeIn($household, 'John’s room');
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::withQueryParams(['inside' => $room->id])
        ->actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->call('useChosenPlace', $kitchen->id)
        ->set('name', 'Kettle')
        ->call('add')
        ->assertHasNoErrors();

    assertDatabaseHas(Item::class, [
        'place_id' => $kitchen->id,
        'name' => 'Kettle',
    ]);
});

test('after choosing a place the breadcrumb shows its path', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);
    $room = placeIn($household, 'John’s room');
    $kitchen = placeIn($household, 'Kitchen');
    $drawer = placeInside($kitchen, 'Drawer 1');

    Livewire::withQueryParams(['inside' => $room->id])
        ->actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->call('useChosenPlace', $drawer->id)
        ->assertSeeInOrder(['Home', 'Kitchen', 'Drawer 1', 'Add item']);
});

test('the household can not be chosen for a new item', function (): void {
    $household = Household::factory()->create();
    $room = placeIn($household, 'John’s room');

    Livewire::withQueryParams(['inside' => $room->id])
        ->actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->call('useChosenPlace', null) // The household was chosen
        ->assertHasErrors('placeId')
        ->assertSet('place.id', $room->id);
});

test('a place of another household can not be chosen', function (): void {
    $household = Household::factory()->create();
    $room = placeIn($household, 'John’s room');
    $placeOfAnotherHousehold = Place::factory()->create();

    Livewire::withQueryParams(['inside' => $room->id])
        ->actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->call('useChosenPlace', $placeOfAnotherHousehold->id)
        ->assertHasErrors('placeId')
        ->assertSet('place.id', $room->id);
});

test('the add item page opens without a place', function (): void {
    $household = Household::factory()->create();
    actingAs($household->owner);

    get(route('items.create', $household))
        ->assertOk();
});

test('without a place the form offers to choose one', function (): void {
    $household = Household::factory()->create();

    Livewire::actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->assertSeeInOrder(['Place', 'Choose place']);
});

test('the owner adds an item to the place chosen when the page opened without a place', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->call('useChosenPlace', $kitchen->id)
        ->set('name', 'Kettle')
        ->call('add')
        ->assertHasNoErrors();

    assertDatabaseHas(Item::class, [
        'place_id' => $kitchen->id,
        'name' => 'Kettle',
    ]);
});

test('the place is required', function (): void {
    $household = Household::factory()->create();

    Livewire::actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->set('name', 'Kettle')
        ->call('add')
        ->assertHasErrors('placeId')
        ->assertSee('Choose a place for the item.');

    assertDatabaseEmpty(Item::class);
});

test('the name can be filled in from the url', function (): void {
    $household = Household::factory()->create();

    Livewire::withQueryParams(['name' => 'Umbrella'])
        ->actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->assertSet('name', 'Umbrella');
});

test('without a place cancel goes back to search', function (): void {
    $household = Household::factory()->create();

    Livewire::actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->assertSeeHtml(sprintf('href="%s"', route('search.index', $household)));
});

test('after adding an item the app opens it with a message', function (): void {
    $household = Household::factory()->create();
    $room = placeIn($household, 'John’s room');

    Livewire::withQueryParams(['inside' => $room->id])
        ->actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->set('name', 'Medal')
        ->call('add')
        ->assertRedirect(route('items.show', [$household, Item::query()->sole()]));

    expect(session('message'))->toBe('Medal added.');
});

test('the name and the notes are trimmed', function (): void {
    $household = Household::factory()->create();
    $room = placeIn($household, 'John’s room');

    Livewire::withQueryParams(['inside' => $room->id])
        ->actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->set('name', '  Medal  ')
        ->set('notes', '  Taekwondo 2019  ')
        ->call('add');

    assertDatabaseHas(Item::class, [
        'name' => 'Medal',
        'notes' => 'Taekwondo 2019',
    ]);
});

test('empty notes are saved as no notes', function (): void {
    $household = Household::factory()->create();
    $room = placeIn($household, 'John’s room');

    Livewire::withQueryParams(['inside' => $room->id])
        ->actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->set('name', 'Medal')
        ->set('notes', '   ')
        ->call('add');

    assertDatabaseHas(Item::class, [
        'name' => 'Medal',
        'notes' => null,
    ]);
});

test('the name is required', function (): void {
    $household = Household::factory()->create();
    $room = placeIn($household, 'John’s room');

    Livewire::withQueryParams(['inside' => $room->id])
        ->actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->set('name', '')
        ->call('add')
        ->assertHasErrors(['name' => 'required'])
        ->assertSee('Give the item a name so you can find it later.');

    assertDatabaseEmpty(Item::class);
});

test('the name can not be longer than 255 characters', function (): void {
    $household = Household::factory()->create();
    $room = placeIn($household, 'John’s room');

    Livewire::withQueryParams(['inside' => $room->id])
        ->actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->set('name', str_repeat('a', 256))
        ->call('add')
        ->assertHasErrors(['name' => 'max']);

    assertDatabaseEmpty(Item::class);
});

test('the notes can not be longer than 255 characters', function (): void {
    $household = Household::factory()->create();
    $room = placeIn($household, 'John’s room');

    Livewire::withQueryParams(['inside' => $room->id])
        ->actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->set('name', 'Medal')
        ->set('notes', str_repeat('a', 256))
        ->call('add')
        ->assertHasErrors(['notes' => 'max']);

    assertDatabaseEmpty(Item::class);
});

test('a place of another household can not be the place of a new item', function (): void {
    $household = Household::factory()->create();
    $placeOfAnotherHousehold = Place::factory()->create();
    actingAs($household->owner);

    get(route('items.create', [$household, 'inside' => $placeOfAnotherHousehold]))
        ->assertNotFound();
});

test('a viewer can not open the add item page', function (): void {
    $household = Household::factory()->create();
    $viewer = memberOf($household, Role::Viewer);
    $kitchen = placeIn($household, 'Kitchen');
    actingAs($viewer);

    get(route('items.create', [$household, 'inside' => $kitchen]))
        ->assertForbidden();
});

test('a member who became a viewer after opening the page can not add an item', function (): void {
    $household = Household::factory()->create();
    $member = memberOf($household, Role::Editor);
    $kitchen = placeIn($household, 'Kitchen');

    $component = Livewire::withQueryParams(['inside' => $kitchen->id])
        ->actingAs($member)
        ->test(Create::class, ['household' => $household]);

    $household->members()->updateExistingPivot($member, ['role' => Role::Viewer]);

    $component
        ->set('name', 'Kettle')
        ->call('add')
        ->assertForbidden();

    assertDatabaseEmpty(Item::class);
});

test('a user who is not a member can not open the add item page', function (): void {
    $kitchen = Place::factory()->create();
    actingAs(User::factory()->create());

    get(route('items.create', [$kitchen->household, 'inside' => $kitchen]))
        ->assertNotFound();
});
