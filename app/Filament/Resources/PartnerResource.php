<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerResource\Pages;
use App\Models\Partner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('referral_code')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('commission_rate')->numeric()->suffix('%')->nullable(),
                Forms\Components\TextInput::make('commission_duration_months')->numeric()->nullable(),
                Forms\Components\Toggle::make('is_active')->label('Active'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('referral_code')->searchable(),
                Tables\Columns\TextColumn::make('commission_rate')->suffix('%')->sortable(),
                Tables\Columns\TextColumn::make('commission_duration_months')->label('Duration (months)')->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Referred Students')
                    ->sortable(),
                Tables\Columns\TextColumn::make('commissions_sum_amount')
                    ->sum('commissions', 'amount')
                    ->label('Total Commissions')
                    ->money('NGN')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn(Partner $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn(Partner $record) => $record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                    ->color(fn(Partner $record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(fn(Partner $record) => $record->update(['is_active' => !$record->is_active])),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPartners::route('/'),
            'edit' => Pages\EditPartner::route('/{record}/edit'),
        ];
    }
}
