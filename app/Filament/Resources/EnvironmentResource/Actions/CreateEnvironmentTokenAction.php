<?php

namespace App\Filament\Resources\EnvironmentResource\Actions;

use App\Models\Environment;
use Carbon\Carbon;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Model;

class CreateEnvironmentTokenAction extends CreateAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Token erstellen')
            ->modalHeading('Neue Deploytoken erstellen')
            ->schema([
                TextInput::make('name')
                    ->label(__('fields.name'))
                    ->required(),
                DatePicker::make('expires_at')
                    ->label(__('fields.expires_at'))
                    ->default(now()->addYear())
                    ->required(),
                Select::make('abilities')
                    ->label('Berechtigungen')
                    ->options([
                        'env.read' => '👁️ Nur lesen',
                        'env.write' => '✍️ In aktueller Env schreiben',
                        'env.write_global' => '🌐 Global schreiben',
                    ])
                    ->multiple()
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false),

            ])

            ->using(function (array $data, RelationManager $livewire): Model {
                /** @var Environment $environment */
                $environment = $livewire->getOwnerRecord();

                $newAccessToken = $environment->createToken(
                    name: $data['name'],
                    expiresAt: Carbon::parse($data['expires_at']),
                    abilities: $data['abilities']
                );

                Notification::make()
                    ->title('Token erstellt')
                    ->body('Bitte kopieren: '.$newAccessToken->plainTextToken)
                    ->persistent()
                    ->send();

                return $newAccessToken->accessToken;
            });
    }
}
