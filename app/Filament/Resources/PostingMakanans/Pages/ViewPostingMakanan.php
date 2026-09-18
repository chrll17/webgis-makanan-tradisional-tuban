<?php

namespace App\Filament\Resources\PostingMakanans\Pages;

use App\Filament\Resources\PostingMakanans\PostingMakananResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPostingMakanan extends ViewRecord
{
    protected static string $resource = PostingMakananResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}
