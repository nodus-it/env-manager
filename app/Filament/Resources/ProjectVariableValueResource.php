<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectVariableValueResource\Pages;
use App\Models\ProjectVariableValue;
use App\Models\VariableKey;
use Filament\Forms;
use Filament\Infolists;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectVariableValueResource extends BaseResource
{
    protected static ?string $model = ProjectVariableValue::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-square-2-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('project_id')
                ->label(__('models.project.label'))
                ->relationship('project', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('variable_key_id')
                ->label(__('fields.variable_key'))
                ->relationship('variableKey', 'key')
                ->searchable()
                ->preload()
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, $set) {
                    $set('value', null);
                })
                ->rules([
                    function ($get, $record) {
                        return \Illuminate\Validation\Rule::unique('project_variable_values', 'variable_key_id')
                            ->where('project_id', (int) $get('project_id'))
                            ->ignore($record?->getKey());
                    },
                ]),
            Forms\Components\TextInput::make('value')
                ->label(__('fields.value'))
                ->password(function ($get) {
                    $vkId = $get('variable_key_id');
                    if (! $vkId) {
                        return false;
                    }

                    return (bool) optional(VariableKey::query()->find($vkId))->is_secret;
                })
                ->revealable(function ($get) {
                    $vkId = $get('variable_key_id');
                    if (! $vkId) {
                        return false;
                    }

                    return (bool) optional(VariableKey::query()->find($vkId))->is_secret;
                })
                ->required(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Infolists\Components\TextEntry::make('project.name')->label(__('models.project.label')),
            Infolists\Components\TextEntry::make('variableKey.key')->label(__('fields.variable_key')),
            Infolists\Components\TextEntry::make('created_at')->label(__('timestamps.created_at'))->dateTime(),
            Infolists\Components\TextEntry::make('updated_at')->label(__('timestamps.updated_at'))->dateTime(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.name')
                    ->label(__('models.project.label'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('variableKey.key')
                    ->label(__('fields.variable_key'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('value')
                    ->label(__('fields.value'))
                    ->formatStateUsing(function ($record) {
                        return $record->variableKey?->is_secret ? '••••' : $record->value;
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions(self::defaultRecordActions())
            ->toolbarActions(self::defaultToolbarActions());
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ViewProjectVariableValue::route('/'),
            'view' => Pages\ViewProjectVariableValue::route('/{record}'),
        ];
    }
}
