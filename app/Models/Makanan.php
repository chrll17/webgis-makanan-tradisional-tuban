<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute; 
use Illuminate\Support\Str;

#[Fillable(['nama_makanan'])]
class Makanan extends Model
{
    /**
     * Relasi One-to-Many (Satu ke Banyak) ke model LokasiMakanan.
     * Artinya: 1 Makanan bisa terdaftar di banyak baris data pivot (tabel lokasi_makanans).
     */
    public function lokasiMakanans()
    {
        return $this->hasMany(LokasiMakanan::class);
    }

    /**
     * Relasi Many-to-Many (Banyak ke Banyak) ke model Lokasi.
     * Artinya: 1 Makanan bisa tersedia di banyak Lokasi (warung), dan sebaliknya 1 Lokasi bisa menjual banyak Makanan.
     */
    public function lokasis()
    {
        return $this->belongsToMany(
            Lokasi::class,
            'lokasi_makanans'
        )
        // ->Memberi tahu Laravel bahwa saat kita memanggil data lokasi, kita juga ingin mengambil kolom 'id', 'deskripsi', dan 'foto' yang ada di dalam tabel pivot
        ->withPivot('id', 'deskripsi', 'foto')
        // ->Memastikan kolom created_at dan updated_at di dalam tabel pivot ikut dicatat dan diperbarui secara otomatis oleh Laravel
        ->withTimestamps();
    }

    /**
     * Mutator untuk kolom 'nama_makanan'.
     * Mutator bertugas sebagai "satpam" yang mengubah data secara otomatis tepat sebelum data tersebut disimpan ke dalam database.
     */
    protected function namaMakanan(): Attribute
    {
        return Attribute::make(
            // Setiap kali kolom 'nama_makanan' diisi, otomatis jadikan Title Case dan hapus spasi sisa
            set: fn (string $value) => Str::title(trim($value)),
        );
    }
}