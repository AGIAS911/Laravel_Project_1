@php
    $tone = match ($state) {
        'confirmed', 'completed', 'approved' => 'success',
        'awaiting_deposit', 'pending' => 'warning',
        'rejected', 'cancelled', 'expired' => 'danger',
        'in_progress' => 'info',
        default => 'secondary',
    };
@endphp
<span class="badge rounded-pill text-{{ $tone }}-emphasis bg-{{ $tone }}-subtle">{{ $label ?? str($state)->headline() }}</span>
