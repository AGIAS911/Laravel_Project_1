@extends('vehicle-types.layout')

@section('title', 'إدارة المركبات')

@section('content')
    <section class="panel">
        <div class="actions" style="justify-content: space-between">
            <h1>المركبات</h1>
            <a class="button" href="{{ route('vehicles.create') }}">إضافة مركبة</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>المركبة</th>
                        <th>رقم اللوحة</th>
                        <th>النوع</th>
                        <th>السائق</th>
                        <th>الحالة</th>
                        <th>الصورة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vehicles as $vehicle)
                        <tr>
                            <td>{{ $vehicle->name }}</td>
                            <td>{{ $vehicle->plate_number }}</td>
                            <td>{{ $vehicle->vehicleType?->name ?? '—' }}</td>
                            <td>{{ $vehicle->driver?->license_number ?? '—' }}</td>
                            <td>
                                @switch($vehicle->status)
                                    @case('active') نشطة @break
                                    @case('inactive') غير نشطة @break
                                    @case('maintenance') تحت الصيانة @break
                                    @default {{ $vehicle->status }}
                                @endswitch
                            </td>
                            <td>
                                @if ($vehicle->image)
                                    <img class="thumbnail" src="{{ asset($vehicle->image) }}" alt="صورة {{ $vehicle->name }}">
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="button secondary" href="{{ route('vehicles.show', $vehicle) }}">عرض</a>
                                    <a class="button" href="{{ route('vehicles.edit', $vehicle) }}">تعديل</a>
                                    <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}" onsubmit="return confirm('هل تريد حذف المركبة؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button danger" type="submit">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">لا توجد مركبات حتى الآن.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $vehicles->links() }}
    </section>
@endsection
