<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Livewire\Places\Index;
use App\Models\Household;
use App\Models\Item;
use App\Models\Place;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;

test('the places page renders the places of the household', function (): void {
    $household = Household::factory()->create();
    actingAs($household->owner);

    get(route('places.index', $household))
        ->assertOk()
        /** @phpstan-ignore method.notFound (a Livewire macro on TestResponse) */
        ->assertSeeLivewire(Index::class);
});

test('opening the places page saves the household as the one opened last', function (): void {
    $household = Household::factory()->create();
    $user = memberOf($household, Role::Viewer);
    actingAs($user);

    get(route('places.index', $household))
        ->assertOk();

    assertDatabaseHas(User::class, [
        'id' => $user->id,
        'current_household_id' => $household->id,
    ]);
});

test('a member sees the empty state when the household has no places', function (): void {
    $household = Household::factory()->create();

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSee('Places')
        ->assertSee('No places yet')
        ->assertSee('Add the rooms and storage spots in your home, like Kitchen or Garage.');
});

test('the places are listed under the household', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);
    placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSeeInOrder(['Home', 'Kitchen'])
        ->assertDontSee('No places yet');
});

test('a member who set a label sees the label at the top of the tree', function (): void {
    $household = Household::factory()->create(['name' => 'Lake house']);
    $user = memberOf($household, Role::Viewer, label: 'Parents’ home');
    placeIn($household, 'Kitchen');

    Livewire::actingAs($user)
        ->test(Index::class, ['household' => $household])
        ->assertSeeInOrder(['Parents’ home', 'Kitchen'])
        ->assertDontSee('Lake house');
});

test('child places are listed under their parent at any depth', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Cupboard');
    placeInside($cupboard, 'Zebra box');
    placeIn($household, 'Living room');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSeeInOrder(['Home', 'Kitchen', 'Cupboard', 'Zebra box', 'Living room']); // By name only, Zebra box would come last
});

test('sibling places are sorted by name ignoring case', function (): void {
    $household = Household::factory()->create();
    placeIn($household, 'Cellar');
    placeIn($household, 'attic');
    placeIn($household, 'Basement');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSeeInOrder(['attic', 'Basement', 'Cellar']); // A case-sensitive sort puts "attic" last
});

test('places of another household are not listed', function (): void {
    $household = Household::factory()->create();
    placeIn($household, 'Kitchen');
    Place::factory()->create(['name' => 'Garage']);

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSee('Kitchen')
        ->assertDontSee('Garage');
});

test('each place in the tree opens its place page', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Cupboard');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSeeHtml(sprintf('href="%s"', route('places.show', [$household, $kitchen])))
        ->assertSeeHtml(sprintf('href="%s"', route('places.show', [$household, $cupboard])));
});

test('the places are loaded with one query at any depth', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Cupboard');
    placeInside($cupboard, 'Drawer');

    DB::enableQueryLog();

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household]);

    $placeQueries = collect(DB::getQueryLog())
        ->filter(fn (array $query): bool => str_contains($query['query'], 'from "places"'));

    expect($placeQueries)->toHaveCount(1);
});

test('a place with one item says 1 item', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    itemIn($kitchen, 'Kettle');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSeeInOrder(['Kitchen', '1 item']);
});

test('a place with more items shows the number of items', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    itemIn($kitchen, 'Kettle');
    itemIn($kitchen, 'Toaster');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSeeInOrder(['Kitchen', '2 items']);
});

test('a place with no items says empty', function (): void {
    $household = Household::factory()->create();
    placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSeeInOrder(['Kitchen', 'Empty']);
});

test('the number of items counts the items in the places inside at any depth', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Cupboard');
    $drawer = placeInside($cupboard, 'Drawer');
    itemIn($kitchen, 'Kettle');
    itemIn($cupboard, 'Plate');
    itemIn($drawer, 'Spoon');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSeeInOrder(['Kitchen', '3 items', 'Cupboard', '2 items', 'Drawer', '1 item']);
});

test('the household row shows the number of all items', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);
    itemIn(placeIn($household, 'Garage'), 'Bike');
    itemIn(placeIn($household, 'Kitchen'), 'Kettle');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSeeInOrder(['Home', '2 items', 'Garage', '1 item']);
});

test('items of another household are not counted', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);
    placeIn($household, 'Kitchen');
    Item::factory()->create();

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSeeInOrder(['Home', 'Empty', 'Kitchen', 'Empty']);
});

test('the items are counted with one query at any depth', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Cupboard');
    itemIn($kitchen, 'Kettle');
    itemIn($cupboard, 'Plate');

    DB::enableQueryLog();

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household]);

    $itemQueries = collect(DB::getQueryLog())
        ->filter(fn (array $query): bool => str_contains($query['query'], 'from "items"'));

    expect($itemQueries)->toHaveCount(1);
});

test('the owner and editors see the add place button', function (Role $role): void {
    $household = Household::factory()->create();
    $member = memberOf($household, $role);

    Livewire::actingAs($member)
        ->test(Index::class, ['household' => $household])
        ->assertSee('Add place')
        ->assertSeeHtml(sprintf('href="%s"', route('places.create', $household)));
})->with([
    'owner' => ['role' => Role::Owner],
    'editor' => ['role' => Role::Editor],
]);

test('a viewer does not see the add place button', function (): void {
    $household = Household::factory()->create();
    $viewer = memberOf($household, Role::Viewer);

    Livewire::actingAs($viewer)
        ->test(Index::class, ['household' => $household])
        ->assertDontSee('Add place');
});

test('the message of the previous page is shown', function (): void {
    $household = Household::factory()->create();

    actingAs($household->owner)
        ->withSession(['message' => 'Home added.'])
        ->get(route('places.index', $household))
        ->assertSee('Home added.');
});

test('a user who is not a member gets a not found page', function (): void {
    $household = Household::factory()->create();
    actingAs(User::factory()->create());

    get(route('places.index', $household))
        ->assertNotFound();
});

test('a user who is not a member does not save the household as the one opened last', function (): void {
    $user = User::factory()->create();
    $household = Household::factory()->create();
    actingAs($user);

    get(route('places.index', $household))
        ->assertNotFound();

    assertDatabaseHas(User::class, [
        'id' => $user->id,
        'current_household_id' => null,
    ]);
});

test('guests are redirected to the login page', function (): void {
    $household = Household::factory()->create();

    get(route('places.index', $household))
        ->assertRedirect(route('login'));
});

test('unverified users are redirected to the verification notice', function (): void {
    $household = Household::factory()->create();
    actingAs(User::factory()->unverified()->create());

    get(route('places.index', $household))
        ->assertRedirect(route('verification.notice'));
});
