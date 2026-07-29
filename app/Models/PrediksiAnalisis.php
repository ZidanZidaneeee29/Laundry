<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrediksiAnalisis extends Model
{
    use HasFactory;

    protected $table = 'prediksi_analisis';
    protected $primaryKey = 'id_prediksi';

    protected $fillable = [
        'id_transaksi',
        'model_version',
        'confidence_score',
        'jumlah_antrean',
        'durasi_estimasi_jam',
        'detail_pohon_json',
    ];

    protected $casts = [
        'detail_pohon_json' => 'array',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi');
    }
}
