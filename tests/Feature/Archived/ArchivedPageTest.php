<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Livewire\Archived\Index;
use App\Livewire\Items\Show;
use App\Livewire\Places;
use App\Models\Household;
use App\Models\Item;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\travelTo;

test('the archived page renders the archived items', function (): void {
    $household = Household::factory()->create();
    actingAs($household->owner);

    get(route('archived.index', $household))
        ->assertOk()
        /** @phpstan-ignore method.notFound (a Livewire macro on TestResponse) */
        ->assertSeeLivewire(Index::class);
});

test('the breadcrumb shows the household', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSeeInOrder(['Home', 'Archived']);
});

test('archived items are listed newest first', function (): void {
    $household = Household::factory()->create();
    Item::factory()->archived()->create([
        'household_id' => $household->id,
        'name' => 'Umbrella',
        'deleted_at' => '2026-10-28 10:00:00',
    ]);
    Item::factory()->archived()->create([
        'household_id' => $household->id,
        'name' => 'Old phone charger',
        'deleted_at' => '2026-11-02 10:00:00',
    ]);

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSeeInOrder(['Old phone charger', 'Umbrella']);
});

test('the items section shows the number of archived items', function (): void {
    $household = Household::factory()->create();
    Item::factory()
        ->archived()
        ->count(2)
        ->create(['household_id' => $household->id]);

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSeeInOrder(['Items', '2']);
});

test('active items are not listed', function (): void {
    $household = Household::factory()->create();
    itemIn(placeIn($household, 'John’s room'), 'Medal');
    archivedItemOf($household, 'Umbrella');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSee('Umbrella')
        ->assertDontSee('Medal');
});

test('an item archived this year shows the date without the year', function (): void {
    travelTo('2026-11-15 10:00:00');
    $household = Household::factory()->create();
    Item::factory()->archived()->create([
        'household_id' => $household->id,
        'name' => 'Umbrella',
        'deleted_at' => '2026-11-02 10:00:00',
    ]);

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSeeInOrder(['Umbrella', 'Archived:', 'Nov 2'])
        ->assertDontSee('Nov 2, 2026');
});

test('an item archived in an earlier year shows the date with the year', function (): void {
    travelTo('2026-11-15 10:00:00');
    $household = Household::factory()->create();
    Item::factory()->archived()->create([
        'household_id' => $household->id,
        'name' => 'Umbrella',
        'deleted_at' => '2025-11-02 10:00:00',
    ]);

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSeeInOrder(['Umbrella', 'Archived:', 'Nov 2, 2025']);
});

test('an archived item shows the place it was in', function (): void {
    $household = Household::factory()->create();
    $kettle = itemIn(placeInside(placeIn($household, 'Kitchen'), 'Left cupboard'), 'Kettle');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $kettle])
        ->call('archive');

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSeeInOrder(['Kettle', 'Was in:', 'Kitchen › Left cupboard', 'Archived:']);
});

test('a member sees a message when nothing is archived', function (): void {
    $household = Household::factory()->create();

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertSee('Nothing is archived')
        ->assertSee('Archived items and borrowers appear here, and can be restored.');
});

test('archived items of another household are not listed', function (): void {
    $household = Household::factory()->create();
    Item::factory()->archived()->create(['name' => 'Umbrella']);

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->assertDontSee('Umbrella');
});

test('a viewer can open the archived page', function (): void {
    $household = Household::factory()->create();
    $viewer = memberOf($household, Role::Viewer);
    actingAs($viewer);

    get(route('archived.index', $household))
        ->assertOk();
});

test('a user who is not a member gets a not found page', function (): void {
    $household = Household::factory()->create();
    actingAs(User::factory()->create());

    get(route('archived.index', $household))
        ->assertNotFound();
});

test('the places page links to the archived page', function (): void {
    $household = Household::factory()->create();

    Livewire::actingAs($household->owner)
        ->test(Places\Index::class, ['household' => $household])
        ->assertSee('Archived items and borrowers')
        ->assertSeeHtml(sprintf('href="%s"', route('archived.index', $household)));
});
