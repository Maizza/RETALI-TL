<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AbsensiJamaah;
use App\Models\AttendanceJamaah;
use App\Models\Jamaah;

class AttendanceJamaahController extends Controller
{
    /**
     * ======================================================
     * GET /tourleader/attendance-jamaah
     * LIST ABSEN (HOME)
     * ======================================================
     */
    public function index(Request $request)
    {
        $tourleader = $request->user(); // Sanctum

        $absens = AbsensiJamaah::with(['kloter', 'sesiAbsen', 'sesiAbsenItem'])
            ->whereHas('jamaah', function ($q) use ($tourleader) {
                $q->where('assigned_tourleader_id', $tourleader->id);
            })
            ->withCount('jamaah')
            ->latest()
            ->get()
            ->map(function ($absen) {

                $sesi = collect([
                    $absen->sesiAbsen?->judul,
                    $absen->sesiAbsenItem?->isi,
                ])->filter()->implode(' – ');

                return [
                    'id'            => $absen->id,
                    'judul'         => $absen->judul_absen,
                    'tanggal'       => $absen->kloter?->tanggal_label ?? '-',
                    'sesi'          => $sesi ?: '-',
                    'jamaah_count'  => $absen->jamaah_count,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $absens,
        ]);
    }

    /**
     * ======================================================
     * GET /tourleader/attendance-jamaah/{absenId}
     * DETAIL ABSEN + LIST JAMAAH
     * ======================================================
     */
    public function show(Request $request, $absenId)
    {
        $tourleaderId = $request->user()->id;

        $absen = AbsensiJamaah::where('id', $absenId)
            ->whereHas('jamaah', function ($q) use ($tourleaderId) {
                $q->where('assigned_tourleader_id', $tourleaderId);
            })
            ->firstOrFail();

        $jamaah = Jamaah::where('absen_id', $absen->id)
            ->where('assigned_tourleader_id', $tourleaderId)
            ->with('latestAttendance')
            ->orderBy('nama_jamaah')
            ->get()
            ->map(fn ($j) => [
                'detail_id'   => $j->id,
                'nama_jamaah' => $j->nama_jamaah,
                'no_hp'       => $j->no_hp,
                'no_paspor'   => $j->no_paspor,
                'kode_kloter' => $j->kode_kloter,
                'nomor_bus'   => $j->nomor_bus,
                'status'      => $j->latestAttendance->status ?? 'BELUM_ABSEN',
                'catatan'     => $j->latestAttendance->catatan,
            ]);

        return response()->json([
            'success' => true,
            'absen' => [
                'id'    => $absen->id,
                'judul' => $absen->judul_absen,
            ],
            'jamaah' => $jamaah,
        ]);
    }

    /**
     * ======================================================
     * POST /tourleader/attendance-jamaah
     * SIMPAN ABSEN (BERULANG)
     * ======================================================
     */
    public function update(Request $request)
    {
        $request->validate([
            'jamaah_id'         => 'required|exists:jamaahs,id',
            'absensi_jamaah_id' => 'required|exists:absensi_jamaah,id',
            'status'            => 'required|in:HADIR,TIDAK_HADIR',
            'catatan'           => 'nullable|string|max:500',
        ]);

        $tourleaderId = $request->user()->id;

        // 🔒 Pastikan jamaah ini milik TL yang login
        Jamaah::where('id', $request->jamaah_id)
            ->where('assigned_tourleader_id', $tourleaderId)
            ->firstOrFail();

        // 🔑 Ambil absen_ke terakhir
        $lastAbsenKe = AttendanceJamaah::where('jamaah_id', $request->jamaah_id)
            ->where('absensi_jamaah_id', $request->absensi_jamaah_id)
            ->max('absen_ke') ?? 0;

        AttendanceJamaah::create([
            'jamaah_id'         => $request->jamaah_id,
            'absensi_jamaah_id' => $request->absensi_jamaah_id,
            'absen_ke'          => $lastAbsenKe + 1,
            'tanggal'           => now()->toDateString(),
            'status'            => $request->status,
            'catatan'           => $request->catatan,
            'created_by'        => $tourleaderId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil disimpan',
        ]);
    }
}
