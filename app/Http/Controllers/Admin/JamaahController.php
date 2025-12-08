<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

use App\Models\TourLeader;
use App\Models\Jamaah;
use App\Models\AbsensiJamaah;
use App\Models\AttendanceJamaah;

class JamaahController extends Controller
{
    /**
     * INDEX – List Absen
     */
    public function index()
    {
       $absen = AbsensiJamaah::with('tourleader')
           ->withCount('jamaah')
           ->orderBy('id', 'desc')
           ->get();

        return view('admin.jamaah.index', compact('absen'));
    }


    /**
     * DETAIL
     */
    public function detail($absenId)
    {
        $absen = AbsensiJamaah::findOrFail($absenId);

        $jamaah = Jamaah::with('latestAttendance')
            ->where('absen_id', $absenId)
            ->orderBy('nama_jamaah')
            ->paginate(100);

        return view('admin.jamaah.detail', compact('absen', 'jamaah'));
    }


    /**
     * FORM IMPORT
     */
    public function importForm()
    {
        $tourleaders = TourLeader::orderBy('name')->get();
        return view('admin.jamaah.import', compact('tourleaders'));
    }


    /**
     * PROSES IMPORT
     */
    public function import(Request $request)
    {
        $request->validate([
            'file'          => 'required|mimes:xlsx,xls,csv',
            'judul_absen'   => 'required|string|max:255',
            'tourleader_id' => 'required|exists:tour_leaders,id',
            'sesi_absen'    => 'required|string|max:50',
        ]);

        // Master Absen
        $absen = AbsensiJamaah::create([
            'judul_absen'   => $request->judul_absen,
            'tourleader_id' => $request->tourleader_id,
            'sesi_absen'    => $request->sesi_absen,   // PENTING
        ]);

        $sheet = IOFactory::load($request->file('file')->getRealPath())
            ->getActiveSheet();

        $highestRow = $sheet->getHighestDataRow();
        $inserted = 0;
        $errors = [];

        for ($row = 2; $row <= $highestRow; $row++) {

            $nama       = trim((string) $sheet->getCell("A{$row}")->getValue());
            $noPaspor   = trim((string) $sheet->getCell("B{$row}")->getValue());
            $noHp       = trim((string) $sheet->getCell("C{$row}")->getValue());
            $jkRaw      = trim((string) $sheet->getCell("D{$row}")->getValue());
            $tglRaw     = trim((string) $sheet->getCell("E{$row}")->getValue());
            $kodeKloter = trim((string) $sheet->getCell("F{$row}")->getValue());
            $nomorBus   = trim((string) $sheet->getCell("G{$row}")->getValue());
            $ket        = trim((string) $sheet->getCell("H{$row}")->getValue());

            if ($nama === '') continue;

            // Format JK
          // Format JK
$jkRaw = strtolower(trim($jkRaw));

// Hilangkan semua karakter selain huruf a-z
$jkClean = preg_replace('/[^a-z]/', '', $jkRaw);

if (in_array($jkClean, ['l', 'lk', 'laki', 'lakilaki', 'lelaki', 'cowok'])) {
    $jenisKelamin = 'L';
} elseif (in_array($jkClean, ['p', 'pr', 'perempuan', 'wanita', 'cewek'])) {
    $jenisKelamin = 'P';
} else {
    $jenisKelamin = null; // fallback
}


            // Format tanggal
            $tanggalLahir = null;
            if (!empty($tglRaw)) {
                try {
                    if (is_numeric($tglRaw)) {
                        $tanggalLahir = ExcelDate::excelToDateTimeObject($tglRaw)->format('Y-m-d');
                    } else {
                        foreach (['Y-m-d', 'd-m-Y', 'd/m/Y'] as $fmt) {
                            $dt = \DateTime::createFromFormat($fmt, $tglRaw);
                            if ($dt) {
                                $tanggalLahir = $dt->format('Y-m-d');
                                break;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    $errors[] = "Baris {$row}: tanggal lahir tidak valid.";
                }
            }

            // SIMPAN JAMA'AH
            $jamaah = Jamaah::create([
                'absen_id'              => $absen->id,
                'assigned_tourleader_id'=> $request->tourleader_id,
                'nama_jamaah'           => $nama,
                'no_paspor'             => $noPaspor ?: null,
                'no_hp'                 => $noHp ?: null,
                'jenis_kelamin'         => $jenisKelamin,
                'tanggal_lahir'         => $tanggalLahir,
                'kode_kloter'           => $kodeKloter ?: null,
                'nomor_bus'             => $nomorBus ?: null,
                'keterangan'            => $ket ?: null,
            ]);

            // ATTENDANCE DEFAULT (BELUM ABSEN)
            AttendanceJamaah::create([
                'jamaah_id'  => $jamaah->id,
                'tanggal'    => date('Y-m-d'),
                'sesi'       => $absen->sesi_absen,  // PENTING
                'status'     => 'BELUM_ABSEN',
                'created_by' => null,
            ]);

            $inserted++;
        }

        return redirect()->route('jamaah.index')
            ->with('success', "Import selesai. Total berhasil: $inserted.")
            ->with('import_errors', $errors);
    }


    /**
     * HAPUS ABSEN
     */
    public function destroy($absenId)
    {
        $absen = AbsensiJamaah::findOrFail($absenId);

        AttendanceJamaah::whereIn('jamaah_id', $absen->jamaah()->pluck('id'))->delete();
        $absen->jamaah()->delete();
        $absen->delete();

        return redirect()->route('jamaah.index')
            ->with('success', 'Absen berhasil dihapus.');
    }
}
