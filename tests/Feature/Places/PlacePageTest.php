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
    $kitchen = Place::factory()->for($household)->create(['name' => 'Kitchen']);
    $cupboard = Place::factory()->inside($kitchen)->create(['name' => 'Left cupboard']);
    $drawer = Place::factory()->inside($cupboard)->create(['name' => 'Drawer 3']);

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $drawer])
        ->assertSeeInOrder(['Home', 'Kitchen', 'Left cupboard', 'Drawer 3']);
});

test('the breadcrumb links to the places page and to the places above', function (): void {
    $household = Household::factory()->create();
    $kitchen = Place::factory()->for($household)->create(['name' => 'Kitchen']);
    $cupboard = Place::factory()->inside($kitchen)->create(['name' => 'Left cupboard']);

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $cupboard])
        ->assertSeeHtml(sprintf('href="%s"', route('places.index', $household)))
        ->assertSeeHtml(sprintf('href="%s"', route('places.show', [$household, $kitchen])));
});

test('a member who set a label sees the label as the first crumb', function (): void {
    $user = User::factory()->create();
    $household = Household::factory()->create(['name' => 'Lake house']);
    $household->members()->attach($user, ['role' => Role::Viewer, 'label' => 'Parents’ home']);
    $place = Place::factory()->for($household)->create(['name' => 'Kitchen']);

    Livewire::actingAs($user)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->assertSeeInOrder(['Parents’ home', 'Kitchen'])
        ->assertDontSee('Lake house');
});

test('the places inside are listed with their paths', function (): void {
    $household = Household::factory()->create();
    $kitchen = Place::factory()->for($household)->create(['name' => 'Kitchen']);
    $cupboard = Place::factory()->inside($kitchen)->create(['name' => 'Left cupboard']);
    $drawer = Place::factory()->inside($cupboard)->create(['name' => 'Drawer 1']);

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $cupboard])
        ->assertSee('Places inside')
        ->assertSeeInOrder(['Drawer 1', 'Kitchen › Left cupboard › Drawer 1'])
        ->assertSeeHtml(sprintf('href="%s"', route('places.show', [$household, $drawer])));
});

test('only the places directly inside are listed', function (): void {
    $household = Household::factory()->create();
    $kitchen = Place::factory()->for($household)->create(['name' => 'Kitchen']);
    $cupboard = Place::factory()->inside($kitchen)->create(['name' => 'Left cupboard']);
    Place::factory()->inside($cupboard)->create(['name' => 'Drawer 1']);

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

test('the owner and editors see the add place button and more actions', function (Role $role): void {
    $member = User::factory()->create();
    $household = Household::factory()->create();
    $household->members()->attach($member, ['role' => $role]);
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
    $viewer = User::factory()->create();
    $household = Household::factory()->create();
    $household->members()->attach($viewer, ['role' => Role::Viewer]);
    $place = Place::factory()->for($household)->create();

    Livewire::actingAs($viewer)
        ->test(Show::class, ['household' => $household, 'place' => $place])
        ->assertDontSee('Add place')
        ->assertDontSeeHtml('aria-label="More actions"');
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
