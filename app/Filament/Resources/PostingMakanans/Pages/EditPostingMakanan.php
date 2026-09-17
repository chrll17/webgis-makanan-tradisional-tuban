<?php

namespace App\Filament\Resources\PostingMakanans\Pages;

use App\Filament\Resources\PostingMakanans\PostingMakananResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPostingMakanan extends EditRecord
{
    protected static string $resource = PostingMakananResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
