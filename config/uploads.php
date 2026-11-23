<?php

return [
    /*
     * Maximum movie video upload size in kilobytes (150 MB).
     *
     * Three layers must agree for a large upload to succeed:
     *  - PHP: upload_max_filesize / post_max_size (see .user.ini for FPM,
     *    `php -d` or php.ini for the CLI server)
     *  - Livewire: config('livewire.temporary_file_upload.rules')
     *  - Filament: FileUpload::maxSize()
     */
    'movie_video_max_kb' => (int) env('MOVIE_VIDEO_MAX_KB', 150 * 1024),

    // Temporary uploads of 150 MB need more than Livewire's 5 minute default.
    'temporary_upload_max_minutes' => (int) env('UPLOAD_MAX_MINUTES', 30),

    // Cover/poster images: 5 MB, JPEG/PNG/WebP only.
    'image_max_kb' => (int) env('IMAGE_MAX_KB', 5 * 1024),
];
