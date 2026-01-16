@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Daftar Muthawif</h2>
        <a href="{{ route('muthawif.create') }}" class="btn btn-primary">
            + Tambah Muthawif
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: #2c3e50; color: white;">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Kloter</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($muthawifs as $muthawif)
                            <tr>
                                <td class="px-4 py-3">{{ $muthawif->nama }}</td>
                                <td class="px-4 py-3">{{ $muthawif->email }}</td>
                                <td class="px-4 py-3">
                                    @if($muthawif->kloter)
                                        <div>
                                            <strong>{{ $muthawif->kloter->nama }}</strong>
                                        </div>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($muthawif->kloter->tgl_berangkat)->format('d M Y') }} -
                                            {{ \Carbon\Carbon::parse($muthawif->kloter->tgl_pulang)->format('d M Y') }}
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('muthawif.edit', $muthawif->id) }}"
                                       class="btn btn-warning btn-sm">Edit</a>

                                    <form action="{{ route('muthawif.destroy', $muthawif->id) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus muthawif ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <em class="text-muted">Belum ada data muthawif</em>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
