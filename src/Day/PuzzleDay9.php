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

class PuzzleDay9 extends Puzzle
{
    /**
     * @param list<string> $inputs
     */
    protected function partOne(array $inputs): int
    {
        //~ Transform each line into list of int
        $inputs = array_map(Parser::toIntList(...), $inputs);

        //~ For each line, guess next number & reduce all the input to the sum of guessed numbers
        return array_reduce($inputs, fn(int $total, array $list) => $total + $this->guessNextNumber($list), 0);
    }

    /**
     * @param list<int> $list
     * @return int
     */
    private function guessNextNumber(array $list): int
    {
        $newList  = [];
        $last     = (int) end($list);
        $previous = (int) array_shift($list);
        foreach ($list as $number) {
            $newList[] = $number - $previous;
            $previous  = $number;
        }

        //~ List is now reduced at max (all value are 0), so return last number of list
        if (array_sum($newList) === 0) {
            return $last;
        }

        //~ Otherwise, continue to reduce list then return last number from given list
        return $last + $this->guessNextNumber($newList);
    }

    /**
     * @param list<string> $inputs
     */
    protected function partTwo(array $inputs): int
    {
        //~ Transform each line into list of int
        $inputs = array_map(Parser::toIntList(...), $inputs);

        //~ For each line, guess next number & reduce all the input to the sum of guessed numbers
        return array_reduce($inputs, fn(int $total, array $list) => $total + $this->guessPreviousNumber($list), 0);
    }

    /**
     * @param list<int> $list
     * @return int
     */
    private function guessPreviousNumber(array $list): int
    {
        $newList  = [];
        $first    = (int) reset($list);
        $previous = (int) array_shift($list);
        foreach ($list as $number) {
            $newList[] = $number - $previous;
            $previous  = $number;
        }

        //~ List is now reduced at max (all value are 0), so return last number of list
        if (array_sum($newList) === 0) {
            return $first;
        }

        //~ Otherwise, continue to reduce list then return last number from given list
        return $first - $this->guessPreviousNumber($newList);
    }
}
