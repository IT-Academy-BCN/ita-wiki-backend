<?php

namespace Tests\Feature;

use Tests\TestCase;
use Laravel\Socialite\Facades\Socialite;

class GitHubRedirectTest extends TestCase
{
    public function test_github_redirect_returns_302_status()
    {
        $response = $this->get('/api/auth/github/redirect');

        $response->assertStatus(302);
    }

    public function test_github_redirect_contains_github_oauth_url()
    {
        $response = $this->get('/api/auth/github/redirect');

        $response->assertStatus(302);

        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('github.com/login/oauth/authorize', $redirectUrl);
    }

    public function test_github_redirect_contains_required_parameters()
    {
        $response = $this->get('/api/auth/github/redirect');

        $response->assertStatus(302);
        
        $redirectUrl = $response->headers->get('Location');
        
        $this->assertStringContainsString('client_id=', $redirectUrl);
        $this->assertStringContainsString('redirect_uri=', $redirectUrl);
        $this->assertStringContainsString('scope=', $redirectUrl);
    }

} 