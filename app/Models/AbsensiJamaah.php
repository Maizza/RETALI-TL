<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiJamaah extends Model
{
    use HasFactory;

    protected $table = 'absensi_jamaah';

    protected $fillable = [
        'judul_absen',
        'tourleader_id',
        'sesi_absen'
    ];

    protected $casts = [
        'sesi_absen' => 'string',
    ];

    // Absen punya banyak jamaah
    public function jamaah()
    {
        return $this->hasMany(Jamaah::class, 'absen_id');
    }

    // Owner absen = Tour Leader
    public function tourleader()
    {
        return $this->belongsTo(TourLeader::class, 'tourleader_id');
    }
}
