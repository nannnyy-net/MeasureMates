<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    public function test_unknown_route_returns_friendly_not_found_response(): void
    {
        $response = $this->get('/definitely-missing-route');

        $response->assertStatus(404);
        $response->assertSeeText('The page you requested could not be found.');
    }
}
