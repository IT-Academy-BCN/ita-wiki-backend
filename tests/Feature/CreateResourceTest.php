<?php

declare (strict_types= 1);

namespace Tests\Feature;

use App\Models\Resource;
use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;


class CreateResourceTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function setUp(): void
    {
        parent::setUp();
    }

    private function GetResourceData(): array
    {
        Role::factory(10)->create();
        return Resource::factory()->raw(); 
    }

    public function testItCanCreateAResource(): void
    {
        $response = $this->postJson(route('resource.store'), $this->GetResourceData());

        $response->assertStatus(201);
    }

    public function testItReturns404WhenRouteIsNotFound(): void
    {
        $response = $this->postJson('/non-existent-route', []);

        $response->assertStatus(404);
    } 
    
    public function testItCanShowStatus_422WhenGithubIdIsAnonymous(): void
    {
        $anonymousGithubId = Role::factory()->create(['role' => 'anonymous'])->github_id;
        $data = $this->GetResourceData();
        $data['github_id'] = $anonymousGithubId;
    
        $response = $this->postJson(route('resource.store'), $data);

        $response->assertStatus(422)
            ->assertJsonPath('github_id', function ($errors) {
                return is_array($errors) && count($errors) > 0;
            });
    }    

    #[DataProvider('resourceValidationProvider')]
    public function testItCanShowStatus_422WithInvalidData(array $invalidData, string $fieldName): void
    {
        $data = $this->GetResourceData();
        $data = array_merge($data, $invalidData);

        $response = $this->postJson(route('resource.store'), $data);

        $response->assertStatus(422)
        // This verifies that the field $fieldName exists in the response and has at least one error message.
        ->assertJsonPath($fieldName, function ($errors) {
            return is_array($errors) && count($errors) > 0;
        });
    }

        
    public static function resourceValidationProvider(): array
    {
        return[
        // github_id
            'missing github_id' => [['github_id' => null], 'github_id'],
        // title
            'missing title' => [['title' => null], 'title'],
            'invalid title (too short)' => [['title' => 'a'], 'title'],
            'invalid title (too long)' => [['title' => self::generateLongText(256)], 'title'],
            'invalid title (array)' => [['title' => []], 'title'],
        // description
            'invalid description (too short)' => [['description' => 'a'], 'description'],
            'invalid description (too long)' => [['description' => self::generateLongText(1001)], 'description'],
            'invalid description (array)' => [['description' => []], 'description'],
        // url
            'missing url' => [['url' => null], 'url'],
            'invalid url (not a url)' => [['url' => 'not a url'], 'url'],
            'invalid url (array)' => [['url' => []], 'url'],
            'invalid url (integer)' => [['url' => 123], 'url'],
        ];
    }

    /**
     * Generates a string of the exact length specified by the `$length` parameter.
     *
     * This method uses a regular expression to ensure the generated string
     * matches the desired length, guaranteeing that the output will have
     * precisely the number of characters requested.
     *
     * @param int $length The desired length of the generated string.
     * @return string A string with the exact specified length.
     */
    private static function generateLongText(int $length): string
    {
        $faker = \Faker\Factory::create();
        return $faker->regexify("[a-zA-Z0-9]{{$length}}");
    }

}
