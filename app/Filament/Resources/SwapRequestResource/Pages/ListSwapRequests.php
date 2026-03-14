<?php

namespace App\Filament\Resources\SwapRequestResource\Pages;

use App\Filament\Resources\SwapRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSwapRequests extends ListRecords
{
    protected static string $resource = SwapRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
