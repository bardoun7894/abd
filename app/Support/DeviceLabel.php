<?php

namespace App\Support;

/**
 * Spec 024 F2 follow-up — turns a raw User-Agent into a short Arabic label for
 * the audit screens ("الجهاز" column). The full string stays in the row's title
 * attribute; this is display sugar only, never a security decision.
 *
 * Deliberately dependency-free string matching rather than the Jenssegers Agent
 * parser: the audit screen renders 50 rows a page and a UA parse per row is not
 * worth it for a label, and this must never throw inside a Blade loop.
 */
class DeviceLabel
{
    /** Order matters — the first match wins (iOS before Safari, Edge before Chrome). */
    private const PLATFORMS = [
        'Windows' => 'ويندوز',
        'iPhone' => 'آيفون',
        'iPad' => 'آيباد',
        'Android' => 'أندرويد',
        'Macintosh' => 'ماك',
        'Mac OS X' => 'ماك',
        'Linux' => 'لينكس',
    ];

    private const BROWSERS = [
        'Edg' => 'Edge',
        'OPR' => 'Opera',
        'Firefox' => 'Firefox',
        'Chrome' => 'Chrome',
        'Safari' => 'Safari',
    ];

    public static function short(?string $userAgent): string
    {
        $userAgent = trim((string) $userAgent);
        if ($userAgent === '') {
            return '—';
        }

        $parts = [];
        foreach (self::PLATFORMS as $needle => $label) {
            if (str_contains($userAgent, $needle)) {
                $parts[] = $label;
                break;
            }
        }
        foreach (self::BROWSERS as $needle => $label) {
            if (str_contains($userAgent, $needle)) {
                $parts[] = $label;
                break;
            }
        }

        // Unrecognised agent (curl, a bot, an internal client): show a trimmed
        // raw value rather than a misleading "unknown".
        return $parts ? implode(' · ', $parts) : mb_substr($userAgent, 0, 40);
    }
}
