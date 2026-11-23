<?php

return [
    'temporary_file_upload' => [
        'disk' => null,
        'directory' => null,
        // config/uploads.php is loaded after this file, so read the env default here.
        'rules' => ['file', 'max:' . (int) env('MOVIE_VIDEO_MAX_KB', 150 * 1024)],
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4', 'mov', 'avi', 'wmv', 'mp3',
            'm4a', 'jpg', 'jpeg', 'mpga', 'webp', 'wma', 'webm',
        ],
        'max_upload_time' => (int) env('UPLOAD_MAX_MINUTES', 30),
        'cleanup' => true,
        'directory_visibility' => null,
    ],
];
