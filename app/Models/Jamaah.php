<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jamaah extends Model
{
    use HasFactory;

    protected $table = 'jamaahs';

    protected $fillable = [
        'absen_id',
        'assigned_tourleader_id',
        'nama_jamaah',
        'no_paspor',
        'no_hp',
        'jenis_kelamin',
        'tanggal_lahir',
        'kode_kloter',
        'nomor_bus',
        'keterangan',
        'sesi_absen'
    ];

    protected $casts = [
        'tanggal_lahir' => 'date:Y-m-d',
        'sesi_absen'     => 'string',
    ];

    // master absen
    public function absen()
    {
        return $this->belongsTo(AbsensiJamaah::class, 'absen_id');
    }

    // assigned TL
    public function tourleader()
    {
        return $this->belongsTo(TourLeader::class, 'assigned_tourleader_id');
    }

    // attendance history
    public function attendance()
    {
        return $this->hasMany(AttendanceJamaah::class, 'jamaah_id');
    }

    // get latest attendance
    public function latestAttendance()
{
    return $this->hasOne(AttendanceJamaah::class, 'jamaah_id')->orderByDesc('id');
}

}
