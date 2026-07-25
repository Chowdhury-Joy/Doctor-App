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
                                    'solo' => 'Solo',
                                    'clinic' => 'Clinic',
                                ])
                                ->default('solo')
                                ->rule(static function (?\App\Models\Tenant $record) {
                                    return function (string $attribute, $value, \Closure $fail) use ($record) {
                                        if ($value === 'solo' && $record) {
                                            $doctorCount = \App\Models\Doctor::where('tenant_id', $record->id)->count();
                                            if ($doctorCount > 1) {
                                                $fail('Cannot downgrade to Solo tier: tenant has multiple doctors.');
                                            }
                                            $chamberCount = \App\Models\Chamber::where('tenant_id', $record->id)->count();
                                            if ($chamberCount > 1) {
                                                $fail('Cannot downgrade to Solo tier: tenant has multiple chambers.');
                                            }
                                        }
                                    };
                                }),
                            \Filament\Forms\Components\Select::make('layout_id')
                                ->options([
                                    'HeroFirst' => 'Hero First',
                                    'Sidebar' => 'Sidebar',
                                    'CardStack' => 'Card Stack',
                                    'Minimal' => 'Minimal',
                                    'ClinicStyle' => 'Clinic Style',
                                ])
                                ->default('HeroFirst'),
                            \Filament\Forms\Components\TextInput::make('theme_name')
                                ->label('Bespoke Theme Name')
                                ->placeholder('e.g., dr-bespoke-v1')
                                ->helperText('Leave blank to use standard layouts.')
                                ->maxLength(50),
                            \Filament\Forms\Components\TextInput::make('payment_gateway_secret')
                                ->label('Payment Gateway Secret')
                                ->password()
                                ->helperText('Used for HMAC-SHA256 webhook signature verification.')
                                ->maxLength(255),
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
