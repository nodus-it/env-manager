<?php

use App\Models\Environment;
use App\Models\EnvironmentVariableValue;
use App\Models\Project;
use App\Models\VariableKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;

uses(RefreshDatabase::class);

it('returns 404 for unknown environment', function () {
    $this->getJson('/api/environment/999999')
        ->assertNotFound();
});

it('returns effective keys with secrets masked by default', function () {
    $project = Project::factory()->create();
    $environment = Environment::factory()->for($project)->create();

    // Secret key with only default value
    $secretKey = VariableKey::factory()->state([
        'is_secret' => true,
        'default_value' => 'super-secret',
        'key' => 'SECRET_TOKEN',
        'type' => 'string',
    ])->create();

    // Non-secret key overridden in environment
    $plainKey = VariableKey::factory()->state([
        'is_secret' => false,
        'default_value' => 'plain-default',
        'key' => 'APP_NAME',
        'type' => 'string',
    ])->create();

    EnvironmentVariableValue::factory()->create([
        'environment_id' => $environment->id,
        'variable_key_id' => $plainKey->id,
        'value' => 'Env App',
    ]);

    $response = $this->getJson('/api/environment/'.$environment->getKey());

    $response->assertSuccessful();

    $response->assertJson(fn (AssertableJson $json) => $json
        ->has('name')
        ->has('slug')
        ->has('type')
        ->has('projectSlug')
        ->has('projectName')
        ->has('keys', fn (AssertableJson $keys) => $keys
            ->whereType('0.key', 'string')
            ->etc()
        )
    );

    $data = $response->json();
    $keys = collect($data['keys'])->keyBy('key');

    expect($keys->has('SECRET_TOKEN'))->toBeTrue();
    expect($keys['SECRET_TOKEN']['source'])->toBe('default');
    expect($keys['SECRET_TOKEN']['value'])->toBe('••••'); // masked

    expect($keys->has('APP_NAME'))->toBeTrue();
    expect($keys['APP_NAME']['source'])->toBe('environment');
    expect($keys['APP_NAME']['value'])->toBe('Env App');
});

it('reveals secrets when show_secrets=1 is provided', function () {
    $project = Project::factory()->create();
    $environment = Environment::factory()->for($project)->create();

    $secretKey = VariableKey::factory()->state([
        'is_secret' => true,
        'default_value' => 'top-secret',
        'key' => 'SECRET_URL',
        'type' => 'string',
    ])->create();

    $response = $this->getJson('/api/environment/'.$environment->getKey().'?show_secrets=1');

    $response->assertSuccessful();

    $keys = collect($response->json('keys'))->keyBy('key');

    expect($keys['SECRET_URL']['value'])->toBe('top-secret'); // unmasked
});
