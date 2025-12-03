<?php

namespace App\Services;

use App\Data\EnvironmentKeyData;
use App\Enums\VariableKeySource;
use App\Models\Environment;
use App\Models\EnvironmentVariableValue;
use App\Models\ProjectVariableValue;
use App\Models\VariableKey;
use Illuminate\Support\Collection;

class EnvironmentService
{
    /**
     * Get effective keys for an environment.
     *
     * @return Collection<int, EnvironmentKeyData>
     */
    public function getKeys(Environment $environment, bool $showSecrets = false): Collection
    {
        $variableKeys = VariableKey::query()
            ->select(['id', 'key', 'type', 'is_secret', 'default_value'])
            ->orderBy('key')
            ->get();

        $projectDefaults = ProjectVariableValue::query()
            ->where('project_id', $environment->project_id)
            ->get()
            ->keyBy('variable_key_id');

        $environmentKeys = EnvironmentVariableValue::query()
            ->where('environment_id', $environment->id)
            ->get()
            ->keyBy('variable_key_id');

        $rows = [];

        foreach ($variableKeys as $variableKey) {
            $source = null;
            $value = null;
            $sourceId = null;

            if (isset($environmentKeys[$variableKey->id])) {
                $source = VariableKeySource::Environment;
                $value = $environmentKeys[$variableKey->id]->value;
                $sourceId = $environment->id;
            } elseif (isset($projectDefaults[$variableKey->id])) {
                $source = VariableKeySource::Project;
                $value = $projectDefaults[$variableKey->id]->value;
                $sourceId = $projectDefaults[$variableKey->id]->id;
            } elseif ($variableKey->default_value !== null && $variableKey->default_value !== '') {
                $source = VariableKeySource::VariableKey;
                $value = $variableKey->default_value;
                $sourceId = $variableKey->id;
            }

            if ($source === null) {
                continue;
            }

            $value = ($variableKey->is_secret && ! $showSecrets) ? '••••' : (string) $value;

            $rows[] = EnvironmentKeyData::from([
                'variable_key_id' => $variableKey->id,
                'key' => $variableKey->key,
                'type' => $variableKey->type,
                'value' => $value,
                'source' => $source,
                'source_id' => $sourceId,
            ]);
        }

        return collect($rows);
    }
}
