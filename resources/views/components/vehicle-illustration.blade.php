@props(['name' => null, 'photo' => null])

@php
    $type = trim(preg_replace('/[^\p{L}\p{N}]+/u', ' ', mb_strtolower($name ?? '')));
    $drawing = match (true) {
        (bool) preg_match('/\b(?:pickup|pick up)\b|بيك أب|بيك اب/u', $type) => 'driver-vehicle-pickup.svg',
        (bool) preg_match('/\b(?:medium truck|midsize truck|mid size truck)\b|شاحنة متوسطة/u', $type) => 'vehicle-medium-truck.svg',
        (bool) preg_match('/\b(?:box truck|large truck|heavy truck|moving truck|lorry)\b|شاحنة كبيرة/u', $type) => 'driver-vehicle-truck.svg',
        (bool) preg_match('/\b(?:long wheelbase|longwheelbase|long van|extended|lwb)\b|فان طويل/u', $type) => 'vehicle-long-van.svg',
        (bool) preg_match('/\b(?:compact|small van|mini van|minivan)\b|فان صغير/u', $type) => 'vehicle-compact-van.svg',
        (bool) preg_match('/\bvan\b|فان/u', $type) => 'vehicle-enclosed-van.svg',
        default => 'driver-vehicle-truck.svg',
    };
@endphp

<img src="{{ asset($photo ?: 'landing/img/'.$drawing) }}" alt="" width="148" height="76"
    {{ $attributes->class(['vehicle-illustration', 'is-photo' => $photo]) }}>
