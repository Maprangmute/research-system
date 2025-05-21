<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Show Warnings
    |--------------------------------------------------------------------------
    |
    | Set to true to show warnings while rendering PDF files.
    |
    */
    'show_warnings' => false,

    /*
    |--------------------------------------------------------------------------
    | Orientation
    |--------------------------------------------------------------------------
    |
    | Set the default paper orientation.
    |
    | Possible values are: 'portrait' and 'landscape'
    |
    */
    'orientation' => 'portrait',

    /*
    |--------------------------------------------------------------------------
    | Defines
    |--------------------------------------------------------------------------
    |
    | The values here will be passed directly to Dompdf::define()
    |
    */
    'defines' => [
        "DOMPDF_FONT_DIR" => storage_path('fonts/'),
        "DOMPDF_FONT_CACHE" => storage_path('fonts/'),
        "DOMPDF_TEMP_DIR" => sys_get_temp_dir(),
        "DOMPDF_CHROOT" => base_path(),
        "DOMPDF_ENABLE_REMOTE" => true,
        "DOMPDF_LOG_OUTPUT_FILE" => storage_path('logs/dompdf.html'),
        "DOMPDF_ENABLE_PHP" => false,
        "DOMPDF_ENABLE_JAVASCRIPT" => false,
        "DOMPDF_DEBUG_PNG" => false,
        "DOMPDF_DEBUG_KEEP_TEMP" => false,
        "DOMPDF_DEBUG_CSS" => false,
        "DOMPDF_DEFAULT_MEDIA_TYPE" => "screen",
        "DOMPDF_DEFAULT_PAPER_SIZE" => "a4",
        "DOMPDF_DEFAULT_FONT" => "thsarabun",
        "DOMPDF_DPI" => 96,
        "DOMPDF_ENABLE_HTML5PARSER" => true,
        "DOMPDF_ENABLE_FONTSUBSETTING" => false,
        "DOMPDF_ADMIN_USERNAME" => 'user',
        "DOMPDF_ADMIN_PASSWORD" => 'password',
    ],

    /*
    |--------------------------------------------------------------------------
    | Font Folder
    |--------------------------------------------------------------------------
    |
    | The location where custom fonts will be stored.
    |
    */
    'font_dir' => storage_path('fonts/'),

    /*
    |--------------------------------------------------------------------------
    | Font Cache
    |--------------------------------------------------------------------------
    |
    | The location where Dompdf will store its font cache
    |
    */
    'font_cache' => storage_path('fonts/'),

    /*
    |--------------------------------------------------------------------------
    | Default Font
    |--------------------------------------------------------------------------
    |
    | What font to use if DOMPDF can't find the requested one
    |
    */
    'default_font' => 'thsarabun',

    /*
    |--------------------------------------------------------------------------
    | Custom Fonts
    |--------------------------------------------------------------------------
    |
    | Register custom fonts to be used in dompdf.
    |
    */
    'custom_font_dir' => storage_path('fonts/'),
    'custom_font_data' => [
        'thsarabun' => [
            'R' => 'THSarabunNew.ttf',
            'B' => 'THSarabunNew-Bold.ttf',
            'useOTL' => 0xFF,
            'useKashida' => 75,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Enable HTML5 Parser
    |--------------------------------------------------------------------------
    |
    | Whether to enable parsing of HTML5 elements.
    |
    */
    'enable_html5_parser' => true,
];
