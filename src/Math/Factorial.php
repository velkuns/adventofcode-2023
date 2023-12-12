<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Math;

class Factorial
{
    /**
     * @param int $n
     * @return int
     */
    public static function get(int $n): int
    {
        if ($n <= 0) {
            return 0;
        }

        $value = 1;
        for ($i = 2; $i <= $n; $i++) {
            $value *= $i;
        }

        return $value;
    }

    /**
     * @param int $n
     * @param array $count
     * @return int
     */
    public static function getMultiple(int $n, array $count): int
    {
        $factorial = self::get($n);

        $divider = 1;
        foreach ($count as $value) {
            $divider *= $value > 2 ? self::get($value) : $value;
        }

        return (int) ($factorial / $divider);
    }
}
