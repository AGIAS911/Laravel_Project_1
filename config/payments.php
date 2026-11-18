<?php

// CliQ beneficiary details shown to the customer when paying the deposit.
// Final values are an operational decision; set them in .env.
return [
    'clik_name' => env('CLIK_BENEFICIARY_NAME', 'MoveIt'),
    'clik_id' => env('CLIK_ID', 'MOVEIT'),
    'currency' => 'JOD',
];
