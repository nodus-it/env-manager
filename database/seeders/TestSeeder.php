<?php

namespace Database\Seeders;

use App\Models\Environment;
use App\Models\EnvironmentVariableValue;
use App\Models\Project;
use App\Models\ProjectVariableValue;
use App\Models\Team;
use App\Models\User;
use App\Models\VariableKey;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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

        // Projects (exactly 2)
        $projectNames = [
            'Environment Manager',
            'Deployment Portal',
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

        // Environments per Project (exactly 3)
        foreach ($projects as $project) {
            $this->seedEnvironmentsForProject($project);
        }

        // Seed many Variable Keys (diverse, some defaults, some secret)
        $variableKeys = $this->seedVariableKeys();

        // Pick a creator/updater for values
        /** @var User $actor */
        $actor = User::query()->inRandomOrder()->first();

        // Project defaults (about half of keys per project)
        foreach ($projects as $project) {
            $keysForProject = $variableKeys->random((int) max(1, floor($variableKeys->count() * 0.5)));
            foreach ($keysForProject as $vk) {
                ProjectVariableValue::query()->firstOrCreate(
                    [
                        'project_id' => $project->getKey(),
                        'variable_key_id' => $vk->getKey(),
                    ],
                    [
                        'value' => $this->fakeValueForType($vk->type, $vk->is_secret),
                        'created_by' => $actor->getKey(),
                        'updated_by' => $actor->getKey(),
                    ]
                );
            }
        }

        // Environment overrides (many keys, prioritize overriding secrets too)
        /** @var Collection<int,Environment> $allEnvs */
        $allEnvs = Environment::query()->whereIn('project_id', $projects->pluck('id'))->get();
        foreach ($allEnvs as $env) {
            // Override ~70% of keys for each environment
            $keysForEnv = $variableKeys->random((int) max(1, ceil($variableKeys->count() * 0.7)));

            foreach ($keysForEnv as $vk) {
                EnvironmentVariableValue::query()->firstOrCreate(
                    [
                        'environment_id' => $env->getKey(),
                        'variable_key_id' => $vk->getKey(),
                    ],
                    [
                        'value' => $this->fakeValueForType($vk->type, $vk->is_secret, $env->slug),
                        'created_by' => $actor->getKey(),
                        'updated_by' => $actor->getKey(),
                    ]
                );
            }
        }
    }

    /**
     * Create multiple environments for a given project.
     */
    protected function seedEnvironmentsForProject(Project $project): void
    {
        // Define exactly three in a deterministic order
        $definitions = [
            ['name' => 'Production', 'slug' => 'prod', 'type' => 'production', 'order' => 0, 'is_default' => true],
            ['name' => 'Staging', 'slug' => 'staging', 'type' => 'staging', 'order' => 10, 'is_default' => false],
            ['name' => 'Testing', 'slug' => 'testing', 'type' => 'testing', 'order' => 20, 'is_default' => false],
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

        // Ensure exactly one default remains
        $hasDefault = Environment::query()
            ->where('project_id', $project->getKey())
            ->where('is_default', true)
            ->exists();

        if (! $hasDefault) {
            optional(Environment::query()->where('project_id', $project->getKey())->where('slug', 'prod')->first())
                ?->update(['is_default' => true]);
        }
    }

    /**
     * Seed a wide range of variable keys.
     *
     * @return Collection<int,VariableKey>
     */
    protected function seedVariableKeys(): Collection
    {
        $predefined = [
            ['APP_NAME', 'string', false, 'required|string|max:255', 'EnvManager'],
            ['APP_ENV', 'string', false, 'in:local,testing,staging,production', 'local'],
            ['APP_URL', 'string', false, 'url', 'http://localhost'],
            ['APP_DEBUG', 'bool', false, 'boolean', 'false'],
            ['LOG_LEVEL', 'string', false, 'in:debug,info,notice,warning,error,critical,alert,emergency', 'info'],
            ['DB_HOST', 'string', false, 'required', '127.0.0.1'],
            ['DB_PORT', 'int', false, 'integer|min:1|max:65535', '3306'],
            ['DB_DATABASE', 'string', false, 'required', 'env_manager'],
            ['DB_USERNAME', 'string', false, 'required', 'root'],
            ['DB_PASSWORD', 'string', true, 'nullable|string', null],
            ['REDIS_HOST', 'string', false, 'required', '127.0.0.1'],
            ['REDIS_PASSWORD', 'string', true, 'nullable|string', null],
            ['MAIL_MAILER', 'string', false, 'in:smtp,sendmail,log', 'smtp'],
            ['MAIL_HOST', 'string', false, 'nullable|string', null],
            ['MAIL_PORT', 'int', false, 'integer|min:1|max:65535', 587],
            ['MAIL_USERNAME', 'string', true, 'nullable|string', null],
            ['MAIL_PASSWORD', 'string', true, 'nullable|string', null],
            ['MAIL_ENCRYPTION', 'string', false, 'in:tls,ssl,null', 'tls'],
            ['MAIL_FROM_ADDRESS', 'string', false, 'email', 'noreply@example.com'],
            ['MAIL_FROM_NAME', 'string', false, 'nullable|string', 'Env Manager'],
            ['SENTRY_DSN', 'string', true, 'nullable|url', null],
            ['FEATURE_FLAGS', 'json', false, 'json', '{"newUI":false}'],
            ['QUEUE_CONNECTION', 'string', false, 'in:sync,redis,database', 'sync'],
            ['CACHE_STORE', 'string', false, 'in:file,redis,array,database', 'file'],
            ['API_RATE_LIMIT', 'int', false, 'integer|min:1', 60],
            ['MAINTENANCE_MODE', 'bool', false, 'boolean', false],
        ];

        $keys = collect();
        foreach ($predefined as [$key, $type, $secret, $rules, $default]) {
            $keys->push(
                VariableKey::query()->firstOrCreate(
                    ['key' => $key],
                    [
                        'description' => Str::title(strtolower(str_replace('_', ' ', $key))).' description',
                        'type' => $type,
                        'is_secret' => $secret,
                        'validation_rules' => $rules,
                        'default_value' => $default,
                        // created_by/updated_by columns are required; pick the first user
                        'created_by' => User::query()->inRandomOrder()->value('id') ?? User::factory()->create()->id,
                        'updated_by' => User::query()->inRandomOrder()->value('id') ?? User::factory()->create()->id,
                    ]
                )
            );
        }

        // Add some random extra keys to increase volume
        if ($keys->count() < 30) {
            $extraCount = 30 - $keys->count();
            for ($i = 0; $i < $extraCount; $i++) {
                $randKey = Str::upper(Str::snake('EXTRA '.fake()->unique()->word()));
                $keys->push(
                    VariableKey::query()->firstOrCreate(
                        ['key' => $randKey],
                        [
                            'description' => fake()->sentence(6),
                            'type' => Arr::random(['string', 'int', 'bool', 'json']),
                            'is_secret' => fake()->boolean(30),
                            'validation_rules' => null,
                            'default_value' => fake()->boolean(50) ? (string) fake()->word() : null,
                            'created_by' => User::query()->inRandomOrder()->value('id') ?? User::factory()->create()->id,
                            'updated_by' => User::query()->inRandomOrder()->value('id') ?? User::factory()->create()->id,
                        ]
                    )
                );
            }
        }

        return $keys->values();
    }

    /**
     * Create a plausible value for a given type, considering secrecy.
     */
    protected function fakeValueForType(string $type, bool $secret, ?string $context = null): string
    {
        $ctx = $context ? strtoupper($context) : 'GEN';

        return match ($type) {
            'int' => (string) fake()->numberBetween(1, 10000),
            'bool' => fake()->boolean() ? 'true' : 'false',
            'json' => json_encode(['ctx' => $ctx, 'enabled' => fake()->boolean()]),
            default => $secret
                ? Str::password(16)
                : (string) ($context ? (Str::lower($context).'-'.fake()->word()) : fake()->word()),
        };
    }
}
