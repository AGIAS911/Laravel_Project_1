@extends('vehicle-types.layout')

@section('title', 'إدارة أنواع المركبات')

@section('content')
    <div class="panel">
        <div class="actions" style="justify-content: space-between">
            <h1>أنواع المركبات</h1>
            <a class="button" href="{{ route('vehicle-types.create') }}">إضافة نوع مركبة</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>الحالة</th>
                        <th>عدد المركبات</th>
                        <th>الصورة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vehicleTypes as $vehicleType)
                        <tr>
                            <td>{{ $vehicleType->name }}</td>
                            <td>{{ $vehicleType->is_active ? 'نشط' : 'غير نشط' }}</td>
                            <td>{{ $vehicleType->vehicles_count }}</td>
                            <td>
                                @if ($vehicleType->image)
                                    <img class="thumbnail" src="{{ asset($vehicleType->image) }}" alt="صورة {{ $vehicleType->name }}">
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="button secondary" href="{{ route('vehicle-types.show', $vehicleType) }}">عرض</a>
                                    <a class="button" href="{{ route('vehicle-types.edit', $vehicleType) }}">تعديل</a>
                                    @if ($vehicleType->vehicles_count === 0)
                                        <form method="POST" action="{{ route('vehicle-types.destroy', $vehicleType) }}" onsubmit="return confirm('هل تريد حذف نوع المركبة؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="button danger" type="submit">حذف</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">لا توجد أنواع مركبات حتى الآن.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $vehicleTypes->links() }}
    </div>
@endsection
