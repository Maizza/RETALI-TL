@extends('layouts.app')

@section('title', 'Sesi Absen')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
   <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Sesi Absen</h3>

    <div class="d-flex gap-2">
        {{-- Button Kembali ke Absen Jamaah --}}
        <a href="{{ route('jamaah.index') }}"
           class="btn btn-outline-secondary">
            ← Kembali
        </a>

        {{-- Button Tambah Sesi --}}
        <a href="{{ route('admin.sesiabsen.create') }}"
           class="btn btn-primary">
            + Tambah Sesi
        </a>
    </div>
</div>


    {{-- ALERT --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- TABLE --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">

            @if ($sesiAbsens->count() === 0)
                <div class="text-center text-muted py-5">
                    Belum ada sesi absen.
                </div>
            @else
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: linear-gradient(90deg, #1e3c72, #2a5298); color: #fff;">
                        <tr>
                            <th width="60">No</th>
                            <th>Judul Sesi</th>
                            <th width="120">Jumlah Isi</th>
                            <th width="240" class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($sesiAbsens as $sesi)
                            <tr>
                                {{-- NOMOR --}}
                                <td class="fw-semibold text-muted">
                                    {{ $loop->iteration }}
                                </td>

                                {{-- JUDUL --}}
                                <td class="fw-semibold">
                                    {{ $sesi->judul }}
                                </td>

                                {{-- JUMLAH ISI --}}
                                <td>
                                    {{ $sesi->items_count }}
                                </td>

                                {{-- AKSI --}}
                                <td class="text-center">
                                    <a href="{{ route('admin.sesiabsen.show', $sesi->id) }}"
                                       class="btn btn-sm btn-secondary me-1">
                                        Detail
                                    </a>

                                    <a href="{{ route('admin.sesiabsen.edit', $sesi->id) }}"
                                       class="btn btn-sm btn-warning me-1">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.sesiabsen.destroy', $sesi->id) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus sesi absen ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-danger">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

        </div>
    </div>

</div>
@endsection
