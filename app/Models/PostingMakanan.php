<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id','nama_makanan','nama_tempat','deskripsi','alamat','latitude','longitude','foto','status','catatan_validasi'])]
class PostingMakanan extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}