<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Day;

use Application\Puzzle;

class PuzzleDay15 extends Puzzle
{
    /**
     * @param list<string> $inputs
     */
    protected function partOne(array $inputs): int
    {
        //~ Transform line into list of sequences, then for each sequence, split into char
        $sequences = array_map(str_split(...), explode(',', $inputs[0]));

        //~ Then reduce each sequence to a hash value, then reduce the whole sequences into a sum of hash values
        return array_reduce($sequences, $this->hash(...), 0);
    }

    /**
     * Reduce list of hash char values
     *
     * @param list<string> $chars
     */
    private function hash(int $value, array $chars): int
    {
        return $value + array_reduce($chars, fn(int $value, string $char) => (($value + ord($char)) * 17) % 256, 0);
    }

    /**
     * @param list<string> $inputs
     */
    protected function partTwo(array $inputs): int
    {
        //~ Transform line into list of sequences, then for each sequence, split into label => focal (if empty = remove)
        $sequences = array_map(fn($string) => preg_split('/[-=]/', $string), explode(',', $inputs[0]));

        //~ Apply HASHMAP on each sequence and get non-empty boxes as result
        $boxes = $this->hashmap($sequences);

        //~ Compute focus power of each box
        $value = 0;
        foreach ($boxes as $boxIndex => $box) {
            $value += $this->focusPower($box, $boxIndex);
        }

        return $value;
    }

    /**
     * @param list<array<int, string>> $sequences
     * @return array<int, array<string, int>>
     */
    private function hashmap(array $sequences): array
    {
        $boxes = [];
        foreach ($sequences as [$label, $focal]) {
            $box = $this->hash(0, str_split((string) $label));
            if (empty($focal) && isset($boxes[$box][$label])) {
                unset($boxes[$box][$label]);
            } elseif (!empty($focal)) {
                $boxes[$box][$label] = (int) $focal;
            }
        }

        //~ Return non empty boxes
        return array_filter($boxes);
    }

    /**
     * @param array<string, int> $box
     */
    private function focusPower(array $box, int $boxIndex): int
    {
        $value     = 0;
        $lensIndex = 1;
        foreach ($box as $lensFocal) {
            $value += ($boxIndex + 1) * $lensIndex++ * $lensFocal;
        }

        return $value;
    }
}
