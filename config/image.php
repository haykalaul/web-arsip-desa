<?php
return [
    'driver' => env('IMAGE_DRIVER', 'imagick'),
    'imagick' => [
        'thread_limit' => env('IMAGICK_THREAD_LIMIT', 1),
        'format' => [
            'jpeg' => 85,
            'png' => 9,
            'webp' => 85,
        ]
    ]
];
