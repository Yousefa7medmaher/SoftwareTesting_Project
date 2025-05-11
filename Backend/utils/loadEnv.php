<?php
 
function loadEnv($path)
{
    if (!file_exists($path)) {
        die(".env file not found!");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (substr(trim($line), 0, 1) === '#') {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}
