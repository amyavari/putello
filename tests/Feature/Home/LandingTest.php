<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Household;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('a member lands on the search of the household they opened last', function (): void {
    $household = Household::factory()->create();
    $user = memberOf($household, Role::Viewer);
    $user->update(['current_household_id' => $household->id]);
    actingAs($user);

    get(route('home'))
        ->assertRedirect(route('search.index', $household));
});

test('a user who has not opened any household yet lands on the households page', function (): void {
    actingAs(User::factory()->create());

    get(route('home'))
        ->assertRedirect(route('households.index'));
});

test('a user who is no longer a member of the household they opened last lands on the households page', function (): void {
    $household = Household::factory()->create();
    $user = User::factory()->create(['current_household_id' => $household->id]);
    actingAs($user);

    get(route('home'))
        ->assertRedirect(route('households.index'));
});

test('guests are redirected to the login page', function (): void {
    get(route('home'))
        ->assertRedirect(route('login'));
});

test('unverified users are redirected to the verification notice', function (): void {
    actingAs(User::factory()->unverified()->create());

    get(route('home'))
        ->assertRedirect(route('verification.notice'));
});
