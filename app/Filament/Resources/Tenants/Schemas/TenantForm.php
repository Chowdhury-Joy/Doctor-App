<?php

namespace App\Filament\Resources\Tenants\Schemas;

use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Wizard::make([
                    \Filament\Schemas\Components\Wizard\Step::make('Identity')
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('id')
                                ->label('Tenant ID (Subdomain)')
                                ->required()
                                ->maxLength(255),
                            \Filament\Forms\Components\TextInput::make('data.name')
                                ->label('Clinic Name')
                                ->required(),
                            \Filament\Forms\Components\Select::make('billing_status')
                                ->options([
                                    'active' => 'Active',
                                    'read_only' => 'Read-Only (No Bookings)',
                                ])
                                ->default('active')
                                ->required(),
                        ]),
                    \Filament\Schemas\Components\Wizard\Step::make('Configuration')
                        ->schema([
                            \Filament\Forms\Components\Select::make('plan_tier')
                                ->options([
                                    'basic' => 'Basic',
                                    'premium' => 'Premium',
                                ])
                                ->default('basic'),
                            \Filament\Forms\Components\Select::make('layout_id')
                                ->options([
                                    'HeroFirst' => 'Hero First',
                                    'Sidebar' => 'Sidebar',
                                    'CardStack' => 'Card Stack',
                                    'Minimal' => 'Minimal',
                                    'ClinicStyle' => 'Clinic Style',
                                ])
                                ->default('HeroFirst'),
                            \Filament\Forms\Components\Select::make('data.slot_cap_type')
                                ->label('Slot Cap Type')
                                ->options([
                                    'session' => 'Per Session',
                                    'day' => 'Per Day',
                                ])
                                ->default('session'),
                            \Filament\Forms\Components\TextInput::make('data.daily_slot_cap')
                                ->label('Daily Slot Cap')
                                ->numeric()
                                ->default(20),
                        ]),
                ])
            ]);
    }
}
