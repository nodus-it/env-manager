<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnvironmentResource\Pages;
use App\Models\Environment;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;

class EnvironmentResource extends Resource
{
    protected static ?string $model = Environment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cloud';

    protected static string|\UnitEnum|null $navigationGroup = null;

    public static function getNavigationGroup(): ?string
    {
        return __('models.navigation.organisation');
    }

    public static function getModelLabel(): string
    {
        return __('models.environment.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models.environment.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('project_id')
                ->label(__('models.project.label'))
                ->relationship('project', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('name')
                ->label(__('fields.name'))
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('slug')
                ->label(__('fields.slug'))
                ->required()
                ->maxLength(255)
                ->rules([
                    function ($get, $record) {
                        return Rule::unique('environments', 'slug')
                            ->where('project_id', (int) $get('project_id'))
                            ->ignore($record?->getKey());
                    },
                ]),
            Forms\Components\TextInput::make('order')
                ->label(__('fields.order'))
                ->numeric()
                ->default(0)
                ->minValue(0)
                ->required(),
            Forms\Components\Select::make('type')
                ->label(__('fields.type'))
                ->options(function (): array {
                    return array_combine(Environment::TYPES, array_map(
                        fn (string $t): string => __('environment.types.'.$t),
                        Environment::TYPES
                    ));
                })
                ->required()
                ->default('custom')
                ->native(false),
            Forms\Components\Toggle::make('is_default')
                ->label(__('fields.is_default'))
                ->helperText(__('fields.environment_is_default_help'))
                ->default(false),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Infolists\Components\TextEntry::make('project.name')->label(__('models.project.label')),
            Infolists\Components\TextEntry::make('name')->label(__('fields.name')),
            Infolists\Components\TextEntry::make('slug')->label(__('fields.slug')),
            Infolists\Components\TextEntry::make('type')->label(__('fields.type')),
            Infolists\Components\IconEntry::make('is_default')->label(__('fields.is_default'))->boolean(),
            Infolists\Components\TextEntry::make('order')->label(__('fields.order')),
            Infolists\Components\TextEntry::make('created_at')->label(__('timestamps.created_at'))->dateTime('d.m.Y H:i'),
            Infolists\Components\TextEntry::make('updated_at')->label(__('timestamps.updated_at'))->dateTime('d.m.Y H:i'),
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
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('environment.types.'.$state))
                    ->color(fn (string $state): string => match ($state) {
                        'production' => 'success',
                        'staging' => 'warning',
                        'testing' => 'info',
                        'local' => 'gray',
                        default => 'secondary',
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->label(__('fields.is_default'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('fields.type'))
                    ->options(array_combine(Environment::TYPES, Environment::TYPES)),
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label(__('fields.is_default')),
            ])
            ->recordActions([
                Actions\ActionGroup::make([
                    Actions\EditAction::make(),
                ])->label(__('actions.group')),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnvironments::route('/'),
            'create' => Pages\CreateEnvironment::route('/create'),
            'view' => Pages\ViewEnvironment::route('/{record}'),
            'edit' => Pages\EditEnvironment::route('/{record}/edit'),
        ];
    }
}
