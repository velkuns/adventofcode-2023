<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Math;

/**
 * Class Permutation
 *
 * @author Romain Cottard
 */
class Permutation
{
    /**
     * Permute an array according to the seed.
     *
     * @param array<mixed> $elements
     * @param int $seed
     * @return array<mixed>
     */
    public static function permute(array $elements, int $seed): array
    {
        $numberOfElement = count($elements);
        $permutations   = [];

        self::permuteTreeLevel($seed, 1, $numberOfElement, $numberOfElement, $elements, $permutations);

        return $permutations;
    }

    /**
     * @param int $seed
     * @param int $level
     * @param int $maxPermutation
     * @param int $numberOfElement
     * @param array<mixed> $elements
     * @param array<mixed> $permutations
     * @return void
     */
    private static function permuteTreeLevel(int $seed, int $level, int $maxPermutation, int $numberOfElement, array &$elements, array &$permutations): void
    {
        if ($numberOfElement === 1) {
            $permutations[] = array_pop($elements);
            return;
        }

        $factorial = Factorial::get($numberOfElement - 1);
        $index     = (int) ($seed / $factorial);

        $permutations[] = $elements[$index];

        //~ Remove element from original indices & reset array indices
        unset($elements[$index]);
        $elements = array_values($elements);
        $numberOfElement--;

        //~ If we are not into max tree level, recurse again.
        self::permuteTreeLevel($seed % $factorial, ($level + 1), $maxPermutation, $numberOfElement, $elements, $permutations);
    }
}
