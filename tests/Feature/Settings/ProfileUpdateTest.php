<?php

declare(strict_types=1);

use App\Livewire\Settings\DeleteUserForm;
use App\Livewire\Settings\Profile;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\post;

test('profile page is displayed', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        /** @phpstan-ignore method.notFound (a Livewire macro on TestResponse) */
        ->assertSeeLivewire(Profile::class);
});

test('profile information can be updated', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    expect($user->refresh())
        ->name->toEqual('Test User')
        ->email->toEqual('test@example.com')
        ->email_verified_at->toBeNull();
});

test('email is stored in lowercase so the user can still log in', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', 'Test@Example.com')
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    expect($user->refresh()->email)->toBe('test@example.com');

    post(route('logout'));

    post(route('login.store'), [
        'email' => 'Test@Example.com',
        'password' => 'password',
    ]);

    assertAuthenticatedAs($user);
});

test('email must be unique regardless of case', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);

    Livewire::actingAs(User::factory()->create())
        ->test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', 'Taken@Example.com')
        ->call('updateProfileInformation')
        ->assertHasErrors(['email']);
});

test('email verification status is unchanged when email address is unchanged', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(DeleteUserForm::class)
        ->set('password', 'password')
        ->call('deleteUser')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    assertModelMissing($user);

    assertGuest();
});

test('correct password must be provided to delete account', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(DeleteUserForm::class)
        ->set('password', 'wrong-password')
        ->call('deleteUser')
        ->assertHasErrors(['password']);

    assertModelExists($user);
});
