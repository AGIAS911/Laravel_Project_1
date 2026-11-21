<?php

return [
    'items' => json_decode(
        file_get_contents(__DIR__.'/../ai-service/inventory-catalog.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    ),
    // Indicative weight (kg) and packed volume (m³) per item.
    'estimates' => [
        'sofa' => [65, 2.2],
        'bed' => [60, 1.65],
        'wardrobe' => [65, 1.7],
        'refrigerator' => [65, 0.85],
        'washing machine' => [65, 0.38],
        'dining table' => [40, 1.1],
        'chair' => [7, 0.24],
        'tv' => [15, 0.16],
        'desk' => [28, 0.66],
        'mirror' => [10, 0.10],
        'microwave' => [15, 0.08],
        'oven' => [45, 0.35],
        'laptop' => [3, 0.02],
        'potted plant' => [12, 0.15],
        'bench' => [24, 0.55],
        'suitcase' => [16, 0.13],
        'keyboard' => [1, 0.01],
        'toaster' => [3, 0.03],
        'book' => [1, 0.004],
        'clock' => [2, 0.03],
        'vase' => [3, 0.04],
        'teddy bear' => [1, 0.03],
    ],
    'size_limits' => [
        'small' => [300, 4],
        'medium' => [900, 12],
        'large' => [2500, 30],
    ],
];
