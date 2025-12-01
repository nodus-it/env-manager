<?php

namespace App\Filament\Resources\EnvironmentResource\RelationManagers;

use App\Filament\Resources\EnvironmentResource\Actions\CreateEnvironmentTokenAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PersonalAccessTokenRelationManager extends RelationManager
{
    protected static string $relationship = 'tokens';

    public static function canViewForRecord($ownerRecord, $pageClass): bool
    {
        return ! str_contains($pageClass, 'Edit');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('expires_at'),
            ])
            ->headerActions([
                CreateEnvironmentTokenAction::make(),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
