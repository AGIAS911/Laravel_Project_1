@php
    $tone = match ($state) {
        'confirmed', 'approved', 'active' => 'success',
        'maintenance', 'pending' => 'warning',
        'rejected', 'cancelled', 'expired', 'awaiting_deposit' => 'danger',
        'in_progress' => 'info',
        default => 'secondary',
    };
@endphp
<span @class(['badge rounded-pill', "text-{$tone}-emphasis bg-{$tone}-subtle", 'admin-completed' => $state === 'completed', 'badge-with-icon' => !empty($icon)])>
    @if (!empty($icon))<i class="fa-regular {{ $icon }} me-1" aria-hidden="true"></i>@endif
    {{ $label ?? str($state)->headline() }}
</span>
