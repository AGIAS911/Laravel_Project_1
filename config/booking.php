<?php

return [
    'osrm_base_url' => env('OSRM_BASE_URL', 'https://router.project-osrm.org'),
    'timezone' => 'Asia/Amman',
    'slot_minutes' => 120,
    'google_routes_key' => env('GOOGLE_ROUTES_API_KEY'),
    'deposit_percent' => env('BOOKING_DEPOSIT_PERCENT'),
    'commission_percent' => env('BOOKING_COMMISSION_PERCENT'),
    'deposit_minutes' => env('BOOKING_DEPOSIT_MINUTES'),
];
