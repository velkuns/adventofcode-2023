<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Day;

use Application\Matrix\Platform;
use Application\Puzzle;

class PuzzleDay14 extends Puzzle
{
    /**
     * 108813 :)
     * @param list<string> $inputs
     */
    protected function partOne(array $inputs): int
    {
        return (new Platform(array_map(str_split(...), $inputs)))
            ->transpose()
            ->init()
            ->tiltOn(Platform::NORTH)
            ->getTotalLoad()
        ;
    }

    /**
     * 104535 to high :(
     * 104533 ?
     * @param list<string> $inputs
     */
    protected function partTwo(array $inputs): int
    {
        $platform = (new Platform(array_map(str_split(...), $inputs)))
            ->transpose()
            ->init()
        ;

        $directions = [Platform::NORTH, Platform::WEST, Platform::SOUTH, Platform::EAST];
        $maxCycle   = 1_000_000_000;
        $hashes     = [];
        $hash       = '';

        for ($currentCycle = 1; $currentCycle <= $maxCycle; $currentCycle++) {
            //~ iterate over all direction to achieve on tilt cycle
            foreach ($directions as $direction) {
                $platform->tiltOn($direction);
            }

            //~ Get hash of current platform's state
            $hash = $platform->hash();

            //~ If we have already registered this hash, we just achieve a pattern cycle
            if (isset($hashes[$hash])) {
                break;
            }

            $hashes[$hash] = $currentCycle;
        }

        //~ Compute pattern length
        $patternLength  = $currentCycle - $hashes[$hash];

        //~ Compute number of remaining cycle after applying pattern at max from current cycle
        $remainingCycle = ($maxCycle - $currentCycle) % $patternLength;

        //~ Iterate on remaining cycle - 1 (because we already have done 1 move on the next pattern cycle)
        for ($i = 0; $i < $remainingCycle; $i++) {
            foreach ($directions as $direction) {
                $platform->tiltOn($direction);
            }
        }

        return $platform->getTotalLoad();
    }
}
