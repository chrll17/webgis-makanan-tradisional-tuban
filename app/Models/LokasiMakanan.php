<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['lokasi_id','makanan_id','deskripsi','foto'])]
class LokasiMakanan extends Model
{
    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class);
    }

    public function makanan()
    {
        return $this->belongsTo(Makanan::class);
    }
}