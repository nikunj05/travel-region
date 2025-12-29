<?php

namespace App\Helpers;

class TextSanitizer
{
    /**
     * Remove or replace restricted phrases from text (multilingual).
     */
    public static function sanitizeHotelDescription(?string $text): ?string
    {
        if (!$text) {
            return $text;
        }

        $patterns = [
            '/[\s\.\-–—]*\bLGTBIQ\s*friendly\b[\s\.\-–—]*/i',
            '/[\s\.\-–—]*\bLGBTQ\s*friendly\b[\s\.\-–—]*/i',
            '/[\s\.\-–—]*\bLGBT\s*friendly\b[\s\.\-–—]*/i',
            '/[\s\.\-–—]*\bGay\s*friendly\b[\s\.\-–—]*/i',
            '/[\s\.\-–—]*\bLGBTQ\s*amigable\b[\s\.\-–—]*/i',
            '/[\s\.\-–—]*\bLGBTQ\s*freundlich\b[\s\.\-–—]*/i',
            '/[\s\.\-–—]*\bLGBTQ\s*amical\b[\s\.\-–—]*/i',
            '/[\s\.\-–—]*\bLGBTQ\s*友好\b[\s\.\-–—]*/u',
            '/[\s\.\-–—]*\bLGTBIQ\b[\s\.\-–—]*/i',
        ];

        $text = preg_replace($patterns, ' ', $text);

        // Normalize spacing and punctuation
        $text = preg_replace('/\s{2,}/', ' ', $text);
        $text = preg_replace('/\.\s*\./', '. ', $text);

        return trim($text);
    }
}
