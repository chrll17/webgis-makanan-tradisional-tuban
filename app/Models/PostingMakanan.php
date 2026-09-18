<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id','nama_makanan','nama_tempat','deskripsi','alamat','latitude','longitude','foto','status','catatan_validasi'])]
class PostingMakanan extends Model
{
    // Otomatis isi kolom geom dari latitude & longitude sebelum data disimpan
    protected static function booted()
    {
        static::saving(function ($posting) {
            // Kita menggunakan format WKT: POINT(longitude latitude)
            // PostgreSQL/PostGIS akan otomatis mengonversi string ini menjadi tipe Geometry.
            if ($posting->latitude && $posting->longitude) {
                $posting->geom = "POINT({$posting->longitude} {$posting->latitude})";
            }
        });
    }
    
    // Tambahkan ini agar string JSON dari database otomatis jadi Array di Laravel
    protected $casts = [
        'foto' => 'array',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}