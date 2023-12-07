<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Day;

use Application\Enum\Card;
use Application\Enum\HandType;
use Application\Puzzle;

class PuzzleDay7 extends Puzzle
{
    /**
     * @param list<string> $inputs
     */
    protected function partOne(array $inputs): int
    {
        $inputs = array_map(fn(string $string) => explode(' ', $string), $inputs);
        $hands  = array_column($inputs, 0);
        $bids   = array_column($inputs, 1);

        //~ Sort hands
        uasort($hands, $this->sortHand(...));

        $value = 0;
        $rank  = 1;

        //~ Sum rank of each hand with associated bid
        foreach ($hands as $index => $hand) {
            $value += $rank * (int) $bids[$index];
            $rank++;
        }

        return $value;
    }

    /**
     * @param string $handA
     * @param string $handB
     * @return int
     */
    private function sortHand(string $handA, string $handB): int
    {
        /** @var array<int, int> $handAsCharA */
        $handAsCharA = count_chars($handA, 1); // Convert hand to list of char with number of it
        /** @var array<int, int> $handAsCharB */
        $handAsCharB = count_chars($handB, 1); // Convert hand to list of char with number of it

        //~ Compare hand and get -1 (A < B), 1 (A > B) or 0 (A = B)
        $best = HandType::best($handAsCharA, $handAsCharB);

        //~ If A < B or A > B, return sort comparison value
        if ($best !== 0) {
            return $best;
        }

        //~ Otherwise, compare each card at same position between hand
        for ($i = 0; $i < 5; $i++) {
            //~ Compare card and get -1 (A < B), 1 (A > B) or 0 (A = B)
            $best = Card::best($handA[$i], $handB[$i]);

            //~ If A < B or A > B, return sort comparison value, otherwise, continue
            if ($best !== 0) {
                return $best;
            }
        }

        //~ Same hand, should not happen
        return 0;
    }

    /**
     * @param list<string> $inputs
     */
    protected function partTwo(array $inputs): int
    {
        $inputs = array_map(fn(string $string) => explode(' ', $string), $inputs);
        $handsJ = array_column($inputs, 0);
        $bids   = array_column($inputs, 1);

        //~ Build list of hand with original card + best hand with replaced Jokers
        $hands = [];
        foreach ($handsJ as $index => $hand) {
            $hands[$index] = [
                'original' => $hand,
                'best'     => $this->bestHandWithReplacedJokers($hand),
            ];
        }

        //~ Sort hands
        uasort($hands, $this->sortHandWithJokers(...));

        $value = 0;
        $rank  = 1;

        //~ Sum rank of each hand with associated bid
        foreach ($hands as $index => $hand) {
            $value += $rank * (int) $bids[$index];
            $rank++;
        }

        return $value;
    }

    private function bestHandWithReplacedJokers(string $hand): string
    {
        /** @var array<int, int> $chars */
        $chars = count_chars($hand, 1);

        //~ No Joker, return hand has his
        if (!isset($chars[ord(Card::Jack->value)])) {
            return $hand;
        }

        $bestReplacementCards = array_map(fn(int $charValue): Card => Card::from(chr($charValue)), array_keys($chars));

        /** @var non-empty-list<array{original: string, best: string}> $possiblesHands */
        $possiblesHands = [];
        foreach ($bestReplacementCards as $card) {
            $possiblesHands[] = [
                'original' => $hand,
                'best'     => strtr($hand, Card::Jack->value, $card->value),
            ];
        }

        //~ Sort possible hands
        uasort($possiblesHands, $this->sortHandWithJokers(...));

        //~ Get the last one (the best)
        $bestHand = array_pop($possiblesHands);

        return $bestHand['best'];
    }

    /**
     * @param array{original: string, best: string} $handA
     * @param array{original: string, best: string} $handB
     * @return int
     */
    private function sortHandWithJokers(array $handA, array $handB): int
    {
        /** @var array<int, int> $handAsCharA */
        $handAsCharA = count_chars($handA['best'], 1); // Convert hand to list of char with number of it
        /** @var array<int, int> $handAsCharB */
        $handAsCharB = count_chars($handB['best'], 1); // Convert hand to list of char with number of it

        //~ Compare hand and get -1 (A < B), 1 (A > B) or 0 (A = B)
        $best = HandType::best($handAsCharA, $handAsCharB);

        //~ If A < B or A > B, return sort comparison value
        if ($best !== 0) {
            return $best;
        }

        //~ Otherwise, compare each card at same position between hand
        for ($i = 0; $i < 5; $i++) {
            //~ Compare card and get -1 (A < B), 1 (A > B) or 0 (A = B)
            $best = Card::best($handA['original'][$i], $handB['original'][$i], true);

            //~ If A < B or A > B, return sort comparison value, otherwise, continue
            if ($best !== 0) {
                return $best;
            }
        }

        //~ Same hand, should not happen
        return 0;
    }
}
