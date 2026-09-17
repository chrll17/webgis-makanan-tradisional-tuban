<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['nama_tempat','alamat','latitude','longitude'])]
class Lokasi extends Model
{
    /**
     * Relasi One-to-Many (Satu ke Banyak) ke model LokasiMakanan (tabel pivot/perantara).
     * Artinya: 1 titik Lokasi (warung) bisa tercatat memiliki banyak baris relasi makanan.
     */
    public function lokasiMakanans()
    {
        return $this->hasMany(LokasiMakanan::class);
    }

    /**
     * Relasi Many-to-Many (Banyak ke Banyak) ke model Makanan.
     * Artinya: 1 Lokasi (warung) bisa menjual banyak jenis Makanan, dan 1 jenis Makanan bisa tersedia di banyak Lokasi.
     */
    public function makanans()
    {
        return $this->belongsToMany(
            Makanan::class,
            'lokasi_makanans'
        );
    }
}