<?php

namespace App\Filament\Resources;

use App\Enums\SubscriptionTypeEnum;
use App\Filament\Resources\SchoolResource\Pages;
use App\Models\School;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SchoolResource extends Resource
{
    protected static ?string $model = School::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('unique_code')->required()->unique(ignoreRecord: true),
                Forms\Components\Select::make('subscription_type')
                    ->options(collect(SubscriptionTypeEnum::cases())->mapWithKeys(fn($e) => [$e->value => $e->value]))
                    ->nullable(),
                Forms\Components\DateTimePicker::make('subscription_expiry')->nullable(),
                Forms\Components\Toggle::make('is_suspended')->label('Suspended'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('unique_code')->searchable(),
                Tables\Columns\TextColumn::make('subscription_type')->badge()->color('primary'),
                Tables\Columns\TextColumn::make('subscription_expiry')->dateTime()->sortable()->toggleable(),
                Tables\Columns\IconColumn::make('is_suspended')->boolean()->label('Suspended'),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Students')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_suspended')->label('Suspended'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_suspend')
                    ->label(fn(School $record) => $record->is_suspended ? 'Unsuspend' : 'Suspend')
                    ->icon(fn(School $record) => $record->is_suspended ? 'heroicon-o-check-circle' : 'heroicon-o-no-symbol')
                    ->color(fn(School $record) => $record->is_suspended ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->action(fn(School $record) => $record->update(['is_suspended' => !$record->is_suspended])),
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
            'index' => Pages\ListSchools::route('/'),
            'edit' => Pages\EditSchool::route('/{record}/edit'),
        ];
    }
}
