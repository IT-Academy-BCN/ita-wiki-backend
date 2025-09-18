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
    public function test_can_get_list(): void
    {
        $response = $this->get(route('resources.index')); // MODIFICATO

        $response->assertStatus(200);
    }
}