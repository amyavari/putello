<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\actingAs;

test('confirm password screen can be rendered', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('password.confirm'))
        ->assertOk();
});
