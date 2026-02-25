<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommissionResource\Pages;
use App\Models\Commission;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class CommissionResource extends Resource
{
    protected static ?string $model = Commission::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Activity';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('partner.name')->label('Partner')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Student')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('amount')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('month_number')->label('Month')->sortable(),
                Tables\Columns\IconColumn::make('is_paid')->boolean()->label('Paid'),
                Tables\Columns\TextColumn::make('created_at')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_paid')->label('Paid'),
                Tables\Filters\SelectFilter::make('partner')
                    ->relationship('partner', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_paid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn(Commission $record) => !$record->is_paid)
                    ->requiresConfirmation()
                    ->action(fn(Commission $record) => $record->update(['is_paid' => true])),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('mark_all_paid')
                    ->label('Mark Selected as Paid')
                    ->icon('heroicon-o-check-badge')
                    ->requiresConfirmation()
                    ->action(fn($records) => $records->each->update(['is_paid' => true])),
            ]);
    }

    public static function canCreate(): bool { return false; }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommissions::route('/'),
        ];
    }
}
