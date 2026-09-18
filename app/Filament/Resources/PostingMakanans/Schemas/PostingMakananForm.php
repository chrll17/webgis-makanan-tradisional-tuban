<?php

namespace App\Filament\Resources\PostingMakanans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PostingMakananForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('nama_makanan')
                    ->required(),
                TextInput::make('nama_tempat')
                    ->required(),
                Textarea::make('deskripsi')
                    ->columnSpanFull(),
                Textarea::make('alamat')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('latitude')
                    ->required()
                    ->numeric(),
                TextInput::make('longitude')
                    ->required()
                    ->numeric(),
                Textarea::make('foto')
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                Textarea::make('catatan_validasi')
                    ->columnSpanFull(),
            ]);
    }
}
