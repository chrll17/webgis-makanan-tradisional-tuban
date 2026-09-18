<?php

namespace App\Filament\Resources\Makanans\Tables;

use App\Models\LokasiMakanan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class MakanansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_makanan')
                    ->searchable(),
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
                    ->label('Hapus Makanan')
                    ->before(function (DeleteAction $action, $record) {
                        // Cek apakah jenis makanan ini masih terdaftar di lokasi mana pun
                        $jumlahLokasi = LokasiMakanan::where('makanan_id', $record->id)->count();

                        if ($jumlahLokasi > 0) {
                            // 1. Munculkan notifikasi error merah
                            Notification::make()
                                ->title('Gagal Menghapus Makanan!')
                                ->body("Makanan ini tidak bisa dihapus karena masih terdaftar di {$jumlahLokasi} tempat. Silakan hapus data relasinya di menu Persebaran Makanan terlebih dahulu.")
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
                        ->label('Hapus Makanan')
                        ->before(function (DeleteBulkAction $action, Collection $records) {
                            // 1. Ambil semua ID makanan dari baris yang dicentang
                            $makananIds = $records->pluck('id');

                            // 2. Cek apakah ada lokasi makanan yang memakai salah satu ID tersebut
                            $jumlahLokasiTerhubung = LokasiMakanan::whereIn('makanan_id', $makananIds)->count();

                            if ($jumlahLokasiTerhubung > 0) {
                                // 3. Munculkan notifikasi
                                Notification::make()
                                    ->title('Gagal Menghapus Data Massal!')
                                    ->body("Beberapa makanan yang kamu pilih tidak bisa dihapus karena masih digunakan di lokasi warung. Silakan batalkan centang pada makanan tersebut atau hapus relasinya terlebih dahulu.")
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
