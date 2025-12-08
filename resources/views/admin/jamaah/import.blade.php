@extends('layouts.app')

@section('title', 'Import Jamaah dari Excel')

@section('content')

<style>
    /* CARD STYLING */
    .card-modern {
        border: none;
        border-radius: 18px;
        padding: 25px;
        background: #ffffff;
        box-shadow: 0 4px 18px rgba(0,0,0,0.07);
    }

    /* INPUT / SELECT */
    .form-control {
        border-radius: 10px;
        padding: 10px 14px;
        border: 1px solid #d0d7e2;
        transition: 0.25s;
    }
    .form-control:focus {
        border-color: #4263eb;
        box-shadow: 0 0 0 3px rgba(66,99,235,0.15);
    }

    /* BUTTON BACK */
    .btn-back {
        background: #6c7a91;
        border: none;
        padding: 10px 18px;
        border-radius: 10px;
        font-weight: 600;
        color: #fff !important;
        text-decoration: none !important;
        transition: 0.2s;
    }
    .btn-back:hover {
        background: #596579;
    }

    /* HEADER INFO */
    .excel-info {
        background: #f3f6ff;
        padding: 10px 14px;
        border-radius: 10px;
        border-left: 4px solid #4c6ef5;
        font-size: 14px;
    }

    /* SUBMIT */
    .btn-submit {
        background: #3b5bdb;
        padding: 12px 18px;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        transition: 0.2s;
    }
    .btn-submit:hover {
        background: #304ecc;
    }
</style>


<div class="container-fluid">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold">Import Jamaah dari Excel</h1>

        <a href="{{ route('jamaah.index') }}" class="btn-back">
            ‹ Kembali ke Data Absen
        </a>
    </div>

    {{-- SUCCESS --}}
    @if (session('success'))
        <div class="alert alert-success rounded-3 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- ERROR IMPORT --}}
    @if (session('import_errors'))
        <div class="alert alert-warning rounded-3 shadow-sm">
            <strong>Beberapa baris dilewati:</strong>
            <ul class="mb-0">
                @foreach (session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <!-- FORM CARD -->
    <div class="card-modern">

        <form action="{{ route('jamaah.import') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- JUDUL ABSEN --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Judul Absen</label>
                <input type="text"
                       name="judul_absen"
                       class="form-control"
                       value="{{ old('judul_absen') }}"
                       placeholder="Contoh: Absen Keberangkatan 20 Desember"
                       required>
            </div>

            {{-- INFO FORMAT --}}
            <div class="excel-info mb-3">
                Format header Excel:
                <code class="text-primary">
                    nama_jamaah, no_paspor, no_hp, jenis_kelamin, tanggal_lahir, kode_kloter, nomor_bus, keterangan
                </code>
            </div>

            {{-- FILE --}}
            <div class="mb-3">
                <label class="form-label fw-bold">File Excel (.xlsx / .xls / .csv)</label>
                <input type="file" name="file" class="form-control" required>
            </div>

            {{-- TOUR LEADER --}}
            <div class="mb-3 mt-4">
                <label class="form-label fw-bold">Pilih Tour Leader</label>
                <select name="tourleader_id" class="form-control" required>
                    <option value="">-- Pilih Tour Leader --</option>
                    @foreach($tourleaders as $tl)
                        <option value="{{ $tl->id }}">{{ $tl->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- SESI ABSEN --}}
            <div class="mb-3 mt-3">
                <label class="form-label fw-bold">Sesi Absen</label>
                <select name="sesi_absen" class="form-control" required>
                    <option value="">-- Pilih Sesi Absen --</option>
                    <option value="pagi">Pagi</option>
                    <option value="siang">Siang</option>
                    <option value="malam">Malam</option>
                </select>
            </div>

            {{-- SUBMIT --}}
            <button class="btn-submit text-white mt-3">
                Upload & Import
            </button>

        </form>

    </div>

</div>
@endsection
