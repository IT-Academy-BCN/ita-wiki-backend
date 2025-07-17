<?php

declare (strict_types= 1);

namespace Tests\Feature;

use App\Models\Tag;
use Tests\TestCase;
use App\Models\Role;
use App\Models\Resource;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\DataProvider;

class CreateResourceTest extends TestCase
{
    use WithFaker;

    private function GetResourceData(): array
    {
        // Crear el rol en Spatie si no existe
        \Spatie\Permission\Models\Role::findOrCreate('student', 'web');
        // Crear el usuario
        $user = \App\Models\User::factory()->create([
            'github_id' => random_int(100000, 999999),
        ]);
        // Asignar el rol con Spatie
        $user->assignRole('student');
        // Crear el registro en user_roles
        \App\Models\Role::create([
            'github_id' => $user->github_id,
            'role' => 'student',
        ]);
        return \App\Models\Resource::factory()->raw([
            'github_id' => $user->github_id,
            //'tags' => null
        ]);
    }

    private function GetResourceDataTagsId(): array
    {
        \Spatie\Permission\Models\Role::findOrCreate('student', 'web');
        $user = \App\Models\User::factory()->create([
            'github_id' => random_int(100000, 999999),
        ]);
      
        $user->assignRole('student');
      
        \App\Models\Role::create([
            'github_id' => $user->github_id,
            'role' => 'student',
        ]);
        $tagIds = \App\Models\Tag::inRandomOrder()->take(3)->pluck('id')->toArray();
        return \App\Models\Resource::factory()->raw([
            'github_id' => $user->github_id,
            'tags' => $tagIds // Assuming these IDs exist in the tags table
        ]);
    }

    public function testItCanCreateAResourceWithTagsId(): void
    {
        $response = $this->postJson(route('resources.store.v2'), $this->GetResourceDataTagsId());

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

    #[DataProvider('resourceValidationProvider')]
    public function testItCanShowStatus_422WithInvalidData(array $invalidData, string $fieldName): void
    {
        $data = $this->GetResourceData();
        $data = array_merge($data, $invalidData);

        $response = $this->postJson(route('resources.store'), $data);

        $response->assertStatus(422)

        ->assertJsonPath($fieldName, function ($errors) {
            return is_array($errors) && count($errors) > 0;
        });
    }

  
        
    public static function resourceValidationProvider(): array
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
