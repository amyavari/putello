<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Livewire\Search\Index;
use App\Models\Household;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;

test('the search page renders for a member', function (): void {
    $household = Household::factory()->create();
    actingAs($household->owner);

    get(route('search.index', $household))
        ->assertOk()
        /** @phpstan-ignore method.notFound (a Livewire macro on TestResponse) */
        ->assertSeeLivewire(Index::class);
});

test('opening the search page saves the household as the one opened last', function (): void {
    $household = Household::factory()->create();
    $user = memberOf($household, Role::Viewer);
    actingAs($user);

    get(route('search.index', $household))
        ->assertOk();

    assertDatabaseHas(User::class, [
        'id' => $user->id,
        'current_household_id' => $household->id,
    ]);
});

test('a member finds an item by its name, with the path of its place', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $drawer = placeInside($kitchen, 'Drawer 1');
    itemIn($drawer, 'Spare house keys');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSee('Search items and places')
        ->set('search', 'keys')
        ->assertSeeHtmlInOrder(['Items', 'Spare house keys', 'Kitchen › Drawer 1']);
});

test('a member finds an item by its notes', function (): void {
    $household = Household::factory()->create();
    $drawer = placeIn($household, 'Drawer 1');
    itemIn($drawer, 'Passport folder', 'Blue cover, with the birth certificates');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->set('search', 'birth')
        ->assertSee('Passport folder');
});

test('the search ignores upper and lower case', function (): void {
    $household = Household::factory()->create();
    $drawer = placeIn($household, 'Drawer 1');
    itemIn($drawer, 'Spare house keys');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->set('search', 'KEYS')
        ->assertSee('Spare house keys');
});

test('the items are listed by name', function (): void {
    $household = Household::factory()->create();
    $shelf = placeIn($household, 'Shelf A');
    itemIn($shelf, 'Drill');
    itemIn($shelf, 'Drawer liners');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->set('search', 'dr')
        ->assertSeeHtmlInOrder(['Drawer liners', 'Drill']);
});

test('a member finds a place by its name, with its path', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Left cupboard');
    placeInside($cupboard, 'Drawer 1');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->set('search', 'drawer')
        ->assertSeeHtmlInOrder(['Places', 'Drawer 1', 'Kitchen › Left cupboard › Drawer 1']);
});

test('archived items are not found', function (): void {
    $household = Household::factory()->create();
    archivedItemOf($household, 'Umbrella');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->set('search', 'umbr')
        ->assertDontSee('Umbrella');
});

test('items of another household are not found', function (): void {
    $household = Household::factory()->create();
    $otherDrawer = placeIn(Household::factory()->create(), 'Drawer 1');
    itemIn($otherDrawer, 'Spare house keys');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->set('search', 'keys')
        ->assertDontSee('Spare house keys');
});

test('places of another household are not found', function (): void {
    $household = Household::factory()->create();
    placeIn(Household::factory()->create(), 'Garage');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->set('search', 'gar')
        ->assertDontSee('Garage');
});

test('a search that finds nothing says so', function (): void {
    $household = Household::factory()->create();

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->set('search', 'umbrella')
        ->assertSee('Nothing found for “umbrella”')
        ->assertSee('Check the spelling, or add it as a new item.');
});

test('a user who is not a member gets a not found page', function (): void {
    $household = Household::factory()->create();
    actingAs(User::factory()->create());

    get(route('search.index', $household))
        ->assertNotFound();
});

test('guests are redirected to the login page', function (): void {
    $household = Household::factory()->create();

    get(route('search.index', $household))
        ->assertRedirect(route('login'));
});

test('unverified users are redirected to the verification notice', function (): void {
    $household = Household::factory()->create();
    actingAs(User::factory()->unverified()->create());

    get(route('search.index', $household))
        ->assertRedirect(route('verification.notice'));
});
