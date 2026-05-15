<?php

namespace App\Support;

use Illuminate\Http\Request;

class ResponsiveView
{
    public static function resolve(Request $request, string $base, string $responsiveSuffix = '_mobile'): string
    {
        $forceView = $request->query('view');

        if ($forceView === 'mobile') {
            return $base . $responsiveSuffix;
        }

        if ($forceView === 'desktop') {
            return $base;
        }

        return self::isPhone($request)
            ? $base . $responsiveSuffix
            : $base;
    }

    public static function isPhone(Request $request): bool
    {
        $ua = (string) $request->header('User-Agent', '');

        return (bool) preg_match(
            '/iPhone|iPod|Android.*Mobile|webOS.*Mobile|BlackBerry|IEMobile|Opera Mini/i',
            $ua
        );
    }
}
