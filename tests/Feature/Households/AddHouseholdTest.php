<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Livewire\Households\Index;
use App\Models\Household;
use App\Models\HouseholdUser;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;

test('a user adds a household', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('name', 'Home')
        ->call('add')
        ->assertHasNoErrors();

    assertDatabaseHas(Household::class, [
        'name' => 'Home',
        'owner_id' => $user->id,
    ]);
});

test('after adding a household the app opens its places page with a message', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('name', 'Home')
        ->call('add')
        ->assertRedirect(route('places.index', Household::query()->sole()));

    expect(session('message'))->toBe('Home added.');
});

test('the user becomes a member of the new household with the role Owner and no label', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('name', 'Home')
        ->call('add');

    assertDatabaseHas(HouseholdUser::class, [
        'user_id' => $user->id,
        'role' => Role::Owner,
        'label' => null,
    ]);
});

test('a user can own more than one household', function (): void {
    $user = User::factory()->create();
    Household::factory()->for($user, 'owner')->create(['name' => 'Home']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('name', 'Office')
        ->call('add')
        ->assertHasNoErrors();

    assertDatabaseHas(Household::class, [
        'name' => 'Office',
        'owner_id' => $user->id,
    ]);
});

test('the name is trimmed', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('name', '  Home  ')
        ->call('add');

    assertDatabaseHas(Household::class, [
        'name' => 'Home',
    ]);
});

test('the name is required', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('name', '')
        ->call('add')
        ->assertHasErrors(['name' => 'required']);

    assertDatabaseEmpty(Household::class);
});

test('the name can not be longer than 255 characters', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('name', str_repeat('a', 256))
        ->call('add')
        ->assertHasErrors(['name' => 'max']);

    assertDatabaseEmpty(Household::class);
});
