<?php

namespace App\Filament\Resources\Lokasis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use App\Models\LokasiMakanan;
use Illuminate\Database\Eloquent\Collection;

class LokasisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_tempat')
                    ->searchable(),
                TextColumn::make('latitude')
                    ->sortable(),
                TextColumn::make('longitude')
                    ->sortable(),
                TextColumn::make('geom'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->label('Hapus Tempat')
                    ->before(function (DeleteAction $action, $record) {
                        // Cek apakah lokasi ini masih memiliki menu makanan terdaftar
                        $jumlahMakanan = LokasiMakanan::where('lokasi_id', $record->id)->count();

                        if ($jumlahMakanan > 0) {
                            // 1. Munculkan notifikasi error berwarna merah
                            Notification::make()
                                ->title('Gagal Menghapus Tempat!')
                                ->body("Tempat ini tidak bisa dihapus karena masih memiliki {$jumlahMakanan} menu makanan tradisional terdaftar. Silakan hapus atau pindahkan menunya terlebih dahulu.")
                                ->danger()
                                ->send();

                            // 2. Batalkan proses penghapusan
                            $action->halt();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Tempat')
                        ->before(function (DeleteBulkAction $action, Collection $records) {
                            // 1. Ambil semua ID tempat dari baris yang dicentang
                            $tempatIds = $records->pluck('id');

                            // 2. Cek apakah ada makanan yang memakai salah satu ID tersebut
                            $jumlahMakananTerhubung = LokasiMakanan::whereIn('lokasi_id', $tempatIds)->count();

                            if ($jumlahMakananTerhubung > 0) {
                                // 3. Munculkan notifikasi
                                Notification::make()
                                    ->title('Gagal Menghapus Data Massal!')
                                    ->body("Beberapa tempat yang kamu pilih tidak bisa dihapus karena masih memiliki menu makanan tradisional. Silakan batalkan centang pada tempat tersebut atau hapus relasinya terlebih dahulu.")
                                    ->danger()
                                    ->send();

                                // 4. Batalkan proses hapus massal
                                $action->halt();
                            }
                        }),
                ]),
            ]);
    }
}
