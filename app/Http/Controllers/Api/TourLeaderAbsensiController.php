<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\AbsensiJamaah;
use App\Models\Jamaah;
use App\Models\AttendanceJamaah;

class TourLeaderAbsensiController extends Controller
{
    /**
     * ============================================================
     * GET /api/tourleader/absensi
     * List absen milik Tour Leader yang login
     * ============================================================
     */
    public function index()
    {
        $tlId = Auth::guard('tourleader')->id();

        $absen = AbsensiJamaah::where('tourleader_id', $tlId)
            ->withCount('jamaah')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($a) {
                return [
                    'id'            => $a->id,
                    'judul_absen'   => $a->judul_absen,
                    'sesi_absen'    => $a->sesi_absen,
                    'jumlah_jamaah' => $a->jamaah_count,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $absen,
        ]);
    }


    /**
     * ============================================================
     * GET /api/tourleader/absensi/{id}
     * Detail absen + list jamaah
     * ============================================================
     */
    public function show($absenId)
    {
        $tlId = Auth::guard('tourleader')->id();

        $absen = AbsensiJamaah::where('id', $absenId)
            ->where('tourleader_id', $tlId)
            ->firstOrFail();

        $jamaah = Jamaah::where('absen_id', $absen->id)
            ->with('latestAttendance')
            ->orderBy('nama_jamaah')
            ->get()
            ->map(function ($j) {
                return [
                    'detail_id'   => $j->id, // dikirim ke Flutter
                    'nama_jamaah' => $j->nama_jamaah,
                    'no_hp'       => $j->no_hp,
                    'no_paspor'   => $j->no_paspor,
                    'kode_kloter' => $j->kode_kloter,
                    'nomor_bus'   => $j->nomor_bus,
                    'status'      => $j->latestAttendance->status ?? 'BELUM_ABSEN',
                ];
            });

        return response()->json([
            'success' => true,
            'absen' => [
                'id'         => $absen->id,
                'judul'      => $absen->judul_absen,
                'sesi_absen' => $absen->sesi_absen,
                'jumlah'     => $jamaah->count(),
            ],
            'jamaah' => $jamaah,
        ]);
    }


    /**
     * ============================================================
     * POST /api/tourleader/absensi/submit
     * Body:
     * {
     *   "detail_id": 3,
     *   "status": "HADIR" / "TIDAK_HADIR"
     * }
     * ============================================================
     */
    public function submit(Request $request)
    {
        // VALIDASI
        $data = $request->validate([
            'detail_id' => 'required|exists:jamaahs,id',
            'status'    => 'required|in:HADIR,TIDAK_HADIR',
        ]);

        $tlId = Auth::guard('tourleader')->id();

        // detail_id = id jamaah
        $jamaah = Jamaah::findOrFail($data['detail_id']);
        $absen  = $jamaah->absen;

        // CEK AKSES TL
        if ($absen->tourleader_id !== $tlId) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses',
            ], 403);
        }

        // INSERT attendance baru
        AttendanceJamaah::create([
            'jamaah_id'  => $jamaah->id,
            'tanggal'    => date('Y-m-d'),
            'sesi'       => $absen->sesi_absen,
            'status'     => $data['status'],
            'created_by' => $tlId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status absen berhasil disimpan',
        ]);
    }
}
