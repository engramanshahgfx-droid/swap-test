<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    public static function getNavigationGroup(): ?string
    {
        return __('labels.user_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('labels.users');
    }

    public static function getPluralModelLabel(): string
    {
        return __('labels.users');
    }

    public static function getModelLabel(): string
    {
        return __('labels.users');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('employee_id')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('full_name')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('phone')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('country_base')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'blocked' => 'Blocked',
                            ])
                            ->default('inactive')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Work Information')
                    ->schema([
                        Forms\Components\Select::make('airline_id')
                            ->relationship('airline', 'name')
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('plane_type_id')
                            ->relationship('planeType', 'name')
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('position_id')
                            ->relationship('position', 'name')
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('roles')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Security')
                    ->schema([
                        Forms\Components\DateTimePicker::make('phone_verified_at'),
                        Forms\Components\DateTimePicker::make('email_verified_at'),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_id')
                    ->label(__('labels.employee_id'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label(__('labels.full_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('labels.email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('labels.phone'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('airline.name')
                    ->label(__('labels.airline'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('planeType.name')
                    ->label(__('labels.plane_type'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('position.name')
                    ->label(__('labels.position'))
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('labels.status'))
                    ->colors([
                        'success' => 'active',
                        'warning' => 'inactive',
                        'danger' => 'blocked',
                    ]),
                Tables\Columns\IconColumn::make('phone_verified_at')
                    ->boolean()
                    ->label(__('labels.verified')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('labels.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'blocked' => 'Blocked',
                    ]),
                Tables\Filters\SelectFilter::make('airline')
                    ->relationship('airline', 'name'),
                Tables\Filters\SelectFilter::make('position')
                    ->relationship('position', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('block')
                    ->action(fn (User $record) => $record->update(['status' => 'blocked']))
                    ->requiresConfirmation()
                    ->color('danger')
                    ->visible(fn (User $record): bool => $record->status !== 'blocked'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
