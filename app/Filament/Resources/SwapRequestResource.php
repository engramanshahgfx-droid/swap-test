<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SwapRequestResource\Pages;
use App\Models\SwapRequest;
use App\Services\SwapService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class SwapRequestResource extends Resource
{
    protected static ?string $model = SwapRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Swap Management';

    public static function getNavigationGroup(): ?string
    {
        return __('labels.swap_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('labels.swap_requests');
    }

    public static function getPluralModelLabel(): string
    {
        return __('labels.swap_requests');
    }

    public static function getModelLabel(): string
    {
        return __('labels.swap_requests');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Swap Details')
                    ->schema([
                        Forms\Components\TextInput::make('fromUser.full_name')
                            ->label('From User')
                            ->disabled(),
                        Forms\Components\TextInput::make('toUser.full_name')
                            ->label('To User')
                            ->disabled(),
                        Forms\Components\TextInput::make('publishedTrip.flight.flight_number')
                            ->label('Flight Number')
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved_by_owner' => 'Approved by Owner',
                                'rejected_by_owner' => 'Rejected by Owner',
                                'manager_approved' => 'Manager Approved',
                                'manager_rejected' => 'Manager Rejected',
                                'completed' => 'Completed',
                            ])
                            ->disabled(),
                        Forms\Components\Select::make('manager_approval_status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ]),
                        Forms\Components\Textarea::make('manager_notes')
                            ->label('Manager Notes')
                            ->rows(3),
                        Forms\Components\DateTimePicker::make('manager_approved_at')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fromUser.full_name')
                    ->label(__('labels.from'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('toUser.full_name')
                    ->label(__('labels.to'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('publishedTrip.flight.flight_number')
                    ->label(__('labels.flight_number')),
                Tables\Columns\TextColumn::make('publishedTrip.flight.departure_airport')
                    ->label(__('labels.departure_airport')),
                Tables\Columns\TextColumn::make('publishedTrip.flight.arrival_airport')
                    ->label(__('labels.arrival_airport')),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('labels.status'))
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'approved_by_owner',
                        'danger' => 'rejected_by_owner',
                        'danger' => 'manager_rejected',
                        'success' => 'completed',
                    ]),
                Tables\Columns\BadgeColumn::make('manager_approval_status')
                    ->label(__('labels.manager_approval_status'))
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('labels.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved_by_owner' => 'Approved by Owner',
                        'rejected_by_owner' => 'Rejected by Owner',
                        'completed' => 'Completed',
                    ]),
                Tables\Filters\SelectFilter::make('manager_approval_status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('approve')
                    ->action(function (SwapRequest $record, SwapService $swapService) {
                        try {
                            $swapService->approveByManager($record, auth()->user(), $record->manager_notes);
                            Notification::make()
                                ->success()
                                ->title('Swap approved successfully')
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Error approving swap')
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->color('success')
                    ->visible(fn (SwapRequest $record): bool => 
                        $record->status === 'approved_by_owner' && 
                        $record->manager_approval_status === 'pending' && 
                        auth()->user()->hasAnyRole(['crew_manager', 'operations_manager'])
                    ),
                Tables\Actions\Action::make('reject')
                    ->action(function (SwapRequest $record, SwapService $swapService) {
                        try {
                            $swapService->rejectByManager($record, auth()->user(), $record->manager_notes);
                            Notification::make()
                                ->success()
                                ->title('Swap rejected')
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Error rejecting swap')
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->color('danger')
                    ->visible(fn (SwapRequest $record): bool => 
                        $record->manager_approval_status === 'pending' && 
                        auth()->user()->hasAnyRole(['crew_manager', 'operations_manager'])
                    ),
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
            'index' => Pages\ListSwapRequests::route('/'),
            'view' => Pages\ViewSwapRequest::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
