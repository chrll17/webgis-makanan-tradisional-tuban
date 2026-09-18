<?php

namespace App\Filament\Resources\PostingMakanans\Tables;

use App\Models\Lokasi;
use App\Models\LokasiMakanan;
use App\Models\Makanan;
use App\Models\PostingMakanan;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class PostingMakanansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_makanan')
                    ->label('Nama Makanan - Tempat')
                    ->getStateUsing(function ($record){
                        return $record->nama_makanan . ' - ' . $record->nama_tempat;
                    })
                    ->searchable(
                        query: function ($query, string $search): void {
                            $query
                                ->where('nama_tempat', 'like', "%{$search}%")
                                ->where('nama_makanan', 'like', "%{$search}%");
                        }
                    ),
                TextColumn::make('user.name')
                    ->label('Nama Pengirim')
                    ->numeric(),
                TextColumn::make('created_at')
                    ->label('Tanggal Kirim')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    })
                    ->searchable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter agar admin bisa langsung fokus ke data yang belum direview
                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'pending' => 'Menunggu Review',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->default('pending'),
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make()
                    ->label('Hapus')
                    ->icon('heroicon-o-trash'),

                // 1. TOMBOL SETUJUI (APPROVE)
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Usulan Makanan Ini?')
                    ->modalDescription('Data ini akan diubah statusnya menjadi Disetujui dan siap ditampilkan di peta utama.')

                    // Tombol hanya muncul jika status masih 'pending'
                    ->visible(fn (PostingMakanan $record): bool => $record->status === 'pending')
                    ->action(function (PostingMakanan $record) {

                        // Menggunakan Transaction agar jika terjadi error, data tidak setengah-setengah masuknya
                        DB::transaction(function () use ($record) {

                        // Ubah status jadi approved
                        $record->update(['status' => 'approved']);

                        // 2. Mencegah Redundansi Makanan:
                            // Cek apakah makanan dengan nama tersebut sudah ada. Jika belum, buat baru.
                            $makanan = Makanan::firstOrCreate([
                                'nama_makanan' => trim($record->nama_makanan),
                            ]);

                            // 3. Mencegah Redundansi Lokasi:
                            // Cek tempat berdasarkan Nama, Latitude, dan Longitude agar titik peta tidak menumpuk ganda.
                            $lokasi = Lokasi::firstOrCreate(
                                [
                                    'nama_tempat' => trim($record->nama_tempat),
                                    'latitude'    => $record->latitude,
                                    'longitude'   => $record->longitude,
                                ],
                                [
                                    'alamat'      => $record->alamat,
                                    'geom'        => $record->geom, // Menyalin data spasial geom
                                ]
                            );

                            // 4. Mencegah Redundansi Relasi (Pivot lokasi_makanans):
                            // Hubungkan Lokasi dan Makanan. Jika warung & makanan yang sama diusulkan ulang,
                            // maka sistem hanya akan memperbarui deskripsi & fotonya (bukan menambah baris kembar).
                            LokasiMakanan::updateOrCreate(
                                [
                                    'lokasi_id'  => $lokasi->id,
                                    'makanan_id' => $makanan->id,
                                ],
                                [
                                    'deskripsi'  => $record->deskripsi,
                                    'foto'       => $record->foto, // Memindahkan JSON/Array foto
                                ]
                            );
                        });
                        Notification::make()
                            ->title('Usulan berhasil disetujui!')
                            ->success()
                            ->send();
                    }),

                // 2. TOMBOL TOLAK DENGAN MODAL ALASAN (REJECT)
                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (PostingMakanan $record): bool => $record->status === 'pending')
                    ->form([
                        Textarea::make('catatan_validasi')
                            ->label('Alasan Penolakan')
                            ->placeholder('Contoh: Foto makanan buram, atau koordinat letak warung tidak berada di wilayah Tuban.')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (PostingMakanan $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'catatan_validasi' => $data['catatan_validasi'],
                        ]);

                        Notification::make()
                            ->title('Usulan telah ditolak.')
                            ->danger()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
