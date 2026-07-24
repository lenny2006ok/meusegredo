<?php
namespace MeuSegredo\Helpers;

class Filter {
    private static $offensiveWords = [
        'palavrao1', 'palavrao2', 'xixi', 'xuxa', 'idiota', 'burro'
    ];

    public static function hasOffensive($text) {
        if (empty($text)) return false;
        $lower = mb_strtolower($text, 'UTF-8');
        foreach (self::$offensiveWords as $word) {
            if (mb_strpos($lower, $word) !== false) {
                return true;
            }
        }
        return false;
    }
}
