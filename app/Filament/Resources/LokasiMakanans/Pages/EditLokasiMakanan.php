<?php

namespace App\Filament\Resources\LokasiMakanans\Pages;

use App\Filament\Resources\LokasiMakanans\LokasiMakananResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLokasiMakanan extends EditRecord
{
    protected static string $resource = LokasiMakananResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
