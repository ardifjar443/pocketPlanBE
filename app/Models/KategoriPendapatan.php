<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class KategoriPendapatan extends Model
{

    protected $table = "kategori_pendapatan";
    protected $primaryKey = "id_kategori_pendapatan";
    protected $fillable = [
        'nama_kategori',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
