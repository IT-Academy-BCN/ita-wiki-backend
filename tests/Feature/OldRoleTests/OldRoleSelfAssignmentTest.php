<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class OldRoleSelfAssignmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->markTestSkipped('DEPRECATED: OldRole system - Skipped for PR');
    }
}
