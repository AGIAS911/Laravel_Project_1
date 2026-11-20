<?php

return [
    'osrm_base_url' => env('OSRM_BASE_URL', 'https://router.project-osrm.org'),
    'deposit_percent' => env('BOOKING_DEPOSIT_PERCENT'),
    'commission_percent' => env('BOOKING_COMMISSION_PERCENT'),
    'deposit_minutes' => env('BOOKING_DEPOSIT_MINUTES'),
];
