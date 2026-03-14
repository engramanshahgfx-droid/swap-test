<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlaneTypeResource\Pages;
use App\Models\PlaneType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlaneTypeResource extends Resource
{
    protected static ?string $model = PlaneType::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Configuration';

    public static function getNavigationGroup(): ?string
    {
        return __('labels.configuration');
    }

    public static function getNavigationLabel(): string
    {
        return __('labels.plane_types');
    }

    public static function getPluralModelLabel(): string
    {
        return __('labels.plane_types');
    }

    public static function getModelLabel(): string
    {
        return __('labels.plane_type');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),
                Forms\Components\Select::make('airline_id')
                    ->relationship('airline', 'name')
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('capacity')
                    ->numeric()
                    ->nullable(),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('labels.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('labels.code'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('airline.name')
                    ->label(__('labels.airline'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->label(__('labels.capacity'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('labels.is_active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('labels.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\SelectFilter::make('airline')
                    ->relationship('airline', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlaneTypes::route('/'),
            'create' => Pages\CreatePlaneType::route('/create'),
            'edit' => Pages\EditPlaneType::route('/{record}/edit'),
        ];
    }
}
