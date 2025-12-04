<?php

namespace App\Filament\Resources;

use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Tables\Table;

abstract class BaseResource extends Resource
{
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordActions(self::defaultRecordActions())
            ->toolbarActions(self::defaultToolbarActions());
    }

    /**
     * Standard record actions for tables (ActionGroup with Edit).
     *
     * @return array<int, \Filament\Actions\Action|\Filament\Actions\ActionGroup>
     */
    public static function defaultRecordActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\EditAction::make(),
            ])->label(__('actions.group')),
        ];
    }

    /**
     * Standard toolbar actions for tables (Bulk Delete).
     *
     * @return array<int, \Filament\Actions\Action|\Filament\Actions\BulkActionGroup>
     */
    public static function defaultToolbarActions(): array
    {
        return [
            Actions\BulkActionGroup::make([
                Actions\DeleteBulkAction::make(),
            ]),
        ];
    }
}
