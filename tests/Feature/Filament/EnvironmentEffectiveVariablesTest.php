<?php

use App\Filament\Resources\EnvironmentResource\Pages\ViewEnvironment;
use App\Models\Environment;
use App\Models\EnvironmentVariableValue;
use App\Models\Project;
use App\Models\ProjectVariableValue;
use App\Models\User;
use App\Models\VariableKey;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Filament::setCurrentPanel('default');
    $this->actingAs(User::factory()->create());
    app()->setLocale('en');
});

it('shows effective variables for an environment with correct precedence and masking', function (): void {
    $project = Project::factory()->create();
    $env = Environment::factory()->for($project)->create(['name' => 'Prod', 'slug' => 'prod']);

    // VariableKey A: only default_value should be used (source: Default)
    $vkA = VariableKey::factory()->create([
        'key' => 'ONLY_DEFAULT',
        'is_secret' => false,
        'default_value' => 'def-A',
    ]);

    // VariableKey B: project default present (source: Project)
    $vkB = VariableKey::factory()->create([
        'key' => 'PROJECT_ONLY',
        'is_secret' => false,
        'default_value' => null,
    ]);
    ProjectVariableValue::factory()->create([
        'project_id' => $project->id,
        'variable_key_id' => $vkB->id,
        'value' => 'proj-B',
    ]);

    // VariableKey C: env override wins over project (source: Environment)
    $vkC = VariableKey::factory()->create([
        'key' => 'ENV_OVERRIDES_PROJECT',
        'is_secret' => false,
        'default_value' => 'def-C',
    ]);
    ProjectVariableValue::factory()->create([
        'project_id' => $project->id,
        'variable_key_id' => $vkC->id,
        'value' => 'proj-C',
    ]);
    EnvironmentVariableValue::factory()->create([
        'environment_id' => $env->id,
        'variable_key_id' => $vkC->id,
        'value' => 'env-C',
    ]);

    // VariableKey D: secret, must be masked and come from environment
    $vkD = VariableKey::factory()->create([
        'key' => 'SECRET_TOKEN',
        'is_secret' => true,
    ]);
    EnvironmentVariableValue::factory()->create([
        'environment_id' => $env->id,
        'variable_key_id' => $vkD->id,
        'value' => 'super-secret-D',
    ]);

    $test = Livewire::test(ViewEnvironment::class, [
        'record' => $env->getKey(),
    ]);

    // Section title
    $test->assertSee('Effective Variables');

    // A: default
    $test->assertSee('ONLY_DEFAULT')
        ->assertSee('def-A')
        ->assertSee('Default');

    // B: project
    $test->assertSee('PROJECT_ONLY')
        ->assertSee('proj-B')
        ->assertSee('Project');

    // C: environment overrides project
    $test->assertSee('ENV_OVERRIDES_PROJECT')
        ->assertSee('env-C')
        ->assertDontSeeText('proj-C')
        ->assertSee('Environment');

    // D: secret masked
    $test->assertSee('SECRET_TOKEN')
        ->assertSee('••••')
        ->assertDontSeeText('super-secret-D');

    // Each key should appear only once in the list (basic sanity by counting occurrences in rendered HTML)
    $html = $test->lastResponse->getContent();
    foreach (['ONLY_DEFAULT', 'PROJECT_ONLY', 'ENV_OVERRIDES_PROJECT', 'SECRET_TOKEN'] as $key) {
        expect(substr_count($html, $key))->toBe(1);
    }
});
