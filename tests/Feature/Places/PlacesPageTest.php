<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Livewire\Places\Index;
use App\Models\Household;
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

test('opening the places page saves the household as the one used last', function (): void {
    $user = User::factory()->create();
    $household = Household::factory()->create();
    $household->members()->attach($user, ['role' => Role::Viewer]);
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
    Place::factory()->for($household)->create(['name' => 'Kitchen']);

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSeeInOrder(['Home', 'Kitchen'])
        ->assertDontSee('No places yet');
});

test('a member who set a label sees the label at the top of the tree', function (): void {
    $user = User::factory()->create();
    $household = Household::factory()->create(['name' => 'Lake house']);
    $household->members()->attach($user, ['role' => Role::Viewer, 'label' => 'Parents’ home']);
    Place::factory()->for($household)->create(['name' => 'Kitchen']);

    Livewire::actingAs($user)
        ->test(Index::class, ['household' => $household])
        ->assertSeeInOrder(['Parents’ home', 'Kitchen'])
        ->assertDontSee('Lake house');
});

test('child places are listed under their parent at any depth', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);
    $kitchen = Place::factory()->for($household)->create(['name' => 'Kitchen']);
    $cupboard = Place::factory()->inside($kitchen)->create(['name' => 'Cupboard']);
    Place::factory()->inside($cupboard)->create(['name' => 'Zebra box']);
    Place::factory()->for($household)->create(['name' => 'Living room']);

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSeeInOrder(['Home', 'Kitchen', 'Cupboard', 'Zebra box', 'Living room']); // By name only, Zebra box would come last
});

test('sibling places are sorted by name ignoring case', function (): void {
    $household = Household::factory()->create();
    Place::factory()->for($household)->create(['name' => 'Cellar']);
    Place::factory()->for($household)->create(['name' => 'attic']);
    Place::factory()->for($household)->create(['name' => 'Basement']);

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSeeInOrder(['attic', 'Basement', 'Cellar']); // A case-sensitive sort puts "attic" last
});

test('places of another household are not listed', function (): void {
    $household = Household::factory()->create();
    Place::factory()->for($household)->create(['name' => 'Kitchen']);
    Place::factory()->create(['name' => 'Garage']);

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSee('Kitchen')
        ->assertDontSee('Garage');
});

test('the places are loaded with one query at any depth', function (): void {
    $household = Household::factory()->create();
    $kitchen = Place::factory()->for($household)->create(['name' => 'Kitchen']);
    $cupboard = Place::factory()->inside($kitchen)->create(['name' => 'Cupboard']);
    Place::factory()->inside($cupboard)->create(['name' => 'Drawer']);

    DB::enableQueryLog();

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household]);

    $placeQueries = collect(DB::getQueryLog())
        ->filter(fn (array $query): bool => str_contains($query['query'], 'from "places"'));

    expect($placeQueries)->toHaveCount(1);
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

test('a user who is not a member does not save the household as the one used last', function (): void {
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
