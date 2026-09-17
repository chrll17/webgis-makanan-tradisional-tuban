<?php

namespace App\Filament\Resources\PostingMakanans;

use App\Filament\Resources\PostingMakanans\Pages\CreatePostingMakanan;
use App\Filament\Resources\PostingMakanans\Pages\EditPostingMakanan;
use App\Filament\Resources\PostingMakanans\Pages\ListPostingMakanans;
use App\Filament\Resources\PostingMakanans\Schemas\PostingMakananForm;
use App\Filament\Resources\PostingMakanans\Tables\PostingMakanansTable;
use App\Models\PostingMakanan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PostingMakananResource extends Resource
{
    protected static ?string $model = PostingMakanan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

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

    public static function getPages(): array
    {
        return [
            'index' => ListPostingMakanans::route('/'),
            'create' => CreatePostingMakanan::route('/create'),
            'edit' => EditPostingMakanan::route('/{record}/edit'),
        ];
    }
}
