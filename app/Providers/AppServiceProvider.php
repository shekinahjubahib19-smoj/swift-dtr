<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

// polyfill for str_starts_with on older PHP versions
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }
}

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Auto-create SQLite database file when using sqlite connection.
        // Useful for ephemeral hosts (Render) where the database file
        // may not be present after deploy.
        try {
            if (Config::get('database.default') === 'sqlite') {
                $db = Config::get('database.connections.sqlite.database');
                if ($db) {
                    // Resolve relative paths against base_path
                    $isAbsolute = str_starts_with($db, '/') || preg_match('/^[A-Z]:\\\\/i', $db);
                    $path = $isAbsolute ? $db : base_path($db);
                    $dir = dirname($path);
                    if (!is_dir($dir)) {
                        @mkdir($dir, 0755, true);
                    }
                    if (!file_exists($path)) {
                        @touch($path);
                    }
                }
            }
        } catch (\Exception $e) {
            // don't break the application if creating file fails
        }
    }
}
