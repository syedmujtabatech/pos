<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;

$viewsDir = resource_path('views');
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));

$invalidCalls = [];

foreach ($files as $file) {
    if ($file->isDir() || !str_ends_with($file->getFilename(), '.blade.php')) {
        continue;
    }

    $content = file_get_contents($file->getPathname());
    // Match route('...', ...) or route("...", ...)
    if (preg_match_all("/route\(\s*['\"]([^'\"]+)['\"]/", $content, $matches, PREG_OFFSET_CAPTURE)) {
        foreach ($matches[1] as $match) {
            $routeName = $match[0];
            if (!Route::has($routeName)) {
                $line = substr_count(substr($content, 0, $match[1]), "\n") + 1;
                $invalidCalls[] = [
                    'file' => str_replace($viewsDir . DIRECTORY_SEPARATOR, '', $file->getPathname()),
                    'line' => $line,
                    'route' => $routeName
                ];
            }
        }
    }
}

if (empty($invalidCalls)) {
    echo "PERFECT: 100% of all route() calls in all Blade views exist and are valid!\n";
} else {
    echo "Found " . count($invalidCalls) . " invalid route calls:\n";
    foreach ($invalidCalls as $item) {
        echo "  - {$item['file']}:{$item['line']} -> route('{$item['route']}')\n";
    }
}
