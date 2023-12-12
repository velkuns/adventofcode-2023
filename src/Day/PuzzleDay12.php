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
use Application\Service\Parser;

class PuzzleDay12 extends Puzzle
{
    /**
     * @param list<string> $inputs
     */
    protected function partOne(array $inputs): int
    {
        $inputs = array_map(fn(string $line) => explode(' ', $line), $inputs);
        $inputs = array_map(fn(array $line) => [strtr($line[0], '.#', '01'), Parser::toIntList($line[1], ',')], $inputs);

        $numberOfArrangements = 0;
        foreach ($inputs as [$springs, $contiguousDamagedSprings]) {
            $numberOfArrangements += $this->getNumberOfArrangements($springs, $contiguousDamagedSprings);
        }

        return $numberOfArrangements;
    }

    /**
     * @param list<int> $contiguousDamagedSprings
     */
    private function getNumberOfArrangements(string $springs, array $contiguousDamagedSprings): int
    {
        $numberOfArrangements = 0;
        $max          = pow(2, strlen($springs));
        $fuzzyBitmask = bindec(strtr($springs, '?', '1'));
        $exactBitmask = bindec(strtr($springs, '?', '0'));

        for ($number = 0; $number < $max; $number++) {
            if (($number & $fuzzyBitmask) !== $number || ($number & $exactBitmask) !== $exactBitmask) {
                continue;
            }

            $part = array_map(
                strlen(...),
                explode(' ', (string) preg_replace('`0+`', ' ', trim(decbin($number), '0')))
            );

            if ($part === $contiguousDamagedSprings) {
                $numberOfArrangements++;
            }
        }

        return $numberOfArrangements;
    }

    /**
     * @param list<string> $inputs
     */
    protected function partTwo(array $inputs): int
    {
        return 0;
    }
}
