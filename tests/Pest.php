<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Borrower;
use App\Models\Household;
use App\Models\Item;
use App\Models\Place;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(LazilyRefreshDatabase::class)
    ->in('Feature');

pest()->tia()
    ->locally();

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * A new user who is a member of the household with the role, and their own label for it if given.
 */
function memberOf(Household $household, Role $role, ?string $label = null): User
{
    $member = User::factory()->create();

    $household->members()->attach($member, [
        'role' => $role,
        'label' => $label,
    ]);

    return $member;
}

/**
 * A place at the top of the household.
 */
function placeIn(Household $household, string $name): Place
{
    return Place::factory()
        ->for($household)
        ->create(['name' => $name]);
}

/**
 * A place inside another place, in the same household.
 */
function placeInside(Place $parent, string $name): Place
{
    return Place::factory()
        ->inside($parent)
        ->create(['name' => $name]);
}

/**
 * An item in the place, with optional notes.
 */
function itemIn(Place $place, string $name, ?string $notes = null): Item
{
    return Item::factory()
        ->in($place)
        ->create([
            'name' => $name,
            'notes' => $notes,
        ]);
}

/**
 * A saved borrower of the household.
 */
function borrowerOf(Household $household, string $name): Borrower
{
    return Borrower::factory()
        ->for($household)
        ->create(['name' => $name]);
}

/**
 * An archived item of the household.
 */
function archivedItemOf(Household $household, string $name): Item
{
    return Item::factory()
        ->archived()
        ->create([
            'household_id' => $household->id,
            'name' => $name,
        ]);
}
