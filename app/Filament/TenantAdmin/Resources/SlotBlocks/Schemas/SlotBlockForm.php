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
                    ->label('Doctor')
                    ->helperText('Leave empty to block the whole chamber (e.g. a clinic holiday).')
                    ->nullable(),
                \Filament\Forms\Components\Select::make('chamber_id')
                    ->relationship('chamber', 'name')
                    ->label('Chamber')
                    ->helperText('Required when no doctor is selected.')
                    ->nullable()
                    ->requiredWithout('doctor_id'),
                \Filament\Forms\Components\DatePicker::make('block_date')
                    ->required(),
                \Filament\Forms\Components\TextInput::make('reason')
                    ->maxLength(255),
            ]);
    }
}
