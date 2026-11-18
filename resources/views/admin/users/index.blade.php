@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">إدارة السائقين</h1>

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="card mb-4">
            <div class="card-header">طلبات السائقين المعلقة</div>
            <div class="list-group list-group-flush">
                @forelse ($pendingDrivers as $driver)
                    <div class="list-group-item d-flex justify-content-between align-items-center gap-3">
                        <div>
                            <strong>{{ $driver->name }}</strong>
                            <div class="text-muted">{{ $driver->email }}</div>
                        </div>
                        <form method="POST" action="{{ route('admin.users.update', $driver) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="approve_driver" value="1">
                            <button class="btn btn-success" type="submit">موافقة</button>
                        </form>
                    </div>
                @empty
                    <div class="list-group-item text-muted">لا توجد طلبات معلقة.</div>
                @endforelse
            </div>
        </section>

        <section class="card">
            <div class="card-header">السائقون النشطون</div>
            <div class="list-group list-group-flush">
                @forelse ($activeDrivers as $driver)
                    <div class="list-group-item">
                        <strong>{{ $driver->name }}</strong>
                        <span class="text-muted">{{ $driver->email }}</span>
                    </div>
                @empty
                    <div class="list-group-item text-muted">لا يوجد سائقون نشطون.</div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
