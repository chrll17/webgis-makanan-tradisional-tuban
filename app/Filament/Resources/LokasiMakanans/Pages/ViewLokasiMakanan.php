<?php

namespace App\Filament\Resources\LokasiMakanans\Pages;

use App\Filament\Resources\LokasiMakanans\LokasiMakananResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLokasiMakanan extends ViewRecord
{
    protected static string $resource = LokasiMakananResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
