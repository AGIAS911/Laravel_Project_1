@extends('vehicle-types.layout')

@section('title', $vehicle->name)

@section('content')
    <section class="panel">
        <h1>{{ $vehicle->name }}</h1>
        @if ($vehicle->image)
            <p><img src="{{ asset($vehicle->image) }}" alt="صورة {{ $vehicle->name }}" style="max-width: 420px; max-height: 300px"></p>
        @endif

        <p><strong>رقم اللوحة:</strong> {{ $vehicle->plate_number }}</p>
        <p><strong>نوع المركبة:</strong> {{ $vehicle->vehicleType?->name ?? '—' }}</p>
        <p><strong>رقم رخصة السائق:</strong> {{ $vehicle->driver?->license_number ?? '—' }}</p>
        <p><strong>السعة بالوزن:</strong> {{ ['small' => 'صغير', 'medium' => 'متوسط', 'large' => 'كبير'][$vehicle->capacity_kg] ?? $vehicle->capacity_kg }}</p>
        <p><strong>السعة بالحجم:</strong> {{ ['small' => 'صغير', 'medium' => 'متوسط', 'large' => 'كبير'][$vehicle->capacity_m3] ?? $vehicle->capacity_m3 }}</p>
        <p><strong>السعر الأساسي:</strong> {{ number_format((float) $vehicle->base_price, 3) }}</p>
        <p><strong>السعر لكل كيلومتر:</strong> {{ number_format((float) $vehicle->price_per_km, 3) }}</p>
        <p><strong>الحالة:</strong>
            @switch($vehicle->status)
                @case('active') نشطة @break
                @case('inactive') غير نشطة @break
                @case('maintenance') تحت الصيانة @break
                @default {{ $vehicle->status }}
            @endswitch
        </p>

        <div class="actions">
            <a class="button" href="{{ route('vehicles.edit', $vehicle) }}">تعديل</a>
            <a class="button secondary" href="{{ route('vehicles.index') }}">رجوع للقائمة</a>
            <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}" onsubmit="return confirm('هل تريد حذف المركبة؟')">
                @csrf
                @method('DELETE')
                <button class="button danger" type="submit">حذف</button>
            </form>
        </div>
    </section>
@endsection
