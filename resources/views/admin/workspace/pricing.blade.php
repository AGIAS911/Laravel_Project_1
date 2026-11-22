@extends('layouts.dashboard')

@section('page-title', 'Time rules')
@section('page-description', 'Time rules cannot overlap for the same day and period.')

@section('actions')
    <a class="btn btn-primary" href="#new-pricing-rule">Add time rule</a>
@endsection

@section('content')
    <p data-workflow-status role="status"></p>
    <div class="table-responsive mb-4">
        <table class="table align-middle mb-0">
            <thead><tr><th scope="col">Rule</th><th scope="col">Day</th><th scope="col">Period</th><th scope="col">Multiplier</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
            <tbody>
                @forelse ($rules as $rule)
                    <tr>
                        <th scope="row">{{ $rule->name }}</th>
                        <td>{{ ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$rule->day_of_week] ?? 'Every day' }}</td>
                        <td class="text-nowrap">{{ substr($rule->start_time, 0, 5) }} – {{ substr($rule->end_time, 0, 5) }}</td>
                        <td>{{ $rule->multiplier }}×</td>
                        <td>@include('booking._status', ['state' => $rule->is_active ? 'approved' : 'draft', 'label' => $rule->is_active ? 'Active' : 'Inactive'])</td>
                        <td class="text-end"><a class="btn btn-outline-secondary btn-sm" href="#edit-rule-{{ $rule->id }}">Edit</a></td>
                    </tr>
                @empty
                    <tr><td class="text-center py-4" colspan="6">No time rules yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $rules->links('pagination::bootstrap-5') }}

    <details id="new-pricing-rule" class="admin-rule-editor mb-3">
        <summary class="fw-semibold py-2">Add a pricing rule</summary>
        @include('admin.workspace.pricing-form', ['rule' => null])
    </details>
    @foreach ($rules as $rule)
        <details id="edit-rule-{{ $rule->id }}" class="admin-rule-editor mb-2">
            <summary class="fw-semibold py-2">Edit {{ $rule->name }}</summary>
            @include('admin.workspace.pricing-form', ['rule' => $rule])
        </details>
    @endforeach

    <script src="{{ asset('js/booking-workflow.js') }}" defer></script>
@endsection
