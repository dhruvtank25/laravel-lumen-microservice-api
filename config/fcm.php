<?php

return [
    'driver' => env('FCM_PROTOCOL', 'http'),
    'log_enabled' => false,

    'http' => [
        'server_key' => env('FCM_SERVER_KEY', 'AAAAKNqCRbc:APA91bHIYoJTe9rCFZ8lm8n9fPkMg2LLYqK6K-xK7AzCQolxRKEnXq3kNfc4RRofSjGjjFcRmAsEgCRQ1sKjzYpVYy9cQReNiYI0baxsddNxDjq0f_13gWFEo-rhnRYkE3zNaYra3zJc'),
        'sender_id' => env('FCM_SENDER_ID', '175464662455'),
        'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
        'server_group_url' => 'https://android.googleapis.com/gcm/notification',
        'timeout' => 30.0, // in second
    ],
];
