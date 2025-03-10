<?php

declare (strict_types= 1);

namespace Tests\Feature;

use App\Models\Resource;
use App\Models\Role;
use Tests\TestCase;

class ResourceTest extends TestCase
{
        /**
     * A basic feature test example.
     */
    public function testCanGetList(): void
    {
        Role::factory(10)->create();
        Resource::factory()->count(5)->create();

        $response = $this->get(route('resources'));

        $response->assertStatus(200)->assertJsonCount(5);
    }

}