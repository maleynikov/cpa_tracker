<?php
if ((PHP_MAJOR_VERSION > 5) && extension_loaded('mysqli')) {
    if (!function_exists('split')) {
        function split($pattern , $string, $limit = -1) {
            return mb_split($pattern, $string, $limit);
        }
    }

    if (!function_exists('ereg')) {
        function ereg($pattern, $string, &$regs = null) {
            return mb_ereg($pattern, $string, $regs);
        }
    }
}
