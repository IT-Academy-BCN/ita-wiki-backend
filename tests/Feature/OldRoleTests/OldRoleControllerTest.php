<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * @deprecated This test suite will be removed after Spatie migration
 */
class OldRoleControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->markTestSkipped('DEPRECATED: OldRole system - Skipped for PR');
    }
}