<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Household;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('the menu shows the app name and the household of the page', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);
    actingAs($household->owner);

    get(route('places.index', $household))
        ->assertSeeInOrder(['Putello', 'Home']);
});

test('the menu shows the label a member gave the household', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);
    $member = memberOf($household, Role::Editor, 'Parents’ home');
    actingAs($member);

    get(route('places.index', $household))
        ->assertSeeInOrder(['Putello', 'Parents’ home']);
});

test('the menu shows that a viewer is a viewer', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);
    $viewer = memberOf($household, Role::Viewer);
    actingAs($viewer);

    get(route('places.index', $household))
        ->assertSee('Home · Viewer');
});

test('the menu links to the search of the household, before its places', function (): void {
    $household = Household::factory()->create();
    actingAs($household->owner);

    get(route('places.index', $household))
        ->assertSeeHtmlInOrder([
            sprintf('href="%s"', route('search.index', $household)),
            sprintf('href="%s"', route('places.index', $household)),
        ]);
});

test('the menu links to the places of the household', function (): void {
    $household = Household::factory()->create();
    actingAs($household->owner);

    get(route('places.index', $household))
        ->assertSeeHtml(sprintf('href="%s"', route('places.index', $household)));
});

test('the menu links to the households on every page', function (): void {
    $household = Household::factory()->create();
    actingAs($household->owner);

    get(route('places.index', $household))
        ->assertSeeHtml(sprintf('href="%s"', route('households.index')));
});

test('a page without a household shows no household in the menu', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);
    actingAs($household->owner);

    get(route('profile.edit'))
        ->assertDontSee('Home')
        ->assertDontSeeHtml(sprintf('href="%s"', route('places.index', $household)));
});

test('the account menu offers settings and log out', function (): void {
    $household = Household::factory()->create();
    actingAs($household->owner);

    get(route('places.index', $household))
        ->assertSeeInOrder(['Settings', 'Log out']);
});

test('the mobile header shows the app name and the household', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);
    actingAs($household->owner);

    get(route('places.index', $household))
        ->assertSeeHtmlInOrder(['data-test="mobile-header-title"', 'Putello', '– Home']);
});

test('the mobile header has a search button on a household page', function (): void {
    $household = Household::factory()->create();
    actingAs($household->owner);

    get(route('places.index', $household))
        ->assertSeeHtmlInOrder(['data-test="mobile-header-title"', 'aria-label="Search"'])
        ->assertSeeHtmlInOrder([
            'data-test="mobile-header-title"',
            sprintf('href="%s"', route('search.index', $household)),
        ]);
});

test('the mobile header has no search button on the search page', function (): void {
    $household = Household::factory()->create();
    actingAs($household->owner);

    get(route('search.index', $household))
        ->assertDontSeeHtml('aria-label="Search"');
});

test('the mobile header has no search button on a page without a household', function (): void {
    $household = Household::factory()->create();
    actingAs($household->owner);

    get(route('profile.edit'))
        ->assertDontSeeHtml('aria-label="Search"');
});

test('the starter links are gone', function (): void {
    $household = Household::factory()->create();
    actingAs($household->owner);

    get(route('places.index', $household))
        ->assertDontSee('Repository')
        ->assertDontSee('Documentation');
});
