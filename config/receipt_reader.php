<?php

return [
    'enabled' => (bool) env('HELIOS_RECEIPT_READER_ENABLED', false),
    'driver' => env('HELIOS_RECEIPT_READER_DRIVER', 'ollama'),
    'url' => env('HELIOS_OLLAMA_URL', 'http://127.0.0.1:11435'),
    'model' => env('HELIOS_RECEIPT_MODEL', 'qwen3.5:4b'),
    'timeout_seconds' => (int) env('HELIOS_OLLAMA_TIMEOUT', 300),
    'max_file_bytes' => 20 * 1024 * 1024,
    'max_pages' => 6,
    'max_image_pixels' => 24_000_000,
    'max_encoded_bytes' => 32 * 1024 * 1024,
    'pdftoppm' => env('HELIOS_PDFTOPPM', 'pdftoppm'),
    'pdfinfo' => env('HELIOS_PDFINFO', 'pdfinfo'),
    'pdftotext' => env('HELIOS_PDFTOTEXT', 'pdftotext'),
];
