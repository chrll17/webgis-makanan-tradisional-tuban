<?php

namespace App\Filament\Resources\Makanans\Pages;

use App\Filament\Resources\Makanans\MakananResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMakanans extends ListRecords
{
    protected static string $resource = MakananResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
