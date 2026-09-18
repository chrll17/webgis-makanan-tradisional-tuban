<?php

namespace App\Filament\Resources\LokasiMakanans\Pages;

use App\Filament\Resources\LokasiMakanans\LokasiMakananResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Lokasi;

class CreateLokasiMakanan extends CreateRecord
{
    protected static string $resource = LokasiMakananResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 1. Cek apakah admin memilih untuk membuat tempat baru
        if (isset($data['is_new_tempat']) && $data['is_new_tempat'] === true) {
            
            // 2. Simpan data tempat baru ke tabel 'tempat'
            $tempat = Lokasi::create([
                'nama_tempat' => $data['new_nama_tempat'],
                'alamat' => $data['new_alamat'],
                'latitude' => $data['new_lat'],
                'longitude' => $data['new_long'],
            ]);

            // 3. Masukkan ID tempat yang baru dibuat ke dalam array data detail_makanan
            $data['lokasi_id'] = $tempat->id;
        }

        // 4. Hapus data temporary form agar tidak menyebabkan error SQL 'Column not found' di tabel detail_makanan
        unset(
            $data['is_new_tempat'],
            $data['new_nama_tempat'],
            $data['new_alamat'],
            $data['new_lat'],
            $data['new_long']
        );

        return $data;
    }
}
