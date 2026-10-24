<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves files from the public disk when symlinks are unavailable (shared hosting).
 */
class PublicStorageController extends Controller
{
    public function __invoke(string $path): StreamedResponse
    {
        $path = str_replace('\\', '/', $path);
        abort_if(str_contains($path, '..'), 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404);

        return $disk->response($path);
    }
}
