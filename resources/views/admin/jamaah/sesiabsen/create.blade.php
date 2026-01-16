@extends('layouts.app')

@section('title', 'Tambah Sesi Absen')

@section('content')
<div class="container-fluid">

    <h4 class="fw-bold mb-4">Tambah Sesi Absen</h4>

    <form action="{{ route('admin.sesiabsen.store') }}" method="POST">
        @csrf

        {{-- JUDUL --}}
        <div class="card mb-4">
            <div class="card-body">
                <label class="form-label fw-semibold">Judul Sesi</label>

                <input type="text"
                       name="judul"
                       id="judul"
                       class="form-control"
                       placeholder="Pesawat / Bus">
            </div>
        </div>

        {{-- ISI --}}
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Isi Sesi Absen</h6>

                <div id="items-wrapper"></div>
            </div>
        </div>

        {{-- ACTION --}}
        <div class="d-flex justify-content-between">
            <a href="{{ route('admin.sesiabsen.index') }}" class="btn btn-light">
                Kembali
            </a>

            <div class="d-flex gap-2">
                <button type="button" id="btnTambahIsi" class="btn btn-primary">
                    + Tambah isi
                </button>

                <button type="submit" id="btnSelesai" class="btn btn-success" disabled>
                    Selesai
                </button>
            </div>
        </div>

    </form>
</div>

{{-- ========================= --}}
{{-- SCRIPT LANGSUNG (ANTI FAIL) --}}
{{-- ========================= --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    let index = 0;

    const judul = document.getElementById('judul');
    const btnTambahIsi = document.getElementById('btnTambahIsi');
    const btnSelesai = document.getElementById('btnSelesai');
    const wrapper = document.getElementById('items-wrapper');

    function validateForm() {
        const judulFilled = judul.value.trim() !== '';
        const items = document.querySelectorAll('.isi-input');
        const hasItem = items.length > 0;
        const allFilled = [...items].every(i => i.value.trim() !== '');

        btnSelesai.disabled = !(judulFilled && hasItem && allFilled);
    }

    btnTambahIsi.addEventListener('click', function () {
        index++;

        const div = document.createElement('div');
        div.classList.add('mb-3');

        div.innerHTML = `
            <label class="form-label">Isi ${index}</label>
            <input type="text"
                   name="items[]"
                   class="form-control isi-input"
                   placeholder="Silakan isi">
        `;

        wrapper.appendChild(div);

        document.querySelectorAll('.isi-input')
            .forEach(i => i.addEventListener('input', validateForm));

        validateForm();
    });

    judul.addEventListener('input', validateForm);
});
</script>
@endsection
