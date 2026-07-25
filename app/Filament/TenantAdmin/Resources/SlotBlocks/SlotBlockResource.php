<?php

namespace App\Filament\TenantAdmin\Resources\SlotBlocks;

use App\Filament\TenantAdmin\Resources\SlotBlocks\Pages\CreateSlotBlock;
use App\Filament\TenantAdmin\Resources\SlotBlocks\Pages\EditSlotBlock;
use App\Filament\TenantAdmin\Resources\SlotBlocks\Pages\ListSlotBlocks;
use App\Filament\TenantAdmin\Resources\SlotBlocks\Schemas\SlotBlockForm;
use App\Filament\TenantAdmin\Resources\SlotBlocks\Tables\SlotBlocksTable;
use App\Models\SlotBlock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SlotBlockResource extends Resource
{
    protected static ?string $model = SlotBlock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return SlotBlockForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SlotBlocksTable::configure($table);
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
            'index' => ListSlotBlocks::route('/'),
            'create' => CreateSlotBlock::route('/create'),
            'edit' => EditSlotBlock::route('/{record}/edit'),
        ];
    }
}
