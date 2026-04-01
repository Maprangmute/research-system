<?php

return [
    'mode' => 'utf-8',
    'format' => 'A4',
    'default_font_size' => 16,
    'default_font' => 'thsarabun',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 10,
    'margin_bottom' => 10,
    'tempDir' => storage_path('app/mpdf-temp'),
    'font_path' => resource_path('fonts/'),
    'font_data' => [
        'thsarabun' => [
            'R' => 'THSarabunNew.ttf',
            'B' => 'THSarabunNew-Bold.ttf',
        ]
    ],
];
