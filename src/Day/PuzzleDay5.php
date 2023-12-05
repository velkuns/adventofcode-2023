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
use Application\Service\RangesMap;

use function array_shift;

class PuzzleDay5 extends Puzzle
{
    /**
     * @param list<string> $inputs
     */
    protected function partOne(array $inputs): int
    {
        $seeds = $this->parseSeeds($inputs);
        $maps  = $this->parseMaps($inputs);

        //~ Iterate on each seed and map seed to location
        $seedsToLocation = [];
        foreach ($seeds as $seed) {
            $seedsToLocation[$seed] = $this->seedToLocation($seed, $maps);
        }

        return min($seedsToLocation);
    }

    /**
     * @param list<string> $inputs
     */
    protected function partTwo(array $inputs): int
    {
        $seeds = $this->parseSeeds($inputs);
        $maps  = $this->parseMaps($inputs);

        //~ Iterate on each seed and map seed to location
        $minLocation = PHP_INT_MAX;
        foreach (array_chunk($seeds, 2) as [$min, $length]) {
            for ($seed = $min; $seed < $min + $length; $seed++) {
                $location = $this->seedToLocation($seed, $maps);
                if ($location < $minLocation) {
                    $minLocation = $location;
                }
            }
        }

        return $minLocation;
    }

    /**
     * @param list<string> $inputs
     * @return list<int>
     */
    private function parseSeeds(array &$inputs): array
    {
        $seeds = Parser::toIntList((string) array_shift($inputs), prefix: 'seeds: ');

        array_shift($inputs); // Skip empty line

        return $seeds;
    }

    /**
     * @param list<string> $inputs
     * @return array<string, RangesMap>
     */
    private function parseMaps(array &$inputs): array
    {
        $maps = [];

        //~ Parse
        do {
            //~ Start new block of map
            $mapName = array_shift($inputs);
            $line    = array_shift($inputs);

            $maps[$mapName] = new RangesMap();

            while (!empty($line)) {
                //~ Transform line into list of integer
                [$destination, $source, $length] = Parser::toIntList($line);

                //~ Add map range
                $maps[$mapName]->add($source, $destination, $length);

                $line = array_shift($inputs);
            }
            $maps[$mapName]->sort();
        } while ($line !== null);

        return $maps;
    }

    /**
     * @param int $seed
     * @param array<string, RangesMap> $maps
     * @return int
     */
    private function seedToLocation(int $seed, array $maps): int
    {
        //~ Then for each seed, iterate on each range map to get final value
        $source = $seed;
        foreach ($maps as $rangesMap) {
            $source = $rangesMap->map($source);
        }

        return $source; // last source value is location
    }
}
