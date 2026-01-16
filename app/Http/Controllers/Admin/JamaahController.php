<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

use App\Models\Kloter;
use App\Models\TourLeader;
use App\Models\Jamaah;
use App\Models\AbsensiJamaah;
use App\Models\AttendanceJamaah;
use App\Models\SesiAbsen;
use App\Models\SesiAbsenItem;

class JamaahController extends Controller
{
    /* ======================================================
     * INDEX – LIST MASTER ABSEN
     * ====================================================== */
    public function index()
    {
        $absen = AbsensiJamaah::with([
                'kloter',
                'sesiAbsen',
                'sesiAbsenItem'
            ])
            ->withCount('jamaah')
            ->latest()
            ->get();

        return view('admin.jamaah.index', compact('absen'));
    }

    /* ======================================================
     * DETAIL #1 – LIST TOUR LEADER
     * ====================================================== */
    public function detail($absenId)
    {
        $absen = AbsensiJamaah::with([
            'kloter',
            'sesiAbsen',
            'sesiAbsenItem'
        ])->findOrFail($absenId);

        $tourleaders = TourLeader::where('kloter_id', $absen->kloter_id)
            ->orderBy('name')
            ->get()
            ->map(function ($tl) use ($absen) {

                $totalJamaah = Jamaah::where('absen_id', $absen->id)
                    ->where('assigned_tourleader_id', $tl->id)
                    ->count();

                $sudahDikerjakan = AttendanceJamaah::where('absensi_jamaah_id', $absen->id)
                    ->where('created_by', $tl->id)
                    ->where('status', '!=', 'BELUM_ABSEN')
                    ->exists();

                return [
                    'tourleader' => $tl,
                    'total'      => $totalJamaah,
                    'done'       => $sudahDikerjakan,
                ];
            });

        return view('admin.jamaah.detail', compact('absen', 'tourleaders'));
    }

    /* ======================================================
     * DETAIL #2 – JAMAAH PER TOUR LEADER
     * ====================================================== */
    public function detailTourleader($absenId, $tourleaderId)
    {
        $absen = AbsensiJamaah::with([
            'kloter',
            'sesiAbsen',
            'sesiAbsenItem'
        ])->findOrFail($absenId);

        $tourleader = TourLeader::findOrFail($tourleaderId);

        $jamaah = Jamaah::where('absen_id', $absen->id)
            ->where('assigned_tourleader_id', $tourleader->id)
            ->orderBy('nama_jamaah')
            ->paginate(100);

        return view(
            'admin.jamaah.detail-tourleader',
            compact('absen', 'tourleader', 'jamaah')
        );
    }

    /* ======================================================
     * FORM IMPORT
     * ====================================================== */
    public function importForm()
    {
        return view('admin.jamaah.import', [
            'kloters'    => Kloter::orderBy('nama')->get(),
            'sesiAbsens' => SesiAbsen::with('items')->orderBy('judul')->get(),
        ]);
    }

    /* ======================================================
     * PROSES IMPORT (MULTI TOUR LEADER)
     * ====================================================== */
    public function import(Request $request)
    {
        $request->validate([
            'kloter_id'          => 'required|exists:kloters,id',
            'sesi_absen_id'      => 'required|exists:sesi_absens,id',
            'sesi_absen_item_id' => 'required|exists:sesi_absen_items,id',
            'files'              => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {

            $kloter = Kloter::findOrFail($request->kloter_id);

            $existing = AbsensiJamaah::where('kloter_id', $kloter->id)
                ->where('sesi_absen_id', $request->sesi_absen_id)
                ->where('sesi_absen_item_id', $request->sesi_absen_item_id)
                ->first();

            if ($existing) {
                return back()->withErrors([
                    'Sesi absen untuk kloter ini sudah pernah dibuat.'
                ]);
            }

            $absen = AbsensiJamaah::create([
                'kloter_id'          => $kloter->id,
                'judul_absen'        => $kloter->nama,
                'sesi_absen_id'      => $request->sesi_absen_id,
                'sesi_absen_item_id' => $request->sesi_absen_item_id,
            ]);

            $totalInserted = 0;

            foreach ((array) $request->file('files') as $tourleaderId => $file) {

                if (!$file) continue;

                $validTL = TourLeader::where('id', $tourleaderId)
                    ->where('kloter_id', $kloter->id)
                    ->exists();

                if (!$validTL) continue;

                $sheet = IOFactory::load($file->getRealPath())
                    ->getActiveSheet();

                $highestRow = $sheet->getHighestDataRow();

                for ($row = 2; $row <= $highestRow; $row++) {

                    $nama = trim((string) $sheet->getCell("A{$row}")->getValue());
                    if ($nama === '') continue;

                    $noPaspor   = trim((string) $sheet->getCell("B{$row}")->getValue());
                    $noHp       = trim((string) $sheet->getCell("C{$row}")->getValue());
                    $jkRaw      = trim((string) $sheet->getCell("D{$row}")->getValue());
                    $tglRaw     = trim((string) $sheet->getCell("E{$row}")->getValue());
                    $kodeKloter = trim((string) $sheet->getCell("F{$row}")->getValue());
                    $nomorBus   = trim((string) $sheet->getCell("G{$row}")->getValue());
                    $ket        = trim((string) $sheet->getCell("H{$row}")->getValue());

                    $jkClean = preg_replace('/[^a-z]/', '', strtolower($jkRaw));
                    $jenisKelamin = in_array($jkClean, ['l','lk','laki','lakilaki'])
                        ? 'L'
                        : (in_array($jkClean, ['p','pr','perempuan','wanita']) ? 'P' : null);

                    $tanggalLahir = null;
                    if ($tglRaw) {
                        try {
                            $tanggalLahir = is_numeric($tglRaw)
                                ? ExcelDate::excelToDateTimeObject($tglRaw)->format('Y-m-d')
                                : \Carbon\Carbon::parse($tglRaw)->format('Y-m-d');
                        } catch (\Throwable $e) {}
                    }

                    $jamaah = Jamaah::create([
                        'absen_id'               => $absen->id,
                        'assigned_tourleader_id' => $tourleaderId,
                        'nama_jamaah'            => $nama,
                        'no_paspor'              => $noPaspor ?: null,
                        'no_hp'                  => $noHp ?: null,
                        'jenis_kelamin'          => $jenisKelamin,
                        'tanggal_lahir'          => $tanggalLahir,
                        'kode_kloter'            => $kodeKloter ?: null,
                        'nomor_bus'              => $nomorBus ?: null,
                        'keterangan'             => $ket ?: null,
                    ]);

                    // 🔑 RECORD AWAL (WAJIB)
                    AttendanceJamaah::create([
                        'jamaah_id'         => $jamaah->id,
                        'absensi_jamaah_id' => $absen->id,
                        'absen_ke'          => 1,
                        'tanggal'           => now()->toDateString(),
                        'status'            => 'BELUM_ABSEN',
                        'catatan'           => null,
                        'created_by'        => $tourleaderId,
                    ]);

                    $totalInserted++;
                }
            }

            DB::commit();

            return redirect()
                ->route('jamaah.index')
                ->with('success', "Import selesai. Total jamaah: {$totalInserted}");

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /* ======================================================
     * DELETE
     * ====================================================== */
    public function destroy($absenId)
    {
        $absen = AbsensiJamaah::findOrFail($absenId);

        AttendanceJamaah::where('absensi_jamaah_id', $absen->id)->delete();
        $absen->jamaah()->delete();
        $absen->delete();

        return redirect()
            ->route('jamaah.index')
            ->with('success', 'Absen berhasil dihapus.');
    }
}
