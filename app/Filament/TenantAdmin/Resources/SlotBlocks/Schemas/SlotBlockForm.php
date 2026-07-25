<?php

namespace App\Filament\TenantAdmin\Resources\SlotBlocks\Schemas;

use Filament\Schemas\Schema;

class SlotBlockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('doctor_id')
                    ->relationship('doctor', 'name')
                    ->required(),
                \Filament\Forms\Components\DatePicker::make('block_date')
                    ->required(),
                \Filament\Forms\Components\TextInput::make('reason')
                    ->maxLength(255),
            ]);
    }
}
