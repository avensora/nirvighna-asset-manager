<?php

if (! function_exists('format_inr')) {
    function format_inr(float|int $amount): string
    {
        $formatted = number_format((float) $amount, 2, '.', '');
        [$integer, $decimal] = explode('.', $formatted);

        if (strlen($integer) <= 3) {
            return '₹' . $integer . '.' . $decimal;
        }

        $result    = substr($integer, -3);
        $remaining = substr($integer, 0, -3);

        while ($remaining !== '') {
            $chunk     = substr($remaining, -2);
            $result    = $chunk . ',' . $result;
            $remaining = (string) substr($remaining, 0, -2);
        }

        return '₹' . $result . '.' . $decimal;
    }
}
