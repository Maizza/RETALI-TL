@extends('layouts.app')

@section('title', 'Data Absen Jamaah')

@section('content')

<style>
    .absen-card {
        background: #ffffff;
        border-radius: 14px;
        padding: 22px 26px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
        transition: .25s ease;
        border: 1px solid #f1f1f1;
    }

    .absen-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 22px rgba(0,0,0,0.14);
    }

    .absen-title {
        font-size: 18px;
        color: #222;
    }

    .absen-info {
        margin: 2px 0;
        font-size: 14px;
        color: #555;
    }

    .btn-tambah {
        background: #0d6efd;
        padding: 10px 18px;
        border-radius: 8px;
        color: white !important;
        font-weight: 500;
        transition: .2s;
    }

    .btn-tambah:hover {
        background: #0b5ed7;
    }

    .btn-detail {
        background: #198754;
        color: white !important;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        transition: .2s ease;
        text-decoration: none;
    }

    .btn-detail:hover {
        background: #157347;
    }

    .btn-hapus {
        background: #dc3545;
        color: white !important;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        border: none;
        transition: .2s ease;
    }

    .btn-hapus:hover {
        background: #bb2d3b;
    }
</style>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold">DATA ABSEN JAMAAH</h1>

        <a href="{{ route('jamaah.importForm') }}" class="btn btn-tambah">
            <i class="fa fa-plus me-1"></i> Tambah Absen
        </a>
    </div>

    {{-- ALERT --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- LIST ABSEN --}}
    @foreach ($absen as $item)
        <div class="absen-card">

            <div>
                <p class="absen-title fw-bold m-0">{{ $item->judul_absen }}</p>

                <p class="absen-info">
                    Tour Leader: <strong>{{ $item->tourleader->name ?? '-' }}</strong>
                </p>

                <p class="absen-info">
                    Jumlah Jamaah: <strong>{{ $item->jamaah_count ?? $item->jamaah()->count() }}</strong>
                </p>

                <p class="absen-info">
                    Sesi Absen: <strong>{{ ucfirst($item->sesi_absen) }}</strong>
                </p>
            </div>

            <div>
                <a href="{{ route('jamaah.detail', $item->id) }}" class="btn-detail me-2">
                    Detail
                </a>

                <form action="{{ route('jamaah.destroy', $item->id) }}"
                      method="POST"
                      style="display:inline;"
                      onsubmit="return confirm('Hapus absen ini beserta data jamaahnya?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn-hapus">Hapus</button>
                </form>
            </div>

        </div>
    @endforeach

    @if ($absen->count() == 0)
        <p class="text-muted mt-4">Belum ada absen jamaah.</p>
    @endif

</div>
@endsection
