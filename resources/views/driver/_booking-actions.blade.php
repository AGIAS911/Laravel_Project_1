@php
    $actions = match ($booking->status) {
        'pending' => ['accept' => 'Accept request', 'reject' => 'Decline request'],
        'confirmed' => ['start' => 'Start move'],
        'in_progress' => ['complete' => 'Complete move'],
        default => [],
    };
@endphp

<div class="d-flex flex-wrap align-items-end gap-3">
    @foreach ($actions as $action => $label)
        <form data-workflow-form method="POST" action="{{ route('driver.bookings.' . $action, $booking, false) }}">
            @csrf @method('PATCH')
            @if ($action === 'reject')
                <label class="form-label" for="rejection_reason">Reason</label>
                <input class="form-control mb-2" id="rejection_reason" name="rejection_reason" required maxlength="1000">
            @endif
            <button class="btn {{ $action === 'reject' ? 'btn-outline-danger' : 'btn-primary' }}">{{ $label }}</button>
        </form>
    @endforeach
</div>
