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

use function array_reduce, array_intersect, array_map, array_filter, array_sum;
use function count, explode;

class PuzzleDay4 extends Puzzle
{
    /**
     * @param list<string> $inputs
     */
    protected function partOne(array $inputs): int
    {
        //~ Parse card numbers
        $cards = array_map($this->parseCard(...), $inputs);

        //~ Filter out card where I have 0 winning numbers
        $cards = array_map($this->getMineWinningNumbers(...), $cards);

        //~ Filter out card where I have 0 winning numbers
        $cards = array_filter($cards);

        //~ Reduce array to sum of (number of mine winning number - 1) ** 2 (because 2^0 = 1, 2^1 = 2...)
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

        $winning = array_filter(explode(' ', $winning)); // split on space for winning numbers
        $mines   = array_filter(explode(' ', $mines));   // split on space for mine numbers

        return [
            array_map('intval', $winning), // cast all number from string to integer
            array_map('intval', $mines),   // cast all number from string to integer
        ];
    }

    /**
     * @param array{0: list<int>, 1: list<int>} $card (0: winning numbers, 1: my numbers)
     * @return list<int>
     */
    private function getMineWinningNumbers(array $card): array
    {
        return array_intersect($card[0], $card[1]);
    }

    /**
     * @param list<string> $inputs
     */
    protected function partTwo(array $inputs): int
    {
        //~ Parse cards
        $cards = array_map($this->parseCard(...), $inputs);

        //~ List of all scratchcards (original + copies)
        $allCards = [];

        //~ Iterate on each card
        foreach ($cards as $index => $card) {

            //~ Add 1 original card #N to list of all card
            $allCards[$index] = ($allCards[$index] ?? 0) + 1;

            //~ Get my winning number
            $numbers = $this->getMineWinningNumbers($card);

            //~ Number of Card #N (original + copies)
            $numberOfCards = $allCards[$index];

            //~ Define the max index for Card #N where we add copies
            $maxIndex = $index + 1 + count($numbers);

            //~ For each of the next Card to max, we add the amount of card (original + copy)
            for ($nextCardIndex = $index + 1, $max = $maxIndex; $nextCardIndex < $max; $nextCardIndex++) {
                $allCards[$nextCardIndex] = ($allCards[$nextCardIndex] ?? 0) + $numberOfCards;
            }
        }

        //~ Finally return the number of all cards (originals + copies)
        return array_sum($allCards);
    }
}
