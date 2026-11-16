<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Livewire\Items\Lend;
use App\Livewire\Items\Show;
use App\Models\Borrower;
use App\Models\Household;
use App\Models\Item;
use App\Models\Loan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\travelTo;

test('the lend page renders the lend form', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');
    actingAs($household->owner);

    get(route('items.lend', [$household, $medal]))
        ->assertOk()
        /** @phpstan-ignore method.notFound (a Livewire macro on TestResponse) */
        ->assertSeeLivewire(Lend::class);
});

test('the breadcrumb shows the household, the places of the path and the item', function (): void {
    $household = Household::factory()->create(['name' => 'Home']);
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $medal])
        ->assertSeeInOrder(['Home', 'John’s room', 'Medal', 'Lend Medal']);
});

test('the owner lends an item to a new borrower', function (): void {
    travelTo('2026-11-11');
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $medal])
        ->set('borrowerName', 'Uncle Hamid')
        ->call('lend')
        ->assertHasNoErrors();

    assertDatabaseHas(Borrower::class, [
        'household_id' => $household->id,
        'name' => 'Uncle Hamid',
    ]);

    expect(Loan::query()->sole())
        ->item_id->toBe($medal->id)
        ->borrower->name->toBe('Uncle Hamid')
        ->lent_at->toEqual(CarbonImmutable::parse('2026-11-11'))
        ->due_at->toBeNull()
        ->note->toBeNull()
        ->closed_at->toBeNull();
});

test('an editor can lend an item', function (): void {
    $household = Household::factory()->create();
    $editor = memberOf($household, Role::Editor);
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($editor)
        ->test(Lend::class, ['household' => $household, 'item' => $medal])
        ->set('borrowerName', 'Uncle Hamid')
        ->call('lend')
        ->assertHasNoErrors();

    assertDatabaseHas(Loan::class, [
        'item_id' => $medal->id,
    ]);
});

test('the loan keeps the due date and the note', function (): void {
    travelTo('2026-11-11');
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $drill])
        ->set('borrowerName', 'Neighbor Reza')
        ->set('dueAt', '2026-11-17')
        ->set('note', 'With the long bits')
        ->call('lend')
        ->assertHasNoErrors();

    expect(Loan::query()->sole())
        ->due_at->toEqual(CarbonImmutable::parse('2026-11-17'))
        ->note->toBe('With the long bits');
});

test('lending to a saved borrower uses that borrower', function (): void {
    $household = Household::factory()->create();
    $reza = borrowerOf($household, 'Neighbor Reza');
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $drill])
        ->set('borrowerName', 'Neighbor Reza')
        ->call('lend')
        ->assertHasNoErrors();

    assertDatabaseCount(Borrower::class, 1);
    assertDatabaseHas(Loan::class, [
        'item_id' => $drill->id,
        'borrower_id' => $reza->id,
    ]);
});

test('a saved borrower is found ignoring upper and lower case', function (): void {
    $household = Household::factory()->create();
    $reza = borrowerOf($household, 'Neighbor Reza');
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $drill])
        ->set('borrowerName', 'neighbor reza')
        ->call('lend')
        ->assertHasNoErrors();

    assertDatabaseCount(Borrower::class, 1);
    assertDatabaseHas(Loan::class, [
        'borrower_id' => $reza->id,
    ]);
});

test('a borrower of another household is not used', function (): void {
    $household = Household::factory()->create();
    borrowerOf(Household::factory()->create(), 'Neighbor Reza');
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $drill])
        ->set('borrowerName', 'Neighbor Reza')
        ->call('lend')
        ->assertHasNoErrors();

    assertDatabaseHas(Borrower::class, [
        'household_id' => $household->id,
        'name' => 'Neighbor Reza',
    ]);
});

test('the borrower name and the note are trimmed', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $medal])
        ->set('borrowerName', '  Uncle Hamid  ')
        ->set('note', '  For the photo  ')
        ->call('lend')
        ->assertHasNoErrors();

    assertDatabaseHas(Borrower::class, [
        'name' => 'Uncle Hamid',
    ]);
    assertDatabaseHas(Loan::class, [
        'note' => 'For the photo',
    ]);
});

test('typing shows the matching saved borrowers', function (): void {
    $household = Household::factory()->create();
    borrowerOf($household, 'Neighbor Reza');
    borrowerOf($household, 'Uncle Hamid');
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $drill])
        ->set('borrowerName', 're')
        ->assertSeeHtml('aria-label="Saved borrowers"')
        ->assertSee('Neighbor Reza')
        ->assertDontSee('Uncle Hamid')
        ->assertSee('Keep typing to add a new borrower.');
});

test('borrowers of another household are not suggested', function (): void {
    $household = Household::factory()->create();
    borrowerOf(Household::factory()->create(), 'Neighbor Reza');
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $drill])
        ->set('borrowerName', 're')
        ->assertDontSee('Neighbor Reza');
});

test('nothing is suggested while the borrower field is empty', function (): void {
    $household = Household::factory()->create();
    borrowerOf($household, 'Neighbor Reza');
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $drill])
        ->assertDontSeeHtml('aria-label="Saved borrowers"')
        ->assertDontSee('Neighbor Reza');
});

test('a name that matches no saved borrower is shown as a new borrower', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $medal])
        ->set('borrowerName', 'Uncle Hamid')
        ->assertSee('New borrower. The name will be saved for next time.');
});

test('the name of a saved borrower is not shown as a new borrower', function (): void {
    $household = Household::factory()->create();
    borrowerOf($household, 'Neighbor Reza');
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $drill])
        ->set('borrowerName', 'Neighbor Reza')
        ->assertDontSee('New borrower. The name will be saved for next time.');
});

test('the borrower is required', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $medal])
        ->call('lend')
        ->assertHasErrors(['borrowerName' => 'required'])
        ->assertSee('Say who is borrowing it.');

    assertDatabaseEmpty(Loan::class);
});

test('the borrower name can not be longer than 255 characters', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $medal])
        ->set('borrowerName', Str::repeat('a', 256))
        ->call('lend')
        ->assertHasErrors(['borrowerName' => 'max']);

    assertDatabaseEmpty(Loan::class);
});

test('the lending date is today by default', function (): void {
    travelTo('2026-11-11');
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $medal])
        ->assertSet('lentAt', '2026-11-11');
});

test('the lending date is required', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $medal])
        ->set('borrowerName', 'Uncle Hamid')
        ->set('lentAt', '')
        ->call('lend')
        ->assertHasErrors(['lentAt' => 'required']);

    assertDatabaseEmpty(Loan::class);
});

test('the lending date can not be after today', function (): void {
    travelTo('2026-11-11');
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $medal])
        ->set('borrowerName', 'Uncle Hamid')
        ->set('lentAt', '2026-11-12')
        ->call('lend')
        ->assertHasErrors(['lentAt' => 'before_or_equal']);

    assertDatabaseEmpty(Loan::class);
});

test('the due date can not be before the lending date', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $medal])
        ->set('borrowerName', 'Uncle Hamid')
        ->set('lentAt', '2026-11-10')
        ->set('dueAt', '2026-11-09')
        ->call('lend')
        ->assertHasErrors(['dueAt' => 'after_or_equal']);

    assertDatabaseEmpty(Loan::class);
});

test('the due date can be the same day as the lending date', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $medal])
        ->set('borrowerName', 'Uncle Hamid')
        ->set('lentAt', '2026-11-10')
        ->set('dueAt', '2026-11-10')
        ->call('lend')
        ->assertHasNoErrors();
});

test('the note can not be longer than 255 characters', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $medal])
        ->set('borrowerName', 'Uncle Hamid')
        ->set('note', Str::repeat('a', 256))
        ->call('lend')
        ->assertHasErrors(['note' => 'max']);

    assertDatabaseEmpty(Loan::class);
});

test('after lending the app opens the item with a message', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $medal])
        ->set('borrowerName', 'Uncle Hamid')
        ->call('lend')
        ->assertRedirect(route('items.show', [$household, $medal]));

    expect(session('message'))->toBe('Medal lent to Uncle Hamid.');
});

test('cancel goes back to the item', function (): void {
    $household = Household::factory()->create();
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $medal])
        ->assertSeeHtml(sprintf('href="%s"', route('items.show', [$household, $medal])));
});

test('the owner and editors see lend on the item page', function (Role $role): void {
    $household = Household::factory()->create();
    $member = memberOf($household, $role);
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($member)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        ->assertSee('Lend')
        ->assertSeeHtml(sprintf('href="%s"', route('items.lend', [$household, $medal])));
})->with([
    'owner' => ['role' => Role::Owner],
    'editor' => ['role' => Role::Editor],
]);

test('a viewer does not see lend on the item page', function (): void {
    $household = Household::factory()->create();
    $viewer = memberOf($household, Role::Viewer);
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    Livewire::actingAs($viewer)
        ->test(Show::class, ['household' => $household, 'item' => $medal])
        ->assertDontSeeHtml(sprintf('href="%s"', route('items.lend', [$household, $medal])));
});

test('the item page does not offer lend while the item is lent', function (): void {
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    Loan::factory()->for($drill)->create(); // Drill is lent

    Livewire::actingAs($household->owner)
        ->test(Show::class, ['household' => $household, 'item' => $drill])
        ->assertDontSeeHtml(sprintf('href="%s"', route('items.lend', [$household, $drill])));
});

test('opening the lend page of an item that is already lent goes back to the item page', function (): void {
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');
    Loan::factory()->for($drill)->create(); // Drill is already lent

    Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $drill])
        ->assertRedirect(route('items.show', [$household, $drill]));
});

test('an item that was lent while the lend page was open can not be lent again', function (): void {
    $household = Household::factory()->create();
    $drill = itemIn(placeIn($household, 'Storage room'), 'Drill');

    $component = Livewire::actingAs($household->owner)
        ->test(Lend::class, ['household' => $household, 'item' => $drill]);

    Loan::factory()->for($drill)->create(); // Someone lends Drill in another tab

    $component
        ->set('borrowerName', 'Uncle Hamid')
        ->call('lend')
        ->assertHasErrors('borrowerName');

    assertDatabaseCount(Loan::class, 1);
});

test('a viewer can not open the lend page', function (): void {
    $household = Household::factory()->create();
    $viewer = memberOf($household, Role::Viewer);
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');
    actingAs($viewer);

    get(route('items.lend', [$household, $medal]))
        ->assertForbidden();
});

test('a member who became a viewer after opening the page can not lend', function (): void {
    $household = Household::factory()->create();
    $member = memberOf($household, Role::Editor);
    $medal = itemIn(placeIn($household, 'John’s room'), 'Medal');

    $component = Livewire::actingAs($member)
        ->test(Lend::class, ['household' => $household, 'item' => $medal]);

    $household->members()->updateExistingPivot($member, ['role' => Role::Viewer]);

    $component
        ->set('borrowerName', 'Uncle Hamid')
        ->call('lend')
        ->assertForbidden();

    assertDatabaseEmpty(Loan::class);
});

test('an item of another household gives a not found page', function (): void {
    $household = Household::factory()->create();
    $itemOfAnotherHousehold = Item::factory()->create();
    actingAs($household->owner);

    get(route('items.lend', [$household, $itemOfAnotherHousehold]))
        ->assertNotFound();
});

test('an archived item gives a not found page', function (): void {
    $household = Household::factory()->create();
    $umbrella = archivedItemOf($household, 'Umbrella');
    actingAs($household->owner);

    get(route('items.lend', [$household, $umbrella]))
        ->assertNotFound();
});

test('guests are redirected to the login page', function (): void {
    $medal = Item::factory()->create();

    get(route('items.lend', [$medal->household, $medal]))
        ->assertRedirect(route('login'));
});

test('unverified users are redirected to the verification notice', function (): void {
    $medal = Item::factory()->create();
    actingAs(User::factory()->unverified()->create());

    get(route('items.lend', [$medal->household, $medal]))
        ->assertRedirect(route('verification.notice'));
});

// Helpers

/**
 * A saved borrower of the household.
 */
function borrowerOf(Household $household, string $name): Borrower
{
    return Borrower::factory()
        ->for($household)
        ->create(['name' => $name]);
}
