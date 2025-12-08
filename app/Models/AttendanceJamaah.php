<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceJamaah extends Model
{
    use HasFactory;

    protected $table = 'attendance_jamaah';

    protected $fillable = [
        'jamaah_id',
        'tanggal',
        'sesi',
        'status',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
    ];

    public function jamaah()
    {
        return $this->belongsTo(Jamaah::class, 'jamaah_id');
    }

    public function creator()
    {
        return $this->belongsTo(TourLeader::class, 'created_by');
    }
}
