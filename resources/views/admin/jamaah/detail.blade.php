@extends('layouts.app')

@section('title', 'Detail Absen Jamaah')

@section('content')

<style>
    /* Header Box */
    .detail-header {
        background: #ffffff;
        padding: 20px 25px;
        border-radius: 14px;
        border: 1px solid #e6e6e6;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        margin-bottom: 25px;
    }

    /* Title */
    .detail-header h1 {
        font-weight: 700;
        color: #1a2b49;
    }

    /* Label text */
    .detail-header p {
        font-size: 14px;
        margin-bottom: 6px;
        color: #555;
    }

    .detail-header strong {
        color: #1a2b49;
    }

    /* Button kembali */
    .btn-kembali {
        background: #6c7a91;
        padding: 9px 16px;
        border-radius: 8px;
        font-size: 14px;
        color: #fff !important;
        text-decoration: none !important;   /* Hilangkan garis */
        transition: 0.2s ease;
    }
    
    .btn-kembali:hover {
        background: #5a6679;
        text-decoration: none !important;   /* Pastikan hover juga tidak garis */
    }

    /* Table Wrapper */
    .table-card {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e3e3e3;
        box-shadow: 0 4px 16px rgba(0,0,0,0.07);
    }

    /* Table header */
    .table thead th {
        background: linear-gradient(90deg, #113b7a, #1c559c);
        color: #ffffff;
        font-size: 14px;
        font-weight: 600;
    }

    /* Table cells */
    .table td {
        vertical-align: middle;
        font-size: 14px;
    }
</style>

<div class="container-fluid">

    {{-- HEADER DETAIL --}}
    <div class="detail-header d-flex justify-content-between align-items-start">

        <div>
            <h1 class="h3 fw-bold mb-3">Detail: {{ $absen->judul_absen }}</h1>

            <p>Tour Leader: <strong>{{ $absen->tourleader->name ?? '-' }}</strong></p>
            <p>Jumlah Jamaah: <strong>{{ $jamaah->total() }}</strong></p>
            <p>Sesi Absen: <strong>{{ ucfirst($absen->sesi_absen) }}</strong></p>
        </div>

        <a href="{{ route('jamaah.index') }}" class="btn-kembali">
            « Kembali
        </a>
    </div>

    {{-- TABEL --}}
    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Jamaah</th>
                        <th>No. Paspor</th>
                        <th>No HP</th>
                        <th>JK</th>
                        <th>Tgl Lahir</th>
                        <th>Kloter</th>
                        <th>Bus</th>
                        <th>Keterangan</th>
                        <th>Status Hadir</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($jamaah as $i => $j)
                        <tr>
                            <td>{{ $jamaah->firstItem() + $i }}</td>
                            <td>{{ $j->nama_jamaah }}</td>
                            <td>{{ $j->no_paspor ?? '-' }}</td>
                            <td>{{ $j->no_hp ?? '-' }}</td>
                            <td>{{ $j->jenis_kelamin ?? '-' }}</td>
                            <td>{{ $j->tanggal_lahir ? \Carbon\Carbon::parse($j->tanggal_lahir)->format('d-m-Y') : '-' }}</td>
                            <td>{{ $j->kode_kloter ?? '-' }}</td>
                            <td>{{ $j->nomor_bus ?? '-' }}</td>
                            <td>{{ $j->keterangan ?? '-' }}</td>

                            <td>
                                @php $status = $j->latestAttendance->status ?? 'BELUM_ABSEN'; @endphp

                                @if($status === 'HADIR')
                                    <span class="badge bg-success">Hadir</span>
                                @elseif($status === 'TIDAK_HADIR')
                                    <span class="badge bg-danger">Tidak Hadir</span>
                                @else
                                    <span class="badge bg-secondary">Belum Absen</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>

        <div class="p-3 bg-white">
            {{ $jamaah->links() }}
        </div>
    </div>

</div>

@endsection
