<?php

declare(strict_types=1);

use App\Enums\LoanClosedAs;
use App\Enums\Role;
use App\Livewire\Items\Show;
use App\Livewire\Loans\Close;
use App\Livewire\Search\Index;
use App\Models\Household;
use App\Models\Item;
use App\Models\Loan;
use Carbon\CarbonImmutable;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\travelTo;

test('the owner and editors get the close loan modal while the item is lent', function (Role $role): void {
    $household = Household::factory()->create();
    $member = memberOf($household, $role);
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    Loan::factory()->for($drill)->create();

    Livewire::actingAs($member)
        ->test(Show::class, ['household' => $household, 'item' => $drill])
        ->assertSee('Close loan')
        /** @phpstan-ignore method.notFound (a Livewire macro on TestResponse) */
        ->assertSeeLivewire(Close::class);
})->with([
    'owner' => ['role' => Role::Owner],
    'editor' => ['role' => Role::Editor],
]);

test('a viewer does not get the close loan modal', function (): void {
    $household = Household::factory()->create();
    $viewer = memberOf($household, Role::Viewer);
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    Loan::factory()->for($drill)->create();

    Livewire::actingAs($viewer)
        ->test(Show::class, ['household' => $household, 'item' => $drill])
        ->assertDontSee('Close loan')
        /** @phpstan-ignore method.notFound (a Livewire macro on TestResponse) */
        ->assertDontSeeLivewire(Close::class);
});

test('the item page has no close loan modal while the item is not lent', function (): void {
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $drill])
        ->assertDontSee('Close loan')
        /** @phpstan-ignore method.notFound (a Livewire macro on TestResponse) */
        ->assertDontSeeLivewire(Close::class);
});

test('the modal shows the item, the borrower and the lending date', function (): void {
    travelTo('2026-11-16');
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    $loan = Loan::factory()
        ->for($drill)
        ->for(borrowerOf($household, 'Neighbor Reza'))
        ->create(['lent_at' => '2026-11-03']);

    Livewire::actingAs($household->owner)
        ->test(Close::class, ['loan' => $loan])
        ->assertSee('Drill · lent to Neighbor Reza since Nov 3');
});

test('the returned choice says the place the item is back in', function (): void {
    $household = Household::factory()->create();
    $drill = itemIn(placeInside(placeIn($household, 'Storage room'), 'Shelf A'), 'Drill');
    $loan = Loan::factory()->for($drill)->create();

    Livewire::actingAs($household->owner)
        ->test(Close::class, ['loan' => $loan])
        ->assertSee('It is back in Storage room › Shelf A.');
});

test('nothing is chosen at first', function (): void {
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    $loan = Loan::factory()->for($drill)->create();

    Livewire::actingAs($household->owner)
        ->test(Close::class, ['loan' => $loan])
        ->assertSet('closedAs', null)
        ->assertSee('Choose Returned or Lost first.');
});

test('the owner closes a loan as returned', function (): void {
    travelTo('2026-11-16 10:00');
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    $loan = Loan::factory()->for($drill)->create();

    Livewire::actingAs($household->owner)
        ->test(Close::class, ['loan' => $loan])
        ->set('closedAs', 'returned')
        ->call('close')
        ->assertHasNoErrors();

    expect($loan->fresh())
        ->closed_at->toEqual(CarbonImmutable::parse('2026-11-16 10:00'))
        ->closed_as->toBe(LoanClosedAs::Returned);
});

test('an editor can close a loan', function (): void {
    $household = Household::factory()->create();
    $editor = memberOf($household, Role::Editor);
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    $loan = Loan::factory()->for($drill)->create();

    Livewire::actingAs($editor)
        ->test(Close::class, ['loan' => $loan])
        ->set('closedAs', 'returned')
        ->call('close')
        ->assertHasNoErrors();

    expect($loan->fresh())->closed_as->toBe(LoanClosedAs::Returned);
});

test('a returned item stays in its place', function (): void {
    $household = Household::factory()->create();
    $storageRoom = placeIn($household, 'Storage room');
    $drill = itemIn($storageRoom, 'Drill');
    $loan = Loan::factory()->for($drill)->create();

    Livewire::actingAs($household->owner)
        ->test(Close::class, ['loan' => $loan])
        ->set('closedAs', 'returned')
        ->call('close');

    assertDatabaseHas(Item::class, [
        'id' => $drill->id,
        'place_id' => $storageRoom->id,
        'deleted_at' => null,
    ]);
});

test('after returned the app opens the item with a message', function (): void {
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    $loan = Loan::factory()->for($drill)->create();

    Livewire::actingAs($household->owner)
        ->test(Close::class, ['loan' => $loan])
        ->set('closedAs', 'returned')
        ->call('close')
        ->assertRedirect(route('items.show', [$household, $drill]));

    expect(session('message'))->toBe('Drill returned.');
});

test('returned or lost must be chosen', function (): void {
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    $loan = Loan::factory()->for($drill)->create();

    Livewire::actingAs($household->owner)
        ->test(Close::class, ['loan' => $loan])
        ->call('close')
        ->assertHasErrors(['closedAs' => 'required']);

    expect($loan->fresh())->closed_at->toBeNull();
});

test('a choice other than returned or lost fails', function (): void {
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    $loan = Loan::factory()->for($drill)->create();

    Livewire::actingAs($household->owner)
        ->test(Close::class, ['loan' => $loan])
        ->set('closedAs', 'borrowed again')
        ->call('close')
        ->assertHasErrors('closedAs');

    expect($loan->fresh())->closed_at->toBeNull();
});

test('a loan that was closed while the modal was open can not be closed again', function (): void {
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    $loan = Loan::factory()->for($drill)->create();

    $component = Livewire::actingAs($household->owner)
        ->test(Close::class, ['loan' => $loan]);

    $loan->update(['closed_at' => '2026-11-15 09:00', 'closed_as' => LoanClosedAs::Returned]); // Someone closes it in another tab

    $component
        ->set('closedAs', 'returned')
        ->call('close')
        ->assertHasErrors('closedAs');

    expect($loan->fresh())
        ->closed_at->toEqual(CarbonImmutable::parse('2026-11-15 09:00'));
});

test('a viewer can not close a loan', function (): void {
    $household = Household::factory()->create();
    $viewer = memberOf($household, Role::Viewer);
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    $loan = Loan::factory()->for($drill)->create();

    Livewire::actingAs($viewer)
        ->test(Close::class, ['loan' => $loan])
        ->set('closedAs', 'returned')
        ->call('close')
        ->assertForbidden();

    expect($loan->fresh())->closed_at->toBeNull();
});

test('the item page shows not lent after the loan is returned', function (): void {
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    Loan::factory()
        ->returned()
        ->for($drill)
        ->for(borrowerOf($household, 'Neighbor Reza'))
        ->create();

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $drill])
        ->assertSee('Not lent')
        ->assertDontSee('Neighbor Reza');
});

test('the item page offers lend again after the loan is returned', function (): void {
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    Loan::factory()
        ->returned()
        ->for($drill)
        ->create();

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $drill])
        ->assertSeeHtml(sprintf('href="%s"', route('items.lend', [$household, $drill])))
        /** @phpstan-ignore method.notFound (a Livewire macro on TestResponse) */
        ->assertDontSeeLivewire(Close::class);
});

test('search shows no lent badge after the loan is returned', function (): void {
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    Loan::factory()
        ->returned()
        ->for($drill)
        ->for(borrowerOf($household, 'Neighbor Reza'))
        ->create();

    Livewire::actingAs($household->owner)
        ->test(Index::class, ['household' => $household])
        ->set('search', 'Drill')
        ->assertSee('Drill')
        ->assertDontSee('Lent to Neighbor Reza');
});
