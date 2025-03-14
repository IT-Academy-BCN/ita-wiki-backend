<?php

declare (strict_types= 1);

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resource>
 */
class ResourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $role = Role::find(1)->inRandomOrder("id")->first();

        return [
            'github_id' => $role->github_id,
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->sentence(6),
            'url' => $this->faker->url(),
            'category' => $this->faker->randomElement(['Node', 'React', 'Angular', 'JavaScript', 'Java', 'Fullstack PHP', 'Data Science', 'BBDD']),
            'theme' => $this->faker->randomElement(['All', 'Components', 'UseState & UseEffect', 'Eventos' , 'Renderizado condicional', 'Listas', 'Estilos', 'Debugging', 'React Router']),
            'type' => $this->faker->randomElement(['Video', 'Cursos', 'Blog']),
            'votes' => $this->faker->numberBetween(0,50)
        ];
    }
}
