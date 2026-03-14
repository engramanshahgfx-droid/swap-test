<?php

namespace App\Filament\Resources\PlaneTypeResource\Pages;

use App\Filament\Resources\PlaneTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlaneType extends EditRecord
{
    protected static string $resource = PlaneTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
