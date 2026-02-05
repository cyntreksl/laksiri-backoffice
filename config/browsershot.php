<?php

return [
    /*
    |--------------------------------------------------------------------------
    | BrowsershotLambda Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for BrowsershotLambda PDF generation
    |
    */

    'timeout' => env('BROWSERSHOT_TIMEOUT', 120), // seconds
    
    'protocol_timeout' => env('BROWSERSHOT_PROTOCOL_TIMEOUT', 120000), // milliseconds
    
    'args' => [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage', // Overcome limited resource problems
        '--disable-gpu',
        '--single-process', // Reduce memory usage
    ],
    
    'pdf' => [
        'format' => 'A4',
        'landscape' => true,
        'margins' => [
            'top' => 10,
            'right' => 10,
            'bottom' => 10,
            'left' => 10,
        ],
        'print_background' => true,
    ],
    
    'limits' => [
        'max_records' => env('PDF_MAX_RECORDS', 500),
        'detailed_template_max' => env('PDF_DETAILED_MAX', 100),
    ],
];
