<?php

namespace Database\Seeders;

use App\Models\Environment;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class TestSeeder extends Seeder
{
    /**
     * Seed a sensible test dataset.
     */
    public function run(): void
    {
        // Users
        $users = User::factory()->count(12)->create();

        // Teams
        $teams = collect();
        foreach (['Core Team', 'Platform Team', 'QA Team'] as $teamName) {
            $owner = $users->shift();

            /** @var Team $team */
            $team = Team::query()->firstOrCreate(
                ['slug' => Str::slug($teamName)],
                [
                    'name' => $teamName,
                    'owner_id' => $owner->id,
                ]
            );

            // Attach members with roles
            $members = $users->take(3);

            // ensure owner is also attached with role "owner"
            $team->users()->syncWithoutDetaching([
                $owner->id => ['role' => 'owner'],
            ]);

            foreach ($members as $i => $member) {
                $role = match ($i % 3) {
                    0 => 'admin',
                    1 => 'member',
                    default => 'viewer',
                };

                $team->users()->syncWithoutDetaching([
                    $member->id => ['role' => $role],
                ]);
            }

            $teams->push($team);
        }

        // Projects
        $projectNames = [
            'Environment Manager',
            'Deployment Portal',
            'Secret Vault',
            'Service Registry',
            'Audit Trail',
        ];

        $projects = collect();
        foreach ($projectNames as $name) {
            /** @var Project $project */
            $project = Project::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'repo_url' => 'https://github.com/example/'.Str::slug($name),
                    'description' => fake()->sentence(10),
                ]
            );

            // Attach the project to 1-2 teams with roles
            $assignedTeams = $teams->random(rand(1, 2));

            foreach ($assignedTeams as $idx => $team) {
                $role = match ($idx) {
                    0 => 'owner',
                    1 => 'contributor',
                    default => 'readonly',
                };
                $team->projects()->syncWithoutDetaching([
                    $project->getKey() => ['role' => $role],
                ]);
            }

            $projects->push($project);
        }

        // Environments per Project
        foreach ($projects as $project) {
            $this->seedEnvironmentsForProject($project);
        }
    }

    /**
     * Create multiple environments for a given project.
     */
    protected function seedEnvironmentsForProject(Project $project): void
    {
        // Define a common set in a deterministic order
        $definitions = [
            ['name' => 'Production', 'slug' => 'prod', 'type' => 'production', 'order' => 0, 'is_default' => true],
            ['name' => 'Staging', 'slug' => 'staging', 'type' => 'staging', 'order' => 10, 'is_default' => false],
            ['name' => 'Testing', 'slug' => 'testing', 'type' => 'testing', 'order' => 20, 'is_default' => false],
            ['name' => 'Local', 'slug' => 'local', 'type' => 'local', 'order' => 30, 'is_default' => false],
            ['name' => 'Preview', 'slug' => 'preview', 'type' => 'custom', 'order' => 40, 'is_default' => false],
        ];

        foreach ($definitions as $def) {
            Environment::query()->firstOrCreate(
                [
                    'project_id' => $project->getKey(),
                    'slug' => $def['slug'], // unique per project
                ],
                [
                    'name' => $def['name'],
                    'order' => $def['order'],
                    'type' => $def['type'],
                    'is_default' => $def['is_default'],
                ]
            );
        }

        // Ensure exactly one default remains (model hook already enforces this when saving true)
        // If none marked default by chance, set Production as default explicitly
        $hasDefault = Environment::query()
            ->where('project_id', $project->getKey())
            ->where('is_default', true)
            ->exists();

        if (! $hasDefault) {
            optional(Environment::query()->where('project_id', $project->getKey())->where('slug', 'prod')->first())
                ?->update(['is_default' => true]);
        }
    }
}
