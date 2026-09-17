<?php

namespace App\Filament\Resources\LokasiMakanans\Pages;

use App\Filament\Resources\LokasiMakanans\LokasiMakananResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLokasiMakanans extends ListRecords
{
    protected static string $resource = LokasiMakananResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
