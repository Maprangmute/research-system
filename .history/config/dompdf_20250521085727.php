<!-- <?php

return [
    'show_warnings' => false,
    'orientation' => 'portrait',
    'defines' => [
        "DOMPDF_FONT_DIR" => storage_path('fonts/'),
        "DOMPDF_FONT_CACHE" => storage_path('fonts/'),
    ],

    'font_dir' => storage_path('fonts/'),
    'font_cache' => storage_path('fonts/'),

    'default_font' => 'thsarabun',

    'custom_font_dir' => storage_path('fonts/'),
    'custom_font_data' => [
        'thsarabun' => [
            'R' => 'THSarabunNew.ttf',
            'B' => 'THSarabunNew-Bold.ttf', // ถ้ามี Bold
            'useOTL' => 0xFF,
            'useKashida' => 75,
        ],
    ],
];
