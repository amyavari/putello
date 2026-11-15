<?php

declare(strict_types=1);

use App\Enums\ActivityType;
use App\Livewire\Places\Create;
use App\Livewire\Places\Rename;
use App\Livewire\Places\Show;
use App\Models\Activity;
use App\Models\Household;
use App\Models\Place;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

test('adding a place records an entry with its path', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::withQueryParams(['inside' => $kitchen->id])
        ->actingAs($household->owner)
        ->test(Create::class, ['household' => $household])
        ->set('name', 'Left cupboard')
        ->call('add');

    assertDatabaseHas(Activity::class, [
        'type' => ActivityType::PlaceCreated,
        'place_id' => Place::query()->where('name', 'Left cupboard')->sole()->id,
        'subject_name' => 'Left cupboard',
        'from_value' => null,
        'to_value' => 'Kitchen › Left cupboard',
    ]);
});

test('renaming a place records an entry with the old and the new name', function (): void {
    $household = Household::factory()->create();
    $johnsRoom = placeIn($household, 'John’s room');

    Livewire::actingAs($household->owner)
        ->test(Rename::class, ['household' => $household, 'place' => $johnsRoom])
        ->set('name', 'Sara’s room')
        ->call('rename');

    assertDatabaseHas(Activity::class, [
        'type' => ActivityType::PlaceRenamed,
        'place_id' => $johnsRoom->id,
        'subject_name' => 'Sara’s room',
        'from_value' => 'John’s room',
        'to_value' => 'Sara’s room',
    ]);
});

test('moving a place records an entry with the old and the new path', function (): void {
    $household = Household::factory()->create();
    $leftCupboard = placeInside(placeIn($household, 'Kitchen'), 'Left cupboard');
    $hall = placeIn($household, 'Hall');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $leftCupboard])
        ->call('move', $hall->id);

    assertDatabaseHas(Activity::class, [
        'type' => ActivityType::PlaceMoved,
        'place_id' => $leftCupboard->id,
        'subject_name' => 'Left cupboard',
        'from_value' => 'Kitchen › Left cupboard',
        'to_value' => 'Hall › Left cupboard',
    ]);
});

test('deleting a place records an entry with its path and no link to it', function (): void {
    $household = Household::factory()->create();
    $drawer = placeInside(placeIn($household, 'Kitchen'), 'Drawer');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'place' => $drawer])
        ->call('delete');

    assertDatabaseHas(Activity::class, [
        'type' => ActivityType::PlaceDeleted,
        'place_id' => null,
        'subject_name' => 'Drawer',
        'from_value' => 'Kitchen › Drawer',
        'to_value' => null,
    ]);
});
