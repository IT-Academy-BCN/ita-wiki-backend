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

class ResourceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    // --- CreateResourceTest.php ---

    private function GetResourceData(): array
    {
        $user = User::factory()->create();

        // ELIMINAR cuando Spatie se implemente totalmente 
        OldRole::factory()->create([ 
            'github_id' => $user->github_id,
            'role' => 'student'
        ]);

        return Resource::factory()->raw([
            'github_id' => $user->github_id,
            'tags' => null
        ]);
       
    }

    private function GetResourceDataTagsId(): array
    {
        $user = User::factory()->create();

        // ELIMINAR cuando Spatie se implemente totalmente 
        OldRole::factory()->create([
            'github_id' => $user->github_id,
            'role' => 'student'
        ]);

        $tagIds = Tag::inRandomOrder()->take(3)->pluck('id')->toArray();

        return Resource::factory()->raw([
            'github_id' => $user->github_id,
            'tags' => $tagIds
        ]);
    }

    public function testItCanCreateAResourceWithTagsId(): void
    {
        $response = $this->postJson(route('resources.store'), $this->GetResourceDataTagsId());

        $response->assertStatus(201);
    }

    public function testItCanCreateAResource(): void
    {
        
        $response = $this->postJson(route('resources.store'), $this->GetResourceData());

        $response->assertStatus(201);
    }

    public function testItReturns404WhenRouteIsNotFound(): void
    {
        $response = $this->postJson('/non-existent-route', []);

        $response->assertStatus(404);
    }    

    #[DataProvider('resourceCreationValidationProvider')]
    public function testItCanShowStatus_422WithInvalidDataOnCreate(array $invalidData, string $fieldName): void
    {
        $data = $this->GetResourceData();
        $data = array_merge($data, $invalidData);

        $response = $this->postJson(route('resources.store'), $data);

        $response->assertStatus(422)
        ->assertJsonPath($fieldName, function ($errors) {
            return is_array($errors) && count($errors) > 0;
        });
    }
  
    public static function resourceCreationValidationProvider(): array
    {
        return[
        // github_id
            'missing github_id' => [['github_id' => null], 'github_id'],
            'github_id does not have a role' => [['github_id'=> 99999999999],'github_id'],
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

    private static function generateLongText(int $length): string
    {
        $faker = \Faker\Factory::create();
        return $faker->regexify("[a-zA-Z0-9]{{$length}}");
    }

    // ---UpdateResourceTest.php ---

    private function createResource(array $overrides = []): Resource
    {
        return Resource::factory()->create($overrides);
    }

    private function updateResourceRequest(int $resourceId, array $data)
    {
        return $this->putJson(route('resources.update', $resourceId), $data);
    }

    public function testItCanUpdateAResource()
    {
        $user = User::factory()->create(['github_id' => 12345]);
        
        $resource = $this->createResource(['github_id' => $user->github_id]);

        $data = [
            'github_id' => $user->github_id, 
            'title' => 'Updated title',
            'description' => 'Updated description',
            'url' => 'https://updated-url.com',
        ];

        $response = $this->updateResourceRequest($resource->id, $data);

        $response->assertStatus(200)
                ->assertJson([
                    'title' => 'Updated title',
                    'description' => 'Updated description',
                    'url' => 'https://updated-url.com',
                ]);

        $this->assertDatabaseHas('resources', [
            'id' => $resource->id,
            'title' => 'Updated title',
            'description' => 'Updated description',
            'url' => 'https://updated-url.com',
        ]);
    }

    public function testItCanShowStatus422WithDuplicateUrl()
    {
        $existingResource = $this->createResource();
        $resourceToUpdate = $this->createResource();

        $response = $this->updateResourceRequest($resourceToUpdate->id, [
            'title' => 'Updated title',
            'description' => 'Updated description',
            'url' => $existingResource->url, 
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseHas('resources', [
            'id' => $resourceToUpdate->id,
            'title' => $resourceToUpdate->title,
            'description' => $resourceToUpdate->description,
            'url' => $resourceToUpdate->url,
        ]);
    }

    #[DataProvider('resourceUpdateValidationProvider')]
    public function testItCanShowStatus422WithInvalidDataOnUpdate(array $invalidData, string $fieldName)
    {
        $resource = $this->createResource();

        $data = [
            'title' => 'Updated title',
            'description' => 'Updated description',
            'url' => 'https://updated-url.com',
        ];

        $data = array_merge($data, $invalidData);
    
        $response = $this->updateResourceRequest($resource->id, $data);

        $response->assertStatus(422)
            
                ->assertJsonPath($fieldName, function ($errors) {
                    return is_array($errors) && count($errors) > 0;
                });

        $this->assertDatabaseHas('resources', [
            'id' => $resource->id,
            'title' => $resource->title,
            'description' => $resource->description,
            'url' => $resource->url,
        ]);
    }

    public static function resourceUpdateValidationProvider()
    {
        return [
            'missing title' => [['title' => null], 'title'],
            'invalid title (too short)' => [['title' => 'a'], 'title'],
            'invalid title (too long)' => [['title' => str_repeat('a', 256)], 'title'],
            'invalid title (array)' => [['title' => []], 'title'],
            'invalid description (too short)' => [['description' => 'short'], 'description'],
            'invalid description (too long)' => [['description' => str_repeat('a', 1001)], 'description'],
            'invalid description (array)' => [['description' => []], 'description'],
            'missing url' => [['url' => null], 'url'],
            'invalid url (not a url)' => [['url' => 'not a url'], 'url'],
            'invalid url (array)' => [['url' => []], 'url'],
            'invalid url (integer)' => [['url' => 123], 'url'],
        ];
    }

    // --- ResourceTest.php (originale) ---

    public function test_can_get_list(): void
    {
        $response = $this->get(route('resources.index'));

        $response->assertStatus(200);
    }

    // --- ShowResourceTest.php ---

    public function test_user_can_search_for_resources(): void
    {
        $response = $this->get(route('resources.index') . '?search=Laravel');

        $response->assertStatus(200);
    }
}