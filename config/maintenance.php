<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Maintenance Page Background Video
    |--------------------------------------------------------------------------
    |
    | Optional URL to a video file (e.g. MP4) played in a loop on the
    | maintenance page. The video is blurred. Leave null to disable.
    | Default: /videos/maintenance-bg.mp4 (place file in public/videos/).
    |
    */

    'background_video_url' => env('MAINTENANCE_BACKGROUND_VIDEO_URL', null),

];
