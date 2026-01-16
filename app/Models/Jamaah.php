<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jamaah extends Model
{
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
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function absen()
    {
        return $this->belongsTo(AbsensiJamaah::class, 'absen_id');
    }

    public function tourleader()
    {
        return $this->belongsTo(TourLeader::class, 'assigned_tourleader_id');
    }

    public function attendance()
    {
        return $this->hasMany(AttendanceJamaah::class, 'jamaah_id');
    }

    public function latestAttendance()
{
    return $this->hasOne(AttendanceJamaah::class, 'jamaah_id')
        ->latest('absen_ke');
}

}
