<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FlightResource\Pages;
use App\Models\Flight;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FlightResource extends Resource
{
    protected static ?string $model = Flight::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Flight Management';

    public static function getNavigationGroup(): ?string
    {
        return __('labels.flight_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('labels.flights');
    }

    public static function getPluralModelLabel(): string
    {
        return __('labels.flights');
    }

    public static function getModelLabel(): string
    {
        return __('labels.flight_number');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Flight Details')
                    ->schema([
                        Forms\Components\TextInput::make('flight_number')
                            ->required()
                            ->maxLength(20),
                        Forms\Components\Select::make('departure_airport_id')
                            ->relationship('departureAirport', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('arrival_airport_id')
                            ->relationship('arrivalAirport', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\DatePicker::make('date')
                            ->required(),
                        Forms\Components\TimePicker::make('time')
                            ->required(),
                        Forms\Components\TextInput::make('duration')
                            ->numeric()
                            ->required()
                            ->suffix('minutes'),
                    ])->columns(2),

                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\Select::make('trip_type')
                            ->options([
                                'domestic' => 'Domestic',
                                'international' => 'International',
                            ])
                            ->required(),
                        Forms\Components\Select::make('airline_id')
                            ->relationship('airline', 'name')
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('plane_type_id')
                            ->relationship('planeType', 'name')
                            ->required()
                            ->searchable(),
                        Forms\Components\TextInput::make('available_crew_positions')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Select::make('status')
                            ->options([
                                'scheduled' => 'Scheduled',
                                'delayed' => 'Delayed',
                                'cancelled' => 'Cancelled',
                                'completed' => 'Completed',
                            ])
                            ->default('scheduled')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('flight_number')
                    ->label(__('labels.flight_number'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('departureAirport.name')
                    ->label(__('labels.departure'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('arrivalAirport.name')
                    ->label(__('labels.arrival'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->label(__('labels.date'))
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('time')
                    ->label(__('labels.time'))
                    ->time()
                    ->sortable(),
                Tables\Columns\TextColumn::make('airline.name')
                    ->label(__('labels.airline'))
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('trip_type')
                    ->label(__('labels.trip_type'))
                    ->colors([
                        'primary' => 'domestic',
                        'success' => 'international',
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('labels.status'))
                    ->colors([
                        'warning' => 'scheduled',
                        'info' => 'delayed',
                        'danger' => 'cancelled',
                        'success' => 'completed',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'delayed' => 'Delayed',
                        'cancelled' => 'Cancelled',
                        'completed' => 'Completed',
                    ]),
                Tables\Filters\SelectFilter::make('trip_type')
                    ->options([
                        'domestic' => 'Domestic',
                        'international' => 'International',
                    ]),
                Tables\Filters\SelectFilter::make('airline')
                    ->relationship('airline', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListFlights::route('/'),
            'create' => Pages\CreateFlight::route('/create'),
            'edit' => Pages\EditFlight::route('/{record}/edit'),
        ];
    }
}
