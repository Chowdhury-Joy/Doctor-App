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
                            \Filament\Forms\Components\TextInput::make('name')
                                ->label('Clinic Name')
                                ->required(),
                            \Filament\Forms\Components\TextInput::make('contact_phone')
                                ->label('Contact / WhatsApp Number')
                                ->tel()
                                ->helperText('Bangladeshi format, e.g. 8801823894527.')
                                ->default('8801823894527'),
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
                            \Filament\Forms\Components\Select::make('slot_cap_type')
                                ->label('Slot Cap Type')
                                ->options([
                                    'session' => 'Per Session',
                                    'day' => 'Per Day',
                                ])
                                ->default('session')
                                ->live(),
                            \Filament\Forms\Components\TextInput::make('daily_slot_cap')
                                ->label('Daily Slot Cap')
                                ->numeric()
                                ->minValue(1)
                                ->default(20)
                                ->visible(fn ($get) => $get('slot_cap_type') === 'day'),
                            \Filament\Forms\Components\TextInput::make('theme_color')
                                ->label('Theme Colour')
                                ->helperText('Used for the per-tenant PWA manifest.')
                                ->default('#0ea5e9'),
                            \Filament\Forms\Components\Textarea::make('custom_code')
                                ->label('Custom Code Snippets (e.g., Analytics)')
                                ->helperText('Injected into the page head only after approval below.')
                                ->columnSpanFull(),
                            \Filament\Forms\Components\DateTimePicker::make('custom_code_approved_at')
                                ->label('Custom Code Approved At')
                                ->helperText('Leave empty to keep the snippet in draft — it will not render.')
                                ->nullable(),
                        ]),
                ])
            ]);
    }
}
