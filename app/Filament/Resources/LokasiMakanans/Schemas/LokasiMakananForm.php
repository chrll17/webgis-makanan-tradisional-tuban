<?php

namespace App\Filament\Resources\LokasiMakanans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class LokasiMakananForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('lokasi_id')
                    ->required()
                    ->numeric(),
                TextInput::make('makanan_id')
                    ->required()
                    ->numeric(),
                Textarea::make('deskripsi')
                    ->columnSpanFull(),
                Textarea::make('foto')
                    ->columnSpanFull(),
            ]);
    }
}
