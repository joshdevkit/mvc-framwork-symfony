<?php

return [
    'SESSION_DRIVER' => getenv('SESSION_DRIVER') ?: 'file',
    'SESSION_PATH'   => __DIR__ . '/../storage/framework/sessions',
];
