<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'id_pelanggan',
        'id_kasir',
        'no_nota',
        'tgl_masuk',
        'total_bayar',
        'status_pengerjaan',
        'estimasi_selesai',
    ];

    protected $casts = [
        'tgl_masuk' => 'datetime',
        'estimasi_selesai' => 'datetime',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'id_kasir', 'id_user');
    }

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi', 'id_transaksi');
    }

    public function prediksiAnalisis()
    {
        return $this->hasOne(PrediksiAnalisis::class, 'id_transaksi', 'id_transaksi');
    }
}
