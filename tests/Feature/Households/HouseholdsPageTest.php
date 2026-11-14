<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Livewire\Households\Index;
use App\Models\Household;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('the households page renders the households list', function (): void {
    actingAs(User::factory()->create());

    get(route('households.index'))
        ->assertOk()
        /** @phpstan-ignore method.notFound (a Livewire macro on TestResponse) */
        ->assertSeeLivewire(Index::class);
});

test('a user with no household sees the first-run state', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee('You have no households yet')
        ->assertSee('Add one for your home, or ask someone to invite you with the email you signed up with.');
});

test('only the households the user belongs to are listed', function (): void {
    $user = User::factory()->create();
    Household::factory()->for($user, 'owner')->create(['name' => 'Home']);
    Household::factory()->create(['name' => 'Office'])->members()->attach($user, ['role' => Role::Viewer]);
    Household::factory()->create(['name' => 'Neighbors']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee('Home')
        ->assertSee('Office')
        ->assertDontSee('Neighbors');
});

test('the owner sees the role Owner and the member count', function (): void {
    $owner = User::factory()->create();
    $household = Household::factory()->for($owner, 'owner')->create(['name' => 'Home']);
    $household->members()->attach(User::factory()->count(2)->create(), ['role' => Role::Viewer]);

    Livewire::actingAs($owner)
        ->test(Index::class)
        ->assertSee('Owner')
        ->assertSee('3 members'); // The owner and 2 viewers
});

test('a household with only its owner shows one member', function (): void {
    $owner = User::factory()->create();
    Household::factory()->for($owner, 'owner')->create(['name' => 'Home']);

    Livewire::actingAs($owner)
        ->test(Index::class)
        ->assertSee('1 member')
        ->assertDontSee('1 members');
});

test('a member sees their role instead of Owner', function (Role $role, string $shownRole): void {
    $user = User::factory()->create();
    Household::factory()->create(['name' => 'Home'])->members()->attach($user, ['role' => $role]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee($shownRole)
        ->assertDontSee('Owner');
})->with([
    'editor' => ['role' => Role::Editor, 'shownRole' => 'Editor'],
    'viewer' => ['role' => Role::Viewer, 'shownRole' => 'Viewer'],
]);

test('a member who set a label sees the label instead of the name', function (): void {
    $user = User::factory()->create();
    $household = Household::factory()->create(['name' => 'Home']);
    $household->members()->attach(User::factory()->count(2)->create(), ['role' => Role::Viewer]);
    $household->members()->attach($user, ['role' => Role::Editor, 'label' => 'Parents’ home']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee('Parents’ home')
        ->assertDontSee('Home')
        ->assertSee('4 members'); // The owner, 2 viewers and the user
});

test('a label set by another member is not shown', function (): void {
    $user = User::factory()->create();
    $household = Household::factory()->for($user, 'owner')->create(['name' => 'Home']);
    $household->members()->attach(User::factory()->create(), ['role' => Role::Viewer, 'label' => 'Parents’ home']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee('Home')
        ->assertDontSee('Parents’ home');
});

test('guests are redirected to the login page', function (): void {
    get(route('households.index'))
        ->assertRedirect(route('login'));
});

test('unverified users are redirected to the verification notice', function (): void {
    actingAs(User::factory()->unverified()->create());

    get(route('households.index'))
        ->assertRedirect(route('verification.notice'));
});
