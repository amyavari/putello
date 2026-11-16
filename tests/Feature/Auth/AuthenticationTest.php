<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Fortify\Features;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

test('login screen can be rendered', function (): void {
    get(route('login'))->assertOk();
});

test('users can authenticate using the login screen', function (): void {
    $user = User::factory()->create();

    post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home', absolute: false));

    assertAuthenticated();
});

test('users can not authenticate with invalid password', function (): void {
    $user = User::factory()->create();

    post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrorsIn('email');

    assertGuest();
});

test('users with two factor enabled are redirected to two factor challenge', function (): void {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withTwoFactor()->create();

    post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('two-factor.login'));

    assertGuest();
});

test('users can logout', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('home'));

    assertGuest();
});
