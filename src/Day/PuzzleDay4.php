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

use function array_reduce;
use function array_intersect;
use function array_map;
use function array_filter;
use function array_sum;
use function count;
use function explode;

class PuzzleDay4 extends Puzzle
{
    /**
     * @param list<string> $inputs
     */
    protected function partOne(array $inputs): int
    {
        //~ For each line parse card numbers
        $cards = array_map($this->parseCard(...), $inputs);

        //~ For each card, get list of my winning numbers
        $cards = array_map($this->getMyWinningNumbers(...), $cards);

        //~ Filter out card where I have 0 winning number
        $cards = array_filter($cards);

        //~ Reduce array to sum of 2^(number of my winning number - 1) - because 2^0 = 1, 2^1 = 2, 2^2 = 4, 2^3 = 8...
        return array_reduce(
            $cards,
            fn(int $sum, array $numbers) => pow(2, count($numbers) - 1) + $sum,
            0
        );
    }

    /**
     * @return array{0: list<int>, 1: list<int>}
     */
    private function parseCard(string $line): array
    {
        //~ First, remove card name from string, then split on |
        [$winning, $mines] = explode('|', substr($line, strpos($line, ':') + 2));

        $winning = array_filter(explode(' ', $winning)); // split on space for winning numbers + clean when have double space
        $mines   = array_filter(explode(' ', $mines));   // split on space for mine numbers + clean when have double space

        return [
            array_map('intval', $winning), // cast all number from string to integer
            array_map('intval', $mines),   // cast all number from string to integer
        ];
    }

    /**
     * @param array{0: list<int>, 1: list<int>} $card (0: winning numbers, 1: my numbers)
     * @return list<int>
     */
    private function getMyWinningNumbers(array $card): array
    {
        return array_intersect($card[0], $card[1]); // Get intersection of number between winning numbers & my numbers
    }

    /**
     * @param list<string> $inputs
     */
    protected function partTwo(array $inputs): int
    {
        //~ For each line parse card numbers
        $cards = array_map($this->parseCard(...), $inputs);

        //~ Init list of all scratchcards (original + copies)
        $allCards = [];

        //~ Iterate on each card
        foreach ($cards as $index => $card) {

            //~ Add 1 original card #{$index} to list of all scratchcards (can already contain copies from previous winning cards)
            $allCards[$index] = ($allCards[$index] ?? 0) + 1;

            //~ Get my winning number
            $numbers = $this->getMyWinningNumbers($card);

            //~ Number of Card #{$index} (original + copies from previous Cards processed)
            $numberOfCards = $allCards[$index];

            //~ Define the max index for Card #N where we add copies
            $maxIndex = $index + 1 + count($numbers);

            //~ For each of the next Card to max, we add the amount of card (original + copy)
            for ($nextCardIndex = $index + 1, $max = $maxIndex; $nextCardIndex < $max; $nextCardIndex++) {
                $allCards[$nextCardIndex] = ($allCards[$nextCardIndex] ?? 0) + $numberOfCards;
                //~ Then, the next card #{$nextCardIndex} have new copy(ies) in all scratchcards list
            }
        }

        //~ Finally return the number of all scratchcards (originals + copies)
        return array_sum($allCards);
    }
}
