<?php

namespace App\Filament\Resources\LokasiMakanans;

use App\Filament\Resources\LokasiMakanans\Pages\CreateLokasiMakanan;
use App\Filament\Resources\LokasiMakanans\Pages\EditLokasiMakanan;
use App\Filament\Resources\LokasiMakanans\Pages\ListLokasiMakanans;
use App\Filament\Resources\LokasiMakanans\Schemas\LokasiMakananForm;
use App\Filament\Resources\LokasiMakanans\Tables\LokasiMakanansTable;
use App\Models\LokasiMakanan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LokasiMakananResource extends Resource
{
    protected static ?string $model = LokasiMakanan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

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

    public static function getPages(): array
    {
        return [
            'index' => ListLokasiMakanans::route('/'),
            'create' => CreateLokasiMakanan::route('/create'),
            'edit' => EditLokasiMakanan::route('/{record}/edit'),
        ];
    }
}
