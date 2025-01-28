<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pendapatan extends Model
{
    protected $table = "pendapatan";
    protected $primaryKey = "id_pendapatan";
    protected $fillable = [
        'pendapatan',
        'id_user',
        'id_kategori_pendapatan',
        'tanggal'
    ];
    protected $hidden = [
        // 'created_at',
        'updated_at',
        'created_at',
        'id_user',
        'id_kategori_pendapatan'
    ];

    public function kategori_pendapatan()
    {
        return $this->belongsTo(KategoriPendapatan::class, 'id_kategori_pendapatan'); // Sesuaikan 'user_id' dengan nama kolom foreign key
    }
}
