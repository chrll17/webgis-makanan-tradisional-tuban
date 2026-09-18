<?php

namespace App\Filament\Resources\LokasiMakanans;

use App\Filament\Resources\LokasiMakanans\Pages\CreateLokasiMakanan;
use App\Filament\Resources\LokasiMakanans\Pages\EditLokasiMakanan;
use App\Filament\Resources\LokasiMakanans\Pages\ListLokasiMakanans;
use App\Filament\Resources\LokasiMakanans\Schemas\LokasiMakananForm;
use App\Filament\Resources\LokasiMakanans\Tables\LokasiMakanansTable;
use App\Filament\Resources\LokasiMakanans\Pages\ViewLokasiMakanan;
use App\Models\LokasiMakanan;
use BackedEnum;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LokasiMakananResource extends Resource
{
    protected static ?string $model = LokasiMakanan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // untuk mengganti nama
    protected static ?string $modelLabel = 'Persebaran Makanan';
    protected static ?string $pluralModelLabel = 'Persebaran Makanan';
    // end untuk mengganti nama

    protected static string | \UnitEnum | null $navigationGroup = 'Pemetaan & Spasial';

    public static function form(Schema $schema): Schema
    {
        return LokasiMakananForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LokasiMakanansTable::configure($table);
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
                
                    TextEntry::make('makanan.nama_makanan')
                        ->label('Nama Makanan'),
                    TextEntry::make('lokasi.nama_tempat')
                        ->label('Nama Tempat'),
                    TextEntry::make('deskripsi')
                        ->label('Deskripsi')
                        ->columnSpanFull(),
                    TextEntry::make('lokasi.alamat')
                        ->label('Alamat')
                        ->columnSpanFull(),
                    TextEntry::make('lokasi.latitude')
                        ->label('Latitude'),
                    TextEntry::make('lokasi.longitude')
                        ->label('Longitude'),
                    ImageEntry::make('foto')
                        ->label('')
                        ->disk('public')
                        ->size(150)
                        ->stacked() // Menumpuk foto dengan rapi jika banyak
                        ->limit(5),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLokasiMakanans::route('/'),
            'create' => CreateLokasiMakanan::route('/create'),
            'view' => ViewLokasiMakanan::route('/{record}'),
            'edit' => EditLokasiMakanan::route('/{record}/edit'),
        ];
    }
}
