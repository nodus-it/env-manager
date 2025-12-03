<?php

namespace App\Http\Controllers;

use App\Data\EnvironmentData;
use App\Models\Environment;
use Illuminate\Http\Request;

class GetEnvironmentKeyController extends Controller
{
    public function __invoke(Request $request)
    {
        /**
         * @var Environment $environment
         */
        $environment = auth()->user();
        $showSecrets = (bool) $request->boolean('show_secrets', false);

        return EnvironmentData::fromEnvironment($environment, $showSecrets);
    }
}
