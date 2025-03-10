<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Resource;
use App\Models\Role;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateResourceTest extends TestCase
{
    use WithFaker;

   
    protected function setUp(): void
    {
        parent::setUp();
        //creamos 10 roles
        Role::factory(10)->create();
    }

  
    //creamos diferentes resources
    private function createResource(array $overrides = []): Resource
    {
        return Resource::factory()->create($overrides);
    }

    //Solicitar una actualizacion de un reource
    private function updateResourceRequest(int $resourceId, array $data)
    {
        return $this->putJson(route('resources.update', $resourceId), $data);
    }

   //Puede actualizar un resource
    public function testItCanUpdateAResource()
    {
  
        $role = Role::factory()->create(['github_id' => 12345]);

        
        $resource = $this->createResource(['github_id' => $role->github_id]);

        // Datos para actualizar
        $data = [
            'github_id' => $role->github_id, 
            'title' => 'Updated title',
            'description' => 'Updated description',
            'url' => 'https://updated-url.com',
        ];

         // Solicitud de actualización
        $response = $this->updateResourceRequest($resource->id, $data);

        //Respuesta
        $response->assertStatus(200)
                ->assertJson([
                    'title' => 'Updated title',
                    'description' => 'Updated description',
                    'url' => 'https://updated-url.com',
                ]);

        // Verificar que se hayan actualizado en la base de datos
        $this->assertDatabaseHas('resources', [
            'id' => $resource->id,
            'title' => 'Updated title',
            'description' => 'Updated description',
            'url' => 'https://updated-url.com',
        ]);
    }

    //Devuelve 404 cuando el Resource no existe.
    public function testItReturns404WhenResourceNotFound()
    {
      // ID que no existe
        $nonExistentId = 9999;

     // Solicitud de actualización
        $response = $this->updateResourceRequest($nonExistentId, [
            'title' => 'Updated title',
            'description' => 'Updated description',
            'url' => 'https://updated-url.com',
        ]);

       // Verificamos que se devuelve el error 404
        $response->assertStatus(404);

      // Verificamos que no se haya creado ningún Resource
        $this->assertDatabaseCount('resources', 0);
    }

    // Devuelve 422 cuando se intenta usar una URL duplicada
    public function testItCanShowStatus422WithDuplicateUrl()
    {
        // A partir de un Resource existente
        $existingResource = $this->createResource();

        // Crear otro Resource para actualizar
        $resourceToUpdate = $this->createResource();

        // Intentar actualizar con la misma URL
        $response = $this->updateResourceRequest($resourceToUpdate->id, [
            'title' => 'Updated title',
            'description' => 'Updated description',
            'url' => $existingResource->url, 
        ]);

        // Verificamos que se devuelva un error 422
        $response->assertStatus(422);

        // Verificamos que no se haya actualizado
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
        // Crear Resource
        $resource = $this->createResource();

      // Datos válidos
        $data = [
            'title' => 'Updated title',
            'description' => 'Updated description',
            'url' => 'https://updated-url.com',
        ];

        // Combinamos datos válidos y inválidos
        $data = array_merge($data, $invalidData);

     
        $response = $this->updateResourceRequest($resource->id, $data);

        // Verificar que se devuelva un error 422
        $response->assertStatus(422)
            
                 ->assertJsonPath($fieldName, function ($errors) {
                     return is_array($errors) && count($errors) > 0;
                 });

         // Verificar que el Resource no se haya actualizado
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