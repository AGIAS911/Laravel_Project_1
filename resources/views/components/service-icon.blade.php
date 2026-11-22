@props(['type' => null])

@php($icon = match ($type) {
    'transport' => 'fa-truck-moving',
    'workers' => 'fa-people-carry-box',
    'packing' => 'fa-box-open',
    'boxes' => 'fa-boxes-stacked',
    'assembly' => 'fa-screwdriver-wrench',
    default => 'fa-clipboard-list',
})

<i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
