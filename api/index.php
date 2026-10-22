<?php

if (($_SERVER['SCRIPT_NAME'] ?? null) === '/api/index.php') {
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['PHP_SELF'] = '/index.php';
}

foreach (['/tmp/views', '/tmp/sessions', '/tmp/cache'] as $directory) {
    if (! is_dir($directory)) {
        @mkdir($directory, 0777, true);
    }
}

require __DIR__ . '/../public/index.php';
