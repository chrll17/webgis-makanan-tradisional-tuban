<?php

namespace App\Filament\Resources\Makanans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MakananForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_makanan')
                    ->required(),
            ]);
    }
}
