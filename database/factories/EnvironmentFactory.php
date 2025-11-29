<?php

namespace Database\Factories;

use App\Models\Environment;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Environment>
 */
class EnvironmentFactory extends Factory
{
    protected $model = Environment::class;

    public function definition(): array
    {
        $name = fake()->randomElement([
            'Production', 'Staging', 'Testing', 'Local', 'Preview', 'Custom '.Str::title(fake()->word()),
        ]);

        $slugBase = Str::slug($name);

        return [
            'project_id' => Project::factory(),
            'name' => $name,
            'slug' => $slugBase.'-'.Str::random(5),
            'order' => fake()->numberBetween(0, 50),
            'type' => fake()->randomElement(Environment::TYPES),
            'is_default' => false,
        ];
    }

    public function default(): self
    {
        return $this->state(fn () => ['is_default' => true]);
    }

    public function type(string $type): self
    {
        return $this->state(fn () => ['type' => $type]);
    }
}
