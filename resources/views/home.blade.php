@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('You are logged in!') }}
                </div>
            </div>

            <!-- Tampilkan Daftar Notifikasi -->
            <div class="card mt-4">
                <div class="card-header">{{ __('Notifikasi') }}</div>

                <div class="card-body">
                    <ul class="list-group">
                        @forelse($notifications as $notification)
                            <li class="list-group-item">
                                <h5>{{ $notification->title }}</h5>
                                <p>{{ \Str::limit($notification->message, 100) }}</p>
                                <a href="{{ route('notification.show', $notification->id) }}" class="btn btn-link">Baca Selengkapnya</a>
                            </li>
                        @empty
                            <li class="list-group-item">
                                {{ __('Tidak ada notifikasi saat ini.') }}
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
