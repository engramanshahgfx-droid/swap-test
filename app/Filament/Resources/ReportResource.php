<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\Report;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Reports & Moderation';

    public static function getNavigationGroup(): ?string
    {
        return __('labels.reports_moderation');
    }

    public static function getNavigationLabel(): string
    {
        return __('labels.reports');
    }

    public static function getPluralModelLabel(): string
    {
        return __('labels.reports');
    }

    public static function getModelLabel(): string
    {
        return __('labels.reports');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Report Details')
                    ->schema([
                        Forms\Components\TextInput::make('reporter.full_name')
                            ->label('Reporter')
                            ->disabled(),
                        Forms\Components\TextInput::make('reportedUser.full_name')
                            ->label('Reported User')
                            ->disabled(),
                        Forms\Components\Select::make('reason')
                            ->options([
                                'spam' => 'Spam',
                                'bad_language' => 'Bad Language',
                                'disrespect' => 'Disrespect',
                                'other' => 'Other',
                            ])
                            ->disabled(),
                        Forms\Components\Textarea::make('details')
                            ->disabled()
                            ->rows(3),
                    ])->columns(2),

                Forms\Components\Section::make('Admin Review')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'reviewed' => 'Reviewed',
                                'resolved' => 'Resolved',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Admin Notes')
                            ->rows(3),
                        Forms\Components\Textarea::make('resolution')
                            ->label('Resolution')
                            ->rows(3),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reporter.full_name')
                    ->label(__('labels.reporter'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reportedUser.full_name')
                    ->label(__('labels.reported_user'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('reason')
                    ->label(__('labels.reason'))
                    ->colors([
                        'warning' => 'spam',
                        'danger' => 'bad_language',
                        'danger' => 'disrespect',
                        'gray' => 'other',
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('labels.status'))
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'reviewed',
                        'success' => 'resolved',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('labels.created_at'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reviewedBy.full_name')
                    ->label('Reviewed By')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('reviewed_at')
                    ->label('Reviewed At')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'reviewed' => 'Reviewed',
                        'resolved' => 'Resolved',
                    ]),
                Tables\Filters\SelectFilter::make('reason')
                    ->options([
                        'spam' => 'Spam',
                        'bad_language' => 'Bad Language',
                        'disrespect' => 'Disrespect',
                        'other' => 'Other',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('resolve')
                    ->action(function (Report $record) {
                        $record->update([
                            'status' => 'resolved',
                            'resolution' => $record->resolution,
                            'admin_notes' => $record->admin_notes,
                            'reviewed_by_id' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);
                        Notification::make()
                            ->success()
                            ->title('Report resolved')
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->color('success')
                    ->visible(fn (Report $record): bool => $record->status !== 'resolved'),
                Tables\Actions\Action::make('block_user')
                    ->action(function (Report $record) {
                        $record->reportedUser->update(['status' => 'blocked']);
                        $record->update([
                            'status' => 'resolved',
                            'resolution' => $record->resolution ?: 'User blocked by moderator.',
                            'admin_notes' => $record->admin_notes,
                            'reviewed_by_id' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);
                        Notification::make()
                            ->success()
                            ->title('User blocked and report resolved')
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->color('danger')
                    ->visible(fn (Report $record): bool => 
                        $record->status !== 'resolved' && 
                        $record->reportedUser->status !== 'blocked'
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'view' => Pages\ViewReport::route('/{record}'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }
}
