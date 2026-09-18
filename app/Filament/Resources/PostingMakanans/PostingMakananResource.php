<?php

namespace App\Filament\Resources\PostingMakanans;

use App\Filament\Resources\PostingMakanans\Pages\CreatePostingMakanan;
use App\Filament\Resources\PostingMakanans\Pages\EditPostingMakanan;
use App\Filament\Resources\PostingMakanans\Pages\ListPostingMakanans;
use App\Filament\Resources\PostingMakanans\Pages\ViewPostingMakanan;
use App\Filament\Resources\PostingMakanans\Schemas\PostingMakananForm;
use App\Filament\Resources\PostingMakanans\Tables\PostingMakanansTable;
use App\Models\PostingMakanan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;

class PostingMakananResource extends Resource
{
    protected static ?string $model = PostingMakanan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // untuk mengganti nama
    protected static ?string $modelLabel = 'Kontribusi Pengguna';
    protected static ?string $pluralModelLabel = 'Kontribusi Pengguna';
    // end untuk mengganti nama

    protected static string | \UnitEnum | null $navigationGroup = 'Manajemen Sistem';

    public static function form(Schema $schema): Schema
    {
        return PostingMakananForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostingMakanansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                    TextEntry::make('user.name')
                        ->label('Nama Pengirim')
                        ->weight('bold')
                        ->columnSpanFull(),
                    TextEntry::make('nama_makanan')
                        ->label('Nama Makanan'),
                    TextEntry::make('nama_tempat')
                        ->label('Nama Tempat'),
                    TextEntry::make('deskripsi')
                        ->label('Deskripsi')
                        ->columnSpanFull(),
                    TextEntry::make('alamat')
                        ->label('Alamat')
                        ->columnSpanFull(),
                    TextEntry::make('latitude')
                        ->label('Latitude'),
                    TextEntry::make('longitude')
                        ->label('Longitude'),
                    ImageEntry::make('foto')
                        ->label('')
                        ->disk('public')
                        ->size(150)
                        ->stacked() // Menumpuk foto dengan rapi jika banyak
                        ->limit(5),
                    TextEntry::make('status')
                        ->label('Status'),                
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPostingMakanans::route('/'),
            'view' => ViewPostingMakanan::route('/{record}')
        ];
    }
}
