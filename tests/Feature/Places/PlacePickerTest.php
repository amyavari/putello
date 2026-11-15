<?php

declare(strict_types=1);

use App\Livewire\Places\Picker;
use App\Models\Household;
use App\Models\Place;
use Livewire\Livewire;

test('the picker shows the place to move and where it is now', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Left cupboard');

    Livewire::actingAs($household->owner)
        ->test(Picker::class, ['household' => $household, 'place' => $cupboard])
        ->assertSee('Move Left cupboard')
        ->assertSee('Now in Kitchen');
});

test('the picker shows the household as where a place at the top is now', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Picker::class, ['household' => $household, 'place' => $kitchen])
        ->assertSee('Now in Home');
});

test('the picker with no place to move asks to choose a place', function (): void {
    $household = Household::factory()->create();

    Livewire::actingAs($household->owner)
        ->test(Picker::class, ['household' => $household])
        ->assertSee('Choose a place')
        ->assertDontSee('Now in')
        ->assertDontSee('can’t be chosen')
        ->assertDontSee('Move here');
});

test('the picker starts with the place it is given as chosen', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Picker::class, ['household' => $household, 'chosenPlaceId' => $kitchen->id])
        ->assertSee('Selected: Kitchen');
});

test('the picker shows the household as the top row, then its places', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);
    $kitchen = placeIn($household, 'Kitchen');
    placeInside($kitchen, 'Left cupboard');
    $box = placeIn($household, 'Box');

    Livewire::actingAs($household->owner)
        ->test(Picker::class, ['household' => $household, 'place' => $box])
        ->assertSeeInOrder(['Home', 'Box', 'Kitchen', 'Left cupboard']);
});

test('the picker says the place and the places inside it can not be chosen', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Left cupboard');

    Livewire::actingAs($household->owner)
        ->test(Picker::class, ['household' => $household, 'place' => $cupboard])
        ->assertSee('Left cupboard and the places inside it can’t be chosen.');
});

test('the picker asks to choose a place first', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Picker::class, ['household' => $household, 'place' => $kitchen])
        ->assertSee('Choose a place first.');
});

test('choosing a place shows its path', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Left cupboard');
    $drawer = placeInside($cupboard, 'Drawer 2');
    $box = placeIn($household, 'Box');

    Livewire::actingAs($household->owner)
        ->test(Picker::class, ['household' => $household, 'place' => $box])
        ->call('choosePlace', $drawer->id) // Clicks the "Drawer 2" row
        ->assertSee('Selected: Kitchen › Left cupboard › Drawer 2')
        ->assertDontSee('Choose a place first.');
});

test('choosing the household shows its name', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Left cupboard');

    Livewire::actingAs($household->owner)
        ->test(Picker::class, ['household' => $household, 'place' => $cupboard])
        ->call('chooseHousehold') // Clicks the "Home" row
        ->assertSee('Selected: Home');
});

test('the household can be chosen when no place is moved', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);

    Livewire::actingAs($household->owner)
        ->test(Picker::class, ['household' => $household])
        ->call('chooseHousehold')
        ->assertSee('Selected: Home');
});

test('the place to move can not be chosen', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Picker::class, ['household' => $household, 'place' => $kitchen])
        ->call('choosePlace', $kitchen->id)
        ->assertSee('Choose a place first.');
});

test('a place inside the place to move can not be chosen', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Left cupboard');
    $drawer = placeInside($cupboard, 'Drawer 1');

    Livewire::actingAs($household->owner)
        ->test(Picker::class, ['household' => $household, 'place' => $kitchen])
        ->call('choosePlace', $drawer->id)
        ->assertSee('Choose a place first.');
});

test('the place it is already in can not be chosen', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Left cupboard');

    Livewire::actingAs($household->owner)
        ->test(Picker::class, ['household' => $household, 'place' => $cupboard])
        ->call('choosePlace', $kitchen->id)
        ->assertSee('Choose a place first.');
});

test('the household can not be chosen for a place at the top', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Picker::class, ['household' => $household, 'place' => $kitchen])
        ->call('chooseHousehold')
        ->assertSee('Choose a place first.');
});

test('a place of another household can not be chosen', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $placeOfAnotherHousehold = Place::factory()->create();

    Livewire::actingAs($household->owner)
        ->test(Picker::class, ['household' => $household, 'place' => $kitchen])
        ->call('choosePlace', $placeOfAnotherHousehold->id)
        ->assertSee('Choose a place first.');
});

test('finding a place lists the matches with their paths ignoring case', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Left cupboard');
    placeInside($cupboard, 'Drawer 1');
    placeIn($household, 'Living room');
    $box = placeIn($household, 'Box');

    Livewire::actingAs($household->owner)
        ->test(Picker::class, ['household' => $household, 'place' => $box])
        ->set('search', 'DR')
        ->assertSee('Kitchen › Left cupboard › Drawer 1')
        ->assertDontSee('Living room');
});

test('finding a place with no match says so', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Picker::class, ['household' => $household, 'place' => $kitchen])
        ->set('search', 'gar')
        ->assertSee('No places match “gar”');
});

test('confirming sends the chosen place to the page', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $storageRoom = placeIn($household, 'Storage room');

    Livewire::actingAs($household->owner)
        ->test(Picker::class, ['household' => $household, 'place' => $kitchen])
        ->call('choosePlace', $storageRoom->id)
        ->call('confirm') // Clicks "Move here"
        ->assertDispatched('place-chosen', placeId: $storageRoom->id);
});

test('confirming the household sends no place to the page', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');
    $cupboard = placeInside($kitchen, 'Left cupboard');

    Livewire::actingAs($household->owner)
        ->test(Picker::class, ['household' => $household, 'place' => $cupboard])
        ->call('chooseHousehold')
        ->call('confirm')
        ->assertDispatched('place-chosen', placeId: null);
});

test('confirming with nothing chosen sends nothing', function (): void {
    $household = Household::factory()->create();
    $kitchen = placeIn($household, 'Kitchen');

    Livewire::actingAs($household->owner)
        ->test(Picker::class, ['household' => $household, 'place' => $kitchen])
        ->call('confirm')
        ->assertNotDispatched('place-chosen');
});
