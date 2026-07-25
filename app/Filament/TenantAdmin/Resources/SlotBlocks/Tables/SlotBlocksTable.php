<?php

namespace App\Filament\TenantAdmin\Resources\SlotBlocks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class SlotBlocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('doctor.name')->searchable(),
                \Filament\Tables\Columns\TextColumn::make('block_date')->date()->sortable(),
                \Filament\Tables\Columns\TextColumn::make('reason'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
