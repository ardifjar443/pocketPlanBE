<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    protected $table = "pengeluaran";
    protected $primaryKey = "id_pengeluaran";
    protected $fillable = [
        'pengeluaran',
        'id_user',
        'id_kategori_pengeluaran',
        'tanggal'
    ];
    protected $hidden = [
        // 'created_at',
        'updated_at',
        'created_at',
        'id_user',
        'id_kategori_pengeluaran'
    ];

    public function kategori_pengeluaran()
    {
        return $this->belongsTo(KategoriPengeluaran::class, 'id_kategori_pengeluaran'); // Sesuaikan 'user_id' dengan nama kolom foreign key
    }
}
