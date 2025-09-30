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

class CreateResourceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

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


}


?>