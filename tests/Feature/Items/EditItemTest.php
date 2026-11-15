<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Livewire\Items\Edit;
use App\Models\Household;
use App\Models\Item;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;

test('the edit item page renders the edit item form', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');
    actingAs($household->owner);

    get(route('items.edit', [$household, $medal]))
        ->assertOk()
        /** @phpstan-ignore method.notFound (a Livewire macro on TestResponse) */
        ->assertSeeLivewire(Edit::class);
});

test('the form starts with the name and the notes of the item', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal', 'Taekwondo 2019');

    Livewire::actingAs($household->owner)
        ->test(Edit::class, ['household' => $household, 'item' => $medal])
        ->assertSet('name', 'Medal')
        ->assertSet('notes', 'Taekwondo 2019');
});

test('the owner changes the name of an item', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Edit::class, ['household' => $household, 'item' => $medal])
        ->set('name', 'Gold medal')
        ->call('save')
        ->assertHasNoErrors();

    assertDatabaseHas(Item::class, [
        'id' => $medal->id,
        'name' => 'Gold medal',
    ]);
});

test('the owner changes the notes of an item', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal', 'Taekwondo 2019');

    Livewire::actingAs($household->owner)
        ->test(Edit::class, ['household' => $household, 'item' => $medal])
        ->set('notes', 'Taekwondo 2020')
        ->call('save')
        ->assertHasNoErrors();

    assertDatabaseHas(Item::class, [
        'id' => $medal->id,
        'notes' => 'Taekwondo 2020',
    ]);
});

test('an editor can edit an item', function (): void {
    $household = Household::factory()->create();
    $editor = memberOf($household, Role::Editor);
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($editor)
        ->test(Edit::class, ['household' => $household, 'item' => $medal])
        ->set('name', 'Gold medal')
        ->call('save')
        ->assertHasNoErrors();

    assertDatabaseHas(Item::class, [
        'id' => $medal->id,
        'name' => 'Gold medal',
    ]);
});

test('the breadcrumb shows the household, the places of the path and the item', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Edit::class, ['household' => $household, 'item' => $medal])
        ->assertSeeInOrder(['Home', 'John’s room', 'Medal', 'Edit item'])
        ->assertSeeHtml(sprintf('href="%s"', route('items.show', [$household, $medal])));
});

test('after saving the app opens the item with a message', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Edit::class, ['household' => $household, 'item' => $medal])
        ->set('name', 'Gold medal')
        ->call('save')
        ->assertRedirect(route('items.show', [$household, $medal]));

    expect(session('message'))->toBe('Gold medal saved.');
});

test('the name and the notes are trimmed', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Edit::class, ['household' => $household, 'item' => $medal])
        ->set('name', '  Gold medal  ')
        ->set('notes', '  Taekwondo 2019  ')
        ->call('save');

    assertDatabaseHas(Item::class, [
        'id' => $medal->id,
        'name' => 'Gold medal',
        'notes' => 'Taekwondo 2019',
    ]);
});

test('emptying the notes removes them', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal', 'Taekwondo 2019');

    Livewire::actingAs($household->owner)
        ->test(Edit::class, ['household' => $household, 'item' => $medal])
        ->set('notes', '   ')
        ->call('save');

    assertDatabaseHas(Item::class, [
        'id' => $medal->id,
        'notes' => null,
    ]);
});

test('the name is required', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Edit::class, ['household' => $household, 'item' => $medal])
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required'])
        ->assertSee('Give the item a name so you can find it later.');

    assertDatabaseHas(Item::class, [
        'id' => $medal->id,
        'name' => 'Medal',
    ]);
});

test('the name can not be longer than 255 characters', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Edit::class, ['household' => $household, 'item' => $medal])
        ->set('name', str_repeat('a', 256))
        ->call('save')
        ->assertHasErrors(['name' => 'max']);

    assertDatabaseHas(Item::class, [
        'id' => $medal->id,
        'name' => 'Medal',
    ]);
});

test('the notes can not be longer than 255 characters', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Edit::class, ['household' => $household, 'item' => $medal])
        ->set('notes', str_repeat('a', 256))
        ->call('save')
        ->assertHasErrors(['notes' => 'max']);

    assertDatabaseHas(Item::class, [
        'id' => $medal->id,
        'notes' => null,
    ]);
});

test('an item of another household gives a not found page', function (): void {
    $household = Household::factory()->create();
    $itemOfAnotherHousehold = Item::factory()->create();
    actingAs($household->owner);

    get(route('items.edit', [$household, $itemOfAnotherHousehold]))
        ->assertNotFound();
});

test('a viewer can not open the edit item page', function (): void {
    $household = Household::factory()->create();
    $viewer = memberOf($household, Role::Viewer);
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');
    actingAs($viewer);

    get(route('items.edit', [$household, $medal]))
        ->assertForbidden();
});

test('a member who became a viewer after opening the page can not edit an item', function (): void {
    $household = Household::factory()->create();
    $member = memberOf($household, Role::Editor);
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    $component = Livewire::actingAs($member)
        ->test(Edit::class, ['household' => $household, 'item' => $medal]);

    $household->members()->updateExistingPivot($member, ['role' => Role::Viewer]);

    $component
        ->set('name', 'Gold medal')
        ->call('save')
        ->assertForbidden();

    assertDatabaseHas(Item::class, [
        'id' => $medal->id,
        'name' => 'Medal',
    ]);
});

test('a user who is not a member can not open the edit item page', function (): void {
    $medal = Item::factory()->create();
    actingAs(User::factory()->create());

    get(route('items.edit', [$medal->household, $medal]))
        ->assertNotFound();
});
