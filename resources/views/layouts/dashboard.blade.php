use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\BookingService;
use App\Models\ScanSession;
use App\Models\InventoryItem;
use App\Models\Payment;
use App\Models\Review;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
@extends('layouts.app')

@section('body')
    <div class="container">
        <div class="row g-4">
            @hasSection('sidebar')
                <aside class="col-md-3">
                    <h2 class="h6">@yield('workspace-title', __('Dashboard'))</h2>
                    @yield('sidebar')
                </aside>
            @endif

            <section class="col">
                <header class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                    <h1 class="h3 mb-0">@yield('page-title', __('Dashboard'))</h1>
                    @yield('actions')
                </header>

                @if (session('success'))
                    <div class="alert alert-success" role="status">{{ session('success') }}</div>
                @endif

                @yield('content')
            </section>
        </div>
    </div>
@endsection
