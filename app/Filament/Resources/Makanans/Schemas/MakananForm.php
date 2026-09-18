<?php

namespace App\Filament\Resources\Makanans\Schemas;

use App\Models\Makanan;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MakananForm
{
    // Method statis yang akan dipanggil oleh Filament untuk merakit dan menampilkan form ke layar admin.
    public static function configure(Schema $schema): Schema
    {
        // Mengembalikan struktur form (schema) yang sudah diisi dengan komponen-komponen input.
        return $schema
            // Mendaftarkan array yang berisi daftar seluruh elemen/inputan yang akan dipasang pada form ini.
            ->components([
                // Membuat sebuah kotak input teks baru yang langsung terikat (data binding) ke kolom 'nama_makanan' di database.
                TextInput::make('nama_makanan')
                    // Memaksa admin agar wajib mengisi inputan ini (form tidak bisa disimpan jika kotak ini dikosongkan).
                    ->required()
                    // Memasang aturan validasi kustom. Kita menangkap parameter '$record' yang berisi data lama (saat mode Edit), atau bernilai 'null' (saat mode Tambah Baru).
                    ->rule(fn ($record) => function (string $attribute, $value, Closure $fail) use ($record) {
                        // Menyeragamkan inputan baru dari admin: trim() menghapus spasi sisa di awal/akhir kata, strtolower() mengubah semua huruf menjadi huruf kecil.
                        $inputBersih = strtolower(trim($value));
                        
                        // Cek langsung ke database dengan mengubah kolom menjadi huruf kecil semua (LOWER)
                        $query = Makanan::whereRaw('LOWER(nama_makanan) = ?', [$inputBersih]);
                        
                        // Jika sedang berada di mode Edit, abaikan ID data yang sedang diedit saat ini
                        if ($record) {
                            // Mengecualikan ID data yang sedang diedit ini dari pencarian, agar sistem tidak mengira data tersebut berduplikasi dengan dirinya sendiri.
                            $query->where('id', '!=', $record->id);
                        }
                        
                        // Jika datanya ditemukan, gagalkan proses simpan dan munculkan error merah
                        if ($query->exists()) {
                            $fail('Makanan ini sudah ada di database.');
                        }
                    }),
            ]);
    }
}
