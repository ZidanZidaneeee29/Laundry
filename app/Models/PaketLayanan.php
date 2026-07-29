<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketLayanan extends Model
{
    use HasFactory;

    protected $table = 'paket_layanan';
    protected $primaryKey = 'id_paket';

    protected $fillable = [
        'nama_paket',
        'harga_per_kg',
        'estimasi',
    ];

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_paket', 'id_paket');
    }
}
