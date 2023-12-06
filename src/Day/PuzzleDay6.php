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

use function array_shift;

class PuzzleDay6 extends Puzzle
{
    /**
     * @param list<string> $inputs
     */
    protected function partOne(array $inputs): int
    {
        $times     = Parser::toIntList((string) array_shift($inputs), prefix: 'Time:');
        $distances = Parser::toIntList((string) array_shift($inputs), prefix: 'Distance:');

        /** @var array<int, int> $races */
        $races = array_combine($times, $distances);

        $numberOfWayToWin = [];
        foreach ($races as $totalTime => $maxDistance) {
            $bestTime = $this->getBestTimeToHoldButtonPressed($totalTime);
            $minTime  = $this->getMinTimeToHoldButtonPressed($totalTime, $maxDistance);

            $numberOfWayToWin[] = ($bestTime - $minTime) * 2 + (($totalTime + 1) % 2); // Add 1 when total time is even
        }

        return (int) array_product($numberOfWayToWin);
    }

    private function getBestTimeToHoldButtonPressed(int $totalTime): int
    {
        return (int) ceil($totalTime / 2);
    }

    private function getMinTimeToHoldButtonPressed(int $totalTime, int $maxDistance): int
    {
        $approximation = (int) ($maxDistance / ($totalTime * 0.75));

        if ($this->isBetterThanMaxDistance($totalTime, $maxDistance, $approximation)) {
            //~ Check if some fewer ms under approximation time is still better
            for ($minTime = $approximation; $minTime > 0; $minTime--) {
                if ($this->isBetterThanMaxDistance($totalTime, $maxDistance, $minTime)) {
                    //~ Still have better max distance, so continue
                    continue;
                }

                return $minTime + 1; // Not better, so return previous min time
            }

            throw new \UnexpectedValueException('Unable to get min time to hold button!');
        }

        for ($minTime = $approximation; $minTime < $totalTime; $minTime++) {
            if ($this->isBetterThanMaxDistance($totalTime, $maxDistance, $minTime)) {
                //~ Find a better max distance, so return it
                return $minTime;
            }

            //~ Not better, so continue until have one
        }

        throw new \UnexpectedValueException('Unable to get min time to hold button!');
    }

    private function isBetterThanMaxDistance(int $totalTime, int $maxDistance, int $timeHoldButton): bool
    {
        return (($totalTime - $timeHoldButton) * $timeHoldButton) > $maxDistance;
    }

    /**
     * @param list<string> $inputs
     */
    protected function partTwo(array $inputs): int
    {
        $times     = Parser::toIntList((string) array_shift($inputs), prefix: 'Time:');
        $distances = Parser::toIntList((string) array_shift($inputs), prefix: 'Distance:');

        $totalTime   = (int) implode('', $times);
        $maxDistance = (int) implode('', $distances);

        $bestTime = $this->getBestTimeToHoldButtonPressed($totalTime);
        $minTime  = $this->getMinTimeToHoldButtonPressed($totalTime, $maxDistance);

        return ($bestTime - $minTime) * 2 + (($totalTime + 1) % 2);
    }
}
