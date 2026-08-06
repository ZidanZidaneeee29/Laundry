<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'no_mesin_cuci',
        'estimasi_selesai',
    ];

    protected $casts = [
        'tgl_masuk' => 'datetime',
        'estimasi_selesai' => 'datetime',
        'no_mesin_cuci' => 'integer',
    ];

    /**
     * Accessor Otomatisasi Status Pengerjaan Real-Time.
     */
    public function getStatusPengerjaanAttribute($value)
    {
        $rawStatus = $this->attributes['status_pengerjaan'] ?? $value;

        if ($rawStatus === 'Selesai') {
            return 'Selesai';
        }

        if (!$this->tgl_masuk || !$this->estimasi_selesai) {
            return $rawStatus ?? 'Cuci';
        }

        $now = Carbon::now();

        if ($now->greaterThanOrEqualTo($this->estimasi_selesai)) {
            return 'Selesai';
        }

        $levels = [
            'Antre' => 1,
            'Cuci' => 2,
            'Kering' => 3,
            'Setrika' => 4,
            'Selesai' => 5,
        ];

        $rawLevel = $levels[$rawStatus] ?? 1;

        $totalSeconds = max(1, $this->tgl_masuk->diffInSeconds($this->estimasi_selesai));
        $elapsedSeconds = max(0, $this->tgl_masuk->diffInSeconds($now, false));
        $percentage = ($elapsedSeconds / $totalSeconds) * 100;

        $calcStatus = 'Cuci';
        if ($percentage >= 75) {
            $calcStatus = 'Setrika';
        } elseif ($percentage >= 45) {
            $calcStatus = 'Kering';
        } else {
            // Jika belum memegang slot mesin (null/0), status Antre
            if (!$this->no_mesin_cuci && $rawStatus === 'Antre') {
                $calcStatus = 'Antre';
            }
        }

        $calcLevel = $levels[$calcStatus] ?? 1;

        if ($rawLevel >= $calcLevel) {
            return $rawStatus;
        }

        return $calcStatus;
    }

    /**
     * Accessor No. Mesin Cuci Real-Time tersinkronisasi 100% dan permanen.
     */
    public function getNoMesinAttribute()
    {
        $st = $this->status_pengerjaan;

        if ($st === 'Selesai') {
            return '-';
        }

        if (in_array($st, ['Kering', 'Setrika'])) {
            return 'Keluar Mesin';
        }

        if ($st === 'Cuci' && $this->no_mesin_cuci) {
            return 'Mesin ' . $this->no_mesin_cuci;
        }

        return 'Antre Mesin';
    }

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
