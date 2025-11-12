<?php

declare(strict_types=1);

namespace Tests\Feature\ContributorTests;

use Tests\TestCase;
use App\Models\ContributorListProject;
use App\Enums\ContributorStatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContributorStatusTest extends TestCase
{
    use RefreshDatabase;


    public function test_new_contributor_defaults_to_pending_status(): void

    {

        $contributor = ContributorListProject::factory()
            ->create([
                'programming_role' => 'Backend Developer',
            ]);

        $this->assertSame(
            ContributorStatusEnum::Pending->value,
            $contributor->status,
            'A new contributor should have pending status by default.'
        );

        $this->assertDatabaseHas('contributors_list_project', [
            'id' => $contributor->id,
            'status' => ContributorStatusEnum::Pending->value,
        ]);
    }
}
