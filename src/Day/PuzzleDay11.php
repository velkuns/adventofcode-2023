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
use Application\Service\Universe;
use Velkuns\Math\_2D\Point2D;

class PuzzleDay11 extends Puzzle
{
    /**
     * For real expansion: ~180ms
     * For virtual expansion: ~380ms
     *
     * @param list<string> $inputs
     */
    protected function partOne(array $inputs): int
    {
        $useRealExpansion = $this->options()->has('real-expansion'); // Check for CLI options

        //~ Create universe
        $universe = (new Universe(array_map(str_split(...), $inputs)));

        //~ Expand universe
        $universe = $useRealExpansion ? $universe->realExpand() : $universe->expand(2);

        //~ Calculate distance between each pair of galaxies
        $distances = $this->calculateDistanceBetweenGalaxies($universe, $useRealExpansion);

        //~ Sum all distances
        return array_sum($distances);
    }

    /**
     * @param list<string> $inputs
     */
    protected function partTwo(array $inputs): int
    {
        //~ Create universe & expand it
        $universe = (new Universe(array_map(str_split(...), $inputs)))->expand(1_000_000);

        //~ Calculate distance between each pair of galaxies
        $distances = $this->calculateDistanceBetweenGalaxies($universe);

        //~ Sum all distances
        return array_sum($distances);
    }

    /**
     * @return list<int>
     */
    private function calculateDistanceBetweenGalaxies(Universe $universe, bool $useRealExpansion = false): array
    {
        //~ Locate all galaxies
        $galaxies = $universe->locateAll(Universe::GALAXY);

        //~ Take first
        $galaxy    = array_shift($galaxies);
        $distances = [];

        while (!empty($galaxies)) {
            //~ For each other remaining galaxy, calculate distance (manhattan) between current and remaining galaxy
            foreach ($galaxies as $anotherGalaxy) {
                /** @var Point2D $galaxy */
                $distances[] = $universe->calculateDistanceBetweenTwoGalaxies($galaxy, $anotherGalaxy, $useRealExpansion);
            }

            //~ Then shift galaxy from list and continue
            $galaxy = array_shift($galaxies);
        }

        return $distances;
    }
}
