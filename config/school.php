<?php
return [
    'code' => env('SCHOOL_CODE', 'SCHOOL001'),
    'name' => env('SCHOOL_NAME', 'Mon École'),
    'address' => env('SCHOOL_ADDRESS', ''),
    'phone' => env('SCHOOL_PHONE', ''),
    'email' => env('SCHOOL_EMAIL', ''),
    
    'card' => [
        'background' => env('CARD_BACKGROUND_PATH', 'assets/images/carte.png'),
        'width' => 85,
        'height' => 55,
        'cards_per_row' => 2,
        'cards_per_column' => 5,
    ],
    
    'photo' => [
        'width' => 800,
        'height' => 1000,
        'photo_section_height' => 800,
        'quality' => 90,
    ],
    
    'allowed_years' => [
        '2023-2024',
        '2024-2025',
        '2025-2026',
    ],
];

?>