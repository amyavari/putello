<?php

declare(strict_types=1);

use App\Models\Household;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('a skip link leads to the page content', function (): void {
    $household = Household::factory()->create();
    actingAs($household->owner);

    get(route('places.index', $household))
        ->assertSeeHtmlInOrder(['href="#content"', 'Skip to content', 'id="content"']);
});
