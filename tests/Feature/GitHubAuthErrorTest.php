<?php

namespace Tests\Feature;

use Tests\TestCase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GitHubAuthErrorTest extends TestCase
{
    public function test_github_callback_returns_500_without_internal_details_on_network_error()
    {
        $mockDriver = Mockery::mock('Laravel\Socialite\Two\GithubProvider');
        $mockDriver->shouldReceive('user')->andThrow(new RequestException('Network error', new \GuzzleHttp\Psr7\Request('GET', 'test')));
        Socialite::shouldReceive('driver')->with('github')->andReturn($mockDriver);

        $response = $this->get('/api/auth/github/callback');

        $response->assertStatus(500);

        $response->assertDontSee('Stack trace');
        $response->assertDontSee('Exception');
        $response->assertDontSee('Guzzle');
        $response->assertJsonMissing(['exception', 'file', 'trace']);
    }


    public function test_github_callback_returns_500_without_internal_details_on_generic_exception()
    {
        $mockDriver = Mockery::mock('Laravel\Socialite\Two\GithubProvider');
        $mockDriver->shouldReceive('user')->andThrow(new \Exception('Unexpected error'));
        Socialite::shouldReceive('driver')->with('github')->andReturn($mockDriver);

        $response = $this->get('/api/auth/github/callback');

        $response->assertStatus(500);
        $response->assertDontSee('Stack trace');
        $response->assertDontSee('Exception');
        $response->assertJsonMissing(['exception', 'file', 'trace']);
    }
} 