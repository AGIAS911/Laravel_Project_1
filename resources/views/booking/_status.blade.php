@php
    $tone = match ($state) {
        'confirmed', 'approved', 'active' => 'success',
        'maintenance', 'pending' => 'warning',
        'rejected', 'cancelled', 'expired', 'awaiting_deposit' => 'danger',
        'in_progress' => 'info',
        default => 'secondary',
    };
@endphp
<span @class(['product-status', "product-status-{$tone}", 'badge-with-icon' => !empty($icon)])>
    @if (!empty($icon))<i class="fa-regular {{ $icon }} me-1 text-secondary-emphasis" aria-hidden="true"></i>@endif
    {{ $label ?? str($state)->headline() }}
</span>
