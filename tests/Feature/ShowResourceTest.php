<?php

declare (strict_types= 1);

namespace Tests\Feature;

use Tests\TestCase;

class ShowResourceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_user_can_search_for_resources(): void
    {
        $response = $this->get('api/v2/resources?search=JavaScript'); 

        $response->assertStatus(200);
    }
}
