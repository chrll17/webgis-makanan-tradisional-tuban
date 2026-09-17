<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

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
        );
    }
}