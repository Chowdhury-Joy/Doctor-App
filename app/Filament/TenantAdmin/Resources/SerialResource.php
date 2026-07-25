<?php

namespace App\Filament\TenantAdmin\Resources;

use App\Filament\TenantAdmin\Resources\SerialResource\Pages;
use App\Filament\TenantAdmin\Resources\SerialResource\RelationManagers;
use App\Models\Serial;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SerialResource extends Resource
{
    protected static ?string $model = Serial::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canCreate(): bool
    {
        return tenant('billing_status') !== 'read_only';
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\Select::make('doctor_id')
                    ->relationship('doctor', 'name')
                    ->required()
                    ->hiddenOn('create'),
                Forms\Components\Select::make('chamber_id')
                    ->relationship('chamber', 'name')
                    ->required()
                    ->hiddenOn('create'),
                Forms\Components\Select::make('schedule_session_id')
                    ->relationship('scheduleSession', 'session_name')
                    ->required(),
                Forms\Components\DatePicker::make('booking_date')
                    ->required(),
                Forms\Components\TextInput::make('patient_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('patient_phone')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('serial_number')
                    ->required()
                    ->numeric()
                    ->hiddenOn('create'),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->hiddenOn('create'),
                Forms\Components\TextInput::make('payment_status')
                    ->required()
                    ->maxLength(255)
                    ->default('unpaid')
                    ->hiddenOn('create'),
                Forms\Components\TextInput::make('payment_reference')
                    ->maxLength(255)
                    ->hiddenOn('create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tenant_id')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('doctor_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('chamber_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('schedule_session_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('booking_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('patient_phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('serial_number')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('payment_status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_reference')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('mark_in_chamber')
                    ->label('Call to Chamber')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('warning')
                    ->visible(fn (Serial $record) => $record->status === 'waiting')
                    ->action(fn (Serial $record) => $record->update(['status' => 'in_chamber'])),
                Tables\Actions\Action::make('mark_completed')
                    ->label('Mark Completed')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Serial $record) => $record->status === 'in_chamber')
                    ->action(fn (Serial $record) => $record->update(['status' => 'completed'])),
                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-oval-left-ellipsis')
                    ->color('success')
                    ->url(fn (Serial $record) => $record->whatsapp_link)
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListSerials::route('/'),
            'create' => Pages\CreateSerial::route('/create'),
            'edit' => Pages\EditSerial::route('/{record}/edit'),
        ];
    }
}
