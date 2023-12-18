<?php

/*
 * Copyright (c) Deezer
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Service;

use Velkuns\Math\Matrix;

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
     * @template T of Matrix
     *
     * @param list<string> $inputs
     * @param class-string<T> $class
     * @return T
     */
    public static function toMatrix(array $inputs, string $class = Matrix::class, bool $forceInt = false)
    {
        $inputs = array_map(str_split(...), $inputs);

        if ($forceInt) {
            foreach ($inputs as $index => $line) {
                $inputs[$index] = array_map(intval(...), $line);
            }
        }

        return (new $class($inputs))->transpose();
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
