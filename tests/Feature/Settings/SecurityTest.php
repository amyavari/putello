<?php

declare(strict_types=1);

use App\Livewire\Settings\Security;
use App\Livewire\Settings\TwoFactor\RecoveryCodes;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Features;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    Features::passkeys([
        'confirmPassword' => true,
    ]);
});

test('security settings page can be rendered', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('security.edit'))
        ->assertOk()
        /** @phpstan-ignore method.notFound (a Livewire macro on TestResponse) */
        ->assertSeeLivewire(Security::class)
        ->assertSee('Passkeys')
        ->assertSee('No passkeys yet')
        ->assertSee('Two-factor authentication')
        ->assertSee('Enable 2FA');
});

test('security settings page requires password confirmation when enabled', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('security.edit'))
        ->assertRedirect(route('password.confirm'));
});

test('security settings page renders without two factor when feature is disabled', function (): void {
    config(['fortify.features' => []]);

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Security::class)
        ->assertSee('Update password')
        ->assertDontSee('Manage your passkeys for passwordless sign-in')
        ->assertDontSee('Add a passkey to sign in without a password')
        ->assertDontSee('Two-factor authentication');
});

test('two factor authentication disabled when confirmation abandoned between requests', function (): void {
    $user = User::factory()->create();

    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => null,
    ])->save();

    Livewire::actingAs($user)
        ->test(Security::class)
        ->assertSet('twoFactorEnabled', false);

    assertDatabaseHas(User::class, [
        'id' => $user->id,
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
    ]);
});

test('two factor setup does not reveal the secret when already enabled', function (): void {
    $user = User::factory()->create();

    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    Livewire::actingAs($user)
        ->test(Security::class)
        ->call('enable')
        ->assertSet('manualSetupKey', '')
        ->assertSet('qrCodeSvg', '');

    $secret = decrypt($user->refresh()->two_factor_secret);

    expect($secret)->toBe('test-secret');
});

test('recovery codes are only loaded when requested', function (): void {
    $user = User::factory()->create();

    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    Livewire::actingAs($user)
        ->test(RecoveryCodes::class)
        ->assertSet('recoveryCodes', [])
        ->call('viewRecoveryCodes')
        ->assertSet('recoveryCodes', ['code1', 'code2']);
});

test('password can be updated', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    Livewire::actingAs($user)
        ->test(Security::class)
        ->set('current_password', 'password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertHasNoErrors();

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

test('correct password must be provided to update password', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    Livewire::actingAs($user)
        ->test(Security::class)
        ->set('current_password', 'wrong-password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertHasErrors(['current_password']);
});
