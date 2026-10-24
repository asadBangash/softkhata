<?php

namespace App\Filesystem;

use Illuminate\Filesystem\Filesystem;

/**
 * Shared-hosting safe storage links: uses symlink() when available, never exec().
 * If symlinks are disabled, prepares public/storage so requests fall through to Laravel.
 */
class SafeFilesystem extends Filesystem
{
    public function link($target, $link): bool
    {
        if ($this->isWindows()) {
            if (function_exists('symlink') && @symlink($target, $link)) {
                return true;
            }

            return $this->ensurePublicStorageAccessible($target, $link);
        }

        if (! file_exists($target) && ! $this->makeDirectory($target, 0755, true)) {
            return false;
        }

        if (is_link($link)) {
            return true;
        }

        if (file_exists($link) && ! is_link($link)) {
            return is_dir($link);
        }

        if (function_exists('symlink') && @symlink($target, $link)) {
            return true;
        }

        return $this->ensurePublicStorageAccessible($target, $link);
    }

    public function relativeLink($target, $link): void
    {
        if (function_exists('symlink')) {
            try {
                parent::relativeLink($target, $link);

                return;
            } catch (\Throwable) {
                // Restricted hosts: route-based public storage.
            }
        }

        if (! $this->ensurePublicStorageAccessible($target, $link)) {
            throw new \RuntimeException("Unable to create storage link at [{$link}].");
        }
    }

    protected function ensurePublicStorageAccessible(string $target, string $link): bool
    {
        if (! file_exists($target)) {
            $this->makeDirectory($target, 0755, true);
        }

        if (is_link($link)) {
            return true;
        }

        if (file_exists($link) && ! is_dir($link)) {
            @unlink($link);
        }

        if (! file_exists($link) && ! $this->makeDirectory($link, 0755, true)) {
            return false;
        }

        if (! is_dir($link)) {
            return false;
        }

        $htaccess = $link.DIRECTORY_SEPARATOR.'.htaccess';
        $contents = <<<'HTACCESS'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ /index.php [L]
</IfModule>
HTACCESS;

        if (! is_file($htaccess) || file_get_contents($htaccess) !== $contents) {
            file_put_contents($htaccess, $contents);
        }

        return true;
    }

    protected function isWindows(): bool
    {
        return DIRECTORY_SEPARATOR === '\\';
    }
}
