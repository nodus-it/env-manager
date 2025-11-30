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

function seedScenarioForActions(): array
{
    $project = Project::factory()->create();
    $env = Environment::factory()->for($project)->create();

    // default only
    $vkDefaultOnly = VariableKey::factory()->create([
        'key' => 'ONLY_DEFAULT',
        'is_secret' => false,
        'default_value' => 'def-A',
    ]);

    // project only
    $vkProjectOnly = VariableKey::factory()->create([
        'key' => 'PROJECT_ONLY',
        'is_secret' => false,
        'default_value' => null,
    ]);
    ProjectVariableValue::factory()->create([
        'project_id' => $project->id,
        'variable_key_id' => $vkProjectOnly->id,
        'value' => 'proj-B',
    ]);

    // environment overrides project
    $vkEnvWins = VariableKey::factory()->create([
        'key' => 'ENV_OVERRIDES_PROJECT',
        'is_secret' => false,
        'default_value' => 'def-C',
    ]);
    ProjectVariableValue::factory()->create([
        'project_id' => $project->id,
        'variable_key_id' => $vkEnvWins->id,
        'value' => 'proj-C',
    ]);
    $envValue = EnvironmentVariableValue::factory()->create([
        'environment_id' => $env->id,
        'variable_key_id' => $vkEnvWins->id,
        'value' => 'env-C',
    ]);

    return compact('project', 'env', 'vkDefaultOnly', 'vkProjectOnly', 'vkEnvWins', 'envValue');
}

it('shows correct action visibility per source', function (): void {
    extract(seedScenarioForActions());

    $test = Livewire::test(ViewEnvironment::class, ['record' => $env->getKey()]);

    // Ensure section title is present
    $test->assertSee('Effective Variables');

    // Row indexes are sorted by key: ENV_OVERRIDES_PROJECT (0), ONLY_DEFAULT (1), PROJECT_ONLY (2)
    // Environment row (index 0): all three actions should exist/visible
    $test->assertInfolistActionExists('effectiveVariables.0.actions', 'editAtSource')
        ->assertInfolistActionVisible('effectiveVariables.0.actions', 'editAtSource')
        ->assertInfolistActionExists('effectiveVariables.0.actions', 'adoptAsProjectDefault')
        ->assertInfolistActionVisible('effectiveVariables.0.actions', 'adoptAsProjectDefault')
        ->assertInfolistActionExists('effectiveVariables.0.actions', 'adoptAsDefault')
        ->assertInfolistActionVisible('effectiveVariables.0.actions', 'adoptAsDefault');

    // Default row (index 1): adoptAsDefault should be hidden/not present
    $test->assertInfolistActionHidden('effectiveVariables.1.actions', 'adoptAsProjectDefault')
        ->assertInfolistActionHidden('effectiveVariables.1.actions', 'adoptAsDefault');
})->group('filament');

it('can adopt environment value as project default', function (): void {
    extract(seedScenarioForActions());

    // Sanity: project has value proj-C for vkEnvWins before action
    expect(ProjectVariableValue::query()->where('project_id', $project->id)->where('variable_key_id', $vkEnvWins->id)->value('value'))
        ->toBe('proj-C');

    $component = Livewire::test(ViewEnvironment::class, ['record' => $env->getKey()]);

    // Rows are sorted by key. With keys [ENV_OVERRIDES_PROJECT, ONLY_DEFAULT, PROJECT_ONLY], the env row is index 0.
    $component->callInfolistAction('effectiveVariables.0.actions', 'adoptAsProjectDefault', data: [], arguments: [
        'project_id' => $project->id,
        'variable_key_id' => $vkEnvWins->id,
        'raw_value' => 'env-C',
    ]);

    expect(ProjectVariableValue::query()->where('project_id', $project->id)->where('variable_key_id', $vkEnvWins->id)->value('value'))
        ->toBe('env-C');
})->group('filament');

it('can adopt any non-default value as default (variable key default)', function (): void {
    extract(seedScenarioForActions());

    // Sanity: default is def-C for vkEnvWins
    expect(VariableKey::query()->whereKey($vkEnvWins->id)->value('default_value'))->toBe('def-C');

    $component = Livewire::test(ViewEnvironment::class, ['record' => $env->getKey()]);

    // Attempt to click the adoptAsDefault action for the environment-sourced row
    $component->callInfolistAction('effectiveVariables.0.actions', 'adoptAsDefault', data: [], arguments: [
        'variable_key_id' => $vkEnvWins->id,
        'raw_value' => 'env-C',
    ]);

    expect(VariableKey::query()->whereKey($vkEnvWins->id)->value('default_value'))->toBe('env-C');
})->group('filament');
