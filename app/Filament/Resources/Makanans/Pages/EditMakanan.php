<?php

namespace App\Filament\Resources\Makanans\Pages;

use App\Filament\Resources\Makanans\MakananResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMakanan extends EditRecord
{
    protected static string $resource = MakananResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
