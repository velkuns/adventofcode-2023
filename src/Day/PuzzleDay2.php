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

class PuzzleDay2 extends Puzzle
{
    /**
     * @param list<string> $inputs
     */
    protected function partOne(array $inputs): int
    {
        $pattern = '`(?P<r>\d+) red|(?P<g>\d+) green|(?P<b>\d+) blue`';
        $value   = 0;

        foreach ($inputs as $input) {
            $sep      = strpos($input, ':');
            //~ Extract Game ID
            $gameId   = (int) substr($input, 5, $sep - 5);

            //~ Split line in clean subsets
            $subsets = \array_map(\trim(...), \explode(';', \substr($input, $sep + 1)));

            $game = ['r' => 0, 'g' => 0, 'b' => 0];
            //~ For each subset, keep max value for each cube color.
            foreach ($subsets as $subset) {
                $matches = [];
                \preg_match_all($pattern, $subset, $matches);
                $game['r'] = \max($game['r'], \max($matches['r']));
                $game['g'] = \max($game['g'], \max($matches['g']));
                $game['b'] = \max($game['b'], \max($matches['b']));
            }

            //~ Then check if it is possible game or not
            if ($game['r'] <= 12 && $game['g'] <= 13 && $game['b'] <= 14) {
                $value += $gameId;
            }
        }

        return $value;
    }

    /**
     * @param list<string> $inputs
     */
    protected function partTwo(array $inputs): int
    {
        $pattern = '`(?P<r>\d+) red|(?P<g>\d+) green|(?P<b>\d+) blue`';
        $value   = 0;

        foreach ($inputs as $input) {
            $sep = strpos($input, ':');

            //~ Split line in clean subsets
            $subsets = \array_map(\trim(...), \explode(';', \substr($input, $sep + 1)));

            $game = ['r' => 0, 'g' => 0, 'b' => 0];
            //~ For each subset, keep max value for each cube color.
            foreach ($subsets as $subset) {
                $matches = [];
                \preg_match_all($pattern, $subset, $matches);
                $game['r'] = \max($game['r'], \max($matches['r']));
                $game['g'] = \max($game['g'], \max($matches['g']));
                $game['b'] = \max($game['b'], \max($matches['b']));
            }

            //~ Then get power of 3 cubes and add to total value
            $value += ($game['r'] * $game['g'] * $game['b']);
        }

        return $value;
    }
}
