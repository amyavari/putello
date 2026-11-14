<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Livewire\Places\Index;
use App\Models\Household;
use App\Models\User;
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
