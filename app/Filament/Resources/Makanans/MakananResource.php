<?php

namespace App\Filament\Resources\Makanans;

use App\Filament\Resources\Makanans\Pages\CreateMakanan;
use App\Filament\Resources\Makanans\Pages\EditMakanan;
use App\Filament\Resources\Makanans\Pages\ListMakanans;
use App\Filament\Resources\Makanans\Schemas\MakananForm;
use App\Filament\Resources\Makanans\Tables\MakanansTable;
use App\Models\Makanan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MakananResource extends Resource
{
    protected static ?string $model = Makanan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nama_makanan';

    public static function form(Schema $schema): Schema
    {
        return MakananForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MakanansTable::configure($table);
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
            'index' => ListMakanans::route('/'),
            'create' => CreateMakanan::route('/create'),
            'edit' => EditMakanan::route('/{record}/edit'),
        ];
    }
}
