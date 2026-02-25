<?php

namespace App\Filament\Resources;

use App\Enums\RoleEnum;
use App\Enums\SubscriptionTypeEnum;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('email')->email()->required(),
                Forms\Components\Select::make('role')
                    ->options(collect(RoleEnum::cases())->mapWithKeys(fn($e) => [$e->value => ucfirst($e->value)]))
                    ->required(),
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
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('role')->badge()
                    ->color(fn($state) => match ($state?->value ?? $state) {
                        'student' => 'info',
                        'partner' => 'warning',
                        'school_admin' => 'success',
                        'system_admin' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('account_type')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('subscription_type')->badge()->color('primary')->toggleable(),
                Tables\Columns\TextColumn::make('subscription_expiry')->dateTime()->sortable()->toggleable(),
                Tables\Columns\IconColumn::make('is_suspended')->boolean()->label('Suspended'),
                Tables\Columns\TextColumn::make('sessions_count')
                    ->counts('sessions')
                    ->label('Sessions')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options(collect(RoleEnum::cases())->mapWithKeys(fn($e) => [$e->value => ucfirst($e->value)])),
                Tables\Filters\TernaryFilter::make('is_suspended')->label('Suspended'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_suspend')
                    ->label(fn(User $record) => $record->is_suspended ? 'Unsuspend' : 'Suspend')
                    ->icon(fn(User $record) => $record->is_suspended ? 'heroicon-o-check-circle' : 'heroicon-o-no-symbol')
                    ->color(fn(User $record) => $record->is_suspended ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->action(fn(User $record) => $record->update(['is_suspended' => !$record->is_suspended])),
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
            'index' => Pages\ListUsers::route('/'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
