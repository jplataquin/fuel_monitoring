<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class JpmController extends Controller
{
    /**
     * Serve the JPM application files.
     *
     * @param  string|null  $path
     * @return BinaryFileResponse|Response
     */
    public function serve($path = null)
    {
        // Default to index.html if no path is provided
        $path = $path ?: 'index.html';

        // Check for directory traversal attacks and construct absolute path safely
        $baseDir = base_path('jpm/ui');
        $absolutePath = $baseDir.'/'.$path;

        $realBaseDir = realpath($baseDir);
        $realPath = realpath($absolutePath);

        if ($realBaseDir === false || $realPath === false || ! str_starts_with($realPath, $realBaseDir)) {
            abort(404);
        }

        // Handle index.html specially to inject base URL
        if (basename($realPath) === 'index.html') {
            $content = file_get_contents($realPath);

            // Inject the base tag to make relative URL assets load relative to /jpm8000/
            if (str_contains($content, '<head>')) {
                $content = str_replace('<head>', "<head>\n    <base href=\"/jpm8000/\">", $content);
            }

            return response($content, 200, [
                'Content-Type' => 'text/html',
            ]);
        }

        // Resolve MIME types for other files
        $extension = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'wasm' => 'application/wasm',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'wav' => 'audio/wav',
            'mp3' => 'audio/mpeg',
            'json' => 'application/json',
        ];

        $mimeType = $mimeTypes[$extension] ?? null;

        if (! $mimeType) {
            $mimeType = mime_content_type($realPath) ?: 'application/octet-stream';
        }

        return response()->file($realPath, [
            'Content-Type' => $mimeType,
        ]);
    }
}
