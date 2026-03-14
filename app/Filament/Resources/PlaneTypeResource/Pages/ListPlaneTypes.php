<?php

namespace App\Filament\Resources\PlaneTypeResource\Pages;

use App\Filament\Resources\PlaneTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlaneTypes extends ListRecords
{
    protected static string $resource = PlaneTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
