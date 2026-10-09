<?php

// Size and quality targets for uploaded files (App\Services\OptimizadorMedios).

return [

    // Images and covers are stored as WebP, scaled down to this longest side in pixels.
    'imagen_lado_maximo' => (int) env('MEDIA_IMAGE_MAX_SIDE', 2560),
    'portada_lado_maximo' => (int) env('MEDIA_COVER_MAX_SIDE', 1200),
    'calidad_webp' => (int) env('MEDIA_WEBP_QUALITY', 80),

    // Ghostscript preset for PDFs: screen (72 dpi), ebook (150 dpi) or printer (300 dpi).
    'pdf_calidad' => env('MEDIA_PDF_PRESET', 'ebook'),

    // Videos are re-encoded to H.264 MP4. The short side is capped (720 = 720p); a higher CRF is lighter.
    'video_lado_corto' => (int) env('MEDIA_VIDEO_SHORT_SIDE', 720),
    'video_crf' => (int) env('MEDIA_VIDEO_CRF', 27),

    'audio_kbps' => (int) env('MEDIA_AUDIO_KBPS', 128),

    // Longest a single PDF, video or audio conversion may run in the queue worker, in seconds.
    'tiempo_maximo' => (int) env('MEDIA_TIMEOUT', 3600),

];
