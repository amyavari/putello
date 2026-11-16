<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Livewire\Items\Show;
use App\Models\Household;
use App\Models\Item;
use App\Models\Loan;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\travelTo;

test('the item page renders the item', function (): void {
    $household = Household::factory()->create();
    $item = itemIn(placeIn($household, 'Kitchen'), 'Kettle');
    actingAs($household->owner);

    get(route('items.show', [$household, $item]))
        ->assertOk()
        /** @phpstan-ignore method.notFound (a Livewire macro on TestResponse) */
        ->assertSeeLivewire(Show::class);
});

test('the breadcrumb shows the household and the places of the path', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);
    $kitchen = placeIn($household, 'Kitchen');
    $drawer = placeInside($kitchen, 'Drawer 1');
    $spoon = itemIn($drawer, 'Spoon');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $spoon])
        ->assertSeeInOrder(['Home', 'Kitchen', 'Drawer 1', 'Spoon']);
});

test('the breadcrumb links to the places page and to the places of the path', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $drawer = placeInside($kitchen, 'Drawer 1');
    $spoon = itemIn($drawer, 'Spoon');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $spoon])
        ->assertSeeHtml(sprintf('href="%s"', route('places.index', $household)))
        ->assertSeeHtml(sprintf('href="%s"', route('places.show', [$household, $kitchen])))
        ->assertSeeHtml(sprintf('href="%s"', route('places.show', [$household, $drawer])));
});

test('the place card shows the path of the place', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $drawer = placeInside($kitchen, 'Drawer 1');
    $spoon = itemIn($drawer, 'Spoon');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $spoon])
        ->assertSeeInOrder(['Place', 'Kitchen', '›', 'Drawer 1']);
});

test('an item that is not lent says so', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        ->assertSeeInOrder(['Loan', 'Not lent']);
});

test('a lent item shows who has it and since when', function (): void {
    travelTo('2026-11-16');
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    Loan::factory()
        ->for($drill)
        ->for(borrowerOf($household, 'Neighbor Reza'))
        ->create(['lent_at' => '2026-11-03']);

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $drill])
        ->assertSeeInOrder(['Loan', 'Lent to Neighbor Reza · since Nov 3'])
        ->assertDontSee('Not lent');
});

test('a lent item shows its due date', function (): void {
    travelTo('2026-11-16');
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    Loan::factory()
        ->for($drill)
        ->create([
            'lent_at' => '2026-11-03',
            'due_at' => '2026-11-17',
        ]);

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $drill])
        ->assertSee('Due Nov 17');
});

test('a loan with no due date shows no due date', function (): void {
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    Loan::factory()->for($drill)->create(); // No due date

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $drill])
        ->assertDontSee('Due ');
});

test('the lending date shows the year when it is not this year', function (): void {
    travelTo('2026-11-16');
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    Loan::factory()
        ->for($drill)
        ->for(borrowerOf($household, 'Neighbor Reza'))
        ->create(['lent_at' => '2025-12-20']);

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $drill])
        ->assertSee('Lent to Neighbor Reza · since Dec 20, 2025');
});

test('the notes are shown', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal', notes: 'Taekwondo 2019');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        ->assertSeeInOrder(['Notes', 'Taekwondo 2019']);
});

test('an item with no notes says so', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        ->assertSee('No notes');
});

test('the owner and editors see more actions with edit', function (Role $role): void {
    $household = Household::factory()->create();
    $member = memberOf($household, $role);
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($member)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        ->assertSeeHtml('aria-label="More actions"')
        ->assertSee('Edit')
        ->assertSeeHtml(sprintf('href="%s"', route('items.edit', [$household, $medal])));
})->with([
    'owner' => ['role' => Role::Owner],
    'editor' => ['role' => Role::Editor],
]);

test('a viewer does not see more actions', function (): void {
    $household = Household::factory()->create();
    $viewer = memberOf($household, Role::Viewer);
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($viewer)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        ->assertDontSeeHtml('aria-label="More actions"');
});

test('the message of the previous page is shown', function (): void {
    $item = Item::factory()->create();

    actingAs($item->household->owner)
        ->withSession(['message' => 'Medal added.'])
        ->get(route('items.show', [$item->household, $item]))
        ->assertSee('Medal added.');
});

test('a viewer can see an item', function (): void {
    $household = Household::factory()->create();
    $viewer = memberOf($household, Role::Viewer);
    $item = itemIn(placeIn($household, 'Kitchen'), 'Kettle');
    actingAs($viewer);

    get(route('items.show', [$household, $item]))
        ->assertOk()
        ->assertSee('Kettle');
});

test('an item of another household gives a not found page', function (): void {
    $household = Household::factory()->create();
    $itemOfAnotherHousehold = Item::factory()->create();
    actingAs($household->owner);

    get(route('items.show', [$household, $itemOfAnotherHousehold]))
        ->assertNotFound();
});

test('a user who is not a member gets a not found page', function (): void {
    $item = Item::factory()->create();
    actingAs(User::factory()->create());

    get(route('items.show', [$item->household, $item]))
        ->assertNotFound();
});
