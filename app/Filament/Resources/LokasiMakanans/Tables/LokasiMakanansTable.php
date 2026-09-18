<?php

namespace App\Filament\Resources\LokasiMakanans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class LokasiMakanansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('makanan.nama_makanan')
                    ->label('Nama Makanan - Tempat')
                    ->getStateUsing(function ($record){
                        return $record->makanan->nama_makanan . ' - ' . $record->lokasi->nama_tempat;
                    })
                    ->searchable(
                        query: function ($query, string $search): void {
                            $query
                                ->orWhereHas('lokasi', function ($query) use ($search) {
                                    $query->where('nama_tempat', 'like', "%{$search}%");
                                })
                                ->orWhereHas('makanan', function ($query) use ($search) {
                                    $query->where('nama_makanan', 'like', "%{$search}%");
                                });
                        }
                    ),
                TextColumn::make('lokasi.alamat')
                    ->label('Alamat')
                    ->searchable(),
                TextColumn::make('lokasi.latitude')
                    ->label('Latitude')
                    ->searchable(),
                TextColumn::make('lokasi.longitude')
                    ->label('Longitude')
                    ->searchable(),
            ])
            ->filters([
                // 1. Filter berdasarkan Nama Makanan yang sama
                SelectFilter::make('makanan_id')
                    ->label('Filter Nama Makanan')
                    ->relationship('makanan', 'nama_makanan')
                    ->searchable()
                    ->preload(),

                // 2. Filter berdasarkan Lokasi (Tempat dengan Lat/Long yang sama)
                SelectFilter::make('lokasi_id')
                    ->label('Filter Lokasi (Lat & Long yang Sama)')
                    ->relationship('lokasi', 'nama_tempat')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->nama_tempat} ({$record->latitude}, {$record->longitude})")
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                // aksi detail, edit, hapus
                ViewAction::make()
                    ->label('Detail')
                    ->icon('heroicon-o-eye'),
                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square'),
                DeleteAction::make()
                    ->label('Hapus Persebaran Makanan')
                    ->icon('heroicon-o-trash'),
                // end aksi detail, edit, hapus
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Persebaran Makanan'),
                ]),
            ]);
    }
}
