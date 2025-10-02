<?php

declare (strict_types= 1);

namespace Tests\Feature;

use App\Models\Tag;
use Tests\TestCase;
use App\Models\User;
use App\Models\OldRole;
use App\Models\Resource;
use App\Models\Role;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\DataProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShowResourceTest extends TestCase
{

     public function test_user_can_search_for_resources(): void
    {
        $response = $this->get(route('resources.index') . '?search=Laravel');

        $response->assertStatus(200);
    }

}