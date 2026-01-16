<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SesiAbsen;          // ⬅️ WAJIB
use App\Models\SesiAbsenItem;      // ⬅️ OPSIONAL (tapi rapi)

class SesiAbsenController extends Controller
{
    public function index()
    {
        $sesiAbsens = SesiAbsen::withCount('items')->latest()->get();
        return view('admin.jamaah.sesiabsen.index', compact('sesiAbsens'));
    }

    public function create()
    {
        return view('admin.jamaah.sesiabsen.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*' => 'required|string|max:255',
        ]);

        $sesiAbsen = SesiAbsen::create([
            'judul' => $request->judul,
        ]);

        foreach ($request->items as $isi) {
            $sesiAbsen->items()->create([
                'isi' => $isi,
            ]);
        }

        return redirect()->route('admin.sesiabsen.index')
            ->with('success', 'Sesi absen berhasil dibuat');
    }

    // =========================
    // DETAIL
    // =========================
    public function show(SesiAbsen $sesiAbsen)
    {
        $sesiAbsen->load('items');
        return view('admin.jamaah.sesiabsen.show', compact('sesiAbsen'));
    }

    // =========================
    // EDIT
    // =========================
    public function edit(SesiAbsen $sesiAbsen)
    {
        $sesiAbsen->load('items');
        return view('admin.jamaah.sesiabsen.edit', compact('sesiAbsen'));
    }

    // =========================
    // UPDATE
    // =========================
    public function update(Request $request, SesiAbsen $sesiAbsen)
{
    $request->validate([
        'judul' => 'required|string|max:255',
        'items' => 'required|array|min:1',
        'items.*.id' => 'required|exists:sesi_absen_items,id',
        'items.*.isi' => 'required|string|max:255',
    ]);

    // update judul sesi
    $sesiAbsen->update([
        'judul' => $request->judul,
    ]);

    // update item TANPA delete
    foreach ($request->items as $item) {
        SesiAbsenItem::where('id', $item['id'])
            ->where('sesi_absen_id', $sesiAbsen->id)
            ->update([
                'isi' => $item['isi'],
            ]);
    }

    return redirect()
        ->route('admin.sesiabsen.index')
        ->with('success', 'Sesi absen berhasil diperbarui (jamaah tetap aman)');
}

    // =========================
    // DELETE
    // =========================
    public function destroy(SesiAbsen $sesiAbsen)
    {
        $sesiAbsen->delete();

        return redirect()->route('admin.sesiabsen.index')
            ->with('success', 'Sesi absen berhasil dihapus');
    }

  public function items(SesiAbsen $sesiAbsen)
{
    return response()->json(
        $sesiAbsen->items()
            ->select('id', 'isi')
            ->orderBy('isi')
            ->get()
    );
}

}
