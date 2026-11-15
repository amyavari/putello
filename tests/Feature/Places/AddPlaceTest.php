<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Livewire\Places\Create;
use App\Models\Household;
use App\Models\Place;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;

test('the add place page renders the add place form', function (): void {
    $household = Household::factory()->create();
    actingAs($household->owner);

    get(route('places.create', $household))
        ->assertOk()
        /** @phpstan-ignore method.notFound (a Livewire macro on TestResponse) */
        ->assertSeeLivewire(Create::class);
});

test('the owner adds a place at the top of the household', function (): void {
    $household = Household::factory()->create();

    Livewire::actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->set('name', 'Kitchen')
        ->call('add')
        ->assertHasNoErrors();

    assertDatabaseHas(Place::class, [
        'household_id' => $household->id,
        'parent_id' => null,
        'name' => 'Kitchen',
    ]);
});

test('the owner adds a place inside another place', function (): void {
    $household = Household::factory()->create();
    $kitchen = Place::factory()->for($household)->create(['name' => 'Kitchen']);

    Livewire::withQueryParams(['inside' => $kitchen->id])
        ->actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->set('name', 'Left cupboard')
        ->call('add')
        ->assertHasNoErrors();

    assertDatabaseHas(Place::class, [
        'household_id' => $household->id,
        'parent_id' => $kitchen->id,
        'name' => 'Left cupboard',
    ]);
});

test('an editor can add a place', function (): void {
    $editor = User::factory()->create();
    $household = Household::factory()->create();
    $household->members()->attach($editor, ['role' => Role::Editor]);

    Livewire::actingAs($editor)
        ->test(Create::class, ['household' => $household])
        ->set('name', 'Kitchen')
        ->call('add')
        ->assertHasNoErrors();

    assertDatabaseHas(Place::class, [
        'household_id' => $household->id,
        'name' => 'Kitchen',
    ]);
});

test('the form shows the household as the place a top place goes inside', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);

    Livewire::actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->assertSeeInOrder(['Inside', 'Home']);
});

test('the form shows the place the new place goes inside', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);
    $kitchen = Place::factory()->for($household)->create(['name' => 'Kitchen']);

    Livewire::withQueryParams(['inside' => $kitchen->id])
        ->actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->assertSeeInOrder(['Inside', 'Kitchen']);
});

test('after adding a place the app opens the places page with a message', function (): void {
    $household = Household::factory()->create();

    Livewire::actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->set('name', 'Kitchen')
        ->call('add')
        ->assertRedirect(route('places.index', $household));

    expect(session('message'))->toBe('Kitchen added.');
});

test('the name is trimmed', function (): void {
    $household = Household::factory()->create();

    Livewire::actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->set('name', '  Kitchen  ')
        ->call('add');

    assertDatabaseHas(Place::class, [
        'name' => 'Kitchen',
    ]);
});

test('the name is required', function (): void {
    $household = Household::factory()->create();

    Livewire::actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->set('name', '')
        ->call('add')
        ->assertHasErrors(['name' => 'required'])
        ->assertSee('Give the place a name so you can find it later.');

    assertDatabaseEmpty(Place::class);
});

test('the name can not be longer than 255 characters', function (): void {
    $household = Household::factory()->create();

    Livewire::actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->set('name', str_repeat('a', 256))
        ->call('add')
        ->assertHasErrors(['name' => 'max']);

    assertDatabaseEmpty(Place::class);
});

test('two sibling places can not have the same name ignoring case', function (): void {
    $household = Household::factory()->create();
    Place::factory()->for($household)->create(['name' => 'Kitchen']);

    Livewire::actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->set('name', 'kitchen')
        ->call('add')
        ->assertHasErrors('name')
        ->assertSee('There is already a place called “kitchen” here.');

    assertDatabaseCount(Place::class, 1);
});

test('the same name can be used inside another place', function (): void {
    $household = Household::factory()->create();
    $kitchen = Place::factory()->for($household)->create(['name' => 'Kitchen']);
    Place::factory()->inside($kitchen)->create(['name' => 'Cupboard']);

    Livewire::actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->set('name', 'Cupboard')
        ->call('add')
        ->assertHasNoErrors();

    assertDatabaseHas(Place::class, [
        'parent_id' => null,
        'name' => 'Cupboard',
    ]);
});

test('the same name can be used in another household', function (): void {
    $household = Household::factory()->create();
    Place::factory()->create(['name' => 'Kitchen']);

    Livewire::actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->set('name', 'Kitchen')
        ->call('add')
        ->assertHasNoErrors();

    assertDatabaseHas(Place::class, [
        'household_id' => $household->id,
        'name' => 'Kitchen',
    ]);
});

test('a place of another household can not be the place a new place goes inside', function (): void {
    $household = Household::factory()->create();
    $placeOfAnotherHousehold = Place::factory()->create();
    actingAs($household->owner);

    get(route('places.create', [$household, 'inside' => $placeOfAnotherHousehold]))
        ->assertNotFound();
});

test('a viewer can not open the add place page', function (): void {
    $viewer = User::factory()->create();
    $household = Household::factory()->create();
    $household->members()->attach($viewer, ['role' => Role::Viewer]);
    actingAs($viewer);

    get(route('places.create', $household))
        ->assertForbidden();
});

test('a member who became a viewer after opening the page can not add a place', function (): void {
    $member = User::factory()->create();
    $household = Household::factory()->create();
    $household->members()->attach($member, ['role' => Role::Editor]);

    $component = Livewire::actingAs($member)
        ->test(Create::class, ['household' => $household]);

    $household->members()->updateExistingPivot($member, ['role' => Role::Viewer]);

    $component
        ->set('name', 'Kitchen')
        ->call('add')
        ->assertForbidden();

    assertDatabaseEmpty(Place::class);
});

test('a user who is not a member can not open the add place page', function (): void {
    $household = Household::factory()->create();
    actingAs(User::factory()->create());

    get(route('places.create', $household))
        ->assertNotFound();
});
