@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <h4 class="fw-bold mb-4">Edit Sesi Absen</h4>

    <form action="{{ route('admin.sesiabsen.update', $sesiAbsen->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Judul --}}
        <div class="card mb-4">
            <div class="card-body">
                <label class="form-label fw-semibold">Judul Sesi</label>
                <input type="text"
                       name="judul"
                       class="form-control"
                       value="{{ $sesiAbsen->judul }}">
            </div>
        </div>

        {{-- Isi --}}
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Isi Sesi Absen</h6>

                <div id="items-wrapper">
                    @foreach ($sesiAbsen->items as $i => $item)
    <div class="mb-3">
        <label class="form-label">Isi {{ $i + 1 }}</label>

        {{-- ID ITEM (WAJIB) --}}
        <input type="hidden"
               name="items[{{ $i }}][id]"
               value="{{ $item->id }}">

        {{-- ISI --}}
        <input type="text"
               name="items[{{ $i }}][isi]"
               class="form-control"
               value="{{ $item->isi }}"
               required>
    </div>
@endforeach

                </div>
            </div>
        </div>

        {{-- Action --}}
        <div class="d-flex justify-content-between">
            <a href="{{ route('admin.sesiabsen.index') }}" class="btn btn-light">
                Kembali
            </a>

            <button class="btn btn-success">
                Simpan Perubahan
            </button>
        </div>

    </form>
</div>
@endsection
