<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Resource;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UpdateResourceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

   
    protected function setUp(): void
    {
        parent::setUp();

        Role::factory(10)->create();
    }

  
    private function createResource(array $overrides = []): Resource
    {
        return Resource::factory()->create($overrides);
    }

    private function updateResourceRequest(int $resourceId, array $data)
    {
        return $this->putJson(route('resource.update', $resourceId), $data);
    }

   
    public function testItCanUpdateAResource()
{
  
    $role = Role::factory()->create(['github_id' => 12345]);

    
    $resource = $this->createResource(['github_id' => $role->github_id]);

   
    $data = [
        'github_id' => $role->github_id, 
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


    public function testItReturns404WhenResourceNotFound()
    {
      
        $nonExistentId = 9999;

     
        $response = $this->updateResourceRequest($nonExistentId, [
            'title' => 'Updated title',
            'description' => 'Updated description',
            'url' => 'https://updated-url.com',
        ]);

       
        $response->assertStatus(404);

      
        $this->assertDatabaseCount('resources', 0);
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

    /**
     *
     * @dataProvider resourceValidationProvider
     */
    public function testItCanShowStatus422WithInvalidData(array $invalidData, string $fieldName)
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

   
    public static function resourceValidationProvider()
    {
        return [
          
            'missing title' => [['title' => null], 'title'],
            'invalid title (too short)' => [['title' => 'a'], 'title'],
            'invalid title (too long)' => [['title' => self::generateLongText(256)], 'title'],
            'invalid title (array)' => [['title' => []], 'title'],
            
            'missing description' => [['description' => null], 'description'],
            'invalid description (too short)' => [['description' => 'short'], 'description'],
            'invalid description (too long)' => [['description' => self::generateLongText(1001)], 'description'],
            'invalid description (array)' => [['description' => []], 'description'],
            
            'missing url' => [['url' => null], 'url'],
            'invalid url (not a url)' => [['url' => 'not a url'], 'url'],
            'invalid url (array)' => [['url' => []], 'url'],
            'invalid url (integer)' => [['url' => 123], 'url'],
        ];
    }

   
    public static function generateLongText(int $length): string
    {
        return str_repeat('a', $length);
    }
}