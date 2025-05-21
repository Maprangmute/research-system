<?php

return [
    'show_warnings' => false,
    'orientation' => 'portrait',
    'defines' => [
        "DOMPDF_FONT_DIR" => storage_path('fonts/'),
        "DOMPDF_FONT_CACHE" => storage_path('fonts/'),
    ],
    'font_dir' => storage_path('fonts/'),
    'font_cache' => storage_path('fonts/'),

    'default_font' => 'thsarabun', // ✅ ตั้งค่าฟอนต์เริ่มต้นเป็น thsarabun

    'custom_font_dir' => storage_path('fonts/'), // ✅ ตำแหน่งฟอนต์เอง
    'custom_font_data' => [
        'thsarabun' => [
            'R' => 'THSarabunNew-Bold.ttf', // Regular
            'B' => 'THSarabunNew.ttf', // Bold
            'useOTL' => 0xFF,
            'useKashida' => 75,
        ],
    ],
];
