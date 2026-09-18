<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\DB;

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
        )
        // Memerintahkan Laravel agar saat mengambil data makanan di lokasi ini, kolom 'id', 'deskripsi', dan 'foto' dari tabel pivot ikut dibawa/dibaca
        ->withPivot('id', 'deskripsi', 'foto')
        // Memastikan waktu pembuatan (created_at) dan perubahan (updated_at) di tabel pivot diurus otomatis oleh Laravel
        ->withTimestamps();
    }

    /**
     * Method booted() adalah "life-cycle hook" bawaan Eloquent.
     * Semua logika di dalam method ini akan otomatis didaftarkan dan dijalankan di latar belakang oleh sistem Laravel
     * setiap kali model Lokasi dipanggil atau terjadi interaksi data (seperti saat akan membuat atau memperbarui data).
     */
    protected static function booted()
    {
        // EVENT CREATING: Event ini dipicu tepat SEBELUM data lokasi baru (Insert/Create) benar-benar disimpan ke tabel database
        static::creating(function ($lokasi) {
            // Mengecek apakah input latitude dan longitude tidak kosong
            if ($lokasi->latitude && $lokasi->longitude) {
                // Mengisi kolom 'geom' secara otomatis dengan query SQL mentah menggunakan fungsi spasial ST_GeomFromText.
                // Catatan GIS penting: Aturan format POINT adalah (X Y), sehingga urutannya HARUS (Longitude Latitude), bukan sebaliknya!
                // Angka 4326 adalah SRID (Spatial Reference System Identifier) standar internasional untuk koordinat GPS bumi (WGS 84)
                $lokasi->geom = DB::raw("ST_GeomFromText('POINT({$lokasi->longitude} {$lokasi->latitude})', 4326)");
            }
        });

        // EVENT UPDATING: Event ini dipicu tepat SEBELUM data lokasi yang sudah ada diperbarui (Update) di database
        static::updating(function ($lokasi) {
            // $lokasi->isDirty(...): Fungsi pintar Laravel untuk mengecek apakah ada PERUBAHAN nilai pada kolom latitude atau longitude di form edit saat ini.
            // Jika admin mengedit data warung (misal cuma ganti nama/alamat) tanpa mengubah angka koordinat, logika di bawah tidak akan dijalankan agar hemat performa database
            if ($lokasi->isDirty('latitude') || $lokasi->isDirty('longitude')) {
                // Jika koordinatnya terdeteksi berubah, hitung ulang dan perbarui titik koordinat spasial di kolom 'geom' menggunakan rumus yang sama seperti di atas
                $lokasi->geom = DB::raw("ST_GeomFromText('POINT({$lokasi->longitude} {$lokasi->latitude})', 4326)");
            }
        });
    }
}