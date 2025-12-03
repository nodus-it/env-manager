<?php

namespace App\Enums;

enum VariableKeySource: string
{
    case VariableKey = 'variable_key';
    case Project = 'project';
    case Environment = 'environment';
}
