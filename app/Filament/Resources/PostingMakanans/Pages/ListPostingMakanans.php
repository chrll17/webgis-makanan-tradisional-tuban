<?php

namespace App\Filament\Resources\PostingMakanans\Pages;

use App\Filament\Resources\PostingMakanans\PostingMakananResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPostingMakanans extends ListRecords
{
    protected static string $resource = PostingMakananResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
