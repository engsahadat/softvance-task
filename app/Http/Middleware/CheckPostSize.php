<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPostSize
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $maxPostSize = $this->getPostMaxSize();
        
        if ($maxPostSize > 0 && $request->server('CONTENT_LENGTH') > $maxPostSize) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The uploaded data is too large. Maximum allowed size is ' . 
                                 $this->formatBytes($maxPostSize) . '. Please reduce file sizes or upload fewer files.',
                    'max_size' => $maxPostSize,
                    'current_size' => $request->server('CONTENT_LENGTH')
                ], 413);
            }
            
            return back()->with('error', 
                'The uploaded data is too large. Maximum allowed size is ' . 
                $this->formatBytes($maxPostSize) . '. Please reduce file sizes or upload fewer files.'
            );
        }

        return $next($request);
    }

    /**
     * Get the post max size in bytes.
     *
     * @return int
     */
    protected function getPostMaxSize(): int
    {
        $postMaxSize = ini_get('post_max_size');
        
        if ($postMaxSize === '') {
            return 0;
        }

        return $this->convertToBytes($postMaxSize);
    }

    /**
     * Convert PHP size format to bytes.
     *
     * @param string $size
     * @return int
     */
    protected function convertToBytes(string $size): int
    {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $size = (int) $size;

        switch ($last) {
            case 'g':
                $size *= 1024;
                // Fall through
            case 'm':
                $size *= 1024;
                // Fall through
            case 'k':
                $size *= 1024;
        }

        return $size;
    }

    /**
     * Format bytes to human readable format.
     *
     * @param int $bytes
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
