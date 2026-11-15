<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Livewire\Places\Show;
use App\Models\Household;
use App\Models\Place;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('the place page renders the place', function (): void {
    $household = Household::factory()->create();
    $place = Place::factory()->for($household)->create();
    actingAs($household->owner);

    get(route('places.show', [$household, $place]))
        ->assertOk()
        /** @phpstan-ignore method.notFound (a Livewire macro on TestResponse) */
        ->assertSeeLivewire(Show::class);
});

test('the breadcrumb shows the household and the places above the place', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Left cupboard');
    $drawer = placeInside($cupboard, 'Drawer 3');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $drawer])
        ->assertSeeInOrder(['Home', 'Kitchen', 'Left cupboard', 'Drawer 3']);
});

test('the breadcrumb links to the places page and to the places above', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Left cupboard');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $cupboard])
        ->assertSeeHtml(sprintf('href="%s"', route('places.index', $household)))
        ->assertSeeHtml(sprintf('href="%s"', route('places.show', [$household, $kitchen])));
});

test('a member who set a label sees the label as the first crumb', function (): void {
    $household = Household::factory()->create(['name' => 'Lake house']);
    $user = memberOf($household, Role::Viewer, label: 'Parents’ home');
    $place = placeIn($household, 'Kitchen');

    Livewire::actingAs($user)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->assertSeeInOrder(['Parents’ home', 'Kitchen'])
        ->assertDontSee('Lake house');
});

test('the places inside are listed with their paths', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Left cupboard');
    $drawer = placeInside($cupboard, 'Drawer 1');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $cupboard])
        ->assertSee('Places inside')
        ->assertSeeInOrder(['Drawer 1', 'Kitchen › Left cupboard › Drawer 1'])
        ->assertSeeHtml(sprintf('href="%s"', route('places.show', [$household, $drawer])));
});

test('only the places directly inside are listed', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Left cupboard');
    placeInside($cupboard, 'Drawer 1');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $kitchen])
        ->assertSee('Left cupboard')
        ->assertDontSee('Drawer 1');
});

test('a place with no places inside shows a message', function (): void {
    $household = Household::factory()->create();
    $place = Place::factory()->for($household)->create();

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->assertSee('No places inside');
});

test('each place inside shows how many items are in it at any depth', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    placeInside($kitchen, 'Box');
    $cupboard = placeInside($kitchen, 'Cupboard');
    itemIn($cupboard, 'Plate');
    itemIn(placeInside($cupboard, 'Drawer'), 'Spoon');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $kitchen])
        ->assertSeeInOrder(['Box', 'Empty', 'Cupboard', '2 items']);
});

test('the items in the place are listed with the path of their place', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $drawer = placeInside($kitchen, 'Drawer 1');
    $spoon = itemIn($drawer, 'Spoon');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $drawer])
        ->assertSeeInOrder(['Items in this place', 'Spoon', 'Kitchen › Drawer 1'])
        ->assertSeeHtml(sprintf('href="%s"', route('items.show', [$household, $spoon])));
});

test('the items are sorted by name ignoring case', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    itemIn($kitchen, 'spoon');
    itemIn($kitchen, 'Fork');
    itemIn($kitchen, 'knife');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $kitchen])
        ->assertSeeInOrder(['Fork', 'knife', 'spoon']);
});

test('only the items directly in the place are listed', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $drawer = placeInside($kitchen, 'Drawer 1');
    itemIn($kitchen, 'Kettle');
    itemIn($drawer, 'Spoon');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $kitchen])
        ->assertSee('Kettle')
        ->assertDontSee('Spoon');
});

test('a place with no items shows a message', function (): void {
    $household = Household::factory()->create();
    $place = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->assertSee('Nothing here yet');
});

test('the owner and editors see the add place button and more actions', function (Role $role): void {
    $household = Household::factory()->create();
    $member = memberOf($household, $role);
    $place = Place::factory()->for($household)->create();

    Livewire::actingAs($member)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->assertSee('Add place')
        ->assertSeeHtml(sprintf('href="%s"', e(route('places.create', [$household, 'inside' => $place]))))
        ->assertSeeHtml('aria-label="More actions"');
})->with([
    'owner' => ['role' => Role::Owner],
    'editor' => ['role' => Role::Editor],
]);

test('a viewer does not see the add place button or more actions', function (): void {
    $household = Household::factory()->create();
    $viewer = memberOf($household, Role::Viewer);
    $place = Place::factory()->for($household)->create();

    Livewire::actingAs($viewer)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->assertDontSee('Add place')
        ->assertDontSeeHtml('aria-label="More actions"');
});

test('the owner and editors see the add item button', function (Role $role): void {
    $household = Household::factory()->create();
    $member = memberOf($household, $role);
    $place = placeIn($household, 'Kitchen');

    Livewire::actingAs($member)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->assertSee('Add item')
        ->assertSeeHtml(sprintf('href="%s"', e(route('items.create', [$household, 'inside' => $place]))));
})->with([
    'owner' => ['role' => Role::Owner],
    'editor' => ['role' => Role::Editor],
]);

test('a viewer does not see the add item button', function (): void {
    $household = Household::factory()->create();
    $viewer = memberOf($household, Role::Viewer);
    $place = placeIn($household, 'Kitchen');

    Livewire::actingAs($viewer)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->assertDontSee('Add item');
});

test('the message of the previous page is shown', function (): void {
    $household = Household::factory()->create();
    $place = Place::factory()->for($household)->create();

    actingAs($household->owner)
        ->withSession(['message' => 'Drawer 1 added.'])
        ->get(route('places.show', [$household, $place]))
        ->assertSee('Drawer 1 added.');
});

test('a place of another household gives a not found page', function (): void {
    $household = Household::factory()->create();
    $placeOfAnotherHousehold = Place::factory()->create();
    actingAs($household->owner);

    get(route('places.show', [$household, $placeOfAnotherHousehold]))
        ->assertNotFound();
});

test('a user who is not a member gets a not found page', function (): void {
    $place = Place::factory()->create();
    actingAs(User::factory()->create());

    get(route('places.show', [$place->household, $place]))
        ->assertNotFound();
});
