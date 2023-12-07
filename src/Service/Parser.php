<?php

/*
 * Copyright (c) Deezer
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Service;

use function array_map;
use function array_filter;
use function array_values;
use function explode;
use function intval;
use function strlen;
use function substr;

class Parser
{
    /**
     * @return list<string>
     */
    public static function parse(
        string $string,
        string $separator = ' ',
        string $prefix = '',
        string $suffix = '',
    ): array {
        if (!empty($prefix)) {
            $string = substr($string, strlen($prefix));
        }

        if (!empty($suffix)) {
            $string = substr($string, 0, -strlen($suffix));
        }

        if ($separator === '') {
            throw new \UnexpectedValueException('Separator cannot be empty!');
        }

        return array_values(array_filter(explode($separator, $string), fn(string $string) => $string !== ''));
    }

    /**
     * @return list<int>
     */
    public static function toIntList(
        string $string,
        string $separator = ' ',
        string $prefix = '',
        string $suffix = ''
    ): array {

        $list = self::parse($string, $separator, $prefix, $suffix);

        return array_map(intval(...), $list);
    }
}
