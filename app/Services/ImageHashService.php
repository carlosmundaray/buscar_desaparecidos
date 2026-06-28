<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageHashService
{
    /**
     * Generates a 64-bit difference hash (dHash) for an image.
     * Returns a 64-character binary string (e.g. "101100...") or null on failure.
     *
     * @param string $pathOrUrl
     * @return string|null
     */
    public static function hash(string $pathOrUrl): ?string
    {
        if (empty($pathOrUrl)) {
            return null;
        }

        $tempFile = null;
        $isUrl = str_starts_with($pathOrUrl, 'http://') || str_starts_with($pathOrUrl, 'https://');

        try {
            // If it's a URL, download it to a temp file
            if ($isUrl) {
                $response = Http::timeout(10)->get($pathOrUrl);
                if (!$response->successful()) {
                    return null;
                }
                
                $tempFile = tempnam(sys_get_temp_dir(), 'imghash_');
                file_put_contents($tempFile, $response->body());
                $path = $tempFile;
            } else {
                $path = $pathOrUrl;
            }

            if (!file_exists($path) || !is_readable($path)) {
                if ($tempFile && file_exists($tempFile)) {
                    @unlink($tempFile);
                }
                return null;
            }

            // Get image info
            $info = @getimagesize($path);
            if (!$info) {
                if ($tempFile && file_exists($tempFile)) {
                    @unlink($tempFile);
                }
                return null;
            }

            $mime = $info['mime'] ?? '';
            $img = null;

            // Load image using appropriate GD function
            if (str_contains($mime, 'jpeg') || str_contains($mime, 'jpg')) {
                if (function_exists('imagecreatefromjpeg')) {
                    $img = @imagecreatefromjpeg($path);
                }
            } elseif (str_contains($mime, 'png')) {
                if (function_exists('imagecreatefrompng')) {
                    $img = @imagecreatefrompng($path);
                }
            } elseif (str_contains($mime, 'gif')) {
                if (function_exists('imagecreatefromgif')) {
                    $img = @imagecreatefromgif($path);
                }
            } elseif (str_contains($mime, 'webp')) {
                if (function_exists('imagecreatefromwebp')) {
                    $img = @imagecreatefromwebp($path);
                }
            }

            // Cleanup temp file immediately after loading the resource
            if ($tempFile && file_exists($tempFile)) {
                @unlink($tempFile);
            }

            if (!$img) {
                return null;
            }

            // Step 1: Resize to 9x8 pixels (9 wide, 8 high) for difference hashing
            $width = 9;
            $height = 8;
            $resized = imagecreatetruecolor($width, $height);
            
            // Turn off alpha blending and preserve transparency
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            
            imagecopyresampled(
                $resized, $img, 
                0, 0, 0, 0, 
                $width, $height, 
                imagesx($img), imagesy($img)
            );

            // Step 2: Convert to grayscale and get luminosity values
            $pixels = [];
            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    $rgb = imagecolorat($resized, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;
                    
                    // Standard relative luminance formula
                    $pixels[$y][$x] = ($r * 0.299) + ($g * 0.587) + ($b * 0.114);
                }
            }

            // Free GD resources
            imagedestroy($img);
            imagedestroy($resized);

            // Step 3: Compare adjacent pixels to produce 64 bits
            $hashString = '';
            for ($y = 0; $y < 8; $y++) {
                for ($x = 0; $x < 8; $x++) {
                    // Compare pixel (x) and pixel (x+1) in the same row
                    $hashString .= ($pixels[$y][$x] < $pixels[$y][$x + 1]) ? '1' : '0';
                }
            }

            return $hashString;

        } catch (\Exception $e) {
            Log::warning("Error generating image hash: " . $e->getMessage());
            if ($tempFile && file_exists($tempFile)) {
                @unlink($tempFile);
            }
            return null;
        }
    }

    /**
     * Calculates the Hamming distance between two 64-bit difference hashes.
     * Returns an integer between 0 (identical) and 64 (totally different).
     *
     * @param string|null $hash1
     * @param string|null $hash2
     * @return int
     */
    public static function distance(?string $hash1, ?string $hash2): int
    {
        if (!$hash1 || !$hash2 || strlen($hash1) !== 64 || strlen($hash2) !== 64) {
            return 64;
        }

        $distance = 0;
        for ($i = 0; $i < 64; $i++) {
            if ($hash1[$i] !== $hash2[$i]) {
                $distance++;
            }
        }

        return $distance;
    }

    /**
     * Calculates the similarity percentage between two 64-bit difference hashes.
     * Returns a float value between 0.0 and 100.0.
     *
     * @param string|null $hash1
     * @param string|null $hash2
     * @return float
     */
    public static function similarity(?string $hash1, ?string $hash2): float
    {
        $dist = self::distance($hash1, $hash2);
        return round(100.0 - ($dist / 64.0 * 100.0), 2);
    }
}
